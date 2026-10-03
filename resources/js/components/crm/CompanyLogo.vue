<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        name: string;
        src?: string | null;
        size?: 'sm' | 'md' | 'lg';
    }>(),
    { src: null, size: 'md' },
);

const failed = ref(false);
watch(
    () => props.src,
    () => (failed.value = false),
);

const initials = computed(() =>
    props.name
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((word) => word[0]?.toUpperCase())
        .join(''),
);

const sizeClass = computed(
    () =>
        ({
            sm: 'size-8 text-xs',
            md: 'size-10 text-sm',
            lg: 'size-20 text-2xl',
        })[props.size],
);
</script>

<template>
    <img
        v-if="src && !failed"
        :src="src"
        :alt="`${name} logo`"
        loading="lazy"
        :class="cn('shrink-0 rounded-md border object-cover', sizeClass)"
        @error="failed = true"
    />
    <div
        v-else
        :class="
            cn(
                'flex shrink-0 items-center justify-center rounded-md border bg-muted font-semibold text-muted-foreground',
                sizeClass,
            )
        "
        aria-hidden="true"
    >
        {{ initials }}
    </div>
</template>
