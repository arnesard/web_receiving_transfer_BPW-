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

class ProductionGroupSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    protected $group;
    protected $receptions;
    protected $filterType;
    protected $startDate;
    protected $endDate;
    protected $startMonth;
    protected $endMonth;
    protected $year;

    protected $cachedDates = null;
    protected $rowMeta     = [];

    const ROW_TITLE    = 1;
    const ROW_SUBTITLE = 2;
    const ROW_EMPTY1   = 3;
    const ROW_PLANT    = 4; // ← BARU
    const ROW_PERIODE  = 5;
    const ROW_EMPTY2   = 6;
    const ROW_HEADER   = 7;
    const ROW_DATA     = 8;

    protected $groupColors = [
        'A' => '4472C4',
        'B' => '70AD47',
        'C' => 'ED7D31',
        'D' => 'FFC000',
    ];

    protected $groupTextColors = [
        'A' => 'FFFFFF',
        'B' => 'FFFFFF',
        'C' => 'FFFFFF',
        'D' => '000000',
    ];

    public function __construct(
        string $group,
        $receptions,
        string $filterType,
        ?string $startDate,
        ?string $endDate,
        ?string $startMonth,
        ?string $endMonth,
        ?string $year
    ) {
        $this->group      = $group;
        $this->receptions = $receptions;
        $this->filterType = $filterType;
        $this->startDate  = $startDate;
        $this->endDate    = $endDate;
        $this->startMonth = $startMonth;
        $this->endMonth   = $endMonth;
        $this->year       = $year;
    }

    public function title(): string
    {
        return 'GRUP ' . $this->group;
    }

    protected function getDateColumns(): array
    {
        if ($this->cachedDates !== null) {
            return $this->cachedDates;
        }

        if ($this->filterType === 'monthly' && $this->startMonth && $this->startMonth === $this->endMonth) {
            $start = Carbon::parse($this->startMonth . '-01');
            $dates = [];
            for ($d = 1; $d <= $start->daysInMonth; $d++) {
                $dates[] = $start->copy()->day($d)->format('Y-m-d');
            }
            $this->cachedDates = $dates;
            return $this->cachedDates;
        }

        if ($this->filterType === 'daily' && $this->startDate && $this->endDate) {
            $cursor = Carbon::parse($this->startDate);
            $end    = Carbon::parse($this->endDate);
            $dates  = [];
            while ($cursor->lte($end)) {
                $dates[] = $cursor->format('Y-m-d');
                $cursor->addDay();
            }
            $this->cachedDates = $dates;
            return $this->cachedDates;
        }

        $this->cachedDates = $this->receptions
            ->map(fn($r) => ($r->date instanceof Carbon ? $r->date : Carbon::parse($r->date))->format('Y-m-d'))
            ->unique()->sort()->values()->toArray();

        return $this->cachedDates;
    }

    protected function getPeriodeLabel(): string
    {
        if ($this->filterType === 'monthly') {
            $s = Carbon::parse(($this->startMonth ?? date('Y-m')) . '-01');
            $e = Carbon::parse(($this->endMonth   ?? date('Y-m')) . '-01');
            return $this->startMonth === $this->endMonth
                ? $s->translatedFormat('F Y')
                : $s->translatedFormat('F Y') . ' s/d ' . $e->translatedFormat('F Y');
        }
        if ($this->filterType === 'yearly') {
            return 'Tahun ' . ($this->year ?? date('Y'));
        }
        return ($this->startDate ?? '-') . ' s/d ' . ($this->endDate ?? '-');
    }

    public function array(): array
    {
        $dates     = $this->getDateColumns();
        $periode   = $this->getPeriodeLabel();
        $totalCols = 5 + count($dates) + 2;
        $emptyRow  = fn() => array_fill(0, $totalCols, '');

        // Pivot data
        $pivot   = [];
        $empInfo = [];

        foreach ($this->receptions as $r) {
            $empId = $r->employee_id;
            $job   = $r->job_today ?? 'Lainnya';
            $date  = ($r->date instanceof Carbon ? $r->date : Carbon::parse($r->date))->format('Y-m-d');
            $prod  = (int) ($r->production_count ?? 0);

            $pivot[$empId][$job][$date] = ($pivot[$empId][$job][$date] ?? 0) + $prod;

            if (!isset($empInfo[$empId])) {
                $empInfo[$empId] = [
                    'name'  => strtoupper($r->emp_name ?? 'Unknown'),
                    'nip'   => $empId,
                    'plant' => $r->emp_plant ?? '-',
                ];
            }
        }

        // Ambil plant dari data (semua operator di grup ini harusnya plant sama)
        $plantName = !empty($empInfo) ? (reset($empInfo)['plant'] ?? '-') : '-';

        $rows = [];

        // ROW 1: Judul
        $row1    = $emptyRow();
        $row1[0] = 'LAPORAN REKAP PRODUKSI OPERATOR';
        $rows[]  = $row1;

        // ROW 2: Sub judul
        $row2    = $emptyRow();
        $row2[0] = 'BAGIAN PENERIMAAN PRODUKSI';
        $rows[]  = $row2;

        // ROW 3: Kosong
        $rows[] = $emptyRow();

        // ROW 4: Plant
        $row4    = $emptyRow();
        $row4[0] = 'Plant : ' . $plantName;
        $rows[]  = $row4;

        // ROW 5: Group & Periode
        $row5    = $emptyRow();
        $row5[0] = 'Group : ' . $this->group . '          Periode : ' . $periode;
        $rows[]  = $row5;

        // ROW 6: Kosong
        $rows[] = $emptyRow();

        // ROW 7: Header kolom
        $header = ['NO', 'NAMA', 'NIP', 'PLANT', 'PEKERJAAN'];
        foreach ($dates as $d) {
            $header[] = (int) Carbon::parse($d)->format('j');
        }
        $header[] = 'TOTAL';
        $header[] = 'RATA-RATA';
        $rows[]   = $header;

        // ROW 8+: Data operator
        $no            = 1;
        $rowNum        = self::ROW_DATA;
        $this->rowMeta = [];

        if (empty($pivot)) {
            $empty    = $emptyRow();
            $empty[1] = 'Tidak ada data untuk grup ini';
            $rows[]   = $empty;
            return $rows;
        }

        foreach ($pivot as $empId => $jobs) {
            $info    = $empInfo[$empId];
            $isFirst = true;

            foreach ($jobs as $job => $dateData) {
                $row = $isFirst
                    ? [$no, $info['name'], $info['nip'], $info['plant'], $job]
                    : ['',  '',            '',           '',             $job];

                $total   = 0;
                $hasDays = 0;
                foreach ($dates as $date) {
                    $val   = $dateData[$date] ?? 0;
                    $row[] = $val > 0 ? $val : '';
                    if ($val > 0) {
                        $total += $val;
                        $hasDays++;
                    }
                }
                $row[] = $total   > 0 ? $total : '';
                $row[] = $hasDays > 0 ? (int) round($total / $hasDays) : '';

                $rows[]          = $row;
                $this->rowMeta[] = [
                    'row'     => $rowNum,
                    'isFirst' => $isFirst,
                    'no'      => $no,
                ];
                $rowNum++;
                $isFirst = false;
            }
            $no++;
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 28,
            'C' => 11,
            'D' => 7,
            'E' => 24,
        ];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet    = $event->sheet->getDelegate();
                $bgColor  = $this->groupColors[$this->group]     ?? '4472C4';
                $txtColor = $this->groupTextColors[$this->group] ?? 'FFFFFF';
                $dates    = $this->getDateColumns();

                $totalCols = 5 + count($dates) + 2;
                $lastCol   = Coordinate::stringFromColumnIndex($totalCols);
                $lastRow   = $sheet->getHighestRow();

                // ── ROW 1: Judul
                $sheet->mergeCells('A1:' . $lastCol . '1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14, 'name' => 'Arial'],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(26);

                // ── ROW 2: Sub judul
                $sheet->mergeCells('A2:' . $lastCol . '2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 11, 'name' => 'Arial'],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(18);

                // ── ROW 3: Kosong kecil
                $sheet->getRowDimension(3)->setRowHeight(8);

                // ── ROW 4: Plant
                $sheet->mergeCells('A4:' . $lastCol . '4');
                $sheet->getStyle('A4')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 11, 'name' => 'Arial'],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EDEDED']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
                ]);
                $sheet->getRowDimension(4)->setRowHeight(18);

                // ── ROW 5: Group & Periode
                $sheet->mergeCells('A5:' . $lastCol . '5');
                $sheet->getStyle('A5')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 11, 'name' => 'Arial'],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EDEDED']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
                ]);
                $sheet->getRowDimension(5)->setRowHeight(18);

                // ── ROW 6: Kosong kecil
                $sheet->getRowDimension(6)->setRowHeight(6);

                // ── ROW 7: Header kolom
                $sheet->getStyle('A7:' . $lastCol . '7')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 9, 'name' => 'Arial', 'color' => ['rgb' => $txtColor]],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
                    ],
                ]);
                $sheet->getRowDimension(7)->setRowHeight(20);

                // ── ROW 8+: Data
                if ($lastRow >= self::ROW_DATA) {
                    $sheet->getStyle('A' . self::ROW_DATA . ':' . $lastCol . $lastRow)->applyFromArray([
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                        'font'      => ['size' => 9, 'name' => 'Arial'],
                    ]);

                    // Center kolom tanggal + total + avg
                    $dateStartLetter = Coordinate::stringFromColumnIndex(6);
                    $sheet->getStyle($dateStartLetter . self::ROW_DATA . ':' . $lastCol . $lastRow)->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);

                    // Kolom NO center
                    $sheet->getStyle('A' . self::ROW_DATA . ':A' . $lastRow)->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);

                    // Alternating color per operator
                    $isEven = false;
                    foreach ($this->rowMeta as $meta) {
                        if ($meta['isFirst']) {
                            $isEven = !$isEven;
                        }
                        if ($isEven) {
                            $sheet->getStyle('A' . $meta['row'] . ':' . $lastCol . $meta['row'])->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
                            ]);
                        }
                    }

                    // Border medium di baris terakhir tiap operator
                    for ($i = 0; $i < count($this->rowMeta); $i++) {
                        $meta     = $this->rowMeta[$i];
                        $nextMeta = $this->rowMeta[$i + 1] ?? null;
                        if (!$nextMeta || $nextMeta['isFirst']) {
                            $sheet->getStyle('A' . $meta['row'] . ':' . $lastCol . $meta['row'])->applyFromArray([
                                'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '888888']]],
                            ]);
                        }
                    }

                    // Kolom TOTAL: bold + hijau muda
                    $totalColLetter = Coordinate::stringFromColumnIndex($totalCols - 1);
                    $sheet->getStyle($totalColLetter . self::ROW_DATA . ':' . $totalColLetter . $lastRow)->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2EFDA']],
                    ]);

                    // Kolom RATA-RATA: biru muda
                    $sheet->getStyle($lastCol . self::ROW_DATA . ':' . $lastCol . $lastRow)->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DAE3F3']],
                    ]);
                }

                // ── Lebar kolom tanggal
                for ($c = 6; $c <= 5 + count($dates); $c++) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setWidth(5.5);
                }
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($totalCols - 1))->setWidth(10);
                $sheet->getColumnDimension($lastCol)->setWidth(10);

                // ── Freeze: kolom A–E + baris 1–7 tetap saat scroll
                $sheet->freezePane('F8');
            },
        ];
    }
}
