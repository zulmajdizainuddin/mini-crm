<script setup lang="ts">
import { Form, Link } from '@inertiajs/vue3';
import { ImageUp, X } from '@lucide/vue';
import { computed, onBeforeUnmount, ref, useTemplateRef } from 'vue';
import CompanyController from '@/actions/App/Http/Controllers/CompanyController';
import CompanyLogo from '@/components/crm/CompanyLogo.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import companies from '@/routes/companies';
import type { Company } from '@/types';

const props = defineProps<{
    company?: Company;
}>();

const action = computed(() =>
    props.company
        ? CompanyController.update.form(props.company.id)
        : CompanyController.store.form(),
);

const fileInput = useTemplateRef<HTMLInputElement>('fileInput');
const previewUrl = ref<string | null>(props.company?.logo_url ?? null);
const removeLogo = ref(false);
const clientError = ref<string | null>(null);
const name = ref(props.company?.name ?? '');

function releasePreview() {
    if (previewUrl.value?.startsWith('blob:')) {
        URL.revokeObjectURL(previewUrl.value);
    }
}

/** Instant feedback for the 100×100 minimum; the server re-validates anyway. */
function onLogoChange(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];
    clientError.value = null;

    if (!file) {
        return;
    }

    const url = URL.createObjectURL(file);
    const img = new Image();
    img.onload = () => {
        if (img.naturalWidth < 100 || img.naturalHeight < 100) {
            clientError.value = `This image is ${img.naturalWidth}×${img.naturalHeight}px — the logo must be at least 100×100px.`;
        }
    };
    img.src = url;

    releasePreview();
    previewUrl.value = url;
    removeLogo.value = false;
}

function clearLogo() {
    releasePreview();
    previewUrl.value = null;
    clientError.value = null;
    removeLogo.value = !!props.company?.logo;

    if (fileInput.value) {
        fileInput.value.value = '';
    }
}

onBeforeUnmount(releasePreview);
</script>

<template>
    <Form
        v-bind="action"
        class="space-y-6"
        v-slot="{ errors, processing, progress }"
    >
        <div class="grid gap-2">
            <Label for="name"
                >Name <span class="text-destructive">*</span></Label
            >
            <Input
                id="name"
                v-model="name"
                name="name"
                required
                autofocus
                maxlength="255"
                placeholder="Acme Sdn. Bhd."
                :aria-invalid="!!errors.name"
            />
            <InputError :message="errors.name" />
        </div>

        <div class="grid items-start gap-6 sm:grid-cols-2">
            <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    name="email"
                    type="email"
                    :default-value="company?.email ?? ''"
                    placeholder="hello@acme.com"
                    :aria-invalid="!!errors.email"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="website">Website</Label>
                <Input
                    id="website"
                    name="website"
                    :default-value="company?.website ?? ''"
                    placeholder="www.acme.com"
                    :aria-invalid="!!errors.website"
                />
                <InputError :message="errors.website" />
            </div>
        </div>

        <div class="grid gap-2">
            <Label for="logo">Logo</Label>
            <div class="flex items-center gap-4">
                <CompanyLogo :name="name || '?'" :src="previewUrl" size="lg" />
                <div class="space-y-2">
                    <div class="flex flex-wrap gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            @click="fileInput?.click()"
                        >
                            <ImageUp />
                            {{ previewUrl ? 'Change logo' : 'Upload logo' }}
                        </Button>
                        <Button
                            v-if="previewUrl"
                            type="button"
                            variant="ghost"
                            size="sm"
                            @click="clearLogo"
                        >
                            <X /> Remove
                        </Button>
                    </div>
                    <p class="text-xs text-muted-foreground">
                        JPG, PNG or WebP · at least 100×100px · max 2 MB
                    </p>
                </div>
            </div>
            <input
                id="logo"
                ref="fileInput"
                type="file"
                name="logo"
                accept="image/jpeg,image/png,image/webp"
                class="sr-only"
                @change="onLogoChange"
            />
            <input
                v-if="company && removeLogo"
                type="hidden"
                name="remove_logo"
                value="1"
            />
            <progress
                v-if="progress"
                :value="progress.percentage"
                max="100"
                class="h-1 w-full"
            />
            <InputError :message="clientError ?? errors.logo" />
        </div>

        <div class="flex items-center gap-3 border-t pt-6">
            <Button
                type="submit"
                :disabled="processing || !!clientError"
                data-test="save-company-button"
            >
                {{ company ? 'Save changes' : 'Create company' }}
            </Button>
            <Button variant="ghost" as-child>
                <Link
                    :href="
                        company ? companies.show(company.id) : companies.index()
                    "
                    >Cancel</Link
                >
            </Button>
        </div>
    </Form>
</template>
