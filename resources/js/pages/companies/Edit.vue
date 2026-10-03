<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import CompanyForm from '@/components/crm/CompanyForm.vue';
import PageHeader from '@/components/crm/PageHeader.vue';
import { Card, CardContent } from '@/components/ui/card';
import companies from '@/routes/companies';
import type { Company } from '@/types';

const props = defineProps<{
    company: Company;
}>();

setLayoutProps({
    breadcrumbs: [
        { title: 'Companies', href: companies.index() },
        { title: props.company.name, href: companies.show(props.company.id) },
        { title: 'Edit', href: companies.edit(props.company.id) },
    ],
});
</script>

<template>
    <Head :title="`Edit ${company.name}`" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <PageHeader
            :title="`Edit ${company.name}`"
            description="Update the company details."
        />
        <Card class="max-w-3xl">
            <CardContent>
                <CompanyForm :company="company" />
            </CardContent>
        </Card>
    </div>
</template>
