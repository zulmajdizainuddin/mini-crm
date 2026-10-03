<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 40 42"
        :class="className"
        v-bind="$attrs"
    >
        <path
            fill="currentColor"
            fill-rule="evenodd"
            clip-rule="evenodd"
            d="M4 2h20v38h-20ZM26 14h10v26h-10ZM8 7h4v4h-4ZM16 7h4v4h-4ZM8 15h4v4h-4ZM16 15h4v4h-4ZM8 23h4v4h-4ZM16 23h4v4h-4ZM11 31h6v9h-6ZM29 19h4v4h-4ZM29 27h4v4h-4Z"
        />
    </svg>
</template>
