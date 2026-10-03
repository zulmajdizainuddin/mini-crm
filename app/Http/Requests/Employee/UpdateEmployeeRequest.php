<?php

namespace App\Http\Requests\Employee;

class UpdateEmployeeRequest extends StoreEmployeeRequest
{
    // Same rules as creating an employee; kept as its own class so update
    // rules can diverge later without touching the store flow.
}
