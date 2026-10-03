<script setup lang="ts">
import { Form, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import EmployeeController from '@/actions/App/Http/Controllers/EmployeeController';
import NativeSelect from '@/components/crm/NativeSelect.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import employees from '@/routes/employees';
import type { CompanyOption, Employee } from '@/types';

const props = defineProps<{
    companies: CompanyOption[];
    employee?: Employee;
    defaultCompanyId?: number | null;
}>();

const action = computed(() =>
    props.employee
        ? EmployeeController.update.form(props.employee.id)
        : EmployeeController.store.form(),
);

const selectedCompany = computed(
    () => props.employee?.company_id ?? props.defaultCompanyId ?? '',
);
</script>

<template>
    <Form v-bind="action" class="space-y-6" v-slot="{ errors, processing }">
        <div class="grid items-start gap-6 sm:grid-cols-2">
            <div class="grid gap-2">
                <Label for="first_name"
                    >First name <span class="text-destructive">*</span></Label
                >
                <Input
                    id="first_name"
                    name="first_name"
                    :default-value="employee?.first_name ?? ''"
                    required
                    autofocus
                    maxlength="255"
                    autocomplete="given-name"
                    :aria-invalid="!!errors.first_name"
                />
                <InputError :message="errors.first_name" />
            </div>

            <div class="grid gap-2">
                <Label for="last_name"
                    >Last name <span class="text-destructive">*</span></Label
                >
                <Input
                    id="last_name"
                    name="last_name"
                    :default-value="employee?.last_name ?? ''"
                    required
                    maxlength="255"
                    autocomplete="family-name"
                    :aria-invalid="!!errors.last_name"
                />
                <InputError :message="errors.last_name" />
            </div>
        </div>

        <div class="grid gap-2">
            <Label for="company_id">Company</Label>
            <NativeSelect
                id="company_id"
                name="company_id"
                :model-value="selectedCompany"
                :aria-invalid="!!errors.company_id"
            >
                <option value="">— Unassigned —</option>
                <option
                    v-for="company in companies"
                    :key="company.id"
                    :value="company.id"
                >
                    {{ company.name }}
                </option>
            </NativeSelect>
            <InputError :message="errors.company_id" />
        </div>

        <div class="grid items-start gap-6 sm:grid-cols-2">
            <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    name="email"
                    type="email"
                    :default-value="employee?.email ?? ''"
                    autocomplete="email"
                    placeholder="name@company.com"
                    :aria-invalid="!!errors.email"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="phone">Phone</Label>
                <Input
                    id="phone"
                    name="phone"
                    type="tel"
                    :default-value="employee?.phone ?? ''"
                    autocomplete="tel"
                    placeholder="+60 12-345 6789"
                    :aria-invalid="!!errors.phone"
                />
                <InputError :message="errors.phone" />
            </div>
        </div>

        <div class="flex items-center gap-3 border-t pt-6">
            <Button
                type="submit"
                :disabled="processing"
                data-test="save-employee-button"
            >
                {{ employee ? 'Save changes' : 'Create employee' }}
            </Button>
            <Button variant="ghost" as-child>
                <Link
                    :href="
                        employee
                            ? employees.show(employee.id)
                            : employees.index()
                    "
                    >Cancel</Link
                >
            </Button>
        </div>
    </Form>
</template>
