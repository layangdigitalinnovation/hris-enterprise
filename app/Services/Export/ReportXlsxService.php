<?php

namespace App\Services\Export;

use ZipArchive;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportXlsxService
{
    // ==========================================
    // 1. EXCEL EXPORT: LAPORAN PRESENSI
    // ==========================================
    public static function downloadAttendance(
        $summaries, string $companyName, string $periodLabel, string $branch, string $dept,
        string $printedAt, array $signers, int $present, int $late, int $onLeave, int $absent, string $filename
    ): BinaryFileResponse {
        $tempFile = tempnam(sys_get_temp_dir(), 'rep_att_');
        $zip = new ZipArchive();
        $zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        self::addStandardPackageFiles($zip);

        // Build Sheet XML
        $rows = [];
        $no = 1;
        foreach ($summaries as $s) {
            $rawStatus = ($s->status === 'absent') ? 'Alpa' : ($s->status_badge['label'] ?? strtoupper($s->status));
            $rows[] = [
                'no' => $no++,
                'date' => $s->date ? $s->date->format('Y-m-d') : '-',
                'code' => $s->employee->employee_code ?? '-',
                'name' => $s->employee->full_name ?? '-',
                'department' => __($s->employee->department->name ?? '-'),
                'shift' => __($s->shift->name ?? 'Reguler'),
                'clock_in' => $s->clock_in ? substr($s->clock_in, 0, 5) : '--:--',
                'clock_out' => $s->clock_out ? substr($s->clock_out, 0, 5) : '--:--',
                'late' => $s->late_minutes > 0 ? $s->late_minutes . ' ' . __('Menit') : '-',
                'work_hours' => $s->work_hours > 0 ? number_format($s->work_hours, 2) . ' ' . __('Jam') : '-',
                'status' => __($rawStatus),
                'notes' => ($s->notes && $s->notes !== '-') ? __($s->notes) : '-',
            ];
        }

        $isAr = (app()->getLocale() === 'ar');
        $deptWidth = $isAr ? 32 : 22;
        $shiftWidth = $isAr ? 24 : 20;

        $headers = [
            __('No'),
            __('Tanggal'),
            __('ID Karyawan'),
            __('Nama Karyawan'),
            __('Departemen'),
            __('Jadwal Shift'),
            __('Jam Masuk'),
            __('Jam Pulang'),
            __('Terlambat'),
            __('Total Jam Kerja'),
            __('Status Presensi'),
            __('Catatan')
        ];
        $widths = [6, 12, 14, 26, $deptWidth, $shiftWidth, 12, 12, 14, 15, 18, 22];

        $totalEmp = $present + $late + $onLeave + $absent;
        $summaryText = __('Ringkasan Presensi:') . "  " . __('Hadir Tepat Waktu:') . " {$present} " . __('Orang') . "   |   " . __('Terlambat:') . " {$late} " . __('Orang') . "   |   " . __('Cuti / Izin:') . " {$onLeave} " . __('Orang') . "   |   " . __('Alpa:') . " {$absent} " . __('Orang') . "   |   " . __('Total:') . " {$totalEmp} " . __('Orang');

        $metaLine = "{$periodLabel} | " . __('Cabang:') . " " . __($branch) . " | " . __('Dept:') . " " . __($dept) . " | " . __('Dicetak:') . " {$printedAt} " . __('oleh') . " {$signers['maker_name']}";

        $sheetXml = self::renderTableSheet(
            $companyName,
            __('LAPORAN REKAPITULASI KEHADIRAN KARYAWAN'),
            $metaLine,
            $summaryText,
            $headers,
            $widths,
            $rows,
            $signers,
            'attendance'
        );

        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // ==========================================
    // 2. EXCEL EXPORT: LAPORAN GAJI
    // ==========================================
    public static function downloadPayroll(
        $items, string $companyName, string $periodLabel, string $branch, string $dept,
        string $printedAt, array $signers, float $totalBasic, float $totalAllowances, float $totalOvertime,
        float $totalDeductions, float $totalNet, string $filename
    ): BinaryFileResponse {
        $tempFile = tempnam(sys_get_temp_dir(), 'rep_pay_');
        $zip = new ZipArchive();
        $zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        self::addStandardPackageFiles($zip);

        $rows = [];
        $no = 1;
        $totalLate = 0;
        $hasIdr = false;
        $hasSar = false;
        $sumIdr = ['count' => 0, 'basic' => 0, 'allowances' => 0, 'overtime' => 0, 'gross' => 0, 'late' => 0, 'deductions' => 0, 'net' => 0];
        $sumSar = ['count' => 0, 'basic' => 0, 'allowances' => 0, 'overtime' => 0, 'gross' => 0, 'late' => 0, 'deductions' => 0, 'net' => 0];

        foreach ($items as $item) {
            $details = $item->details ?? [];
            $lateDed = (float)($details['late_deduction'] ?? 0);
            $totalLate += $lateDed;
            $curr = $item->currency ?? ($item->employee->effective_currency ?? 'IDR');

            $basic = (float)$item->basic_salary;
            $allowance = (float)$item->total_allowances;
            $overtime = (float)$item->total_overtime;
            $gross = $basic + $allowance + $overtime;
            $deduction = (float)$item->total_deductions;
            $net = (float)$item->net_salary;

            if ($curr === 'SAR') {
                $hasSar = true;
                $sumSar['count']++;
                $sumSar['basic'] += $basic;
                $sumSar['allowances'] += $allowance;
                $sumSar['overtime'] += $overtime;
                $sumSar['gross'] += $gross;
                $sumSar['late'] += $lateDed;
                $sumSar['deductions'] += $deduction;
                $sumSar['net'] += $net;
            } else {
                $hasIdr = true;
                $sumIdr['count']++;
                $sumIdr['basic'] += $basic;
                $sumIdr['allowances'] += $allowance;
                $sumIdr['overtime'] += $overtime;
                $sumIdr['gross'] += $gross;
                $sumIdr['late'] += $lateDed;
                $sumIdr['deductions'] += $deduction;
                $sumIdr['net'] += $net;
            }

            $rawStatus = $item->status ?? 'PAID';
            $rows[] = [
                'no' => $no++,
                'code' => $item->employee->employee_code ?? '-',
                'name' => $item->employee->full_name ?? '-',
                'dept' => __($item->employee->department->name ?? '-'),
                'position' => __($item->employee->position->title ?? '-'),
                'currency' => $curr,
                'bank' => __($item->employee->bank_name ?? '-'),
                'account' => $item->employee->bank_account_no ?? '-',
                'basic' => $basic,
                'allowance' => $allowance,
                'overtime' => $overtime,
                'gross' => $gross,
                'late' => $lateDed,
                'deduction' => $deduction,
                'net' => $net,
                'status' => strtoupper(__($rawStatus)),
            ];
        }

        $isAr = (app()->getLocale() === 'ar');
        $deptWidth = $isAr ? 30 : 22;
        $posWidth = $isAr ? 26 : 22;

        $headers = [
            __('No'),
            __('ID Karyawan'),
            __('Nama Karyawan'),
            __('Departemen'),
            __('Jabatan'),
            __('Mata Uang'),
            __('Bank'),
            __('No. Rekening'),
            __('Gaji Pokok'),
            __('Tunjangan'),
            __('Lembur'),
            __('Gaji Bruto'),
            __('Potongan Telat'),
            __('Total Potongan'),
            __('Gaji Bersih / Netto'),
            __('Status')
        ];
        $widths = [6, 14, 26, $deptWidth, $posWidth, 12, 14, 20, 18, 16, 16, 18, 18, 18, 22, 14];

        $rate = \App\Helpers\CurrencyHelper::getSarToIdrRate();
        if ($hasIdr && $hasSar) {
            $summaryText = __('Total Karyawan:') . " " . count($rows) . " " . __('Orang') . " (" . __('IDR') . ": {$sumIdr['count']}, " . __('SAR') . ": {$sumSar['count']})   |   " . __('Ringkasan IDR:') . " Rp " . number_format($sumIdr['net'], 0, ',', '.') . "   |   " . __('Ringkasan SAR:') . " SAR " . number_format($sumSar['net'], 2, '.', ',') . " (" . __('Kurs 1 SAR = Rp :rate', ['rate' => number_format($rate, 0, ',', '.')]) . ")";
        } elseif ($hasSar) {
            $summaryText = __('Total Karyawan:') . " " . count($rows) . " " . __('Orang') . "   |   " . __('Total Gaji Pokok:') . " SAR " . number_format($sumSar['basic'], 2, '.', ',') . "   |   " . __('Total Lembur:') . " SAR " . number_format($sumSar['overtime'], 2, '.', ',') . "   |   " . __('Total Netto (THP):') . " SAR " . number_format($sumSar['net'], 2, '.', ',');
        } else {
            $summaryText = __('Total Karyawan:') . " " . count($rows) . " " . __('Orang') . "   |   " . __('Total Gaji Pokok:') . " Rp " . number_format($sumIdr['basic'], 0, ',', '.') . "   |   " . __('Total Lembur:') . " Rp " . number_format($sumIdr['overtime'], 0, ',', '.') . "   |   " . __('Total Netto (THP):') . " Rp " . number_format($sumIdr['net'], 0, ',', '.');
        }

        $extraData = [
            'has_idr' => $hasIdr,
            'has_sar' => $hasSar,
            'sum_idr' => $sumIdr,
            'sum_sar' => $sumSar,
            'rate' => $rate,
            'total_basic' => $totalBasic,
            'total_allowances' => $totalAllowances,
            'total_overtime' => $totalOvertime,
            'total_gross' => ($totalBasic + $totalAllowances + $totalOvertime),
            'total_late' => $totalLate,
            'total_deductions' => $totalDeductions,
            'total_net' => $totalNet,
        ];

        $metaLine = "{$periodLabel} | " . __('Cabang:') . " " . __($branch) . " | " . __('Dept:') . " " . __($dept) . " | " . __('Dicetak:') . " {$printedAt} " . __('oleh') . " {$signers['maker_name']}";

        $sheetXml = self::renderTableSheet(
            $companyName,
            __('LAPORAN REKAPITULASI PENGGAJIAN & PAYROLL KARYAWAN'),
            $metaLine,
            $summaryText,
            $headers,
            $widths,
            $rows,
            $signers,
            'payroll',
            $extraData
        );

        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // ==========================================
    // 3. EXCEL EXPORT: LAPORAN JADWAL KERJA
    // ==========================================
    public static function downloadSchedule(
        array $matrix, array $dates, $shifts, string $companyName, string $periodLabel, string $branch, string $dept,
        string $printedAt, array $signers, string $filename
    ): BinaryFileResponse {
        $tempFile = tempnam(sys_get_temp_dir(), 'rep_sch_');
        $zip = new ZipArchive();
        $zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        self::addStandardPackageFiles($zip);

        $isAr = (app()->getLocale() === 'ar');
        $deptWidth = $isAr ? 32 : 24;

        $headers = [__('No'), __('ID'), __('Nama Karyawan'), __('Departemen')];
        $widths = [6, 12, 26, $deptWidth];
        foreach ($dates as $d) {
            $headers[] = date('d/m', strtotime($d));
            $widths[] = 8.5;
        }

        $rows = [];
        $no = 1;
        foreach ($matrix as $m) {
            $r = [
                'no' => $no++,
                'code' => $m['employee']->employee_code ?? '-',
                'name' => $m['employee']->full_name ?? '-',
                'dept' => __($m['employee']->department->name ?? '-'),
            ];
            foreach ($dates as $d) {
                $rawCode = $m['dates'][$d]['code'] ?? '-';
                $r['d_' . $d] = self::cleanShiftCode($rawCode);
            }
            $rows[] = $r;
        }

        $legendParts = [];
        foreach ($shifts as $sh) {
            $c = self::cleanShiftCode($sh->code);
            $cleanName = trim(preg_replace('/\s*\(\d{2}:\d{2}\s*-\s*\d{2}:\d{2}\)/', '', $sh->name));
            $timeStr = substr($sh->start_time, 0, 5) . '-' . substr($sh->end_time, 0, 5);
            $legendParts[] = "[{$c}] " . __($cleanName) . " ({$timeStr})";
        }
        $legendParts[] = "[OFF] " . __('Libur / Day Off');
        $legendText = implode('   |   ', $legendParts);

        $summaryText = "{$periodLabel}   |   " . __('Keterangan Shift:') . " {$legendText}   |   " . __('Total:') . " " . count($matrix) . " " . __('Karyawan');

        $metaLine = "{$periodLabel} | " . __('Cabang:') . " " . __($branch) . " | " . __('Dept:') . " " . __($dept) . " | " . __('Dicetak:') . " {$printedAt} " . __('oleh') . " {$signers['maker_name']}";

        $sheetXml = self::renderTableSheet(
            $companyName,
            __('LAPORAN MATRIKS PENUGASAN JADWAL & SHIFT KERJA'),
            $metaLine,
            $summaryText,
            $headers,
            $widths,
            $rows,
            $signers,
            'schedule'
        );

        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // ==========================================
    // 4. EXCEL EXPORT: LAPORAN CUTI
    // ==========================================
    public static function downloadLeave(
        array $reportData, string $companyName, string $periodLabel, string $branch, string $dept, string $leaveType,
        string $printedAt, array $signers, int $totalInitial, int $totalUsed, int $totalRemaining, string $filename
    ): BinaryFileResponse {
        $tempFile = tempnam(sys_get_temp_dir(), 'rep_lev_');
        $zip = new ZipArchive();
        $zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        self::addStandardPackageFiles($zip);

        $isAr = (app()->getLocale() === 'ar');
        $deptWidth = $isAr ? 32 : 20;

        $headers = [
            __('No'), __('ID'), __('Nama Karyawan'), __('Departemen'), __('Hak Cuti'),
            __('Jan'), __('Feb'), __('Mar'), __('Apr'), __('Mei'), __('Jun'),
            __('Jul'), __('Agu'), __('Sep'), __('Okt'), __('Nov'), __('Des'),
            __('Total Ambil'), __('Sisa Cuti')
        ];
        $widths = [6, 12, 26, $deptWidth, 10, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 12, 12];

        $rows = [];
        $no = 1;
        foreach ($reportData as $item) {
            $r = [
                'no' => $no++,
                'code' => $item['employee']->employee_code ?? '-',
                'name' => $item['employee']->full_name ?? '-',
                'dept' => __($item['employee']->department->name ?? '-'),
                'quota' => $item['initial_quota'],
            ];
            for ($m = 1; $m <= 12; $m++) {
                $r['m_' . $m] = $item['months'][$m] > 0 ? $item['months'][$m] : '-';
            }
            $r['taken'] = $item['total_taken'];
            $r['remaining'] = $item['remaining'];
            $rows[] = $r;
        }

        $summaryText = __('Periode:') . " {$periodLabel}   |   " . __('Jenis Cuti:') . " " . __($leaveType) . "   |   " . __('Total Kuota Hak:') . " {$totalInitial} " . __('Hari') . "   |   " . __('Total Diambil:') . " {$totalUsed} " . __('Hari') . "   |   " . __('Sisa Saldo Cuti:') . " {$totalRemaining} " . __('Hari');

        $metaLine = "{$periodLabel} | " . __('Jenis:') . " " . __($leaveType) . " | " . __('Cabang:') . " " . __($branch) . " | " . __('Dept:') . " " . __($dept) . " | " . __('Dicetak:') . " {$printedAt} " . __('oleh') . " {$signers['maker_name']}";

        $sheetXml = self::renderTableSheet(
            $companyName,
            __('LAPORAN REKAPITULASI CUTI & SALDO LEAVE LEDGER'),
            $metaLine,
            $summaryText,
            $headers,
            $widths,
            $rows,
            $signers,
            'leave'
        );

        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // ==========================================
    // 5. EXCEL EXPORT: LAPORAN REIMBURSEMENT
    // ==========================================
    public static function downloadReimbursement(
        $items, string $companyName, string $periodLabel, string $branch, string $dept, string $category,
        string $status, string $printedAt, array $signers, float $totalAmountIdr, float $totalApprovedIdr,
        float $totalAmountSar, float $totalApprovedSar, string $filename
    ): BinaryFileResponse {
        $tempFile = tempnam(sys_get_temp_dir(), 'rep_rmb_');
        $zip = new ZipArchive();
        $zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        self::addStandardPackageFiles($zip);

        $isAr = (app()->getLocale() === 'ar');
        $deptWidth = $isAr ? 32 : 22;

        $headers = [
            __('No'), __('No. Klaim'), __('Tanggal'), __('ID'), __('Nama Karyawan'),
            __('Departemen'), __('Kategori'), __('Keperluan / Judul'), __('Mata Uang'),
            __('Jumlah Diajukan'), __('Jumlah Disetujui'), __('Status'), __('Metode Bayar')
        ];
        $widths = [6, 18, 12, 12, 26, $deptWidth, 18, 28, 10, 16, 16, 16, 16];

        $hasIdr = false;
        $hasSar = false;
        $rows = [];
        $no = 1;
        foreach ($items as $item) {
            $c = $item->currency ?? 'IDR';
            if ($c === 'SAR') $hasSar = true;
            else $hasIdr = true;

            $rows[] = [
                'no' => $no++,
                'claim_number' => $item->claim_number,
                'date' => $item->claim_date ? $item->claim_date->format('d/m/Y') : '-',
                'code' => $item->employee?->employee_code ?? '-',
                'name' => $item->employee?->full_name ?? '-',
                'dept' => __($item->employee?->department?->name ?? '-'),
                'category' => __($item->category?->name ?? '-'),
                'title' => $item->title,
                'currency' => $c,
                'amount' => (float)$item->amount,
                'approved_amount' => (float)($item->approved_amount ?? $item->amount),
                'status' => $item->status_label,
                'payment_method' => $item->payment_method ? ucfirst($item->payment_method) : '-',
            ];
        }

        $summaryText = __('Periode:') . " {$periodLabel}   |   " . __('Total Data:') . " " . count($items) . " " . __('Transaksi') . "   |   " . __('Disetujui (IDR):') . " Rp " . number_format($totalApprovedIdr, 0, ',', '.');
        if ($totalApprovedSar > 0) {
            $summaryText .= "   |   " . __('Disetujui (SAR):') . " SAR " . number_format($totalApprovedSar, 2, ',', '.');
        }

        $metaLine = "{$periodLabel} | " . __('Cabang:') . " " . __($branch) . " | " . __('Dept:') . " " . __($dept) . " | " . __('Kategori:') . " " . __($category) . " | " . __('Status:') . " " . __($status) . " | " . __('Dicetak:') . " {$printedAt} " . __('oleh') . " {$signers['maker_name']}";

        $extraData = [
            'has_idr' => $hasIdr || $totalAmountIdr > 0 || $totalApprovedIdr > 0,
            'has_sar' => $hasSar || $totalAmountSar > 0 || $totalApprovedSar > 0,
            'total_amount_idr' => $totalAmountIdr,
            'total_approved_idr' => $totalApprovedIdr,
            'total_amount_sar' => $totalAmountSar,
            'total_approved_sar' => $totalApprovedSar,
        ];

        $sheetXml = self::renderTableSheet(
            $companyName,
            __('LAPORAN REKAPITULASI KLAIM REIMBURSEMENT KARYAWAN'),
            $metaLine,
            $summaryText,
            $headers,
            $widths,
            $rows,
            $signers,
            'reimbursement',
            $extraData
        );

        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // ==========================================
    // 6. EXCEL EXPORT: LAPORAN PINJAMAN KARYAWAN
    // ==========================================
    public static function downloadLoans(
        $loans, string $companyName, string $periodLabel, string $branch, string $dept,
        string $status, string $printedAt, array $signers, float $totalPrincipalIdr, float $totalRepaidIdr,
        float $totalRemainingIdr, float $totalPrincipalSar, float $totalRepaidSar, float $totalRemainingSar,
        string $filename
    ): BinaryFileResponse {
        $tempFile = tempnam(sys_get_temp_dir(), 'rep_loa_');
        $zip = new ZipArchive();
        $zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        self::addStandardPackageFiles($zip);

        $isAr = (app()->getLocale() === 'ar');
        $deptWidth = $isAr ? 32 : 22;

        $headers = [
            __('No'), __('No. Pinjaman'), __('Tgl Pengajuan'), __('ID'), __('Nama Karyawan'),
            __('Departemen'), __('Mata Uang'), __('Pokok Pinjaman'), __('Tenor'),
            __('Cicilan / Bln'), __('Total Terbayar'), __('Sisa Pinjaman'), __('Progress'),
            __('Status'), __('Metode Pencairan')
        ];
        $widths = [6, 18, 14, 12, 26, $deptWidth, 10, 18, 12, 16, 18, 18, 12, 16, 16];

        $hasIdr = false;
        $hasSar = false;
        $rows = [];
        $no = 1;
        foreach ($loans as $loan) {
            $c = $loan->currency ?? 'IDR';
            if ($c === 'SAR') $hasSar = true;
            else $hasIdr = true;

            $rows[] = [
                'no' => $no++,
                'loan_number' => $loan->loan_number,
                'date' => $loan->created_at ? $loan->created_at->format('d/m/Y') : '-',
                'code' => $loan->employee?->employee_code ?? '-',
                'name' => $loan->employee?->full_name ?? '-',
                'dept' => __($loan->employee?->department?->name ?? '-'),
                'currency' => $c,
                'principal' => (float)$loan->effective_principal,
                'tenor' => $loan->tenor_months . ' ' . __('Bln'),
                'monthly' => (float)$loan->monthly_installment,
                'repaid' => (float)$loan->total_repaid_amount,
                'remaining_loan' => (float)$loan->remaining_amount,
                'progress' => $loan->repayment_progress_percentage . '%',
                'status' => $loan->status_label,
                'disbursement_method' => $loan->payment_method ? ucfirst($loan->payment_method) : '-',
            ];
        }

        $summaryText = __('Periode:') . " {$periodLabel}   |   " . __('Total Data:') . " " . count($loans) . " " . __('Pinjaman') . "   |   " . __('Sisa Pinjaman (IDR):') . " Rp " . number_format($totalRemainingIdr, 0, ',', '.');
        if ($totalRemainingSar > 0) {
            $summaryText .= "   |   " . __('Sisa Pinjaman (SAR):') . " SAR " . number_format($totalRemainingSar, 2, ',', '.');
        }

        $metaLine = "{$periodLabel} | " . __('Cabang:') . " " . __($branch) . " | " . __('Dept:') . " " . __($dept) . " | " . __('Status:') . " " . __($status) . " | " . __('Dicetak:') . " {$printedAt} " . __('oleh') . " {$signers['maker_name']}";

        $extraData = [
            'has_idr' => $hasIdr || $totalPrincipalIdr > 0 || $totalRemainingIdr > 0,
            'has_sar' => $hasSar || $totalPrincipalSar > 0 || $totalRemainingSar > 0,
            'total_principal_idr' => $totalPrincipalIdr,
            'total_repaid_idr' => $totalRepaidIdr,
            'total_remaining_idr' => $totalRemainingIdr,
            'total_principal_sar' => $totalPrincipalSar,
            'total_repaid_sar' => $totalRepaidSar,
            'total_remaining_sar' => $totalRemainingSar,
        ];

        $sheetXml = self::renderTableSheet(
            $companyName,
            __('LAPORAN REKAPITULASI PINJAMAN & PEMBIAYAAN KARYAWAN'),
            $metaLine,
            $summaryText,
            $headers,
            $widths,
            $rows,
            $signers,
            'loans',
            $extraData
        );

        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // ==========================================
    // XML TEMPLATE ENGINE FOR OPENXML
    // ==========================================
    protected static function addStandardPackageFiles(ZipArchive $zip): void
    {
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);

        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';
        $zip->addFromString('_rels/.rels', $rels);

        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>';
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);

        $sheetName = (app()->getLocale() === 'ar') ? 'التقرير' : 'Laporan';
        $wb = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="' . $sheetName . '" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>';
        $zip->addFromString('xl/workbook.xml', $wb);

        $styles = self::buildStylesXml();
        $zip->addFromString('xl/styles.xml', $styles);
    }

    protected static function buildStylesXml(): string
    {
        $isArabic = (app()->getLocale() === 'ar');
        $textAlign = $isArabic ? 'right' : 'left';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="10">
    <font><name val="Calibri"/><sz val="11"/><color rgb="FF000000"/></font>
    <font><b/><name val="Calibri"/><sz val="11"/><color rgb="FF000000"/></font>
    <font><b/><name val="Calibri"/><sz val="14"/><color rgb="FF1E3A8A"/></font>
    <font><b/><name val="Calibri"/><sz val="10"/><color rgb="FFFFFFFF"/></font>
    <font><name val="Calibri"/><sz val="10"/><color rgb="FF64748B"/></font>
    <font><b/><name val="Calibri"/><sz val="10"/><color rgb="FF15803D"/></font>
    <font><b/><name val="Calibri"/><sz val="10"/><color rgb="FFB45309"/></font>
    <font><b/><name val="Calibri"/><sz val="10"/><color rgb="FF1D4ED8"/></font>
    <font><b/><name val="Calibri"/><sz val="10"/><color rgb="FFB91C1C"/></font>
    <font><b/><name val="Calibri"/><sz val="10"/><color rgb="FF6D28D9"/></font>
  </fonts>
  <fills count="9">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF1E293B"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFDCFCE7"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFFEF3C7"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFDBEAFE"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFFEE2E2"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFEDE9FE"/></patternFill></fill>
  </fills>
  <borders count="2">
    <border><left/><right/><top/><bottom/><diagonal/></border>
    <border>
      <left style="thin"><color rgb="FFCBD5E1"/></left>
      <right style="thin"><color rgb="FFCBD5E1"/></right>
      <top style="thin"><color rgb="FFCBD5E1"/></top>
      <bottom style="thin"><color rgb="FFCBD5E1"/></bottom>
      <diagonal/>
    </border>
  </borders>
  <cellStyleXfs count="1">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
  </cellStyleXfs>
  <cellXfs count="24">
    <!-- 0: Default Normal (Summary text, wrapText enabled) -->
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"><alignment horizontal="' . $textAlign . '" vertical="center" wrapText="1"/></xf>
    <!-- 1: Company Title -->
    <xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0"><alignment horizontal="center" vertical="center"/></xf>
    <!-- 2: Subtitle / Meta line -->
    <xf numFmtId="0" fontId="4" fillId="0" borderId="0" xfId="0"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <!-- 3: Table Header -->
    <xf numFmtId="0" fontId="3" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <!-- 4: Table Data Text (Left in LTR, Right in RTL) -->
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"><alignment horizontal="' . $textAlign . '" vertical="center"/></xf>
    <!-- 5: Table Data Center -->
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>
    <!-- 6: Table Data Bold Text (Left in LTR, Right in RTL) -->
    <xf numFmtId="0" fontId="1" fillId="0" borderId="1" xfId="0" applyBorder="1"><alignment horizontal="' . $textAlign . '" vertical="center"/></xf>
    <!-- 7: Status Present / Shift PAG (Green) -->
    <xf numFmtId="0" fontId="5" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>
    <!-- 8: Status Late / Shift SIA (Amber) -->
    <xf numFmtId="0" fontId="6" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>
    <!-- 9: Status Leave / Shift REG (Blue) -->
    <xf numFmtId="0" fontId="7" fillId="5" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>
    <!-- 10: Status Absent / Shift OFF (Red Bold) -->
    <xf numFmtId="0" fontId="8" fillId="6" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>
    <!-- 11: Table Data Zebra Text (Left in LTR, Right in RTL) -->
    <xf numFmtId="0" fontId="0" fillId="7" borderId="1" xfId="0" applyFill="1" applyBorder="1"><alignment horizontal="' . $textAlign . '" vertical="center"/></xf>
    <!-- 12: Table Data Zebra Center -->
    <xf numFmtId="0" fontId="0" fillId="7" borderId="1" xfId="0" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>
    <!-- 13: Sign Title Bold Center -->
    <xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0"><alignment horizontal="center" vertical="center"/></xf>
    <!-- 14: Sign Name Bold Underline Center -->
    <xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0"><alignment horizontal="center" vertical="center"/></xf>
    <!-- 15: Sign Position Center -->
    <xf numFmtId="0" fontId="4" fillId="0" borderId="0" xfId="0"><alignment horizontal="center" vertical="center"/></xf>
    <!-- 16: Shift MLM (Purple) -->
    <xf numFmtId="0" fontId="9" fillId="8" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>
    <!-- 17: Table Data Right (Money) -->
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"><alignment horizontal="right" vertical="center"/></xf>
    <!-- 18: Table Data Zebra Right (Money) -->
    <xf numFmtId="0" fontId="0" fillId="7" borderId="1" xfId="0" applyFill="1" applyBorder="1"><alignment horizontal="right" vertical="center"/></xf>
    <!-- 19: Table Data Bold Right (Net Money) -->
    <xf numFmtId="0" fontId="1" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1"><alignment horizontal="right" vertical="center"/></xf>
    <!-- 20: Table Data Zebra Bold Right (Net Money) -->
    <xf numFmtId="0" fontId="1" fillId="7" borderId="1" xfId="0" applyFont="1" applyBorder="1"><alignment horizontal="right" vertical="center"/></xf>
    <!-- 21: Total Summary Row Label (Bold Center, Soft Blue fill) -->
    <xf numFmtId="0" fontId="1" fillId="5" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>
    <!-- 22: Total Summary Row Numbers (Bold Right, Soft Blue fill) -->
    <xf numFmtId="0" fontId="1" fillId="5" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="right" vertical="center"/></xf>
    <!-- 23: Total Summary Row Empty (Soft Blue fill) -->
    <xf numFmtId="0" fontId="1" fillId="5" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>
  </cellXfs>
</styleSheet>';
    }

    protected static function renderTableSheet(
        string $companyName, string $reportTitle, string $metaLine, string $summaryText,
        array $headers, array $widths, array $rows, array $signers, string $reportType,
        array $extraData = []
    ): string {
        $colCount = count($headers);
        $lastColLetter = self::colLetter($colCount);
        $isArabic = (app()->getLocale() === 'ar');
        $sheetViewAttr = $isArabic ? 'tabSelected="1" workbookViewId="0" rightToLeft="1"' : 'tabSelected="1" workbookViewId="0"';

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheetViews>
    <sheetView ' . $sheetViewAttr . '>
      <pane ySplit="7" topLeftCell="A8" activePane="bottomLeft" state="frozen"/>
    </sheetView>
  </sheetViews>
  <sheetFormatPr defaultRowHeight="20"/>
  <cols>';

        for ($i = 0; $i < $colCount; $i++) {
            $colNum = $i + 1;
            $w = $widths[$i] ?? 15;
            $xml .= '<col min="' . $colNum . '" max="' . $colNum . '" width="' . $w . '" customWidth="1"/>';
        }
        $xml .= '</cols><sheetData>';

        $merges = [];

        // Row 1: Company Title
        $xml .= '<row r="1" ht="24" customHeight="1">';
        $xml .= self::c('A1', 1, strtoupper($companyName));
        $xml .= '</row>';
        $merges[] = "A1:{$lastColLetter}1";

        // Row 2: Report Title
        $xml .= '<row r="2" ht="22" customHeight="1">';
        $xml .= self::c('A2', 1, $reportTitle);
        $xml .= '</row>';
        $merges[] = "A2:{$lastColLetter}2";

        // Row 3: Meta line
        $metaHt = (mb_strlen($metaLine, 'UTF-8') > 110 && $colCount < 16) ? 30 : 18;
        $xml .= '<row r="3" ht="' . $metaHt . '" customHeight="1">';
        $xml .= self::c('A3', 2, $metaLine);
        $xml .= '</row>';
        $merges[] = "A3:{$lastColLetter}3";

        // Row 4: Blank
        $xml .= '<row r="4" ht="10" customHeight="1"></row>';

        // Row 5: Summary text line (wrap text and dynamic height to prevent truncation on smaller column ranges)
        $sumLen = mb_strlen($summaryText, 'UTF-8');
        $sumHt = ($sumLen > 180 && $colCount < 16) ? 46 : (($sumLen > 90 && $colCount < 20) ? 36 : 22);
        $xml .= '<row r="5" ht="' . $sumHt . '" customHeight="1">';
        $xml .= self::c('A5', 0, $summaryText);
        $xml .= '</row>';
        $merges[] = "A5:{$lastColLetter}5";

        // Row 6: Blank
        $xml .= '<row r="6" ht="10" customHeight="1"></row>';

        // Row 7: Headers
        $xml .= '<row r="7" ht="26" customHeight="1">';
        for ($i = 0; $i < $colCount; $i++) {
            $cellRef = self::colLetter($i + 1) . '7';
            $xml .= self::c($cellRef, 3, $headers[$i]);
        }
        $xml .= '</row>';

        // Data Rows
        $rowIndex = 8;
        $rowNo = 1;
        foreach ($rows as $item) {
            $isZebra = ($rowNo % 2 === 0);
            $cLeft = $isZebra ? 11 : 4;
            $cCenter = $isZebra ? 12 : 5;
            $cRight = $isZebra ? 18 : 17;
            $cBoldRight = $isZebra ? 20 : 19;

            $xml .= '<row r="' . $rowIndex . '" ht="20" customHeight="1">';
            $colIdx = 1;
            foreach ($item as $key => $val) {
                if ($colIdx > $colCount) break;
                $cellRef = self::colLetter($colIdx) . $rowIndex;
                $style = $cCenter;

                $rowCurr = $item['currency'] ?? 'IDR';
                if (is_numeric($val) && !in_array($key, ['no', 'code', 'date', 'quota', 'taken', 'remaining', 'account', 'bank', 'currency', 'tenor', 'claim_number', 'loan_number'])) {
                    $style = in_array($key, ['approved_amount', 'remaining_loan', 'net']) ? $cBoldRight : $cRight;
                    $decimals = ($rowCurr === 'SAR') ? 2 : 0;
                    $valStr = number_format((float)$val, $decimals, ',', '.');
                } else {
                    $valStr = (string)$val;
                    if (in_array($key, ['name', 'dept', 'position', 'shift', 'notes', 'category', 'title'])) {
                        $style = ($key === 'name') ? 6 : $cLeft;
                    } elseif (in_array($key, ['account', 'bank', 'currency', 'claim_number', 'loan_number', 'date', 'tenor', 'progress', 'payment_method', 'disbursement_method'])) {
                        $style = $cCenter;
                    }
                }

                if ($key === 'status') {
                    $valUpper = mb_strtoupper(trim($valStr), 'UTF-8');
                    $style = match(true) {
                        str_contains($valUpper, 'HADIR') || str_contains($valUpper, 'PAID') || str_contains($valUpper, 'LOCKED') || str_contains($valUpper, 'LUNAS') || str_contains($valUpper, 'AKTIF') || str_contains($valUpper, 'DISBURSED') || str_contains($valUpper, 'حاضر') || str_contains($valUpper, 'مدفوع') || str_contains($valUpper, 'ساري') || str_contains($valUpper, 'نشط') || str_contains($valUpper, 'تم الصرف') || str_contains($valUpper, 'تم السداد') => 7,
                        str_contains($valUpper, 'TERLAMBAT') || str_contains($valUpper, 'CALCULATED') || str_contains($valUpper, 'PENDING') || str_contains($valUpper, 'MENUNGGU') || str_contains($valUpper, 'متأخر') || str_contains($valUpper, 'محسوب') || str_contains($valUpper, 'قيد') || str_contains($valUpper, 'انتظار') => 8,
                        str_contains($valUpper, 'APPROVED') || str_contains($valUpper, 'DISETUJUI') || str_contains($valUpper, 'CUTI') || str_contains($valUpper, 'SAKIT') || str_contains($valUpper, 'IZIN') || str_contains($valUpper, 'DRAFT') || str_contains($valUpper, 'إجازة') || str_contains($valUpper, 'مرضي') || str_contains($valUpper, 'إذن') || str_contains($valUpper, 'مسودة') || str_contains($valUpper, 'معتمد') || str_contains($valUpper, 'موافق') => 9,
                        str_contains($valUpper, 'DITOLAK') || str_contains($valUpper, 'REJECTED') || str_contains($valUpper, 'BATAL') || str_contains($valUpper, 'CANCEL') || str_contains($valUpper, 'مرفوض') || str_contains($valUpper, 'ملغي') => 10,
                        default => 10,
                    };
                }

                // Schedule Matrix Shift cells styling (OFF = Red Bold, REG = Blue, PAG = Green, SIA = Amber, MLM = Purple)
                if ($reportType === 'schedule' && str_starts_with($key, 'd_')) {
                    $valUpper = mb_strtoupper(trim($valStr), 'UTF-8');
                    $style = match($valUpper) {
                        'OFF' => 10, // Soft Red fill + Bold Red text
                        'REG' => 9,  // Soft Blue fill + Bold Blue text
                        'PAG' => 7,  // Soft Green fill + Bold Green text
                        'SIA' => 8,  // Soft Amber fill + Bold Amber text
                        'MLM' => 16, // Soft Purple fill + Bold Purple text
                        '-', '' => $cCenter,
                        default => 9,
                    };
                }

                $xml .= self::c($cellRef, $style, $valStr);
                $colIdx++;
            }
            $xml .= '</row>';
            $rowIndex++;
            $rowNo++;
        }

        // Total Row for Payroll
        if ($reportType === 'payroll' && !empty($extraData)) {
            $hasIdr = $extraData['has_idr'] ?? false;
            $hasSar = $extraData['has_sar'] ?? false;
            $sumIdr = $extraData['sum_idr'] ?? [];
            $sumSar = $extraData['sum_sar'] ?? [];
            $rate = (float)($extraData['rate'] ?? 4250);

            // Render IDR Subtotal Row if present
            if ($hasIdr) {
                $xml .= '<row r="' . $rowIndex . '" ht="24" customHeight="1">';
                for ($c = 1; $c <= 8; $c++) {
                    $ref = self::colLetter($c) . $rowIndex;
                    $tVal = ($c === 1) ? __('TOTAL KESELURUHAN (IDR)') : '';
                    $xml .= self::c($ref, 21, $tVal);
                }
                $merges[] = 'A' . $rowIndex . ':H' . $rowIndex;

                $xml .= self::c('I' . $rowIndex, 22, number_format((float)($sumIdr['basic'] ?? 0), 0, ',', '.'));
                $xml .= self::c('J' . $rowIndex, 22, number_format((float)($sumIdr['allowances'] ?? 0), 0, ',', '.'));
                $xml .= self::c('K' . $rowIndex, 22, number_format((float)($sumIdr['overtime'] ?? 0), 0, ',', '.'));
                $xml .= self::c('L' . $rowIndex, 22, number_format((float)($sumIdr['gross'] ?? 0), 0, ',', '.'));
                $xml .= self::c('M' . $rowIndex, 22, number_format((float)($sumIdr['late'] ?? 0), 0, ',', '.'));
                $xml .= self::c('N' . $rowIndex, 22, number_format((float)($sumIdr['deductions'] ?? 0), 0, ',', '.'));
                $xml .= self::c('O' . $rowIndex, 22, number_format((float)($sumIdr['net'] ?? 0), 0, ',', '.'));
                $xml .= self::c('P' . $rowIndex, 23, '');
                $xml .= '</row>';
                $rowIndex++;
            }

            // Render SAR Subtotal Row if present
            if ($hasSar) {
                $xml .= '<row r="' . $rowIndex . '" ht="24" customHeight="1">';
                for ($c = 1; $c <= 8; $c++) {
                    $ref = self::colLetter($c) . $rowIndex;
                    $tVal = ($c === 1) ? __('TOTAL KESELURUHAN (SAR)') : '';
                    $xml .= self::c($ref, 21, $tVal);
                }
                $merges[] = 'A' . $rowIndex . ':H' . $rowIndex;

                $xml .= self::c('I' . $rowIndex, 22, number_format((float)($sumSar['basic'] ?? 0), 2, ',', '.'));
                $xml .= self::c('J' . $rowIndex, 22, number_format((float)($sumSar['allowances'] ?? 0), 2, ',', '.'));
                $xml .= self::c('K' . $rowIndex, 22, number_format((float)($sumSar['overtime'] ?? 0), 2, ',', '.'));
                $xml .= self::c('L' . $rowIndex, 22, number_format((float)($sumSar['gross'] ?? 0), 2, ',', '.'));
                $xml .= self::c('M' . $rowIndex, 22, number_format((float)($sumSar['late'] ?? 0), 2, ',', '.'));
                $xml .= self::c('N' . $rowIndex, 22, number_format((float)($sumSar['deductions'] ?? 0), 2, ',', '.'));
                $xml .= self::c('O' . $rowIndex, 22, number_format((float)($sumSar['net'] ?? 0), 2, ',', '.'));
                $xml .= self::c('P' . $rowIndex, 23, '');
                $xml .= '</row>';
                $rowIndex++;
            }

            // If mixed, render converted Consolidated Row in IDR
            if ($hasIdr && $hasSar) {
                $cBasic = ($sumIdr['basic'] ?? 0) + (($sumSar['basic'] ?? 0) * $rate);
                $cAllow = ($sumIdr['allowances'] ?? 0) + (($sumSar['allowances'] ?? 0) * $rate);
                $cOver = ($sumIdr['overtime'] ?? 0) + (($sumSar['overtime'] ?? 0) * $rate);
                $cGross = ($sumIdr['gross'] ?? 0) + (($sumSar['gross'] ?? 0) * $rate);
                $cLate = ($sumIdr['late'] ?? 0) + (($sumSar['late'] ?? 0) * $rate);
                $cDed = ($sumIdr['deductions'] ?? 0) + (($sumSar['deductions'] ?? 0) * $rate);
                $cNet = ($sumIdr['net'] ?? 0) + (($sumSar['net'] ?? 0) * $rate);

                $xml .= '<row r="' . $rowIndex . '" ht="24" customHeight="1">';
                for ($c = 1; $c <= 8; $c++) {
                    $ref = self::colLetter($c) . $rowIndex;
                    $tVal = ($c === 1) ? __('TOTAL KONSOLIDASI (IDR - Kurs 1 SAR = Rp :rate)', ['rate' => number_format($rate, 0, ',', '.')]) : '';
                    $xml .= self::c($ref, 21, $tVal);
                }
                $merges[] = 'A' . $rowIndex . ':H' . $rowIndex;

                $xml .= self::c('I' . $rowIndex, 22, number_format($cBasic, 0, ',', '.'));
                $xml .= self::c('J' . $rowIndex, 22, number_format($cAllow, 0, ',', '.'));
                $xml .= self::c('K' . $rowIndex, 22, number_format($cOver, 0, ',', '.'));
                $xml .= self::c('L' . $rowIndex, 22, number_format($cGross, 0, ',', '.'));
                $xml .= self::c('M' . $rowIndex, 22, number_format($cLate, 0, ',', '.'));
                $xml .= self::c('N' . $rowIndex, 22, number_format($cDed, 0, ',', '.'));
                $xml .= self::c('O' . $rowIndex, 22, number_format($cNet, 0, ',', '.'));
                $xml .= self::c('P' . $rowIndex, 23, '');
                $xml .= '</row>';
                $rowIndex++;
            }
        }

        // Total Row for Reimbursement
        if ($reportType === 'reimbursement' && !empty($extraData)) {
            $hasIdr = $extraData['has_idr'] ?? false;
            $hasSar = $extraData['has_sar'] ?? false;

            if ($hasIdr) {
                $xml .= '<row r="' . $rowIndex . '" ht="24" customHeight="1">';
                for ($c = 1; $c <= 9; $c++) {
                    $ref = self::colLetter($c) . $rowIndex;
                    $tVal = ($c === 1) ? __('TOTAL KESELURUHAN (IDR)') : '';
                    $xml .= self::c($ref, 21, $tVal);
                }
                $merges[] = 'A' . $rowIndex . ':I' . $rowIndex;

                $xml .= self::c('J' . $rowIndex, 22, number_format((float)($extraData['total_amount_idr'] ?? 0), 0, ',', '.'));
                $xml .= self::c('K' . $rowIndex, 22, number_format((float)($extraData['total_approved_idr'] ?? 0), 0, ',', '.'));
                $xml .= self::c('L' . $rowIndex, 23, '');
                $xml .= self::c('M' . $rowIndex, 23, '');
                $xml .= '</row>';
                $rowIndex++;
            }

            if ($hasSar) {
                $xml .= '<row r="' . $rowIndex . '" ht="24" customHeight="1">';
                for ($c = 1; $c <= 9; $c++) {
                    $ref = self::colLetter($c) . $rowIndex;
                    $tVal = ($c === 1) ? __('TOTAL KESELURUHAN (SAR)') : '';
                    $xml .= self::c($ref, 21, $tVal);
                }
                $merges[] = 'A' . $rowIndex . ':I' . $rowIndex;

                $xml .= self::c('J' . $rowIndex, 22, number_format((float)($extraData['total_amount_sar'] ?? 0), 2, ',', '.'));
                $xml .= self::c('K' . $rowIndex, 22, number_format((float)($extraData['total_approved_sar'] ?? 0), 2, ',', '.'));
                $xml .= self::c('L' . $rowIndex, 23, '');
                $xml .= self::c('M' . $rowIndex, 23, '');
                $xml .= '</row>';
                $rowIndex++;
            }
        }

        // Total Row for Loans
        if ($reportType === 'loans' && !empty($extraData)) {
            $hasIdr = $extraData['has_idr'] ?? false;
            $hasSar = $extraData['has_sar'] ?? false;

            if ($hasIdr) {
                $xml .= '<row r="' . $rowIndex . '" ht="24" customHeight="1">';
                for ($c = 1; $c <= 7; $c++) {
                    $ref = self::colLetter($c) . $rowIndex;
                    $tVal = ($c === 1) ? __('TOTAL KESELURUHAN (IDR)') : '';
                    $xml .= self::c($ref, 21, $tVal);
                }
                $merges[] = 'A' . $rowIndex . ':G' . $rowIndex;

                $xml .= self::c('H' . $rowIndex, 22, number_format((float)($extraData['total_principal_idr'] ?? 0), 0, ',', '.'));
                $xml .= self::c('I' . $rowIndex, 23, '');
                $xml .= self::c('J' . $rowIndex, 23, '');
                $xml .= self::c('K' . $rowIndex, 22, number_format((float)($extraData['total_repaid_idr'] ?? 0), 0, ',', '.'));
                $xml .= self::c('L' . $rowIndex, 22, number_format((float)($extraData['total_remaining_idr'] ?? 0), 0, ',', '.'));
                $xml .= self::c('M' . $rowIndex, 23, '');
                $xml .= self::c('N' . $rowIndex, 23, '');
                $xml .= self::c('O' . $rowIndex, 23, '');
                $xml .= '</row>';
                $rowIndex++;
            }

            if ($hasSar) {
                $xml .= '<row r="' . $rowIndex . '" ht="24" customHeight="1">';
                for ($c = 1; $c <= 7; $c++) {
                    $ref = self::colLetter($c) . $rowIndex;
                    $tVal = ($c === 1) ? __('TOTAL KESELURUHAN (SAR)') : '';
                    $xml .= self::c($ref, 21, $tVal);
                }
                $merges[] = 'A' . $rowIndex . ':G' . $rowIndex;

                $xml .= self::c('H' . $rowIndex, 22, number_format((float)($extraData['total_principal_sar'] ?? 0), 2, ',', '.'));
                $xml .= self::c('I' . $rowIndex, 23, '');
                $xml .= self::c('J' . $rowIndex, 23, '');
                $xml .= self::c('K' . $rowIndex, 22, number_format((float)($extraData['total_repaid_sar'] ?? 0), 2, ',', '.'));
                $xml .= self::c('L' . $rowIndex, 22, number_format((float)($extraData['total_remaining_sar'] ?? 0), 2, ',', '.'));
                $xml .= self::c('M' . $rowIndex, 23, '');
                $xml .= self::c('N' . $rowIndex, 23, '');
                $xml .= self::c('O' . $rowIndex, 23, '');
                $xml .= '</row>';
                $rowIndex++;
            }
        }

        // Space before signature
        $rowIndex++;
        $xml .= '<row r="' . $rowIndex . '" ht="15" customHeight="1"></row>';
        $rowIndex++;

        // Signature Rows
        $sigRow1 = $rowIndex;
        if ($reportType === 'schedule') {
            $leftCol1 = 'B';
            $leftCol2 = 'E';
            $spanRight = min(9, max(5, $colCount - 5));
            $rightStartIdx = max(6, $colCount - $spanRight + 1);
            $rightCol1 = self::colLetter($rightStartIdx);
            $rightCol2 = self::colLetter($colCount);
        } else {
            $leftCol1 = 'B';
            $leftCol2 = 'E';
            $rightCol1 = self::colLetter(max(5, $colCount - 3));
            $rightCol2 = self::colLetter($colCount);
        }

        // 1. Signature Title Row: "Dibuat Oleh:" & "Mengetahui & Menyetujui:"
        $xml .= self::renderMergedSignatureRow(
            $sigRow1, 18,
            $leftCol1, $leftCol2, 13, __('Dibuat Oleh:'),
            $rightCol1, $rightCol2, 13, __('Mengetahui & Menyetujui:'),
            $merges
        );

        $rowIndex++;
        $sigRowSpace = $rowIndex;
        // 2. Signature Space Row (height 45 for physical signature / stamp)
        $xml .= self::renderMergedSignatureRow(
            $sigRowSpace, 45,
            $leftCol1, $leftCol2, 0, "",
            $rightCol1, $rightCol2, 0, "",
            $merges
        );

        $rowIndex++;
        $sigRow2 = $rowIndex;
        // 3. Signer Name Row (bold underline)
        $xml .= self::renderMergedSignatureRow(
            $sigRow2, 18,
            $leftCol1, $leftCol2, 14, $signers['maker_name'],
            $rightCol1, $rightCol2, 14, $signers['approver_name'],
            $merges
        );

        $rowIndex++;
        $sigRow3 = $rowIndex;
        // 4. Signer Role/Title Row
        $xml .= self::renderMergedSignatureRow(
            $sigRow3, 16,
            $leftCol1, $leftCol2, 15, __($signers['maker_title']),
            $rightCol1, $rightCol2, 15, __($signers['approver_title']),
            $merges
        );

        $xml .= '</sheetData>';

        if (!empty($merges)) {
            $xml .= '<mergeCells count="' . count($merges) . '">';
            foreach ($merges as $m) {
                $xml .= '<mergeCell ref="' . $m . '"/>';
            }
            $xml .= '</mergeCells>';
        }

        $xml .= '</worksheet>';
        return $xml;
    }

    protected static function c(string $cell, int $style, string $value): string
    {
        $escaped = htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        return '<c r="' . $cell . '" s="' . $style . '" t="inlineStr"><is><t>' . $escaped . '</t></is></c>';
    }

    protected static function renderMergedSignatureRow(
        int $rowNum, int $height,
        string $leftCol1, string $leftCol2, int $leftStyle, ?string $leftVal,
        string $rightCol1, string $rightCol2, int $rightStyle, ?string $rightVal,
        array &$merges
    ): string {
        $xml = '<row r="' . $rowNum . '" ht="' . $height . '" customHeight="1">';

        $leftStart = self::colIndex($leftCol1);
        $leftEnd = self::colIndex($leftCol2);
        for ($c = $leftStart; $c <= $leftEnd; $c++) {
            $colLetter = self::colLetter($c);
            $cellRef = "{$colLetter}{$rowNum}";
            $val = ($c === $leftStart && $leftVal !== null) ? $leftVal : '';
            $xml .= self::c($cellRef, $leftStyle, $val);
        }
        $merges[] = "{$leftCol1}{$rowNum}:{$leftCol2}{$rowNum}";

        $rightStart = self::colIndex($rightCol1);
        $rightEnd = self::colIndex($rightCol2);
        for ($c = $rightStart; $c <= $rightEnd; $c++) {
            $colLetter = self::colLetter($c);
            $cellRef = "{$colLetter}{$rowNum}";
            $val = ($c === $rightStart && $rightVal !== null) ? $rightVal : '';
            $xml .= self::c($cellRef, $rightStyle, $val);
        }
        $merges[] = "{$rightCol1}{$rowNum}:{$rightCol2}{$rowNum}";

        $xml .= '</row>';
        return $xml;
    }

    protected static function colIndex(string $col): int
    {
        $col = strtoupper(trim($col));
        $len = strlen($col);
        $idx = 0;
        for ($i = 0; $i < $len; $i++) {
            $idx = $idx * 26 + (ord($col[$i]) - ord('A') + 1);
        }
        return $idx;
    }

    protected static function colLetter(int $colIndex): string
    {
        $letter = '';
        while ($colIndex > 0) {
            $mod = ($colIndex - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $colIndex = intval(($colIndex - $mod) / 26);
        }
        return $letter;
    }

    public static function cleanShiftCode(?string $rawCode): string
    {
        $clean = strtoupper(trim(str_replace(['SH-', 'SHIFT-'], '', $rawCode ?? '-')));
        if ($clean === 'OFF' || $clean === '-' || $clean === '') {
            return $clean ?: '-';
        }
        if ($clean === 'REGULER') return 'REG';
        if ($clean === 'SIANG') return 'SIA';
        if ($clean === 'PAGI') return 'PAG';
        if ($clean === 'MALAM') return 'MLM';
        return strlen($clean) > 4 ? substr($clean, 0, 3) : $clean;
    }
}

