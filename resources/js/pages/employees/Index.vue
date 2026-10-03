<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Pencil, Plus, Search, Trash2, Users } from '@lucide/vue';
import EmployeeController from '@/actions/App/Http/Controllers/EmployeeController';
import CompanyLogo from '@/components/crm/CompanyLogo.vue';
import ConfirmDelete from '@/components/crm/ConfirmDelete.vue';
import EmptyState from '@/components/crm/EmptyState.vue';
import NativeSelect from '@/components/crm/NativeSelect.vue';
import PageHeader from '@/components/crm/PageHeader.vue';
import Pagination from '@/components/crm/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useListFilters } from '@/composables/useListFilters';
import companies from '@/routes/companies';
import employees from '@/routes/employees';
import type { CompanyOption, Employee, Paginated } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Employees', href: employees.index() }],
    },
});

const props = defineProps<{
    employees: Paginated<Employee>;
    companies: CompanyOption[];
    filters: { search: string; company: number | null };
}>();

const filters = useListFilters(() => employees.index().url, {
    search: props.filters.search ?? '',
    company: (props.filters.company ?? '') as number | string,
});
</script>

<template>
    <Head title="Employees" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <PageHeader
            title="Employees"
            description="Manage employees across all companies."
        >
            <template #actions>
                <Button as-child data-test="create-employee-button">
                    <Link :href="employees.create()">
                        <Plus /> Create new employee
                    </Link>
                </Button>
            </template>
        </PageHeader>

        <Card class="gap-0 overflow-hidden py-0">
            <div
                class="flex flex-col gap-3 border-b p-4 sm:flex-row sm:items-center"
            >
                <div class="relative w-full sm:max-w-sm">
                    <Search
                        class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="filters.search"
                        type="search"
                        placeholder="Search name, email or phone…"
                        class="pl-9"
                        aria-label="Search employees"
                    />
                </div>
                <NativeSelect
                    v-model="filters.company"
                    class="sm:max-w-60"
                    aria-label="Filter by company"
                >
                    <option value="">All companies</option>
                    <option
                        v-for="company in props.companies"
                        :key="company.id"
                        :value="company.id"
                    >
                        {{ company.name }}
                    </option>
                </NativeSelect>
            </div>

            <div v-if="props.employees.data.length" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead
                        class="bg-muted/50 text-left text-xs tracking-wide text-muted-foreground uppercase"
                    >
                        <tr>
                            <th class="px-4 py-3 font-medium">Name</th>
                            <th class="px-4 py-3 font-medium">Company</th>
                            <th class="px-4 py-3 font-medium">Email</th>
                            <th class="px-4 py-3 font-medium">Phone</th>
                            <th class="px-4 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="employee in props.employees.data"
                            :key="employee.id"
                            class="transition-colors hover:bg-muted/40"
                        >
                            <td class="px-4 py-3 font-medium">
                                <Link
                                    :href="employees.show(employee.id)"
                                    class="hover:underline"
                                    >{{ employee.full_name }}</Link
                                >
                            </td>
                            <td class="px-4 py-3">
                                <Link
                                    v-if="employee.company"
                                    :href="companies.show(employee.company.id)"
                                    class="flex items-center gap-2 hover:underline"
                                >
                                    <CompanyLogo
                                        :name="employee.company.name"
                                        :src="employee.company.logo_url"
                                        size="sm"
                                    />
                                    {{ employee.company.name }}
                                </Link>
                                <span
                                    v-else
                                    class="text-muted-foreground italic"
                                    >Unassigned</span
                                >
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ employee.email ?? '—' }}
                            </td>
                            <td
                                class="px-4 py-3 whitespace-nowrap text-muted-foreground"
                            >
                                {{ employee.phone ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon-sm"
                                        as-child
                                    >
                                        <Link
                                            :href="employees.edit(employee.id)"
                                            :aria-label="`Edit ${employee.full_name}`"
                                        >
                                            <Pencil />
                                        </Link>
                                    </Button>
                                    <ConfirmDelete
                                        :title="`Delete ${employee.full_name}?`"
                                        description="This employee will be permanently deleted."
                                        :action="
                                            EmployeeController.destroy.form(
                                                employee.id,
                                            )
                                        "
                                    >
                                        <Button
                                            variant="ghost"
                                            size="icon-sm"
                                            class="text-destructive hover:text-destructive"
                                            :aria-label="`Delete ${employee.full_name}`"
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
                :icon="Users"
                :title="
                    filters.search || filters.company
                        ? 'No employees match your filters'
                        : 'No employees yet'
                "
                :description="
                    filters.search || filters.company
                        ? 'Try a different search or company.'
                        : 'Create your first employee to get started.'
                "
            />

            <div v-if="props.employees.total" class="border-t p-4">
                <Pagination :paginator="props.employees" noun="employees" />
            </div>
        </Card>
    </div>
</template>
