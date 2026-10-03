<script setup lang="ts">
import { Head, Link, setLayoutProps } from '@inertiajs/vue3';
import { Pencil, Trash2 } from '@lucide/vue';
import EmployeeController from '@/actions/App/Http/Controllers/EmployeeController';
import CompanyLogo from '@/components/crm/CompanyLogo.vue';
import ConfirmDelete from '@/components/crm/ConfirmDelete.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { useInitials } from '@/composables/useInitials';
import companies from '@/routes/companies';
import employees from '@/routes/employees';
import type { Employee } from '@/types';

const props = defineProps<{
    employee: Employee;
}>();

const { getInitials } = useInitials();

setLayoutProps({
    breadcrumbs: [
        { title: 'Employees', href: employees.index() },
        {
            title: props.employee.full_name,
            href: employees.show(props.employee.id),
        },
    ],
});

const formatDate = (iso: string) =>
    new Date(iso).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
</script>

<template>
    <Head :title="employee.full_name" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="flex items-center gap-4">
                <Avatar class="size-16 text-xl">
                    <AvatarFallback>{{
                        getInitials(employee.full_name)
                    }}</AvatarFallback>
                </Avatar>
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight">
                        {{ employee.full_name }}
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Added {{ formatDate(employee.created_at) }}
                    </p>
                </div>
            </div>
            <div class="flex gap-2">
                <Button variant="outline" as-child>
                    <Link :href="employees.edit(employee.id)">
                        <Pencil /> Edit
                    </Link>
                </Button>
                <ConfirmDelete
                    :title="`Delete ${employee.full_name}?`"
                    description="This employee will be permanently deleted."
                    :action="EmployeeController.destroy.form(employee.id)"
                >
                    <Button variant="destructive"><Trash2 /> Delete</Button>
                </ConfirmDelete>
            </div>
        </div>

        <Card class="max-w-3xl">
            <CardContent>
                <dl class="grid gap-6 sm:grid-cols-2">
                    <div class="space-y-1">
                        <dt class="text-sm text-muted-foreground">Company</dt>
                        <dd>
                            <Link
                                v-if="employee.company"
                                :href="companies.show(employee.company.id)"
                                class="inline-flex items-center gap-2 font-medium hover:underline"
                            >
                                <CompanyLogo
                                    :name="employee.company.name"
                                    :src="employee.company.logo_url"
                                    size="sm"
                                />
                                {{ employee.company.name }}
                            </Link>
                            <span v-else class="text-muted-foreground italic"
                                >Unassigned</span
                            >
                        </dd>
                    </div>
                    <div class="space-y-1">
                        <dt class="text-sm text-muted-foreground">Email</dt>
                        <dd class="font-medium">
                            <a
                                v-if="employee.email"
                                :href="`mailto:${employee.email}`"
                                class="hover:underline"
                                >{{ employee.email }}</a
                            >
                            <span v-else class="text-muted-foreground">—</span>
                        </dd>
                    </div>
                    <div class="space-y-1">
                        <dt class="text-sm text-muted-foreground">Phone</dt>
                        <dd class="font-medium">
                            <a
                                v-if="employee.phone"
                                :href="`tel:${employee.phone.replace(/[^\d+]/g, '')}`"
                                class="hover:underline"
                                >{{ employee.phone }}</a
                            >
                            <span v-else class="text-muted-foreground">—</span>
                        </dd>
                    </div>
                    <div class="space-y-1">
                        <dt class="text-sm text-muted-foreground">
                            Last updated
                        </dt>
                        <dd class="font-medium">
                            {{ formatDate(employee.updated_at) }}
                        </dd>
                    </div>
                </dl>
            </CardContent>
        </Card>
    </div>
</template>
