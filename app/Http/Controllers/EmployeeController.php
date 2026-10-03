<?php

namespace App\Http\Controllers;

use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    public const PER_PAGE = 10;

    /**
     * Paginated list of employees, filterable by search term and company.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();
        $companyId = $request->integer('company') ?: null;

        $employees = Employee::query()
            ->with('company:id,name,logo')
            ->search($search)
            ->when($companyId, fn ($query, int $id) => $query->where('company_id', $id))
            ->latest()
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return Inertia::render('employees/Index', [
            'employees' => $employees,
            'companies' => $this->companyOptions(),
            'filters' => ['search' => $search, 'company' => $companyId],
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('employees/Create', [
            'companies' => $this->companyOptions(),
            'defaultCompanyId' => $request->integer('company') ?: null,
        ]);
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $employee = Employee::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => "Employee \"{$employee->full_name}\" created."]);

        return to_route('employees.show', $employee);
    }

    public function show(Employee $employee): Response
    {
        return Inertia::render('employees/Show', [
            'employee' => $employee->load('company'),
        ]);
    }

    public function edit(Employee $employee): Response
    {
        return Inertia::render('employees/Edit', [
            'employee' => $employee,
            'companies' => $this->companyOptions(),
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $employee->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => "Employee \"{$employee->full_name}\" updated."]);

        return to_route('employees.show', $employee);
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => "Employee \"{$employee->full_name}\" deleted."]);

        return to_route('employees.index');
    }

    /**
     * Lightweight list used by the company <select> inputs.
     *
     * @return Collection<int, Company>
     */
    private function companyOptions(): Collection
    {
        return Company::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->each->setAppends([]);
    }
}
