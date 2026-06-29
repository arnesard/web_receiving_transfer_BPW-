<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Exportable;

class GroupRankingExport implements WithMultipleSheets
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
        // 1 sheet per plant
        $plants = $this->receptions->pluck('emp_plant')->unique()->filter()->sort()->values();

        $sheets = [];
        foreach ($plants as $plant) {
            $plantData = $this->receptions->filter(fn($r) => ($r->emp_plant ?? '') === $plant);
            $sheets[]  = new GroupRankingSheet($plant, $plantData, $this->year);
        }

        return $sheets;
    }
}
