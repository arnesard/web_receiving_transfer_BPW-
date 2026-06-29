<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ProductionMonthlySheet implements FromArray, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    protected $group;
    protected $receptions;
    protected $year;
    protected $rowMeta      = [];
    protected $cachedMonths = null;

    protected $groupColors = [
        'A' => '4472C4', 'B' => '70AD47',
        'C' => 'ED7D31', 'D' => 'FFC000',
    ];
    protected $groupTextColors = [
        'A' => 'FFFFFF', 'B' => 'FFFFFF',
        'C' => 'FFFFFF', 'D' => '000000',
    ];
    protected $bulanNames = [
        1=>'JANUARI', 2=>'FEBRUARI', 3=>'MARET', 4=>'APRIL',
        5=>'MEI', 6=>'JUNI', 7=>'JULI', 8=>'AGUSTUS',
        9=>'SEPTEMBER', 10=>'OKTOBER', 11=>'NOVEMBER', 12=>'DESEMBER',
    ];

    const ROW_TITLE    = 1;
    const ROW_SUBTITLE = 2;
    const ROW_EMPTY1   = 3;
    const ROW_PLANT    = 4;
    const ROW_PERIODE  = 5;
    const ROW_EMPTY2   = 6;
    const ROW_HEADER   = 7;
    const ROW_DATA     = 8;

    public function __construct(string $group, $receptions, string $year)
    {
        $this->group      = $group;
        $this->receptions = $receptions;
        $this->year       = $year;
    }

    public function title(): string { return 'GRUP ' . $this->group; }

    protected function getMonthColumns(): array
    {
        if ($this->cachedMonths !== null) return $this->cachedMonths;
        $months = $this->receptions
            ->map(fn($r) => (int)($r->date instanceof Carbon ? $r->date : Carbon::parse($r->date))->format('n'))
            ->unique()->sort()->values()->toArray();
        $this->cachedMonths = empty($months) ? range(1, 12) : $months;
        return $this->cachedMonths;
    }

    public function array(): array
    {
        $months    = $this->getMonthColumns();
        // NO, NAMA, NIP, PEKERJAAN + bulan + TOTAL + AVG (ACTION dihapus)
        $totalCols = 4 + count($months) + 2;
        $emptyRow  = fn() => array_fill(0, $totalCols, '');

        $pivot   = [];
        $empInfo = [];

        foreach ($this->receptions as $r) {
            $empId = $r->employee_id;
            $job   = $r->job_today ?? 'Lainnya';
            $month = (int)($r->date instanceof Carbon ? $r->date : Carbon::parse($r->date))->format('n');
            $prod  = (int)($r->production_count ?? 0);

            if (!isset($pivot[$empId][$job][$month])) {
                $pivot[$empId][$job][$month] = ['total' => 0, 'days' => 0];
            }
            $pivot[$empId][$job][$month]['total'] += $prod;
            $pivot[$empId][$job][$month]['days']  += 1;

            if (!isset($empInfo[$empId])) {
                $empInfo[$empId] = [
                    'name'  => strtoupper($r->emp_name ?? 'Unknown'),
                    'nip'   => $empId,
                    'plant' => $r->emp_plant ?? '-',
                ];
            }
        }

        // Ranking per operator berdasarkan grand avg semua job
        $ranked = [];
        foreach ($pivot as $empId => $jobs) {
            $totalAvg = 0; $totalMonths = 0;
            foreach ($jobs as $job => $monthData) {
                foreach ($months as $m) {
                    if (isset($monthData[$m]) && $monthData[$m]['days'] > 0) {
                        $totalAvg += $monthData[$m]['total'] / $monthData[$m]['days'];
                        $totalMonths++;
                    }
                }
            }
            $ranked[$empId] = $totalMonths > 0 ? $totalAvg / $totalMonths : 0;
        }
        arsort($ranked);

        $plantName = !empty($empInfo) ? (reset($empInfo)['plant'] ?? '-') : '-';

        $rows = [];

        $row1 = $emptyRow(); $row1[0] = 'LAPORAN REKAP BULANAN OPERATOR';
        $rows[] = $row1;

        $row2 = $emptyRow(); $row2[0] = 'BAGIAN PENERIMAAN PRODUKSI';
        $rows[] = $row2;

        $rows[] = $emptyRow();

        $row4 = $emptyRow(); $row4[0] = 'Plant : ' . $plantName;
        $rows[] = $row4;

        $row5 = $emptyRow(); $row5[0] = 'Group : ' . $this->group . '          Tahun : ' . $this->year;
        $rows[] = $row5;

        $rows[] = $emptyRow();

        // Header — tanpa ACTION
        $header = ['NO', 'NAMA', 'NIP', 'PEKERJAAN'];
        foreach ($months as $m) { $header[] = $this->bulanNames[$m] ?? 'BLN'.$m; }
        $header[] = 'TOTAL AVG';
        $header[] = 'RATA-RATA';
        $rows[]   = $header;

        $rowNum = self::ROW_DATA;
        $this->rowMeta = [];

        if (empty($pivot)) {
            $empty = $emptyRow(); $empty[1] = 'Tidak ada data untuk grup ini';
            $rows[] = $empty;
            return $rows;
        }

        $no = 1;
        foreach (array_keys($ranked) as $empId) {
            $jobs    = $pivot[$empId];
            $info    = $empInfo[$empId];
            $isFirst = true;

            foreach ($jobs as $job => $monthData) {
                $jobTotalAvg = 0; $jobMonths = 0;

                // Tanpa ACTION
                $row = $isFirst
                    ? [$no, $info['name'], $info['nip'], $job]
                    : ['',  '',            '',           $job];

                foreach ($months as $m) {
                    if (isset($monthData[$m]) && $monthData[$m]['days'] > 0) {
                        $avg   = $monthData[$m]['total'] / $monthData[$m]['days'];
                        $row[] = (int) round($avg);
                        $jobTotalAvg += $avg; $jobMonths++;
                    } else {
                        $row[] = '';
                    }
                }

                $row[] = $jobTotalAvg > 0 ? (int) round($jobTotalAvg) : '';
                $row[] = $jobMonths   > 0 ? (int) round($jobTotalAvg / $jobMonths) : '';

                $rows[]          = $row;
                $this->rowMeta[] = ['row' => $rowNum, 'isFirst' => $isFirst, 'no' => $no];
                $rowNum++;
                $isFirst = false;
            }
            $no++;
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        return ['A' => 5, 'B' => 28, 'C' => 11, 'D' => 22];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet) { return []; }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet    = $event->sheet->getDelegate();
                $bgColor  = $this->groupColors[$this->group]     ?? '4472C4';
                $txtColor = $this->groupTextColors[$this->group] ?? 'FFFFFF';
                $months   = $this->getMonthColumns();
                $totalCols = 4 + count($months) + 2;
                $lastCol   = Coordinate::stringFromColumnIndex($totalCols);
                $lastRow   = $sheet->getHighestRow();

                foreach ([1, 2] as $r) {
                    $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                    $sheet->getStyle("A{$r}")->applyFromArray([
                        'font'      => ['bold' => true, 'size' => $r === 1 ? 14 : 11, 'name' => 'Arial'],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $sheet->getRowDimension($r)->setRowHeight($r === 1 ? 26 : 18);
                }

                $sheet->getRowDimension(3)->setRowHeight(8);

                foreach ([4, 5] as $r) {
                    $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                    $sheet->getStyle("A{$r}")->applyFromArray([
                        'font'      => ['bold' => true, 'size' => 11, 'name' => 'Arial'],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EDEDED']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
                    ]);
                    $sheet->getRowDimension($r)->setRowHeight(18);
                }

                $sheet->getRowDimension(6)->setRowHeight(6);

                $sheet->getStyle("A7:{$lastCol}7")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 9, 'name' => 'Arial', 'color' => ['rgb' => $txtColor]],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                ]);
                $sheet->getRowDimension(7)->setRowHeight(20);

                if ($lastRow >= self::ROW_DATA) {
                    $sheet->getStyle('A' . self::ROW_DATA . ":{$lastCol}{$lastRow}")->applyFromArray([
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                        'font'      => ['size' => 9, 'name' => 'Arial'],
                    ]);

                    $monthStartLetter = Coordinate::stringFromColumnIndex(5);
                    $sheet->getStyle($monthStartLetter . self::ROW_DATA . ":{$lastCol}{$lastRow}")->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);

                    $sheet->getStyle('A' . self::ROW_DATA . ":A{$lastRow}")->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);

                    $isEven = false;
                    for ($i = 0; $i < count($this->rowMeta); $i++) {
                        $meta     = $this->rowMeta[$i];
                        $nextMeta = $this->rowMeta[$i + 1] ?? null;
                        if ($meta['isFirst']) $isEven = !$isEven;
                        if ($isEven) {
                            $sheet->getStyle("A{$meta['row']}:{$lastCol}{$meta['row']}")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
                            ]);
                        }
                        if (!$nextMeta || $nextMeta['isFirst']) {
                            $sheet->getStyle("A{$meta['row']}:{$lastCol}{$meta['row']}")->applyFromArray([
                                'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '888888']]],
                            ]);
                        }
                    }

                    $medals = ['FFD700', 'C0C0C0', 'CD7F32'];
                    foreach ($this->rowMeta as $meta) {
                        if ($meta['isFirst'] && $meta['no'] <= 3) {
                            $sheet->getStyle("A{$meta['row']}")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $medals[$meta['no'] - 1]]],
                                'font' => ['bold' => true],
                            ]);
                        }
                    }

                    $totalColLetter = Coordinate::stringFromColumnIndex($totalCols - 1);
                    $sheet->getStyle("{$totalColLetter}" . self::ROW_DATA . ":{$totalColLetter}{$lastRow}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2EFDA']],
                    ]);
                    $sheet->getStyle("{$lastCol}" . self::ROW_DATA . ":{$lastCol}{$lastRow}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DAE3F3']],
                    ]);
                }

                for ($c = 5; $c <= 4 + count($months); $c++) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setWidth(12);
                }
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($totalCols - 1))->setWidth(12);
                $sheet->getColumnDimension($lastCol)->setWidth(12);

                $sheet->freezePane('E8');
            },
        ];
    }
}
