<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import EmployeeForm from '@/components/crm/EmployeeForm.vue';
import PageHeader from '@/components/crm/PageHeader.vue';
import { Card, CardContent } from '@/components/ui/card';
import employees from '@/routes/employees';
import type { CompanyOption } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Employees', href: employees.index() },
            { title: 'Create', href: employees.create() },
        ],
    },
});

defineProps<{
    companies: CompanyOption[];
    defaultCompanyId: number | null;
}>();
</script>

<template>
    <Head title="Create employee" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <PageHeader
            title="Create employee"
            description="Add a new employee and link them to a company."
        />
        <Card class="max-w-3xl">
            <CardContent>
                <EmployeeForm
                    :companies="companies"
                    :default-company-id="defaultCompanyId"
                />
            </CardContent>
        </Card>
    </div>
</template>
