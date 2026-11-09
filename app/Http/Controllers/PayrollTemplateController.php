<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\PayrollTemplate;
use App\Models\PayrollTemplateItem;
use App\Models\SalaryComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PayrollTemplateController extends Controller
{
    public function index(?Request $request = null)
    {
        $request = $request ?: request();
        if (!auth()->user()->canManagePayroll()) {
            abort(403, 'Akses ditolak. Pengelolaan template payroll hanya dapat diakses oleh Admin atau Bagian Payroll.');
        }

        $query = PayrollTemplate::with(['branch', 'department', 'items.component'])
            ->withCount('employees');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($currency = $request->input('currency')) {
            if (in_array($currency, ['IDR', 'SAR'])) {
                $query->where('currency', $currency);
            }
        }

        if ($branchId = $request->input('branch_id')) {
            $query->where('branch_id', $branchId);
        }

        $templates = $query->orderBy('currency', 'asc')->orderBy('name', 'asc')->get();

        $stats = [
            'total' => PayrollTemplate::count(),
            'active' => PayrollTemplate::where('is_active', true)->count(),
            'idr' => PayrollTemplate::where('currency', 'IDR')->count(),
            'sar' => PayrollTemplate::where('currency', 'SAR')->count(),
            'assigned_employees' => Employee::whereNotNull('payroll_template_id')->count(),
            'total_employees' => Employee::where('status', 'active')->count(),
        ];

        $branches = Branch::where('is_active', true)->get();

        $payrollTemplates = $templates;
        $totalEmployees = Employee::where('status', 'active')->count();

        return view('payroll.templates.index', compact('templates', 'payrollTemplates', 'stats', 'branches', 'totalEmployees'));
    }

    public function create()
    {
        if (!auth()->user()->canManagePayroll()) {
            abort(403, 'Akses ditolak.');
        }

        $branches = Branch::where('is_active', true)->get();
        $departments = Department::where('is_active', true)->get();
        $salaryComponents = SalaryComponent::where('is_active', true)->where('code', '!=', 'BASIC')->orderBy('type')->orderBy('name')->get();

        return view('payroll.templates.create', compact('branches', 'departments', 'salaryComponents'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->canManagePayroll()) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:50|unique:payroll_templates,code',
            'currency' => 'required|in:IDR,SAR',
            'base_salary' => 'required|numeric|min:0',
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
            'items' => 'nullable|array',
            'items.*.salary_component_id' => 'required|exists:salary_components,id',
            'items.*.calculation_mode' => 'required|in:fixed,percentage,per_attendance,per_late_minute',
            'items.*.rate_value' => 'nullable|numeric|min:0',
            'items.*.amount' => 'nullable|numeric|min:0',
            'items.*.is_fixed' => 'nullable|boolean',
            'items.*.is_taxable' => 'nullable|boolean',
            'items.*.notes' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $template = PayrollTemplate::create([
                'name' => $validated['name'],
                'code' => strtoupper($validated['code']),
                'currency' => $validated['currency'],
                'base_salary' => $validated['base_salary'],
                'branch_id' => $validated['branch_id'] ?? null,
                'department_id' => $validated['department_id'] ?? null,
                'description' => $validated['description'] ?? null,
                'is_active' => $request->boolean('is_active', true),
                'is_default' => $request->boolean('is_default', false),
            ]);

            // Save items
            if (!empty($validated['items'])) {
                $order = 1;
                foreach ($validated['items'] as $itemData) {
                    $comp = SalaryComponent::find($itemData['salary_component_id']);
                    if (!$comp) continue;

                    $rateVal = isset($itemData['rate_value']) ? (float)$itemData['rate_value'] : 0.00;
                    $amountVal = isset($itemData['amount']) && (float)$itemData['amount'] > 0
                        ? (float)$itemData['amount']
                        : ($itemData['calculation_mode'] === 'fixed' ? $rateVal : 0.00);

                    PayrollTemplateItem::create([
                        'payroll_template_id' => $template->id,
                        'salary_component_id' => $comp->id,
                        'type' => $comp->type,
                        'calculation_mode' => $itemData['calculation_mode'],
                        'rate_value' => $rateVal,
                        'amount' => $amountVal,
                        'is_fixed' => !empty($itemData['is_fixed']),
                        'is_taxable' => !empty($itemData['is_taxable']),
                        'order_index' => $order++,
                        'notes' => $itemData['notes'] ?? null,
                    ]);
                }
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'event' => 'create_payroll_template',
                'description' => "Membuat template payroll: {$template->name} ({$template->code})",
                'auditable_type' => PayrollTemplate::class,
                'auditable_id' => $template->id,
                'new_values' => $template->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('payroll.templates.show', $template)
                ->with('success', "Template payroll '{$template->name}' berhasil dibuat dan siap digunakan!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal membuat template payroll: ' . $e->getMessage());
        }
    }

    public function show(PayrollTemplate $payrollTemplate)
    {
        if (!auth()->user()->canManagePayroll()) {
            abort(403, 'Akses ditolak.');
        }

        $payrollTemplate->load([
            'branch',
            'department',
            'items.component',
            'employees.department',
            'employees.position',
            'employees.branch'
        ]);

        // Available employees who can be assigned to this template
        $availableEmployees = Employee::where('status', 'active')
            ->with(['department', 'position', 'branch', 'payrollTemplate'])
            ->orderBy('first_name')
            ->get();

        $departments = Department::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();

        return view('payroll.templates.show', compact('payrollTemplate', 'availableEmployees', 'departments', 'branches'));
    }

    public function edit(PayrollTemplate $payrollTemplate)
    {
        if (!auth()->user()->canManagePayroll()) {
            abort(403, 'Akses ditolak.');
        }

        $payrollTemplate->load(['items.component']);
        $branches = Branch::where('is_active', true)->get();
        $departments = Department::where('is_active', true)->get();
        $salaryComponents = SalaryComponent::where('is_active', true)->where('code', '!=', 'BASIC')->orderBy('type')->orderBy('name')->get();

        return view('payroll.templates.edit', compact('payrollTemplate', 'branches', 'departments', 'salaryComponents'));
    }

    public function update(Request $request, PayrollTemplate $payrollTemplate)
    {
        if (!auth()->user()->canManagePayroll()) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => ['required', 'string', 'max:50', Rule::unique('payroll_templates', 'code')->ignore($payrollTemplate->id)],
            'currency' => 'required|in:IDR,SAR',
            'base_salary' => 'required|numeric|min:0',
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
            'sync_existing_employees' => 'nullable|boolean',
            'items' => 'nullable|array',
            'items.*.salary_component_id' => 'required|exists:salary_components,id',
            'items.*.calculation_mode' => 'required|in:fixed,percentage,per_attendance,per_late_minute',
            'items.*.rate_value' => 'nullable|numeric|min:0',
            'items.*.amount' => 'nullable|numeric|min:0',
            'items.*.is_fixed' => 'nullable|boolean',
            'items.*.is_taxable' => 'nullable|boolean',
            'items.*.notes' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $oldValues = $payrollTemplate->toArray();

            $payrollTemplate->update([
                'name' => $validated['name'],
                'code' => strtoupper($validated['code']),
                'currency' => $validated['currency'],
                'base_salary' => $validated['base_salary'],
                'branch_id' => $validated['branch_id'] ?? null,
                'department_id' => $validated['department_id'] ?? null,
                'description' => $validated['description'] ?? null,
                'is_active' => $request->boolean('is_active', true),
                'is_default' => $request->boolean('is_default', false),
            ]);

            // Sync template items
            $existingItemIds = [];
            $order = 1;

            if (!empty($validated['items'])) {
                foreach ($validated['items'] as $itemData) {
                    $comp = SalaryComponent::find($itemData['salary_component_id']);
                    if (!$comp) continue;

                    $rateVal = isset($itemData['rate_value']) ? (float)$itemData['rate_value'] : 0.00;
                    $amountVal = isset($itemData['amount']) && (float)$itemData['amount'] > 0
                        ? (float)$itemData['amount']
                        : ($itemData['calculation_mode'] === 'fixed' ? $rateVal : 0.00);

                    $item = PayrollTemplateItem::updateOrCreate(
                        [
                            'payroll_template_id' => $payrollTemplate->id,
                            'salary_component_id' => $comp->id,
                        ],
                        [
                            'type' => $comp->type,
                            'calculation_mode' => $itemData['calculation_mode'],
                            'rate_value' => $rateVal,
                            'amount' => $amountVal,
                            'is_fixed' => !empty($itemData['is_fixed']),
                            'is_taxable' => !empty($itemData['is_taxable']),
                            'order_index' => $order++,
                            'notes' => $itemData['notes'] ?? null,
                        ]
                    );

                    $existingItemIds[] = $item->id;
                }
            }

            // Remove items no longer in form
            PayrollTemplateItem::where('payroll_template_id', $payrollTemplate->id)
                ->whereNotIn('id', $existingItemIds)
                ->delete();

            // Optionally sync changes to assigned employees
            if ($request->boolean('sync_existing_employees')) {
                $assignedEmpIds = $payrollTemplate->employees()->pluck('id')->toArray();
                if (!empty($assignedEmpIds)) {
                    $payrollTemplate->applyToEmployees($assignedEmpIds, true);
                }
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'event' => 'update_payroll_template',
                'description' => "Memperbarui template payroll: {$payrollTemplate->name} ({$payrollTemplate->code})",
                'auditable_type' => PayrollTemplate::class,
                'auditable_id' => $payrollTemplate->id,
                'old_values' => $oldValues,
                'new_values' => $payrollTemplate->fresh()->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('payroll.templates.show', $payrollTemplate)
                ->with('success', "Template payroll '{$payrollTemplate->name}' berhasil diperbarui!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui template: ' . $e->getMessage());
        }
    }

    public function destroy(PayrollTemplate $payrollTemplate)
    {
        if (!auth()->user()->canManagePayroll()) {
            abort(403, 'Akses ditolak.');
        }

        DB::beginTransaction();
        try {
            $name = $payrollTemplate->name;

            // Unlink any assigned employees
            Employee::where('payroll_template_id', $payrollTemplate->id)->update([
                'payroll_template_id' => null,
            ]);

            $payrollTemplate->delete();

            AuditLog::create([
                'user_id' => auth()->id(),
                'event' => 'delete_payroll_template',
                'description' => "Menghapus template payroll: {$name}",
                'auditable_type' => PayrollTemplate::class,
                'auditable_id' => $payrollTemplate->id,
                'old_values' => ['name' => $name],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            $redirectTo = request('redirect_to');
            if ($redirectTo) {
                return redirect($redirectTo)->with('success', "Template '{$name}' berhasil dihapus.");
            }

            return redirect()->route('payroll.index', ['tab' => 'templates'])
                ->with('success', "Template '{$name}' berhasil dihapus.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus template: ' . $e->getMessage());
        }
    }

    public function duplicate(PayrollTemplate $payrollTemplate)
    {
        if (!auth()->user()->canManagePayroll()) {
            abort(403, 'Akses ditolak.');
        }

        DB::beginTransaction();
        try {
            $newCode = $payrollTemplate->code . '_COPY_' . rand(10, 99);
            // Ensure unique
            while (PayrollTemplate::where('code', $newCode)->exists()) {
                $newCode = $payrollTemplate->code . '_COPY_' . rand(100, 999);
            }

            $newTemplate = PayrollTemplate::create([
                'name' => $payrollTemplate->name . ' (Salinan)',
                'code' => $newCode,
                'currency' => $payrollTemplate->currency,
                'base_salary' => $payrollTemplate->base_salary,
                'branch_id' => $payrollTemplate->branch_id,
                'department_id' => $payrollTemplate->department_id,
                'description' => "Salinan dari {$payrollTemplate->name}. " . ($payrollTemplate->description ?? ''),
                'is_active' => true,
                'is_default' => false,
            ]);

            foreach ($payrollTemplate->items as $item) {
                PayrollTemplateItem::create([
                    'payroll_template_id' => $newTemplate->id,
                    'salary_component_id' => $item->salary_component_id,
                    'type' => $item->type,
                    'calculation_mode' => $item->calculation_mode,
                    'rate_value' => $item->rate_value,
                    'amount' => $item->amount,
                    'is_fixed' => $item->is_fixed,
                    'is_taxable' => $item->is_taxable,
                    'order_index' => $item->order_index,
                    'notes' => $item->notes,
                ]);
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'event' => 'duplicate_payroll_template',
                'description' => "Menduplikasi template payroll dari '{$payrollTemplate->name}' ke '{$newTemplate->name}'",
                'auditable_type' => PayrollTemplate::class,
                'auditable_id' => $newTemplate->id,
                'new_values' => $newTemplate->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('payroll.templates.edit', $newTemplate)
                ->with('success', "Template berhasil diduplikasi menjadi '{$newTemplate->name}'. Anda dapat langsung menyesuaikannya!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menduplikasi template: ' . $e->getMessage());
        }
    }

    public function apply(Request $request, PayrollTemplate $payrollTemplate)
    {
        if (!auth()->user()->canManagePayroll()) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
            'overwrite_existing' => 'nullable|boolean',
        ]);

        $empIds = $request->input('employee_ids', []);
        $overwrite = $request->boolean('overwrite_existing', true);

        if (empty($empIds)) {
            return back()->with('error', 'Pilih setidaknya satu karyawan untuk menerapkan template.');
        }

        $count = $payrollTemplate->applyToEmployees($empIds, $overwrite);

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => 'apply_payroll_template',
            'description' => "Menerapkan template payroll '{$payrollTemplate->name}' ke {$count} karyawan",
            'auditable_type' => PayrollTemplate::class,
            'auditable_id' => $payrollTemplate->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "Template '{$payrollTemplate->name}' berhasil diterapkan kepada {$count} karyawan!");
    }
}
