<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Employee;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Dashboard', [
            'stats' => [
                'companies' => Company::count(),
                'employees' => Employee::count(),
                'unassigned' => Employee::whereNull('company_id')->count(),
            ],
            'recentCompanies' => Company::query()
                ->withCount('employees')
                ->latest()
                ->limit(5)
                ->get(),
            'recentEmployees' => Employee::query()
                ->with('company:id,name,logo')
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}
