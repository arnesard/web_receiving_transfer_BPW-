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

class GroupRankingSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    protected $plant;
    protected $receptions;
    protected $year;
    protected $rowMeta      = [];
    protected $cachedMonths = null;

    protected $bulanNames = [
        1=>'JANUARI', 2=>'FEBRUARI', 3=>'MARET', 4=>'APRIL',
        5=>'MEI', 6=>'JUNI', 7=>'JULI', 8=>'AGUSTUS',
        9=>'SEPTEMBER', 10=>'OKTOBER', 11=>'NOVEMBER', 12=>'DESEMBER',
    ];

    // Warna per grup untuk kolom NO
    protected $groupColors = [
        'A' => 'DAEEF3', 'B' => 'EBF1DE',
        'C' => 'FDE9D9', 'D' => 'FFF2CC',
    ];

    const ROW_TITLE    = 1;
    const ROW_SUBTITLE = 2;
    const ROW_EMPTY1   = 3;
    const ROW_PLANT    = 4;
    const ROW_PERIODE  = 5;
    const ROW_EMPTY2   = 6;
    const ROW_HEADER   = 7;
    const ROW_DATA     = 8;

    public function __construct(string $plant, $receptions, string $year)
    {
        $this->plant      = $plant;
        $this->receptions = $receptions;
        $this->year       = $year;
    }

    public function title(): string { return 'PLANT ' . $this->plant; }

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
        // NO, GROUP, NAMA LEADER, NIP LEADER + bulan + TOTAL + AVG
        $totalCols = 4 + count($months) + 2;
        $emptyRow  = fn() => array_fill(0, $totalCols, '');

        // Pivot: [group][month] = total produksi seluruh anggota grup
        // Leader: [group] = {name, nip} — dari default_status/primary_job_type = 'Leader'
        $groupPivot  = [];
        $groupLeader = [];

        foreach ($this->receptions as $r) {
            $group = $r->emp_group ?? '-';
            $month = (int)($r->date instanceof Carbon ? $r->date : Carbon::parse($r->date))->format('n');
            $prod  = (int)($r->production_count ?? 0);

            if (!isset($groupPivot[$group][$month])) {
                $groupPivot[$group][$month] = 0;
            }
            $groupPivot[$group][$month] += $prod;

            // Tandai sebagai leader kalau default_status atau primary_job_type = 'Leader'
            $isLeader = strtolower($r->emp_default_status ?? '') === 'leader'
                     || strtolower($r->emp_primary_job_type ?? '') === 'leader';

            if ($isLeader && !isset($groupLeader[$group])) {
                $groupLeader[$group] = [
                    'name' => strtoupper($r->emp_name ?? 'Unknown'),
                    'nip'  => $r->employee_id,
                ];
            }
        }

        // Hitung total per group untuk ranking
        $ranked = [];
        foreach ($groupPivot as $group => $monthData) {
            $ranked[$group] = array_sum($monthData);
        }
        arsort($ranked);

        $rows = [];

        $row1 = $emptyRow(); $row1[0] = 'RANKING GRUP PER PLANT';
        $rows[] = $row1;

        $row2 = $emptyRow(); $row2[0] = 'BAGIAN PENERIMAAN PRODUKSI';
        $rows[] = $row2;

        $rows[] = $emptyRow();

        $row4 = $emptyRow(); $row4[0] = 'Plant : ' . $this->plant;
        $rows[] = $row4;

        $row5 = $emptyRow(); $row5[0] = 'Tahun : ' . $this->year;
        $rows[] = $row5;

        $rows[] = $emptyRow();

        // Header
        $header = ['RANK', 'GROUP', 'NAMA LEADER', 'NIP LEADER'];
        foreach ($months as $m) { $header[] = $this->bulanNames[$m] ?? 'BLN'.$m; }
        $header[] = 'TOTAL';
        $header[] = 'RATA-RATA';
        $rows[]   = $header;

        $rowNum        = self::ROW_DATA;
        $this->rowMeta = [];

        if (empty($ranked)) {
            $empty = $emptyRow(); $empty[1] = 'Tidak ada data untuk plant ini';
            $rows[] = $empty;
            return $rows;
        }

        $rank = 1;
        foreach (array_keys($ranked) as $group) {
            $monthData = $groupPivot[$group];
            $leader    = $groupLeader[$group] ?? ['name' => '-', 'nip' => '-'];

            $row   = [$rank, 'GRUP ' . $group, $leader['name'], $leader['nip']];
            $total = 0;
            $count = 0;

            foreach ($months as $m) {
                $val   = $monthData[$m] ?? 0;
                $row[] = $val > 0 ? $val : '';
                if ($val > 0) { $total += $val; $count++; }
            }

            $row[] = $total > 0 ? $total : '';
            $row[] = $count > 0 ? (int) round($total / $count) : '';

            $rows[]          = $row;
            $this->rowMeta[] = ['row' => $rowNum, 'group' => $group, 'rank' => $rank];
            $rowNum++;
            $rank++;
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        return ['A' => 6, 'B' => 10, 'C' => 28, 'D' => 12];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet) { return []; }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet    = $event->sheet->getDelegate();
                $months   = $this->getMonthColumns();
                $totalCols = 4 + count($months) + 2;
                $lastCol   = Coordinate::stringFromColumnIndex($totalCols);
                $lastRow   = $sheet->getHighestRow();

                // Header judul
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

                // Header kolom — warna navy
                $sheet->getStyle("A7:{$lastCol}7")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 9, 'name' => 'Arial', 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '243F60']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                ]);
                $sheet->getRowDimension(7)->setRowHeight(20);

                if ($lastRow >= self::ROW_DATA) {
                    $sheet->getStyle('A' . self::ROW_DATA . ":{$lastCol}{$lastRow}")->applyFromArray([
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                        'font'      => ['size' => 10, 'name' => 'Arial'],
                    ]);

                    // Center kolom bulan + total + avg
                    $monthStartLetter = Coordinate::stringFromColumnIndex(5);
                    $sheet->getStyle($monthStartLetter . self::ROW_DATA . ":{$lastCol}{$lastRow}")->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);

                    // Center kolom RANK & GROUP
                    $sheet->getStyle('A' . self::ROW_DATA . ":B{$lastRow}")->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        'font'      => ['bold' => true],
                    ]);

                    // Warna baris per group + medal rank 1-3
                    $medals = ['FFD700', 'C0C0C0', 'CD7F32'];
                    foreach ($this->rowMeta as $meta) {
                        // Warna baris sesuai group
                        $groupBg = $this->groupColors[$meta['group']] ?? 'FFFFFF';
                        $sheet->getStyle("A{$meta['row']}:{$lastCol}{$meta['row']}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $groupBg]],
                        ]);

                        // Medal di kolom RANK
                        if ($meta['rank'] <= 3) {
                            $sheet->getStyle("A{$meta['row']}")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $medals[$meta['rank'] - 1]]],
                                'font' => ['bold' => true, 'size' => 11],
                            ]);
                        }

                        // Border tebal per baris
                        $sheet->getStyle("A{$meta['row']}:{$lastCol}{$meta['row']}")->applyFromArray([
                            'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'AAAAAA']]],
                        ]);
                    }

                    // Kolom TOTAL bold + hijau
                    $totalColLetter = Coordinate::stringFromColumnIndex($totalCols - 1);
                    $sheet->getStyle("{$totalColLetter}" . self::ROW_DATA . ":{$totalColLetter}{$lastRow}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2EFDA']],
                    ]);

                    // Kolom AVG biru
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
