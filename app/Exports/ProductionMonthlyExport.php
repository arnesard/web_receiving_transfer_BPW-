<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Exportable;

class ProductionMonthlyExport implements WithMultipleSheets
{
    use Exportable;

    protected $receptions;
    protected $year;

    public function __construct($receptions, string $year)
    {
        $this->receptions = $receptions;
        $this->year       = $year;
    }

    public function sheets(): array
    {
        $sheets = [];
        foreach (['A', 'B', 'C', 'D'] as $group) {
            $groupData = $this->receptions->filter(fn($r) => ($r->emp_group ?? '') === $group);
            $sheets[]  = new ProductionMonthlySheet($group, $groupData, $this->year);
        }
        return $sheets;
    }
}
