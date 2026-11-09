<?php

namespace App\Services\Export;

use ZipArchive;

class AttendanceXlsxService
{
    public static function generate(
        array $rows,
        string $companyName,
        string $dateStr,
        ?string $deptName,
        string $printedAt,
        string $makerName,
        string $makerTitle,
        string $approverName,
        string $approverTitle,
        int $present,
        int $late,
        int $onLeave,
        int $absent
    ): string {
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');

        $zip = new ZipArchive();
        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("Tidak dapat membuat file arsip Excel.");
        }

        // 1. [Content_Types].xml
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);

        // 2. _rels/.rels
        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';
        $zip->addFromString('_rels/.rels', $rels);

        // 3. xl/_rels/workbook.xml.rels
        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>';
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);

        // 4. xl/workbook.xml
        $sheetName = (app()->getLocale() === 'ar') ? 'سجل الحضور اليومي' : 'Rekap Presensi Harian';
        $wb = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="' . $sheetName . '" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>';
        $zip->addFromString('xl/workbook.xml', $wb);

        // 5. xl/styles.xml
        $styles = self::buildStylesXml();
        $zip->addFromString('xl/styles.xml', $styles);

        // 6. xl/worksheets/sheet1.xml
        $sheetXml = self::buildSheetXml(
            $rows, $companyName, $dateStr, $deptName, $printedAt, $makerName, $makerTitle, $approverName, $approverTitle, $present, $late, $onLeave, $absent
        );
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);

        $zip->close();

        return $tempFile;
    }

    protected static function buildStylesXml(): string
    {
        $isArabic = (app()->getLocale() === 'ar');
        $textAlign = $isArabic ? 'right' : 'left';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="9">
    <font><name val="Calibri"/><sz val="11"/><color rgb="FF000000"/></font>
    <font><b/><name val="Calibri"/><sz val="11"/><color rgb="FF000000"/></font>
    <font><b/><name val="Calibri"/><sz val="14"/><color rgb="FF1E3A8A"/></font>
    <font><b/><name val="Calibri"/><sz val="10"/><color rgb="FFFFFFFF"/></font>
    <font><name val="Calibri"/><sz val="10"/><color rgb="FF64748B"/></font>
    <font><b/><name val="Calibri"/><sz val="10"/><color rgb="FF15803D"/></font>
    <font><b/><name val="Calibri"/><sz val="10"/><color rgb="FFB45309"/></font>
    <font><b/><name val="Calibri"/><sz val="10"/><color rgb="FF1D4ED8"/></font>
    <font><b/><name val="Calibri"/><sz val="10"/><color rgb="FFB91C1C"/></font>
  </fonts>
  <fills count="8">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF1E293B"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFDCFCE7"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFFEF3C7"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFDBEAFE"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFFEE2E2"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/></patternFill></fill>
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
  <cellXfs count="16">
    <!-- 0: Default Normal (Summary text, wrapText enabled) -->
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"><alignment horizontal="' . $textAlign . '" vertical="center" wrapText="1"/></xf>
    <!-- 1: Company Title -->
    <xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0"><alignment horizontal="center" vertical="center"/></xf>
    <!-- 2: Subtitle -->
    <xf numFmtId="0" fontId="4" fillId="0" borderId="0" xfId="0"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <!-- 3: Table Header (Dark Blue/Slate, white text, bold) -->
    <xf numFmtId="0" fontId="3" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <!-- 4: Table Data Text (Border thin) -->
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"><alignment horizontal="' . $textAlign . '" vertical="center"/></xf>
    <!-- 5: Table Data Center (Border thin) -->
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>
    <!-- 6: Table Data Bold Text -->
    <xf numFmtId="0" fontId="1" fillId="0" borderId="1" xfId="0" applyBorder="1"><alignment horizontal="' . $textAlign . '" vertical="center"/></xf>
    <!-- 7: Status Present (Green) -->
    <xf numFmtId="0" fontId="5" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>
    <!-- 8: Status Late (Amber) -->
    <xf numFmtId="0" fontId="6" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>
    <!-- 9: Status Leave (Blue) -->
    <xf numFmtId="0" fontId="7" fillId="5" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>
    <!-- 10: Status Absent (Red) -->
    <xf numFmtId="0" fontId="8" fillId="6" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>
    <!-- 11: Table Data Zebra Text -->
    <xf numFmtId="0" fontId="0" fillId="7" borderId="1" xfId="0" applyFill="1" applyBorder="1"><alignment horizontal="' . $textAlign . '" vertical="center"/></xf>
    <!-- 12: Table Data Zebra Center -->
    <xf numFmtId="0" fontId="0" fillId="7" borderId="1" xfId="0" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>
    <!-- 13: Sign Title Bold Center -->
    <xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0"><alignment horizontal="center" vertical="center"/></xf>
    <!-- 14: Sign Name Bold Underline Center -->
    <xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0"><alignment horizontal="center" vertical="center"/></xf>
    <!-- 15: Sign Subtitle / Position Center -->
    <xf numFmtId="0" fontId="4" fillId="0" borderId="0" xfId="0"><alignment horizontal="center" vertical="center"/></xf>
  </cellXfs>
</styleSheet>';
    }

    protected static function buildSheetXml(
        array $rows,
        string $companyName,
        string $dateStr,
        ?string $deptName,
        string $printedAt,
        string $makerName,
        string $makerTitle,
        string $approverName,
        string $approverTitle,
        int $present,
        int $late,
        int $onLeave,
        int $absent
    ): string {
        $isArabic = (app()->getLocale() === 'ar');
        $sheetViewAttr = $isArabic ? 'tabSelected="1" workbookViewId="0" rightToLeft="1"' : 'tabSelected="1" workbookViewId="0"';
        $deptWidth = $isArabic ? 32 : 22;
        $shiftWidth = $isArabic ? 24 : 20;

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheetViews>
    <sheetView ' . $sheetViewAttr . '>
      <pane ySplit="7" topLeftCell="A8" activePane="bottomLeft" state="frozen"/>
    </sheetView>
  </sheetViews>
  <sheetFormatPr defaultRowHeight="20"/>
  <cols>
    <col min="1" max="1" width="6" customWidth="1"/>
    <col min="2" max="2" width="13" customWidth="1"/>
    <col min="3" max="3" width="14" customWidth="1"/>
    <col min="4" max="4" width="26" customWidth="1"/>
    <col min="5" max="5" width="' . $deptWidth . '" customWidth="1"/>
    <col min="6" max="6" width="' . $shiftWidth . '" customWidth="1"/>
    <col min="7" max="7" width="12" customWidth="1"/>
    <col min="8" max="8" width="12" customWidth="1"/>
    <col min="9" max="9" width="15" customWidth="1"/>
    <col min="10" max="10" width="15" customWidth="1"/>
    <col min="11" max="11" width="20" customWidth="1"/>
    <col min="12" max="12" width="24" customWidth="1"/>
  </cols>
  <sheetData>';

        $merges = [];

        // Row 1: Company Title
        $xml .= '<row r="1" ht="24" customHeight="1">';
        $xml .= self::c('A1', 1, strtoupper($companyName));
        $xml .= '</row>';
        $merges[] = 'A1:L1';

        // Row 2: Report Title
        $xml .= '<row r="2" ht="22" customHeight="1">';
        $xml .= self::c('A2', 1, __('LAPORAN REKAPITULASI KEHADIRAN HARIAN'));
        $xml .= '</row>';
        $merges[] = 'A2:L2';

        // Row 3: Subtitle Info
        $sub = __('Tanggal Presensi:') . " {$dateStr}";
        if ($deptName) {
            $sub .= " | " . __('Departemen:') . " " . __($deptName);
        }
        $sub .= " | " . __('Dicetak pada: :time oleh :name', ['time' => $printedAt, 'name' => $makerName]);
        $subHt = mb_strlen($sub, 'UTF-8') > 110 ? 30 : 18;
        $xml .= '<row r="3" ht="' . $subHt . '" customHeight="1">';
        $xml .= self::c('A3', 2, $sub);
        $xml .= '</row>';
        $merges[] = 'A3:L3';

        // Row 4: Empty space
        $xml .= '<row r="4" ht="10" customHeight="1"></row>';

        // Row 5: Ringkasan Presensi (Teks Biasa Sesuai Standar Laporan)
        $totalEmp = $present + $late + $onLeave + $absent;
        $summaryText = __('Ringkasan Presensi:') . "  " . __('Hadir Tepat Waktu:') . " {$present} " . __('Orang') . "   |   " . __('Terlambat:') . " {$late} " . __('Orang') . "   |   " . __('Cuti / Izin:') . " {$onLeave} " . __('Orang') . "   |   " . __('Alpa:') . " {$absent} " . __('Orang') . "   |   " . __('Total:') . " {$totalEmp} " . __('Orang');
        $sumHt = mb_strlen($summaryText, 'UTF-8') > 110 ? 36 : 20;
        $xml .= '<row r="5" ht="' . $sumHt . '" customHeight="1">';
        $xml .= self::c('A5', 0, $summaryText);
        $xml .= '</row>';
        $merges[] = 'A5:L5';

        // Row 6: Empty space
        $xml .= '<row r="6" ht="10" customHeight="1"></row>';

        // Row 7: Table Headers
        $headers = [
            'A7' => __('No'),
            'B7' => __('Tanggal'),
            'C7' => __('ID Karyawan'),
            'D7' => __('Nama Karyawan'),
            'E7' => __('Departemen'),
            'F7' => __('Jadwal Shift'),
            'G7' => __('Jam Masuk'),
            'H7' => __('Jam Pulang'),
            'I7' => __('Terlambat (Mnt)'),
            'J7' => __('Total Jam Kerja'),
            'K7' => __('Status Presensi'),
            'L7' => __('Catatan'),
        ];
        $xml .= '<row r="7" ht="26" customHeight="1">';
        foreach ($headers as $cell => $title) {
            $xml .= self::c($cell, 3, $title);
        }
        $xml .= '</row>';

        // Data Rows
        $rowIndex = 8;
        $no = 1;
        foreach ($rows as $item) {
            $isZebra = ($no % 2 === 0);
            $cLeft = $isZebra ? 11 : 4;
            $cCenter = $isZebra ? 12 : 5;

            $statusStyle = match($item['status']) {
                'present' => 7,
                'late' => 8,
                'on_leave', 'sick', 'permit' => 9,
                default => 10,
            };

            $statusLabel = __($item['status_label'] ?? '-');
            $deptTrans = __($item['department'] ?? '-');
            $shiftTrans = __($item['shift'] ?? '-');
            $notesTrans = ($item['notes'] && $item['notes'] !== '-') ? __($item['notes']) : '-';

            $xml .= '<row r="' . $rowIndex . '" ht="20" customHeight="1">';
            $xml .= self::c("A{$rowIndex}", $cCenter, (string)$no);
            $xml .= self::c("B{$rowIndex}", $cCenter, (string)($item['date'] ?? '-'));
            $xml .= self::c("C{$rowIndex}", $cCenter, (string)($item['code'] ?? '-'));
            $xml .= self::c("D{$rowIndex}", 6, (string)($item['name'] ?? '-'));
            $xml .= self::c("E{$rowIndex}", $cLeft, (string)$deptTrans);
            $xml .= self::c("F{$rowIndex}", $cLeft, (string)$shiftTrans);
            $xml .= self::c("G{$rowIndex}", $cCenter, (string)($item['clock_in'] ?? '--:--'));
            $xml .= self::c("H{$rowIndex}", $cCenter, (string)($item['clock_out'] ?? '--:--'));
            $xml .= self::c("I{$rowIndex}", $cCenter, (string)($item['late'] ?? '-'));
            $xml .= self::c("J{$rowIndex}", $cCenter, (string)($item['work_hours'] ?? '-'));
            $xml .= self::c("K{$rowIndex}", $statusStyle, (string)$statusLabel);
            $xml .= self::c("L{$rowIndex}", $cLeft, (string)$notesTrans);
            $xml .= '</row>';

            $rowIndex++;
            $no++;
        }

        // Space before signature
        $rowIndex++;
        $xml .= '<row r="' . $rowIndex . '" ht="15" customHeight="1"></row>';
        $rowIndex++;

        // Signature Row 1: Title
        $sigRow1 = $rowIndex;
        $xml .= '<row r="' . $sigRow1 . '" ht="18" customHeight="1">';
        $xml .= self::c("B{$sigRow1}", 13, __('Dibuat Oleh:'));
        $xml .= self::c("C{$sigRow1}", 13, "");
        $xml .= self::c("D{$sigRow1}", 13, "");
        $xml .= self::c("I{$sigRow1}", 13, __('Mengetahui & Menyetujui:'));
        $xml .= self::c("J{$sigRow1}", 13, "");
        $xml .= self::c("K{$sigRow1}", 13, "");
        $xml .= '</row>';
        $merges[] = "B{$sigRow1}:D{$sigRow1}";
        $merges[] = "I{$sigRow1}:K{$sigRow1}";

        // Signature Row 2 & 3: Blank height for signature
        $rowIndex++;
        $xml .= '<row r="' . $rowIndex . '" ht="45" customHeight="1"></row>';
        $rowIndex++;

        // Signature Row 4: Name
        $sigRow2 = $rowIndex;
        $xml .= '<row r="' . $sigRow2 . '" ht="18" customHeight="1">';
        $xml .= self::c("B{$sigRow2}", 14, $makerName);
        $xml .= self::c("C{$sigRow2}", 14, "");
        $xml .= self::c("D{$sigRow2}", 14, "");
        $xml .= self::c("I{$sigRow2}", 14, $approverName);
        $xml .= self::c("J{$sigRow2}", 14, "");
        $xml .= self::c("K{$sigRow2}", 14, "");
        $xml .= '</row>';
        $merges[] = "B{$sigRow2}:D{$sigRow2}";
        $merges[] = "I{$sigRow2}:K{$sigRow2}";

        // Signature Row 5: Role Title (using style 15 for center alignment)
        $rowIndex++;
        $sigRow3 = $rowIndex;
        $xml .= '<row r="' . $sigRow3 . '" ht="16" customHeight="1">';
        $xml .= self::c("B{$sigRow3}", 15, __($makerTitle));
        $xml .= self::c("C{$sigRow3}", 15, "");
        $xml .= self::c("D{$sigRow3}", 15, "");
        $xml .= self::c("I{$sigRow3}", 15, __($approverTitle));
        $xml .= self::c("J{$sigRow3}", 15, "");
        $xml .= self::c("K{$sigRow3}", 15, "");
        $xml .= '</row>';
        $merges[] = "B{$sigRow3}:D{$sigRow3}";
        $merges[] = "I{$sigRow3}:K{$sigRow3}";

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
}

