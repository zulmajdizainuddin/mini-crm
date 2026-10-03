<script setup lang="ts">
import { Head, Link, setLayoutProps } from '@inertiajs/vue3';
import { Globe, Mail, Pencil, Trash2, UserPlus, Users } from '@lucide/vue';
import CompanyController from '@/actions/App/Http/Controllers/CompanyController';
import CompanyLogo from '@/components/crm/CompanyLogo.vue';
import ConfirmDelete from '@/components/crm/ConfirmDelete.vue';
import EmptyState from '@/components/crm/EmptyState.vue';
import Pagination from '@/components/crm/Pagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import companies from '@/routes/companies';
import employees from '@/routes/employees';
import type { Company, Employee, Paginated } from '@/types';

const props = defineProps<{
    company: Company;
    employees: Paginated<Employee>;
}>();

setLayoutProps({
    breadcrumbs: [
        { title: 'Companies', href: companies.index() },
        { title: props.company.name, href: companies.show(props.company.id) },
    ],
});
</script>

<template>
    <Head :title="company.name" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="flex min-w-0 items-center gap-4">
                <CompanyLogo
                    :name="company.name"
                    :src="company.logo_url"
                    size="lg"
                />
                <div class="min-w-0 space-y-2">
                    <h1 class="truncate text-2xl font-semibold tracking-tight">
                        {{ company.name }}
                    </h1>
                    <div
                        class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-muted-foreground"
                    >
                        <a
                            v-if="company.email"
                            :href="`mailto:${company.email}`"
                            class="flex items-center gap-1.5 hover:text-foreground"
                        >
                            <Mail class="size-4" /> {{ company.email }}
                        </a>
                        <a
                            v-if="company.website"
                            :href="company.website"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-center gap-1.5 hover:text-foreground"
                        >
                            <Globe class="size-4" /> {{ company.website }}
                        </a>
                    </div>
                </div>
            </div>
            <div class="flex shrink-0 gap-2">
                <Button variant="outline" as-child>
                    <Link :href="companies.edit(company.id)">
                        <Pencil /> Edit
                    </Link>
                </Button>
                <ConfirmDelete
                    :title="`Delete ${company.name}?`"
                    :description="
                        company.employees_count
                            ? `This company and its logo will be permanently deleted. Its ${company.employees_count} employee(s) will be kept but marked as unassigned.`
                            : 'This company and its logo will be permanently deleted.'
                    "
                    :action="CompanyController.destroy.form(company.id)"
                >
                    <Button variant="destructive"><Trash2 /> Delete</Button>
                </ConfirmDelete>
            </div>
        </div>

        <Card class="gap-0 overflow-hidden py-0">
            <CardHeader
                class="flex flex-row items-center justify-between gap-4 border-b py-4"
            >
                <CardTitle class="flex items-center gap-2">
                    Employees
                    <Badge variant="secondary">{{
                        company.employees_count ?? 0
                    }}</Badge>
                </CardTitle>
                <Button size="sm" as-child>
                    <Link
                        :href="
                            employees.create({
                                query: { company: company.id },
                            })
                        "
                    >
                        <UserPlus /> Add employee
                    </Link>
                </Button>
            </CardHeader>

            <CardContent class="p-0">
                <div v-if="props.employees.data.length" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead
                            class="bg-muted/50 text-left text-xs tracking-wide text-muted-foreground uppercase"
                        >
                            <tr>
                                <th class="px-4 py-3 font-medium">Name</th>
                                <th class="px-4 py-3 font-medium">Email</th>
                                <th class="px-4 py-3 font-medium">Phone</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr
                                v-for="employee in props.employees.data"
                                :key="employee.id"
                                class="hover:bg-muted/40"
                            >
                                <td class="px-4 py-3 font-medium">
                                    <Link
                                        :href="employees.show(employee.id)"
                                        class="hover:underline"
                                        >{{ employee.full_name }}</Link
                                    >
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ employee.email ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ employee.phone ?? '—' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <EmptyState
                    v-else
                    :icon="Users"
                    title="No employees yet"
                    description="Employees you add to this company will show up here."
                />
            </CardContent>

            <div v-if="props.employees.total" class="border-t p-4">
                <Pagination :paginator="props.employees" noun="employees" />
            </div>
        </Card>
    </div>
</template>
