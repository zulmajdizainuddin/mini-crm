<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import type { Paginated } from '@/types';

const props = defineProps<{
    paginator: Paginated<unknown>;
    noun?: string;
}>();

// Laravel's first and last links are "Previous"/"Next"; keep the numbered ones.
const pageLinks = computed(() => props.paginator.links.slice(1, -1));
</script>

<template>
    <nav
        v-if="paginator.total > 0"
        class="flex flex-col items-center justify-between gap-3 sm:flex-row"
        aria-label="Pagination"
    >
        <p class="text-sm text-muted-foreground">
            Showing
            <span class="font-medium text-foreground">{{
                paginator.from
            }}</span>
            to
            <span class="font-medium text-foreground">{{ paginator.to }}</span>
            of
            <span class="font-medium text-foreground">{{
                paginator.total
            }}</span>
            {{ noun ?? 'results' }}
        </p>

        <div v-if="paginator.last_page > 1" class="flex items-center gap-1">
            <Button
                variant="outline"
                size="icon-sm"
                :disabled="!paginator.prev_page_url"
                :as-child="!!paginator.prev_page_url"
                aria-label="Previous page"
            >
                <Link
                    v-if="paginator.prev_page_url"
                    :href="paginator.prev_page_url"
                    preserve-scroll
                    preserve-state
                >
                    <ChevronLeft />
                </Link>
                <ChevronLeft v-else />
            </Button>

            <template v-for="link in pageLinks" :key="link.label">
                <span
                    v-if="!link.url"
                    class="px-2 text-sm text-muted-foreground"
                    >…</span
                >
                <Button
                    v-else
                    :variant="link.active ? 'default' : 'ghost'"
                    size="icon-sm"
                    as-child
                >
                    <Link
                        :href="link.url"
                        preserve-scroll
                        preserve-state
                        :aria-current="link.active ? 'page' : undefined"
                    >
                        {{ link.label }}
                    </Link>
                </Button>
            </template>

            <Button
                variant="outline"
                size="icon-sm"
                :disabled="!paginator.next_page_url"
                :as-child="!!paginator.next_page_url"
                aria-label="Next page"
            >
                <Link
                    v-if="paginator.next_page_url"
                    :href="paginator.next_page_url"
                    preserve-scroll
                    preserve-state
                >
                    <ChevronRight />
                </Link>
                <ChevronRight v-else />
            </Button>
        </div>
    </nav>
</template>
