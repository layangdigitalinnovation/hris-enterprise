<?php

namespace App\Http\Controllers;

use App\Models\AttendanceDailySummary;
use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\EmployeeCompensation;
use App\Models\OvertimeRate;
use App\Models\OvertimeRequest;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Models\PayrollTemplate;
use App\Models\Payslip;
use App\Models\SalaryComponent;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();
        if (!$currentUser->canManagePayroll()) {
            if ($currentUser->employee) {
                return redirect()->route('payroll.my-slips');
            }
            abort(403, 'Akses ditolak. Pengelolaan payroll hanya dapat diakses oleh Bagian Finance, HR Administrator, atau Super Admin.');
        }

        $periodQuery = PayrollPeriod::withCount('items')
            ->orderBy('start_date', 'desc');

        if ($periodSearch = $request->input('period_search')) {
            $periodQuery->where('name', 'like', "%{$periodSearch}%");
        }

        $periods = $periodQuery->paginate(6, ['*'], 'period_page')
            ->appends(array_merge($request->except('period_page'), ['tab' => 'periods']));

        $salaryComponents = SalaryComponent::orderBy('type')->orderBy('id')->get();
        $allowances = SalaryComponent::where('type', 'earning')->orderBy('id')->get();
        $deductions = SalaryComponent::where('type', 'deduction')->orderBy('id')->get();
        $basicComponent = SalaryComponent::where('code', 'BASIC')->first();

        // Payroll Templates (Dinamis & Reusable)
        $payrollTemplates = PayrollTemplate::with(['branch', 'department', 'items.component'])
            ->withCount('employees')
            ->orderBy('currency', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        $empQuery = Employee::where('status', 'active')
            ->with(['department', 'position', 'branch', 'compensations.component', 'payrollTemplate']);

        if ($empSearch = $request->input('emp_search')) {
            $empQuery->where(function ($q) use ($empSearch) {
                $q->where('first_name', 'like', "%{$empSearch}%")
                  ->orWhere('last_name', 'like', "%{$empSearch}%")
                  ->orWhere('employee_code', 'like', "%{$empSearch}%")
                  ->orWhereHas('department', function ($dq) use ($empSearch) {
                      $dq->where('name', 'like', "%{$empSearch}%");
                  })
                  ->orWhereHas('position', function ($pq) use ($empSearch) {
                      $pq->where('title', 'like', "%{$empSearch}%");
                  });
            });
        }

        $employees = $empQuery->orderBy('first_name')
            ->paginate(10, ['*'], 'emp_page')
            ->appends(array_merge($request->except('emp_page'), ['tab' => 'salaries']));

        return view('payroll.index', compact('periods', 'salaryComponents', 'allowances', 'deductions', 'basicComponent', 'employees', 'payrollTemplates'));
    }

    public function createPeriod()
    {
        if (!auth()->user()->canManagePayroll()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas membuat periode payroll.');
        }

        return view('payroll.periods.create');
    }

    public function storePeriod(Request $request)
    {
        if (!auth()->user()->canManagePayroll()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas membuat periode payroll.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'cut_off_date' => 'required|date',
            'payment_date' => 'required|date',
        ]);

        $validated['status'] = 'draft';
        $period = PayrollPeriod::create($validated);

        return redirect()->route('payroll.show', $period)->with('success', "Periode payroll '{$period->name}' berhasil dibuat! Silakan lakukan kalkulasi gaji.");
    }

    public function show(Request $request, PayrollPeriod $payrollPeriod)
    {
        if (!auth()->user()->canManagePayroll()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas melihat rincian kalkulasi payroll karyawan.');
        }

        $payrollPeriod->loadCount('items');

        // Multi-Currency Grand Totals by Currency (IDR & SAR)
        $allItems = $payrollPeriod->items()->with(['employee.branch'])->get();

        $totalsByCurrency = [
            'IDR' => [
                'count' => 0,
                'basic' => 0.0,
                'allowances' => 0.0,
                'overtime' => 0.0,
                'deductions' => 0.0,
                'net' => 0.0,
            ],
            'SAR' => [
                'count' => 0,
                'basic' => 0.0,
                'allowances' => 0.0,
                'overtime' => 0.0,
                'deductions' => 0.0,
                'net' => 0.0,
            ],
        ];

        foreach ($allItems as $it) {
            $curr = $it->currency ?: 'IDR';
            if (!isset($totalsByCurrency[$curr])) {
                $totalsByCurrency[$curr] = [
                    'count' => 0,
                    'basic' => 0.0,
                    'allowances' => 0.0,
                    'overtime' => 0.0,
                    'deductions' => 0.0,
                    'net' => 0.0,
                ];
            }
            $totalsByCurrency[$curr]['count']++;
            $totalsByCurrency[$curr]['basic'] += (float) $it->basic_salary;
            $totalsByCurrency[$curr]['allowances'] += (float) $it->total_allowances;
            $totalsByCurrency[$curr]['overtime'] += (float) $it->total_overtime;
            $totalsByCurrency[$curr]['deductions'] += (float) $it->total_deductions;
            $totalsByCurrency[$curr]['net'] += (float) $it->net_salary;
        }

        $exchangeRateSarToIdr = \App\Helpers\CurrencyHelper::getSarToIdrRate();

        // Paginated items for the table
        $itemsQuery = $payrollPeriod->items()->with(['employee.department', 'employee.position', 'employee.branch', 'payslip']);

        if ($search = $request->input('search')) {
            $itemsQuery->whereHas('employee', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%")
                  ->orWhereHas('department', function ($dq) use ($search) {
                      $dq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $items = $itemsQuery->paginate(10)->withQueryString();

        $totalBasic = $totalsByCurrency['IDR']['basic'];
        $totalAllowances = $totalsByCurrency['IDR']['allowances'];
        $totalOvertime = $totalsByCurrency['IDR']['overtime'];
        $totalDeductions = $totalsByCurrency['IDR']['deductions'];
        $totalNet = $totalsByCurrency['IDR']['net'];

        return view('payroll.show', compact(
            'payrollPeriod',
            'items',
            'totalsByCurrency',
            'exchangeRateSarToIdr',
            'totalBasic',
            'totalAllowances',
            'totalOvertime',
            'totalDeductions',
            'totalNet'
        ));
    }

    public function calculate(PayrollPeriod $payrollPeriod)
    {
        if (!auth()->user()->canManagePayroll()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas menjalankan kalkulasi payroll.');
        }

        if ($payrollPeriod->status === 'locked') {
            return back()->with('error', 'Periode payroll ini telah dikunci dan tidak dapat dihitung ulang.');
        }

        $employees = Employee::where('status', 'active')
            ->with(['branch', 'payrollTemplate.items.component'])
            ->get();
        $processedCount = 0;

        $basicComponent = SalaryComponent::where('code', 'BASIC')->first();
        $earningComponents = SalaryComponent::where('type', 'earning')
            ->where('code', '!=', 'BASIC')
            ->where('is_active', true)
            ->get();
        $deductionComponents = SalaryComponent::where('type', 'deduction')
            ->where('is_active', true)
            ->get();

        DB::beginTransaction();
        try {
            foreach ($employees as $employee) {
                $empCurrency = $employee->effective_currency;
                $isSaudiBranch = ($empCurrency === 'SAR' || $employee->branch?->country_code === 'SA' || $employee->branch?->timezone === 'Asia/Riyadh');

                // 1. Gaji Pokok dari Kompensasi Karyawan
                $comp = EmployeeCompensation::where('employee_id', $employee->id)
                    ->where('salary_component_id', $basicComponent?->id)
                    ->first();
                if (!$comp) {
                    $comp = EmployeeCompensation::where('employee_id', $employee->id)->first();
                }
                $defaultBasic = ($empCurrency === 'SAR') ? 6000.00 : 5000000.00;
                $basicSalary = $comp ? (float) $comp->amount : $defaultBasic;

                // Hari hadir dalam periode cut-off
                $presentDays = AttendanceDailySummary::where('employee_id', $employee->id)
                    ->whereBetween('date', [$payrollPeriod->start_date, $payrollPeriod->cut_off_date])
                    ->whereIn('status', ['present', 'early_leave'])
                    ->count();

                // Menit keterlambatan
                $totalLateMins = AttendanceDailySummary::where('employee_id', $employee->id)
                    ->whereBetween('date', [$payrollPeriod->start_date, $payrollPeriod->cut_off_date])
                    ->sum('late_minutes');

                // 2. Perhitungan Tunjangan Perusahaan (Fleksibel Berdasarkan Kebijakan & Kompensasi)
                $totalAllowances = 0.00;
                $allowanceBreakdown = [];
                $posAllowance = 0.00;
                $mealAllowance = 0.00;

                foreach ($earningComponents as $ec) {
                    $customComp = EmployeeCompensation::where('employee_id', $employee->id)
                        ->where('salary_component_id', $ec->id)
                        ->first();

                    if ($customComp && (float)$customComp->amount > 0) {
                        $amt = (float)$customComp->amount;
                    } else {
                        // Check if employee's template specifies a policy for this component
                        $tplItem = $employee->payrollTemplate?->items->firstWhere('salary_component_id', $ec->id);
                        if ($tplItem) {
                            $mode = $tplItem->calculation_mode;
                            $rate = (float)$tplItem->rate_value;

                            if ($mode === 'percentage') {
                                $amt = round($basicSalary * ($rate / 100), 2);
                            } elseif ($mode === 'per_attendance') {
                                $amt = round($presentDays * $rate, 2);
                            } else {
                                $amt = (float)$tplItem->amount > 0 ? (float)$tplItem->amount : $rate;
                            }
                        } else {
                            $mode = $ec->calculation_mode ?? 'fixed';
                            $rate = (float)$ec->rate_value;

                            if ($mode === 'percentage') {
                                $amt = round($basicSalary * ($rate / 100), 2);
                            } elseif ($mode === 'per_attendance') {
                                $amt = round($presentDays * $rate, 2);
                            } else {
                                $amt = $rate > 0 ? $rate : (float)$ec->default_amount;
                            }
                        }
                    }

                    if ($ec->code === 'ALW_POS') $posAllowance = $amt;
                    if ($ec->code === 'ALW_MEAL') $mealAllowance = $amt;

                    $allowanceBreakdown[$ec->code] = [
                        'name' => $ec->name,
                        'amount' => $amt,
                        'mode' => $ec->calculation_mode,
                        'rate' => $ec->rate_value,
                    ];
                    $totalAllowances += $amt;
                }

                // Fixed Allowances (Tunjangan Tetap) - used for Article 107 Saudi Overtime & Depnaker
                $fixedAllowances = 0.00;
                foreach ($earningComponents as $ec) {
                    if ($ec->is_fixed) {
                        $fixedAllowances += $allowanceBreakdown[$ec->code]['amount'] ?? 0;
                    }
                }

                // 3. Perhitungan Lembur berdasarkan Kebijakan & Rate Lembur Perusahaan
                $otRequests = OvertimeRequest::with('overtimeRate')
                    ->where('employee_id', $employee->id)
                    ->whereBetween('date', [$payrollPeriod->start_date, $payrollPeriod->cut_off_date])
                    ->where('status', 'approved')
                    ->get();

                $otHours = (float) $otRequests->sum('actual_hours');
                $overtimePay = 0.00;

                $calcType = SystemSetting::get('overtime_calculation_type', 'flat');
                $defaultRatePerHour = (float) SystemSetting::get('overtime_rate_per_hour', 0);
                $weekendRatePerHour = (float) SystemSetting::get('overtime_rate_weekend_per_hour', 0);
                $depnakerDivisor = (int) SystemSetting::get('overtime_depnaker_divisor', 173);

                $isSaudiBranch = ($employee->branch?->country_code === 'SA' || $employee->branch?->timezone === 'Asia/Riyadh');
                $isSaudiPolicy = ($calcType === 'saudi_article_107' || ($calcType === 'auto_country' && $isSaudiBranch) || $isSaudiBranch);

                $overtimeItemBreakdown = [];

                foreach ($otRequests as $otReq) {
                    $hours = (float) $otReq->actual_hours;
                    if ($hours <= 0) continue;

                    // Prioritaskan rate yang dipilih langsung pada SPKL jika ada, atau resolve otomatis
                    $matchedRate = ($otReq->overtime_rate_id && $otReq->overtimeRate && $otReq->overtimeRate->is_active)
                        ? $otReq->overtimeRate
                        : OvertimeRate::resolveForDate($otReq->date, $employee);

                    if ($isSaudiPolicy || ($matchedRate && $matchedRate->calculation_type === 'saudi_article_107')) {
                        $payForReq = $matchedRate
                            ? $matchedRate->calculatePay($hours, $basicSalary, $fixedAllowances, $employee)
                            : OvertimeRate::calculateSaudiOvertimePay($hours, $basicSalary, $fixedAllowances);
                        $overtimePay += $payForReq;
                        $overtimeItemBreakdown[] = [
                            'request_number' => $otReq->request_number,
                            'date' => $otReq->date->format('Y-m-d'),
                            'hours' => $hours,
                            'type' => 'saudi_article_107',
                            'rate_name' => __('Pasal 107 UU Arab Saudi'),
                            'amount' => $payForReq,
                        ];
                    } elseif ($matchedRate) {
                        $payForReq = $matchedRate->calculatePay($hours, $basicSalary, $fixedAllowances, $employee);
                        $overtimePay += $payForReq;
                        $overtimeItemBreakdown[] = [
                            'request_number' => $otReq->request_number,
                            'date' => $otReq->date->format('Y-m-d'),
                            'hours' => $hours,
                            'type' => $matchedRate->calculation_type,
                            'rate_name' => $matchedRate->name,
                            'amount' => $payForReq,
                        ];
                    } elseif ($calcType === 'depnaker') {
                        $hourlyRate = $depnakerDivisor > 0 ? ($basicSalary / $depnakerDivisor) : ($basicSalary / 173);
                        $payForReq = round($hours * $hourlyRate * 1.5, 2);
                        $overtimePay += $payForReq;
                        $overtimeItemBreakdown[] = [
                            'request_number' => $otReq->request_number,
                            'date' => $otReq->date->format('Y-m-d'),
                            'hours' => $hours,
                            'type' => 'depnaker',
                            'rate_name' => __('Depnaker RI (PP 35/2021)'),
                            'amount' => $payForReq,
                        ];
                    } else {
                        $d = Carbon::parse($otReq->date);
                        $isWeekend = ($d->dayOfWeek === 0 || $d->dayOfWeek === 6);
                        $rateVal = $isWeekend ? $weekendRatePerHour : $defaultRatePerHour;
                        if ($rateVal <= 0) {
                            $dbRate = OvertimeRate::active()->where('day_type', $isWeekend ? 'weekend' : 'weekday')->first()
                                ?? OvertimeRate::active()->first();
                            $rateVal = $dbRate ? (float) $dbRate->rate_per_hour : 0;
                        }
                        $payForReq = round($hours * $rateVal, 2);
                        $overtimePay += $payForReq;
                        $overtimeItemBreakdown[] = [
                            'request_number' => $otReq->request_number,
                            'date' => $otReq->date->format('Y-m-d'),
                            'hours' => $hours,
                            'type' => 'flat',
                            'rate_name' => __('Tarif Tetap'),
                            'amount' => $payForReq,
                        ];
                    }
                }
                $overtimePay = round($overtimePay, 2);

                // 4. Perhitungan Potongan Perusahaan (BPJS, Telat, Kasbon, Kebijakan Khusus)
                $totalDeductions = 0.00;
                $deductionBreakdown = [];
                $bpjsKes = 0.00;
                $bpjsTk = 0.00;
                $lateDeduction = 0.00;

                foreach ($deductionComponents as $dc) {
                    $customComp = EmployeeCompensation::where('employee_id', $employee->id)
                        ->where('salary_component_id', $dc->id)
                        ->first();

                    // BPJS Kesehatan & Ketenagakerjaan adalah jaminan sosial regulasi Indonesia (tidak berlaku otomatis untuk karyawan Saudi SAR)
                    if ($empCurrency === 'SAR' && in_array($dc->code, ['DED_BPJS_KES', 'DED_BPJS_TK']) && !$customComp) {
                        continue;
                    }

                    if ($customComp && (float)$customComp->amount > 0) {
                        $amt = (float)$customComp->amount;
                    } else {
                        // Check if employee's template specifies a policy for this deduction
                        $tplItem = $employee->payrollTemplate?->items->firstWhere('salary_component_id', $dc->id);
                        if ($tplItem) {
                            $mode = $tplItem->calculation_mode;
                            $rate = (float)$tplItem->rate_value;

                            if ($mode === 'percentage') {
                                $amt = round($basicSalary * ($rate / 100), 2);
                            } elseif ($mode === 'per_late_minute') {
                                $amt = round($totalLateMins * $rate, 2);
                            } else {
                                $amt = (float)$tplItem->amount > 0 ? (float)$tplItem->amount : $rate;
                            }
                        } else {
                            $mode = $dc->calculation_mode ?? 'fixed';
                            $rate = (float)$dc->rate_value;

                            if ($mode === 'percentage') {
                                $amt = round($basicSalary * ($rate / 100), 2);
                            } elseif ($mode === 'per_late_minute') {
                                $amt = round($totalLateMins * $rate, 2);
                            } else {
                                $amt = $rate > 0 ? $rate : (float)$dc->default_amount;
                            }
                        }
                    }

                    if ($dc->code === 'DED_BPJS_KES') $bpjsKes = $amt;
                    if ($dc->code === 'DED_BPJS_TK') $bpjsTk = $amt;
                    if ($dc->code === 'DED_LATE') $lateDeduction = $amt;

                    $deductionBreakdown[$dc->code] = [
                        'name' => $dc->name,
                        'amount' => $amt,
                        'mode' => $dc->calculation_mode,
                        'rate' => $dc->rate_value,
                    ];
                    $totalDeductions += $amt;
                }

                // 4b. Potongan Cicilan Pinjaman Karyawan (Pinjaman / Kasbon Aktif)
                $loanDeduction = 0.00;
                $activeLoanInstallment = \App\Models\EmployeeLoanInstallment::whereHas('loan', function($q) use ($employee) {
                    $q->where('employee_id', $employee->id)->whereIn('status', ['active', 'disbursed']);
                })
                ->where('status', 'unpaid')
                ->whereDate('due_date', '<=', $payrollPeriod->end_date)
                ->orderBy('installment_number', 'asc')
                ->first();

                if ($activeLoanInstallment) {
                    $loanAmt = (float) $activeLoanInstallment->amount;
                    $loanDeduction = $loanAmt;
                    $deductionBreakdown['DED_LOAN'] = [
                        'name' => 'Cicilan Pinjaman (' . $activeLoanInstallment->loan->loan_number . ' Ke-' . $activeLoanInstallment->installment_number . '/' . $activeLoanInstallment->loan->tenor_months . ')',
                        'amount' => $loanAmt,
                        'mode' => 'fixed',
                        'rate' => 0,
                        'installment_id' => $activeLoanInstallment->id,
                        'loan_id' => $activeLoanInstallment->employee_loan_id,
                    ];
                    $totalDeductions += $loanAmt;
                }

                // 5. Gaji Bersih (Net Salary)
                $netSalary = ($basicSalary + $totalAllowances + $overtimePay) - $totalDeductions;

                $details = [
                    'currency' => $empCurrency,
                    'basic_salary' => $basicSalary,
                    'position_allowance' => $posAllowance,
                    'meal_transport_allowance' => $mealAllowance,
                    'allowance_breakdown' => $allowanceBreakdown,
                    'deduction_breakdown' => $deductionBreakdown,
                    'present_days' => $presentDays,
                    'overtime_hours' => $otHours,
                    'overtime_pay' => $overtimePay,
                    'overtime_rate_per_hour' => $defaultRatePerHour,
                    'overtime_calc_type' => $isSaudiPolicy ? 'saudi_article_107' : $calcType,
                    'overtime_fixed_allowances' => $fixedAllowances,
                    'overtime_item_breakdown' => $overtimeItemBreakdown,
                    'overtime_saudi_breakdown' => $isSaudiPolicy && $otHours > 0 ? OvertimeRate::getSaudiCalculationBreakdown($otHours, $basicSalary, $fixedAllowances) : null,
                    'late_minutes' => $totalLateMins,
                    'late_deduction' => $lateDeduction,
                    'loan_deduction' => $loanDeduction,
                    'bpjs_kesehatan' => $bpjsKes,
                    'bpjs_ketenagakerjaan' => $bpjsTk,
                    'total_allowances' => $totalAllowances,
                    'total_deductions' => $totalDeductions,
                    'net_salary' => $netSalary,
                ];

                $payrollItem = PayrollItem::updateOrCreate(
                    [
                        'payroll_period_id' => $payrollPeriod->id,
                        'employee_id' => $employee->id,
                    ],
                    [
                        'currency' => $empCurrency,
                        'basic_salary' => $basicSalary,
                        'total_allowances' => $totalAllowances,
                        'total_overtime' => $overtimePay,
                        'total_deductions' => $totalDeductions,
                        'net_salary' => $netSalary,
                        'details' => $details,
                        'status' => 'draft',
                    ]
                );

                // Generate Payslip record
                $slipNum = 'SLIP-' . $payrollPeriod->id . '-' . str_pad($employee->id, 4, '0', STR_PAD_LEFT);
                Payslip::updateOrCreate(
                    ['payroll_item_id' => $payrollItem->id],
                    [
                        'slip_number' => $slipNum,
                        'generated_at' => now(),
                        'is_published' => true,
                    ]
                );

                $processedCount++;
            }

            $payrollPeriod->update(['status' => 'calculated']);

            AuditLog::create([
                'user_id' => auth()->id(),
                'event' => 'calculate_payroll',
                'description' => "Kalkulasi payroll '{$payrollPeriod->name}' untuk {$processedCount} karyawan aktif berdasarkan kebijakan tunjangan dan potongan.",
            ]);

            DB::commit();

            return back()->with('success', "Kalkulasi payroll berhasil diselesaikan untuk {$processedCount} karyawan aktif!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses kalkulasi payroll: ' . $e->getMessage());
        }
    }

    // ========================================================
    // MANAJEMEN KOMPONEN TUNJANGAN & POTONGAN PERUSAHAAN
    // ========================================================

    public function createComponent(Request $request)
    {
        if (!auth()->user()->canManagePayroll()) {
            abort(403, 'Akses ditolak. Anda tidak berwenang menambah komponen gaji.');
        }

        $defaultType = $request->query('type', 'earning');
        if (!in_array($defaultType, ['earning', 'deduction'])) {
            $defaultType = 'earning';
        }

        return view('payroll.components.create', compact('defaultType'));
    }

    public function storeComponent(Request $request)
    {
        if (!auth()->user()->canManagePayroll()) {
            abort(403, 'Akses ditolak. Anda tidak berwenang menambah komponen gaji.');
        }

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:salary_components,code',
            'name' => 'required|string|max:255',
            'type' => 'required|in:earning,deduction',
            'calculation_mode' => 'required|in:fixed,percentage,per_attendance,per_late_minute',
            'rate_value' => 'required|numeric|min:0',
            'is_taxable' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_taxable'] = $request->has('is_taxable') ? $request->boolean('is_taxable') : ($validated['type'] === 'earning');
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;
        $validated['is_fixed'] = ($validated['calculation_mode'] === 'fixed');
        $validated['default_amount'] = $validated['rate_value'];

        $component = SalaryComponent::create($validated);

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => 'create_salary_component',
            'auditable_type' => SalaryComponent::class,
            'auditable_id' => $component->id,
            'description' => "Menambahkan master komponen " . ($component->type === 'earning' ? 'tunjangan' : 'potongan') . " '{$component->name}' ({$component->code}) dengan nilai tarif {$component->rate_value}.",
        ]);

        return redirect()->route('payroll.index', ['tab' => 'components'])->with('success', "Komponen " . ($component->type === 'earning' ? 'tunjangan' : 'potongan') . " '{$component->name}' berhasil ditambahkan ke kebijakan perusahaan.");
    }

    public function editComponent(SalaryComponent $salaryComponent)
    {
        if (!auth()->user()->canManagePayroll()) {
            abort(403, 'Akses ditolak. Anda tidak berwenang mengedit komponen gaji.');
        }

        return view('payroll.components.edit', compact('salaryComponent'));
    }

    public function updateComponent(Request $request, SalaryComponent $salaryComponent)
    {
        if (!auth()->user()->canManagePayroll()) {
            abort(403, 'Akses ditolak. Anda tidak berwenang mengedit komponen gaji.');
        }

        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:50', Rule::unique('salary_components')->ignore($salaryComponent->id)],
            'name' => 'required|string|max:255',
            'type' => 'required|in:earning,deduction',
            'calculation_mode' => 'required|in:fixed,percentage,per_attendance,per_late_minute',
            'rate_value' => 'required|numeric|min:0',
            'is_taxable' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['code'] = !empty($validated['code']) ? strtoupper(trim($validated['code'])) : $salaryComponent->code;
        $validated['is_taxable'] = $request->has('is_taxable') ? $request->boolean('is_taxable') : $salaryComponent->is_taxable;
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : $salaryComponent->is_active;
        $validated['is_fixed'] = ($validated['calculation_mode'] === 'fixed');
        $validated['default_amount'] = $validated['rate_value'];

        $salaryComponent->update($validated);

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => 'update_salary_component',
            'auditable_type' => SalaryComponent::class,
            'auditable_id' => $salaryComponent->id,
            'description' => "Memperbarui kebijakan komponen '{$salaryComponent->name}' ({$salaryComponent->code}): Nilai {$salaryComponent->rate_value}, Mode {$salaryComponent->calculation_mode}.",
        ]);

        return redirect()->route('payroll.index', ['tab' => 'components'])->with('success', "Kebijakan komponen '{$salaryComponent->name}' berhasil diperbarui.");
    }

    public function destroyComponent(SalaryComponent $salaryComponent)
    {
        if (!auth()->user()->canManagePayroll()) {
            abort(403, 'Akses ditolak. Anda tidak berwenang menghapus komponen gaji.');
        }

        if ($salaryComponent->code === 'BASIC') {
            return back()->with('error', 'Komponen Gaji Pokok (BASIC) adalah komponen utama sistem dan tidak boleh dihapus.');
        }

        if ($salaryComponent->compensations()->count() > 0) {
            return back()->with('error', "Komponen '{$salaryComponent->name}' masih dialokasikan pada data gaji karyawan. Ubah statusnya menjadi Non-Aktif jika tidak ingin digunakan.");
        }

        $name = $salaryComponent->name;
        $salaryComponent->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => 'delete_salary_component',
            'auditable_type' => SalaryComponent::class,
            'auditable_id' => $salaryComponent->id,
            'description' => "Menghapus komponen gaji '{$name}'.",
        ]);

        return back()->with('success', "Komponen '{$name}' berhasil dihapus.");
    }

    // ========================================================
    // INPUT & MANAJEMEN GAJI KARYAWAN (COMPENSATIONS)
    // ========================================================

    public function editEmployeeSalary(Employee $employee)
    {
        if (!auth()->user()->canManagePayroll()) {
            abort(403, 'Akses ditolak. Hanya Bagian Finance atau HR yang berhak mengatur gaji karyawan.');
        }

        $employee->load(['department', 'position', 'branch', 'compensations.component', 'payrollTemplate']);
        $salaryComponents = SalaryComponent::where('is_active', true)->where('code', '!=', 'BASIC')->orderBy('type')->orderBy('id')->get();
        $basicComp = SalaryComponent::where('code', 'BASIC')->first();
        $basicSalary = $employee->compensations->firstWhere('salary_component_id', $basicComp?->id)?->amount ?? 0;
        
        $currentCompensations = $employee->compensations->keyBy('salary_component_id');

        $availableTemplates = PayrollTemplate::with('items.component')
            ->where('is_active', true)
            ->orderBy('currency', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return view('payroll.compensations.edit', compact('employee', 'salaryComponents', 'basicSalary', 'currentCompensations', 'availableTemplates'));
    }

    public function updateEmployeeSalary(Request $request, Employee $employee)
    {
        if (!auth()->user()->canManagePayroll()) {
            abort(403, 'Akses ditolak. Hanya Bagian Finance atau HR yang berhak mengatur gaji karyawan.');
        }

        $validated = $request->validate([
            'payroll_template_id' => 'nullable|exists:payroll_templates,id',
            'currency' => 'nullable|in:IDR,SAR',
            'basic_salary' => 'required|numeric|min:0',
            'components' => 'nullable|array',
            'components.*' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Update assigned payroll template and currency
            $employee->payroll_template_id = $validated['payroll_template_id'] ?? null;
            if (!empty($validated['currency'])) {
                $employee->currency = $validated['currency'];
            } elseif (!empty($validated['payroll_template_id'])) {
                $tpl = PayrollTemplate::find($validated['payroll_template_id']);
                if ($tpl && !empty($tpl->currency)) {
                    $employee->currency = $tpl->currency;
                }
            }
            $employee->save();
            // Update or Create Gaji Pokok (BASIC)
            $basicComp = SalaryComponent::firstOrCreate(
                ['code' => 'BASIC'],
                [
                    'name' => 'Gaji Pokok',
                    'type' => 'earning',
                    'calculation_mode' => 'fixed',
                    'rate_value' => 0.00,
                    'is_taxable' => true,
                    'is_fixed' => true,
                    'is_active' => true,
                ]
            );

            EmployeeCompensation::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'salary_component_id' => $basicComp->id,
                ],
                [
                    'amount' => $validated['basic_salary'],
                ]
            );

            // Update other specific allowances / deductions if provided
            if (!empty($validated['components'])) {
                foreach ($validated['components'] as $compId => $amount) {
                    if ($compId == $basicComp->id) continue;

                    if ($amount !== null && $amount !== '' && (float)$amount > 0) {
                        EmployeeCompensation::updateOrCreate(
                            [
                                'employee_id' => $employee->id,
                                'salary_component_id' => $compId,
                            ],
                            [
                                'amount' => (float)$amount,
                            ]
                        );
                    } else {
                        EmployeeCompensation::where('employee_id', $employee->id)
                            ->where('salary_component_id', $compId)
                            ->delete();
                    }
                }
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'event' => 'update_employee_salary',
                'auditable_type' => Employee::class,
                'auditable_id' => $employee->id,
                'description' => "Mengatur gaji pokok karyawan {$employee->full_name} menjadi " . \App\Helpers\CurrencyHelper::format($validated['basic_salary'], $employee->effective_currency) . " beserta komponen tunjangan/potongan khusus.",
            ]);

            DB::commit();

            if ($request->input('redirect_to') === 'profile') {
                return redirect()->route('employees.show', $employee)->with('success', "Data gaji dan komponen kompensasi untuk {$employee->full_name} berhasil disimpan.");
            }

            return redirect()->route('payroll.index', ['tab' => 'salaries'])->with('success', "Data gaji dan komponen kompensasi untuk {$employee->full_name} berhasil disimpan.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan gaji karyawan: ' . $e->getMessage());
        }
    }

    public function lockPeriod(PayrollPeriod $payrollPeriod)
    {
        if (!auth()->user()->canManagePayroll()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas mengesahkan dan mengunci payroll.');
        }

        $payrollPeriod->update([
            'status' => 'locked',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $payrollPeriod->items()->update(['status' => 'approved']);

        // Auto-mark loan installments as paid
        $periodItems = $payrollPeriod->items()->get();
        foreach ($periodItems as $pItem) {
            $details = $pItem->details;
            if (isset($details['deduction_breakdown']['DED_LOAN']['installment_id'])) {
                $instId = $details['deduction_breakdown']['DED_LOAN']['installment_id'];
                $inst = \App\Models\EmployeeLoanInstallment::find($instId);
                if ($inst && $inst->status !== 'paid') {
                    $inst->markAsPaidByPayroll($payrollPeriod->id, auth()->id());
                }
            }
        }

        return back()->with('success', "Periode payroll {$payrollPeriod->name} telah disahkan dan dikunci!");
    }

    public function payslip(Payslip $payslip)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();

        // If regular employee or manager, STRICTLY only allow viewing their own payslip
        if (!$currentUser->canManagePayroll()) {
            $employeeId = $currentUser->employee?->id;
            if (!$employeeId || $payslip->payrollItem->employee_id !== $employeeId) {
                abort(403, 'Akses ditolak. Anda hanya diizinkan melihat dan mencetak slip gaji milik Anda sendiri.');
            }
        }

        $payslip->load(['payrollItem.period', 'payrollItem.employee.department', 'payrollItem.employee.position', 'payrollItem.employee.branch']);
        return view('payroll.payslip', compact('payslip'));
    }

    public function myPayslips(Request $request)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();
        $employee = $currentUser->employee;

        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'Akun login Anda belum terhubung dengan data master karyawan.');
        }

        $payslips = Payslip::whereHas('payrollItem', function ($q) use ($employee) {
            $q->where('employee_id', $employee->id);
        })->where('is_published', true)
          ->with(['payrollItem.period'])
          ->orderBy('id', 'desc')
          ->paginate(12);

        return view('payroll.my_slips', compact('payslips', 'employee'));
    }

    public function exportExcel(PayrollPeriod $payrollPeriod)
    {
        if (!auth()->user()->canManagePayroll()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas mengekspor laporan penggajian.');
        }

        $payrollPeriod->load(['items.employee.department', 'items.employee.position', 'items.employee.branch']);

        $companyName = SystemSetting::get('company_name', 'PT Solusi Teknologi Nusantara');
        $periodLabel = $payrollPeriod->name . " (" . $payrollPeriod->start_date->format('d/m/Y') . " - " . $payrollPeriod->end_date->format('d/m/Y') . ")";
        $branch = 'Semua Cabang';
        $dept = 'Semua Departemen';
        $printedAt = Carbon::now()->translatedFormat('d F Y H:i');

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $employee = $user?->employee;
        $makerName = $employee ? $employee->full_name : ($user->name ?? 'Administrator');
        $makerTitle = $employee?->position?->title 
            ?? ($user?->role === 'super_admin' ? 'Super Administrator' : ($user?->role === 'hr_admin' ? 'HR Administrator' : 'Finance & Payroll Officer'));

        $customApproverName = SystemSetting::get('report_approver_name');
        $customApproverTitle = SystemSetting::get('report_approver_title');

        if ($customApproverName && $customApproverTitle) {
            $approverName = $customApproverName;
            $approverTitle = $customApproverTitle;
        } else {
            $hrManager = Employee::whereHas('position', function ($q) {
                $q->where('title', 'like', '%HR Manager%')
                  ->orWhere('title', 'like', '%Manager HR%')
                  ->orWhere('title', 'like', '%General Manager%');
            })->first();

            $approverName = $hrManager ? $hrManager->full_name : 'Siti Rahmawati';
            $approverTitle = $hrManager?->position?->title ?? 'HR Manager';
        }

        $signers = [
            'maker_name' => $makerName,
            'maker_title' => $makerTitle,
            'approver_name' => $approverName,
            'approver_title' => $approverTitle,
        ];

        $totalBasic = (float)$payrollPeriod->items->sum('basic_salary');
        $totalAllowances = (float)$payrollPeriod->items->sum('total_allowances');
        $totalOvertime = (float)$payrollPeriod->items->sum('total_overtime');
        $totalDeductions = (float)$payrollPeriod->items->sum('total_deductions');
        $totalNet = (float)$payrollPeriod->items->sum('net_salary');

        $cleanPeriodName = str_replace([' ', '/', '\\', ':', '.'], '_', $payrollPeriod->name);
        $filename = "Laporan_Payroll_{$cleanPeriodName}_" . date('Ymd_His') . ".xlsx";

        return \App\Services\Export\ReportXlsxService::downloadPayroll(
            $payrollPeriod->items,
            $companyName,
            $periodLabel,
            $branch,
            $dept,
            $printedAt,
            $signers,
            $totalBasic,
            $totalAllowances,
            $totalOvertime,
            $totalDeductions,
            $totalNet,
            $filename
        );
    }

    public function exportCsv(PayrollPeriod $payrollPeriod)
    {
        return $this->exportExcel($payrollPeriod);
    }

    /**
     * Show form for editing exchange rates.
     */
    public function editExchangeRate(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        if (!$user || (!$user->canManagePayroll() && !$user->isSuperAdmin())) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas mengubah kurs mata uang.');
        }

        $currentRate = (float) SystemSetting::get('exchange_rate_sar_to_idr', 4250);
        $baseCurrency = SystemSetting::get('base_currency', 'IDR');
        $redirectTo = $request->query('redirect_to', route('payroll.index'));

        return view('payroll.exchange_rate.edit', compact('currentRate', 'baseCurrency', 'redirectTo'));
    }

    /**
     * Update exchange rate dynamically (SAR to IDR and vice versa).
     */
    public function updateExchangeRate(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        if (!$user || (!$user->canManagePayroll() && !$user->isSuperAdmin())) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas mengubah kurs mata uang.');
        }

        $validated = $request->validate([
            'exchange_rate_sar_to_idr' => 'required|numeric|min:1',
            'redirect_to' => 'nullable|string',
        ]);

        $oldRate = (float) SystemSetting::get('exchange_rate_sar_to_idr', 4250);
        $newRate = round((float) $validated['exchange_rate_sar_to_idr'], 2);

        SystemSetting::set('exchange_rate_sar_to_idr', $newRate);

        AuditLog::create([
            'user_id' => $user->id,
            'event' => 'update_exchange_rate',
            'description' => "Memperbarui kurs valuta asing: 1 SAR = Rp " . number_format($newRate, 0, ',', '.') . " (sebelumnya Rp " . number_format($oldRate, 0, ',', '.') . ").",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $msg = __('Kurs valuta asing berhasil diperbarui: 1 SAR = Rp :rate (atau Rp 10.000 ≈ :sar SAR).', [
            'rate' => number_format($newRate, 0, ',', '.'),
            'sar' => number_format(10000 / $newRate, 2, '.', ','),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'rate' => $newRate,
                'message' => $msg,
            ]);
        }

        $redirectUrl = $request->input('redirect_to');
        if (!empty($redirectUrl)) {
            return redirect($redirectUrl)->with('success', $msg);
        }

        return redirect()->route('payroll.index')->with('success', $msg);
    }
}
