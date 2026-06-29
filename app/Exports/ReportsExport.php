<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReportsExport implements WithMultipleSheets
{
    protected $receptions;
    protected $overtimes;

    public function __construct($receptions, $overtimes)
    {
        $this->receptions = $receptions;
        $this->overtimes = $overtimes;
    }

    public function sheets(): array
    {
        $sheets = [];

      $sheets[] = new ProductionExport($this->receptions, 'monthly');
        $sheets[] = new OvertimeExport($this->overtimes);

        return $sheets;
    }
}
