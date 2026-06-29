<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reception;
use App\Models\Employee;
use App\Exports\ReportsExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $filterType    = $request->get('filter_type', 'daily');
        $shift         = $request->get('shift', '');
        $plant_filter  = $request->get('plant', '');
        $group_filter  = $request->get('group', '');
        $operator_name = trim($request->get('operator_name', ''));
        $job_today     = $request->get('job_today', '');
        $date          = $request->get('date', Carbon::today()->format('Y-m-d'));

        $default_start = Carbon::parse($date)->subDays(7)->format('Y-m-d');
        $start_date    = $request->get('start_date', $default_start);
        $end_date      = $request->get('end_date', $date);

        $month       = $request->get('month', Carbon::now()->format('Y-m'));
        $start_month = $request->get('start_month', $month);
        $end_month   = $request->get('end_month', $month);

        $year = $request->get('year', Carbon::now()->format('Y'));

        $receptions = $this->getFilteredReceptions(
            $filterType, $shift, $plant_filter, $group_filter,
            $job_today, $start_date, $end_date,
            $start_month, $end_month, $year, $operator_name
        );

        $operatorRanking = $receptions->groupBy('emp_plant')
            ->map(function ($plantGroup) {
                return $plantGroup->groupBy('employee_id')
                    ->map(function ($employeeGroup) {
                        return [
                            'name'       => $employeeGroup->first()->emp_name ?? 'Unknown',
                            'production' => $employeeGroup->sum('production_count'),
                            'ritase'     => $employeeGroup->sum('ritase_result'),
                        ];
                    })
                    ->sortByDesc(fn($item) => $item['production'] + $item['ritase'])
                    ->values()->toArray();
            })->toArray();

        $plantRanking = $receptions->groupBy('emp_plant')
            ->map(fn($group, $key) => ['name' => $key ?: 'Unknown', 'count' => $group->sum('production_count')])
            ->sortByDesc('count');

        $groupRanking = $receptions->groupBy('emp_plant')
            ->map(function ($plantGroup) {
                return $plantGroup->groupBy('emp_group')
                    ->map(fn($group, $key) => [
                        'name'       => $key ?: 'Unknown',
                        'production' => $group->sum('production_count'),
                        'ritase'     => $group->sum('ritase_result'),
                    ])->values()->toArray();
            })->toArray();

        $all_employee_names = Cache::remember('all_employee_names', 300, function () {
            return Employee::orderBy('name')->distinct()->pluck('name');
        });

        $all_jobs = Reception::select('job_today')
            ->whereNotNull('job_today')->distinct()->orderBy('job_today')->pluck('job_today');

        return view('reports.index', compact(
            'receptions', 'operatorRanking', 'plantRanking', 'groupRanking',
            'all_employee_names', 'filterType', 'shift', 'plant_filter',
            'group_filter', 'all_jobs', 'operator_name', 'job_today',
            'date', 'start_date', 'end_date', 'month', 'start_month', 'end_month', 'year'
        ));
    }

    private function getFilteredReceptions($filterType, $shift, $plant, $group, $job_today, $start_date, $end_date, $start_month, $end_month, $year, $operator_name = '')
    {
        $query = $this->baseQuery();
        $this->applyDateFilter($query, $filterType, $start_date, $end_date, $start_month, $end_month, $year);
        $this->applyCommonFilters($query, $shift, $plant, $group, $operator_name, $job_today);
        return $query->orderBy('receptions.date', 'desc')->orderBy('receptions.created_at', 'desc')->limit(1000)->get();
    }

    private function getFilteredReceptionsForExport($filterType, $shift, $plant, $group, $job_today, $start_date, $end_date, $start_month, $end_month, $year, $operator_name = '')
    {
        $query = $this->baseQuery();
        $this->applyDateFilter($query, $filterType, $start_date, $end_date, $start_month, $end_month, $year);
        $this->applyCommonFilters($query, $shift, $plant, $group, $operator_name, $job_today);
        return $query->orderBy('receptions.date', 'asc')->orderBy('employees.name', 'asc')->get();
    }

    private function baseQuery()
    {
        return Reception::query()
            ->leftJoin('employees', 'receptions.employee_id', '=', 'employees.employee_id')
            ->select(
                'receptions.id',
                'receptions.employee_id',
                'receptions.shift',
                'receptions.ritase_result',
                'receptions.production_count',
                'receptions.date',
                'receptions.job_today',
                'receptions.notes',
                'receptions.photo',
                'employees.name as emp_name',
                'employees.plant as emp_plant',
                'employees.group as emp_group',
                'employees.default_status as emp_default_status',
                'employees.primary_job_type as emp_primary_job_type'
            );
    }

    private function applyDateFilter($query, $filterType, $start_date, $end_date, $start_month, $end_month, $year)
    {
        if ($filterType === 'daily') {
            $query->whereBetween('receptions.date', [$start_date, $end_date]);
        } elseif ($filterType === 'monthly') {
            $start = Carbon::parse($start_month . '-01')->startOfMonth();
            $end   = Carbon::parse($end_month . '-01')->endOfMonth();
            $query->whereBetween('receptions.date', [$start, $end]);
        } elseif ($filterType === 'yearly') {
            $query->whereYear('receptions.date', $year);
        }
    }

    private function applyCommonFilters($query, $shift, $plant, $group, $operator_name, $job_today)
    {
        if ($shift)  $query->where('receptions.shift', $shift);
        if ($plant)  $query->where('employees.plant', $plant);
        if ($group)  $query->where('employees.group', $group);
        if ($operator_name) {
            $query->where(function ($q) use ($operator_name) {
                $term = '%' . strtolower($operator_name) . '%';
                $q->whereRaw('LOWER(employees.name) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(receptions.employee_id) LIKE ?', [$term]);
            });
        }
        if ($job_today) $query->where('receptions.job_today', 'like', '%' . $job_today . '%');
    }


// ============================================================
// GANTI method exportExcel() di ReportController.php
// ============================================================

public function exportExcel(Request $request)
{
    $exportType    = $request->get('export_type', 'daily');
    $filterType    = $request->get('filter_type', 'daily');
    $shift         = $request->get('shift', '');
    $plant_filter  = $request->get('plant', '');
    $group_filter  = $request->get('group', '');
    $operator_name = trim($request->get('operator_name', ''));
    $job_today     = $request->get('job_today', '');

    $date        = $request->get('date', Carbon::today()->format('Y-m-d'));
    $start_date  = $request->get('start_date', $date);
    $end_date    = $request->get('end_date', $date);

    $month       = $request->get('month', Carbon::now()->format('Y-m'));
    $start_month = $request->get('start_month', $month);
    $end_month   = $request->get('end_month', $month);

    $year = $request->get('year', Carbon::now()->format('Y'));

    // ── Rekap Bulanan per Operator
    if ($exportType === 'monthly_recap') {
        $receptions = $this->getFilteredReceptionsForExport(
            'yearly',
            $shift,
            $plant_filter,  // ← ikut filter plant
            $group_filter,  // ← ikut filter group
            $job_today,
            $start_date, $end_date,
            $start_month, $end_month,
            $year,
            $operator_name
        );
        return Excel::download(
            new \App\Exports\ProductionMonthlyExport($receptions, $year),
            'rekap_bulanan_' . ($plant_filter ?: 'semua') . '_' . ($group_filter ?: 'semua') . '_' . $year . '.xlsx'
        );
    }

    // ── Ranking Grup per Plant
    if ($exportType === 'group_ranking') {
        $receptions = $this->getFilteredReceptionsForExport(
            'yearly',
            $shift,
            $plant_filter,  // ← ikut filter plant
            $group_filter,  // ← ikut filter group
            $job_today,
            $start_date, $end_date,
            $start_month, $end_month,
            $year,
            $operator_name
        );
        return Excel::download(
            new \App\Exports\GroupRankingExport($receptions, $year),
            'ranking_grup_' . ($plant_filter ?: 'semua') . '_' . $year . '.xlsx'
        );
    }

    // ── Rekap Harian (default)
    $receptions = $this->getFilteredReceptionsForExport(
        $filterType,
        $shift,
        $plant_filter,
        $group_filter,
        $job_today,
        $start_date, $end_date,
        $start_month, $end_month,
        $year,
        $operator_name
    );
    return Excel::download(
        new \App\Exports\ProductionExport(
            $receptions, $filterType,
            $start_date, $end_date,
            $start_month, $end_month, $year
        ),
        'rekap_harian_' . ($plant_filter ?: 'semua') . '_' . $filterType . '_' . date('Y-m-d') . '.xlsx'
    );
}

}
