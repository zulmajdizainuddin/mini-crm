<?php

namespace App\Http\Controllers;

use App\Http\Requests\Company\StoreCompanyRequest;
use App\Http\Requests\Company\UpdateCompanyRequest;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class CompanyController extends Controller
{
    public const PER_PAGE = 10;

    /**
     * Paginated list of companies, optionally filtered by a search term.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();

        $companies = Company::query()
            ->search($search)
            ->withCount('employees')
            ->latest()
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return Inertia::render('companies/Index', [
            'companies' => $companies,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('companies/Create');
    }

    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('logo');

        if ($request->hasFile('logo')) {
            $data['logo'] = $this->storeLogo($request->file('logo'));
        }

        $company = Company::create($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => "Company \"{$company->name}\" created."]);

        return to_route('companies.show', $company);
    }

    /**
     * Company profile with its (paginated) employees.
     */
    public function show(Company $company): Response
    {
        $company->loadCount('employees');

        return Inertia::render('companies/Show', [
            'company' => $company,
            'employees' => $company->employees()
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->paginate(self::PER_PAGE)
                ->withQueryString(),
        ]);
    }

    public function edit(Company $company): Response
    {
        return Inertia::render('companies/Edit', [
            'company' => $company,
        ]);
    }

    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        $data = $request->safe()->except(['logo', 'remove_logo']);

        if ($request->hasFile('logo')) {
            $company->deleteLogoFile();
            $data['logo'] = $this->storeLogo($request->file('logo'));
        } elseif ($request->boolean('remove_logo')) {
            $company->deleteLogoFile();
            $data['logo'] = null;
        }

        $company->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => "Company \"{$company->name}\" updated."]);

        return to_route('companies.show', $company);
    }

    /**
     * Delete the company. Its logo is removed by the model's `deleted` hook and
     * its employees are kept but become unassigned (FK is `nullOnDelete`).
     */
    public function destroy(Company $company): RedirectResponse
    {
        $company->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => "Company \"{$company->name}\" deleted."]);

        return to_route('companies.index');
    }

    /**
     * Store the logo on the public disk (storage/app/public/logos).
     */
    private function storeLogo(UploadedFile $file): string
    {
        $path = $file->store('logos', 'public');

        if ($path === false) {
            throw new RuntimeException('The company logo could not be stored.');
        }

        return $path;
    }
}
