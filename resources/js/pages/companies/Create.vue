<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CompanyForm from '@/components/crm/CompanyForm.vue';
import PageHeader from '@/components/crm/PageHeader.vue';
import { Card, CardContent } from '@/components/ui/card';
import companies from '@/routes/companies';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Companies', href: companies.index() },
            { title: 'Create', href: companies.create() },
        ],
    },
});
</script>

<template>
    <Head title="Create company" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <PageHeader
            title="Create company"
            description="Add a new company to the CRM."
        />
        <Card class="max-w-3xl">
            <CardContent>
                <CompanyForm />
            </CardContent>
        </Card>
    </div>
</template>
