<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CompanyController extends Controller
{
    /**
     * GET /api/v1/companies — paginated companies with their employee_count.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $companies = Company::query()
            ->search($request->string('search')->trim()->value())
            ->withCount('employees')
            ->orderBy('name')
            ->paginate(min(max($request->integer('per_page', 10), 1), 100))
            ->withQueryString();

        return CompanyResource::collection($companies);
    }

    /**
     * GET /api/v1/companies/{company} — a single company with its employees
     * and the `employee_count` attribute.
     */
    public function show(Company $company): CompanyResource
    {
        $company->load(['employees' => fn ($query) => $query->orderBy('last_name')->orderBy('first_name')])
            ->loadCount('employees');

        return new CompanyResource($company);
    }
}
