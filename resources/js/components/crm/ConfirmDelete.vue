<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import type { RouteFormDefinition } from '@/wayfinder';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

defineProps<{
    title: string;
    description: string;
    action: RouteFormDefinition<'post'>;
    confirmLabel?: string;
}>();
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <slot />
        </DialogTrigger>
        <DialogContent>
            <Form v-bind="action" v-slot="{ processing }" class="space-y-6">
                <DialogHeader class="space-y-3">
                    <DialogTitle>{{ title }}</DialogTitle>
                    <DialogDescription>{{ description }}</DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button type="button" variant="secondary"
                            >Cancel</Button
                        >
                    </DialogClose>
                    <Button
                        type="submit"
                        variant="destructive"
                        :disabled="processing"
                        data-test="confirm-delete-button"
                    >
                        {{ confirmLabel ?? 'Delete' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
