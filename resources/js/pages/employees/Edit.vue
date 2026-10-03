<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import EmployeeForm from '@/components/crm/EmployeeForm.vue';
import PageHeader from '@/components/crm/PageHeader.vue';
import { Card, CardContent } from '@/components/ui/card';
import employees from '@/routes/employees';
import type { CompanyOption, Employee } from '@/types';

const props = defineProps<{
    employee: Employee;
    companies: CompanyOption[];
}>();

setLayoutProps({
    breadcrumbs: [
        { title: 'Employees', href: employees.index() },
        {
            title: props.employee.full_name,
            href: employees.show(props.employee.id),
        },
        { title: 'Edit', href: employees.edit(props.employee.id) },
    ],
});
</script>

<template>
    <Head :title="`Edit ${employee.full_name}`" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <PageHeader
            :title="`Edit ${employee.full_name}`"
            description="Update the employee's details."
        />
        <Card class="max-w-3xl">
            <CardContent>
                <EmployeeForm :employee="employee" :companies="companies" />
            </CardContent>
        </Card>
    </div>
</template>
