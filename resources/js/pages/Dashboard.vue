<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, Building2, UserX, Users } from '@lucide/vue';
import { computed } from 'vue';
import CompanyLogo from '@/components/crm/CompanyLogo.vue';
import PageHeader from '@/components/crm/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import companies from '@/routes/companies';
import employees from '@/routes/employees';
import type { Company, Employee } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
    },
});

const props = defineProps<{
    stats: { companies: number; employees: number; unassigned: number };
    recentCompanies: Company[];
    recentEmployees: Employee[];
}>();

const cards = computed(() => [
    {
        label: 'Companies',
        value: props.stats.companies,
        icon: Building2,
        href: companies.index(),
    },
    {
        label: 'Employees',
        value: props.stats.employees,
        icon: Users,
        href: employees.index(),
    },
    {
        label: 'Unassigned employees',
        value: props.stats.unassigned,
        icon: UserX,
        href: employees.index(),
    },
]);
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <PageHeader
            title="Dashboard"
            description="An overview of your companies and employees."
        />

        <div class="grid gap-4 sm:grid-cols-3">
            <Link
                v-for="card in cards"
                :key="card.label"
                :href="card.href"
                class="group"
            >
                <Card class="transition-colors group-hover:bg-muted/40">
                    <CardContent class="flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-sm text-muted-foreground">
                                {{ card.label }}
                            </p>
                            <p class="text-3xl font-semibold tabular-nums">
                                {{ card.value }}
                            </p>
                        </div>
                        <div
                            class="flex size-11 items-center justify-center rounded-full bg-muted text-muted-foreground"
                        >
                            <component :is="card.icon" class="size-5" />
                        </div>
                    </CardContent>
                </Card>
            </Link>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between gap-2"
                >
                    <CardTitle>Recent companies</CardTitle>
                    <Button variant="ghost" size="sm" as-child>
                        <Link :href="companies.index()"
                            >View all <ArrowRight
                        /></Link>
                    </Button>
                </CardHeader>
                <CardContent>
                    <ul v-if="recentCompanies.length" class="divide-y">
                        <li
                            v-for="company in recentCompanies"
                            :key="company.id"
                            class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0"
                        >
                            <Link
                                :href="companies.show(company.id)"
                                class="flex min-w-0 items-center gap-3 hover:underline"
                            >
                                <CompanyLogo
                                    :name="company.name"
                                    :src="company.logo_url"
                                    size="sm"
                                />
                                <span class="truncate font-medium">{{
                                    company.name
                                }}</span>
                            </Link>
                            <Badge variant="secondary"
                                >{{ company.employees_count ?? 0 }} staff</Badge
                            >
                        </li>
                    </ul>
                    <p v-else class="text-sm text-muted-foreground">
                        No companies yet.
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between gap-2"
                >
                    <CardTitle>Recent employees</CardTitle>
                    <Button variant="ghost" size="sm" as-child>
                        <Link :href="employees.index()"
                            >View all <ArrowRight
                        /></Link>
                    </Button>
                </CardHeader>
                <CardContent>
                    <ul v-if="recentEmployees.length" class="divide-y">
                        <li
                            v-for="employee in recentEmployees"
                            :key="employee.id"
                            class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0"
                        >
                            <Link
                                :href="employees.show(employee.id)"
                                class="truncate font-medium hover:underline"
                                >{{ employee.full_name }}</Link
                            >
                            <span
                                class="truncate text-sm text-muted-foreground"
                                >{{
                                    employee.company?.name ?? 'Unassigned'
                                }}</span
                            >
                        </li>
                    </ul>
                    <p v-else class="text-sm text-muted-foreground">
                        No employees yet.
                    </p>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
