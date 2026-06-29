<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Exportable;

class ProductionExport implements WithMultipleSheets
{
    use Exportable;

    protected $receptions;
    protected $filterType;
    protected $startDate;
    protected $endDate;
    protected $startMonth;
    protected $endMonth;
    protected $year;

    public function __construct(
        $receptions,
        string $filterType  = 'monthly',
        ?string $startDate  = null,
        ?string $endDate    = null,
        ?string $startMonth = null,
        ?string $endMonth   = null,
        ?string $year       = null
    ) {
        $this->receptions  = $receptions;
        $this->filterType  = $filterType;
        $this->startDate   = $startDate;
        $this->endDate     = $endDate;
        $this->startMonth  = $startMonth;
        $this->endMonth    = $endMonth;
        $this->year        = $year;
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach (['A', 'B', 'C', 'D'] as $group) {
            $groupData = $this->receptions->filter(
                fn($r) => ($r->emp_group ?? '') === $group
            );

            $sheets[] = new ProductionGroupSheet(
                $group,
                $groupData,
                $this->filterType,
                $this->startDate,
                $this->endDate,
                $this->startMonth,
                $this->endMonth,
                $this->year
            );
        }

        return $sheets;
    }
}
