<?php

namespace App\Services;

use App\Models\User;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\AttendanceAdjustment;
use App\Models\ReimbursementRequest;
use App\Models\EmployeeLoan;
use App\Models\EmployeeResignation;
use App\Models\EmployeeCareerTransition;
use Illuminate\Support\Carbon;

class ApprovalNotificationService
{
    /**
     * Get pending approval summary and items for the given user.
     */
    public function getSummary(?User $user, int $limit = 20): array
    {
        if (!$user) {
            return [
                'total_count' => 0,
                'counts' => [
                    'leave' => 0,
                    'overtime' => 0,
                    'adjustment' => 0,
                    'reimbursement' => 0,
                    'loan' => 0,
                    'resignation' => 0,
                    'transition' => 0,
                ],
                'items' => [],
            ];
        }

        $items = collect();
        $counts = [
            'leave' => 0,
            'overtime' => 0,
            'adjustment' => 0,
            'reimbursement' => 0,
            'loan' => 0,
            'resignation' => 0,
            'transition' => 0,
        ];

        // 1. Cuti & Izin (Leave Requests)
        if ($user->canApprove()) {
            $leaveQuery = LeaveRequest::with(['employee.department', 'leaveType'])
                ->where('status', 'submitted');

            if ($user->role === 'manager' && $user->employee?->department_id) {
                $leaveQuery->whereHas('employee', function ($q) use ($user) {
                    $q->where('department_id', $user->employee->department_id)
                      ->orWhere('manager_id', $user->employee->id);
                });
            }

            $counts['leave'] = (clone $leaveQuery)->count();
            $leaves = $leaveQuery->latest()->take($limit)->get();

            foreach ($leaves as $lv) {
                $items->push([
                    'id' => 'leave_' . $lv->id,
                    'category' => 'leave',
                    'category_label' => __('Cuti & Izin'),
                    'category_badge' => __('Cuti'),
                    'emoji' => '🏖️',
                    'badge_color' => 'amber',
                    'icon' => 'calendar',
                    'number' => $lv->request_number,
                    'employee_name' => $lv->employee?->full_name ?? '-',
                    'employee_code' => $lv->employee?->employee_code ?? '',
                    'employee_initials' => $lv->employee ? strtoupper(substr($lv->employee->first_name, 0, 1) . substr($lv->employee->last_name ?? '', 0, 1)) : 'EM',
                    'employee_department' => $lv->employee?->department?->name ? __($lv->employee->department->name) : '',
                    'title' => ($lv->employee?->full_name ?? '-') . ' · ' . __($lv->leaveType?->name ?? 'Cuti'),
                    'description' => __('Durasi: :days Hari (:start s/d :end)', [
                        'days' => $lv->total_days,
                        'start' => $lv->start_date ? $lv->start_date->format('d/m/Y') : '-',
                        'end' => $lv->end_date ? $lv->end_date->format('d/m/Y') : '-',
                    ]),
                    'notes' => $lv->reason ? \Illuminate\Support\Str::limit($lv->reason, 70) : null,
                    'created_at' => $lv->created_at,
                    'time_ago' => $lv->created_at ? $lv->created_at->diffForHumans() : '-',
                    'url' => route('leaves.index', ['tab' => 'requests', 'status' => 'submitted', 'search' => $lv->request_number]),
                    'action_label' => __('Tinjau'),
                ]);
            }
        }

        // 2. Lembur / SPKL (Overtime Requests)
        if ($user->canApprove()) {
            $otQuery = OvertimeRequest::with(['employee.department'])
                ->where('status', 'submitted');

            if ($user->role === 'manager' && $user->employee?->department_id) {
                $otQuery->whereHas('employee', function ($q) use ($user) {
                    $q->where('department_id', $user->employee->department_id)
                      ->orWhere('manager_id', $user->employee->id);
                });
            }

            $counts['overtime'] = (clone $otQuery)->count();
            $ots = $otQuery->latest()->take($limit)->get();

            foreach ($ots as $ot) {
                $items->push([
                    'id' => 'overtime_' . $ot->id,
                    'category' => 'overtime',
                    'category_label' => __('Lembur (SPKL)'),
                    'category_badge' => __('Lembur'),
                    'emoji' => '⏱️',
                    'badge_color' => 'blue',
                    'icon' => 'clock',
                    'number' => $ot->request_number,
                    'employee_name' => $ot->employee?->full_name ?? '-',
                    'employee_code' => $ot->employee?->employee_code ?? '',
                    'employee_initials' => $ot->employee ? strtoupper(substr($ot->employee->first_name, 0, 1) . substr($ot->employee->last_name ?? '', 0, 1)) : 'EM',
                    'employee_department' => $ot->employee?->department?->name ? __($ot->employee->department->name) : '',
                    'title' => ($ot->employee?->full_name ?? '-') . ' · ' . __('Pengajuan Lembur'),
                    'description' => __('Estimasi :hours Jam pada :date', [
                        'hours' => $ot->estimated_hours,
                        'date' => $ot->date ? $ot->date->format('d/m/Y') : '-',
                    ]),
                    'notes' => $ot->reason ? \Illuminate\Support\Str::limit($ot->reason, 70) : null,
                    'created_at' => $ot->created_at,
                    'time_ago' => $ot->created_at ? $ot->created_at->diffForHumans() : '-',
                    'url' => route('overtime.index', ['status' => 'submitted', 'search' => $ot->request_number]),
                    'action_label' => __('Tinjau'),
                ]);
            }
        }

        // 3. Koreksi Presensi (Attendance Adjustments)
        if ($user->canApprove()) {
            $adjQuery = AttendanceAdjustment::with(['employee.department'])
                ->where('status', 'pending');

            if ($user->role === 'manager' && $user->employee?->department_id) {
                $adjQuery->whereHas('employee', function ($q) use ($user) {
                    $q->where('department_id', $user->employee->department_id)
                      ->orWhere('manager_id', $user->employee->id);
                });
            }

            $counts['adjustment'] = (clone $adjQuery)->count();
            $adjs = $adjQuery->latest()->take($limit)->get();

            foreach ($adjs as $adj) {
                $items->push([
                    'id' => 'adjustment_' . $adj->id,
                    'category' => 'adjustment',
                    'category_label' => __('Koreksi Presensi'),
                    'category_badge' => __('Koreksi'),
                    'emoji' => '⏰',
                    'badge_color' => 'indigo',
                    'icon' => 'fingerprint',
                    'number' => 'ADJ-' . $adj->id,
                    'employee_name' => $adj->employee?->full_name ?? '-',
                    'employee_code' => $adj->employee?->employee_code ?? '',
                    'employee_initials' => $adj->employee ? strtoupper(substr($adj->employee->first_name, 0, 1) . substr($adj->employee->last_name ?? '', 0, 1)) : 'EM',
                    'employee_department' => $adj->employee?->department?->name ? __($adj->employee->department->name) : '',
                    'title' => ($adj->employee?->full_name ?? '-') . ' · ' . __('Koreksi Jam Kerja'),
                    'description' => __('Tanggal :date (Waktu Permintaan: :time)', [
                        'date' => $adj->date ? Carbon::parse($adj->date)->format('d/m/Y') : '-',
                        'time' => $adj->requested_time ?? '-',
                    ]),
                    'notes' => $adj->reason ? \Illuminate\Support\Str::limit($adj->reason, 70) : null,
                    'created_at' => $adj->created_at,
                    'time_ago' => $adj->created_at ? $adj->created_at->diffForHumans() : '-',
                    'url' => route('attendance.adjustments', ['status' => 'pending']),
                    'action_label' => __('Tinjau'),
                ]);
            }
        }

        // 4. Reimbursement (Klaim Biaya)
        if ($user->canApproveReimbursement() || $user->canPayReimbursement()) {
            $reimQuery = ReimbursementRequest::with(['employee.department', 'category']);

            if ($user->canApproveReimbursement()) {
                $reimQuery->where('status', 'pending');
            } elseif ($user->canPayReimbursement()) {
                $reimQuery->where('status', 'approved')->where('payment_status', 'pending');
            }

            if ($user->role === 'manager' && $user->employee?->department_id) {
                $reimQuery->whereHas('employee', function ($q) use ($user) {
                    $q->where('department_id', $user->employee->department_id)
                      ->orWhere('manager_id', $user->employee->id);
                });
            }

            $counts['reimbursement'] = (clone $reimQuery)->count();
            $reims = $reimQuery->latest()->take($limit)->get();

            foreach ($reims as $rm) {
                $currency = $rm->currency ?? 'IDR';
                $items->push([
                    'id' => 'reimbursement_' . $rm->id,
                    'category' => 'reimbursement',
                    'category_label' => __('Reimbursement'),
                    'category_badge' => __('Klaim'),
                    'emoji' => '🧾',
                    'badge_color' => 'purple',
                    'icon' => 'receipt',
                    'number' => $rm->claim_number,
                    'employee_name' => $rm->employee?->full_name ?? '-',
                    'employee_code' => $rm->employee?->employee_code ?? '',
                    'employee_initials' => $rm->employee ? strtoupper(substr($rm->employee->first_name, 0, 1) . substr($rm->employee->last_name ?? '', 0, 1)) : 'EM',
                    'employee_department' => $rm->employee?->department?->name ? __($rm->employee->department->name) : '',
                    'title' => ($rm->employee?->full_name ?? '-') . ' · ' . ($rm->title ?? __('Klaim Reimbursement')),
                    'description' => format_currency($rm->amount, $currency) . ' (' . ($rm->category?->name ? __($rm->category->name) : __('Umum')) . ')',
                    'notes' => $rm->description ? \Illuminate\Support\Str::limit($rm->description, 70) : null,
                    'created_at' => $rm->created_at,
                    'time_ago' => $rm->created_at ? $rm->created_at->diffForHumans() : '-',
                    'url' => route('reimbursements.show', $rm->id),
                    'action_label' => __('Tinjau'),
                ]);
            }
        }

        // 5. Pinjaman Karyawan (Loans)
        if ($user->canApproveLoan() || $user->canDisburseLoan()) {
            $loanQuery = EmployeeLoan::with(['employee.department', 'employee.branch']);

            if ($user->canApproveLoan()) {
                $loanQuery->where('status', 'submitted');
            } elseif ($user->canDisburseLoan()) {
                $loanQuery->where('status', 'approved');
            }

            if ($user->role === 'manager' && $user->employee?->department_id) {
                $loanQuery->whereHas('employee', function ($q) use ($user) {
                    $q->where('department_id', $user->employee->department_id)
                      ->orWhere('manager_id', $user->employee->id);
                });
            }

            $counts['loan'] = (clone $loanQuery)->count();
            $loans = $loanQuery->latest()->take($limit)->get();

            foreach ($loans as $ln) {
                $currency = $ln->currency ?? 'IDR';
                $items->push([
                    'id' => 'loan_' . $ln->id,
                    'category' => 'loan',
                    'category_label' => __('Pinjaman Karyawan'),
                    'category_badge' => __('Pinjaman'),
                    'emoji' => '💳',
                    'badge_color' => 'emerald',
                    'icon' => 'credit-card',
                    'number' => $ln->loan_number,
                    'employee_name' => $ln->employee?->full_name ?? '-',
                    'employee_code' => $ln->employee?->employee_code ?? '',
                    'employee_initials' => $ln->employee ? strtoupper(substr($ln->employee->first_name, 0, 1) . substr($ln->employee->last_name ?? '', 0, 1)) : 'EM',
                    'employee_department' => $ln->employee?->department?->name ? __($ln->employee->department->name) : '',
                    'title' => ($ln->employee?->full_name ?? '-') . ' · ' . ucfirst($ln->loan_type ?? __('Pinjaman')),
                    'description' => format_currency($ln->amount, $currency) . ' (' . ($ln->installments_count ?? 1) . ' ' . __('Cicilan') . ')',
                    'notes' => $ln->reason ? \Illuminate\Support\Str::limit($ln->reason, 70) : null,
                    'created_at' => $ln->created_at,
                    'time_ago' => $ln->created_at ? $ln->created_at->diffForHumans() : '-',
                    'url' => route('loans.show', $ln->id),
                    'action_label' => __('Tinjau'),
                ]);
            }
        }

        // 6. Resign & Offboarding
        if ($user->canApproveResignation()) {
            $rsgQuery = EmployeeResignation::with(['employee.department'])
                ->where('status', 'pending');

            if ($user->role === 'manager' && $user->employee?->department_id) {
                $rsgQuery->whereHas('employee', function ($q) use ($user) {
                    $q->where('department_id', $user->employee->department_id)
                      ->orWhere('manager_id', $user->employee->id);
                });
            }

            $counts['resignation'] = (clone $rsgQuery)->count();
            $rsgs = $rsgQuery->latest()->take($limit)->get();

            foreach ($rsgs as $rsg) {
                $items->push([
                    'id' => 'resignation_' . $rsg->id,
                    'category' => 'resignation',
                    'category_label' => __('Resign & Offboarding'),
                    'category_badge' => __('Resign'),
                    'emoji' => '🚪',
                    'badge_color' => 'rose',
                    'icon' => 'user-minus',
                    'number' => $rsg->resignation_number,
                    'employee_name' => $rsg->employee?->full_name ?? '-',
                    'employee_code' => $rsg->employee?->employee_code ?? '',
                    'employee_initials' => $rsg->employee ? strtoupper(substr($rsg->employee->first_name, 0, 1) . substr($rsg->employee->last_name ?? '', 0, 1)) : 'EM',
                    'employee_department' => $rsg->employee?->department?->name ? __($rsg->employee->department->name) : '',
                    'title' => ($rsg->employee?->full_name ?? '-') . ' · ' . __('Pengajuan Pengunduran Diri'),
                    'description' => __('Hari Terakhir Diminta: :date', [
                        'date' => $rsg->requested_last_day ? Carbon::parse($rsg->requested_last_day)->format('d/m/Y') : '-',
                    ]),
                    'notes' => $rsg->reason_detail ? \Illuminate\Support\Str::limit($rsg->reason_detail, 70) : ($rsg->reason_type ?? null),
                    'created_at' => $rsg->created_at,
                    'time_ago' => $rsg->created_at ? $rsg->created_at->diffForHumans() : '-',
                    'url' => route('resignations.show', $rsg->id),
                    'action_label' => __('Tinjau'),
                ]);
            }
        }

        // 7. Mutasi & Promosi (Career Transitions)
        if ($user->canApproveCareerTransition()) {
            $trnQuery = EmployeeCareerTransition::with(['employee.department', 'toPosition', 'toDepartment'])
                ->where('status', 'pending');

            if ($user->role === 'manager' && $user->employee?->department_id) {
                $trnQuery->whereHas('employee', function ($q) use ($user) {
                    $q->where('department_id', $user->employee->department_id)
                      ->orWhere('manager_id', $user->employee->id);
                });
            }

            $counts['transition'] = (clone $trnQuery)->count();
            $trns = $trnQuery->latest()->take($limit)->get();

            foreach ($trns as $trn) {
                $items->push([
                    'id' => 'transition_' . $trn->id,
                    'category' => 'transition',
                    'category_label' => __('Mutasi & Promosi'),
                    'category_badge' => match($trn->type) {
                        'promotion' => __('Promosi'),
                        'transfer' => __('Mutasi'),
                        'rotation' => __('Rotasi'),
                        'demotion' => __('Demosi'),
                        default => __('Mutasi'),
                    },
                    'emoji' => match($trn->type) {
                        'promotion' => '📈',
                        'demotion' => '📉',
                        'transfer' => '🏢',
                        'rotation' => '🔄',
                        default => '💼',
                    },
                    'badge_color' => 'teal',
                    'icon' => 'briefcase',
                    'number' => $trn->transition_number,
                    'employee_name' => $trn->employee?->full_name ?? '-',
                    'employee_code' => $trn->employee?->employee_code ?? '',
                    'employee_initials' => $trn->employee ? strtoupper(substr($trn->employee->first_name, 0, 1) . substr($trn->employee->last_name ?? '', 0, 1)) : 'EM',
                    'employee_department' => $trn->employee?->department?->name ? __($trn->employee->department->name) : '',
                    'title' => ($trn->employee?->full_name ?? '-') . ' · ' . $trn->type_label,
                    'description' => __('Jabatan Baru: :pos', [
                        'pos' => $trn->toPosition ? __($trn->toPosition->title) : '-',
                    ]),
                    'notes' => $trn->reason ? \Illuminate\Support\Str::limit($trn->reason, 70) : null,
                    'created_at' => $trn->created_at,
                    'time_ago' => $trn->created_at ? $trn->created_at->diffForHumans() : '-',
                    'url' => route('career-transitions.show', $trn->id),
                    'action_label' => __('Tinjau'),
                ]);
            }
        }

        // 8. If Employee has no approval authority, show their own recent status updates
        if ($user->isEmployee() && $user->employee) {
            $myEmpId = $user->employee->id;

            // Recent Leaves
            $myLeaves = LeaveRequest::with('leaveType')
                ->where('employee_id', $myEmpId)
                ->whereIn('status', ['submitted', 'approved', 'rejected'])
                ->latest()
                ->take(3)
                ->get();

            foreach ($myLeaves as $ml) {
                $statusText = match($ml->status) {
                    'approved' => __('Disetujui'),
                    'rejected' => __('Ditolak'),
                    default => __('Menunggu Review'),
                };
                $items->push([
                    'id' => 'myleave_' . $ml->id,
                    'category' => 'leave',
                    'category_label' => __('Cuti Saya'),
                    'category_badge' => __('Cuti'),
                    'emoji' => '🏖️',
                    'badge_color' => $ml->status === 'approved' ? 'emerald' : ($ml->status === 'rejected' ? 'rose' : 'amber'),
                    'icon' => 'calendar',
                    'number' => $ml->request_number,
                    'employee_name' => $user->name,
                    'employee_code' => $user->employee->employee_code,
                    'employee_initials' => strtoupper(substr($user->employee->first_name, 0, 1) . substr($user->employee->last_name ?? '', 0, 1)),
                    'employee_department' => $user->employee->department?->name ? __($user->employee->department->name) : '',
                    'title' => __($ml->leaveType?->name ?? 'Cuti') . ' · ' . $statusText,
                    'description' => $ml->total_days . ' ' . __('Hari') . ' (' . ($ml->start_date ? $ml->start_date->format('d/m/Y') : '') . ')',
                    'notes' => null,
                    'created_at' => $ml->updated_at ?? $ml->created_at,
                    'time_ago' => ($ml->updated_at ?? $ml->created_at)->diffForHumans(),
                    'url' => route('leaves.index', ['tab' => 'requests', 'search' => $ml->request_number]),
                    'action_label' => __('Lihat'),
                ]);
            }

            // Recent Reimbursements
            $myReims = ReimbursementRequest::with('category')
                ->where('employee_id', $myEmpId)
                ->latest()
                ->take(3)
                ->get();

            foreach ($myReims as $mr) {
                $statusText = match($mr->status) {
                    'approved' => __('Disetujui'),
                    'rejected' => __('Ditolak'),
                    'paid' => __('Dibayar'),
                    default => __('Menunggu Review'),
                };
                $items->push([
                    'id' => 'myreim_' . $mr->id,
                    'category' => 'reimbursement',
                    'category_label' => __('Klaim Saya'),
                    'category_badge' => __('Klaim'),
                    'emoji' => '🧾',
                    'badge_color' => in_array($mr->status, ['approved', 'paid']) ? 'emerald' : ($mr->status === 'rejected' ? 'rose' : 'purple'),
                    'icon' => 'receipt',
                    'number' => $mr->claim_number,
                    'employee_name' => $user->name,
                    'employee_code' => $user->employee->employee_code,
                    'employee_initials' => strtoupper(substr($user->employee->first_name, 0, 1) . substr($user->employee->last_name ?? '', 0, 1)),
                    'employee_department' => $user->employee->department?->name ? __($user->employee->department->name) : '',
                    'title' => ($mr->title ?? __('Reimbursement')) . ' · ' . $statusText,
                    'description' => format_currency($mr->amount, $mr->currency ?? 'IDR'),
                    'notes' => null,
                    'created_at' => $mr->updated_at ?? $mr->created_at,
                    'time_ago' => ($mr->updated_at ?? $mr->created_at)->diffForHumans(),
                    'url' => route('reimbursements.show', $mr->id),
                    'action_label' => __('Lihat'),
                ]);
            }

            $counts['leave'] = $myLeaves->where('status', 'submitted')->count();
            $counts['reimbursement'] = $myReims->where('status', 'pending')->count();
        }

        // Sort all items newest first
        $sortedItems = $items->sortByDesc(function ($item) {
            return $item['created_at'] ? $item['created_at']->timestamp : 0;
        })->values()->take($limit)->all();

        $totalCount = array_sum($counts);

        return [
            'total_count' => $totalCount,
            'counts' => $counts,
            'items' => $sortedItems,
        ];
    }
}
