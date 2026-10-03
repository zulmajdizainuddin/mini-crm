<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Building2, Pencil, Plus, Search, Trash2 } from '@lucide/vue';
import CompanyController from '@/actions/App/Http/Controllers/CompanyController';
import CompanyLogo from '@/components/crm/CompanyLogo.vue';
import ConfirmDelete from '@/components/crm/ConfirmDelete.vue';
import EmptyState from '@/components/crm/EmptyState.vue';
import PageHeader from '@/components/crm/PageHeader.vue';
import Pagination from '@/components/crm/Pagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useListFilters } from '@/composables/useListFilters';
import companies from '@/routes/companies';
import type { Company, Paginated } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Companies', href: companies.index() }],
    },
});

const props = defineProps<{
    companies: Paginated<Company>;
    filters: { search: string };
}>();

const filters = useListFilters(() => companies.index().url, {
    search: props.filters.search ?? '',
});

const displayHost = (url: string) => url.replace(/^https?:\/\/(www\.)?/, '');
</script>

<template>
    <Head title="Companies" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <PageHeader
            title="Companies"
            description="Manage the companies in your CRM."
        >
            <template #actions>
                <Button as-child data-test="create-company-button">
                    <Link :href="companies.create()">
                        <Plus /> Create new company
                    </Link>
                </Button>
            </template>
        </PageHeader>

        <Card class="gap-0 overflow-hidden py-0">
            <div class="flex items-center gap-3 border-b p-4">
                <div class="relative w-full max-w-sm">
                    <Search
                        class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="filters.search"
                        type="search"
                        placeholder="Search name, email or website…"
                        class="pl-9"
                        aria-label="Search companies"
                    />
                </div>
            </div>

            <div v-if="props.companies.data.length" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead
                        class="bg-muted/50 text-left text-xs tracking-wide text-muted-foreground uppercase"
                    >
                        <tr>
                            <th class="px-4 py-3 font-medium">Company</th>
                            <th class="px-4 py-3 font-medium">Email</th>
                            <th class="px-4 py-3 font-medium">Website</th>
                            <th class="px-4 py-3 text-center font-medium">
                                Employees
                            </th>
                            <th class="px-4 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="company in props.companies.data"
                            :key="company.id"
                            class="transition-colors hover:bg-muted/40"
                        >
                            <td class="px-4 py-3">
                                <Link
                                    :href="companies.show(company.id)"
                                    class="flex items-center gap-3 font-medium hover:underline"
                                >
                                    <CompanyLogo
                                        :name="company.name"
                                        :src="company.logo_url"
                                    />
                                    {{ company.name }}
                                </Link>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                <a
                                    v-if="company.email"
                                    :href="`mailto:${company.email}`"
                                    class="hover:text-foreground hover:underline"
                                    >{{ company.email }}</a
                                >
                                <span v-else>—</span>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                <a
                                    v-if="company.website"
                                    :href="company.website"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="hover:text-foreground hover:underline"
                                    >{{ displayHost(company.website) }}</a
                                >
                                <span v-else>—</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <Badge variant="secondary">{{
                                    company.employees_count ?? 0
                                }}</Badge>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon-sm"
                                        as-child
                                    >
                                        <Link
                                            :href="companies.edit(company.id)"
                                            :aria-label="`Edit ${company.name}`"
                                        >
                                            <Pencil />
                                        </Link>
                                    </Button>
                                    <ConfirmDelete
                                        :title="`Delete ${company.name}?`"
                                        :description="
                                            company.employees_count
                                                ? `This company and its logo will be permanently deleted. Its ${company.employees_count} employee(s) will be kept but marked as unassigned.`
                                                : 'This company and its logo will be permanently deleted.'
                                        "
                                        :action="
                                            CompanyController.destroy.form(
                                                company.id,
                                            )
                                        "
                                    >
                                        <Button
                                            variant="ghost"
                                            size="icon-sm"
                                            class="text-destructive hover:text-destructive"
                                            :aria-label="`Delete ${company.name}`"
                                        >
                                            <Trash2 />
                                        </Button>
                                    </ConfirmDelete>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <EmptyState
                v-else
                :icon="Building2"
                :title="
                    filters.search
                        ? 'No companies match your search'
                        : 'No companies yet'
                "
                :description="
                    filters.search
                        ? 'Try a different name, email or website.'
                        : 'Create your first company to get started.'
                "
            >
                <Button v-if="!filters.search" as-child>
                    <Link :href="companies.create()">
                        <Plus /> Create new company
                    </Link>
                </Button>
            </EmptyState>

            <div v-if="props.companies.total" class="border-t p-4">
                <Pagination :paginator="props.companies" noun="companies" />
            </div>
        </Card>
    </div>
</template>
