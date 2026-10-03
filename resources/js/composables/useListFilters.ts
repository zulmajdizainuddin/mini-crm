import { router } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { reactive } from 'vue';

/**
 * Keeps list filters (search box, selects) in sync with the query string.
 * Typing is debounced; every change resets the list to page 1.
 */
export function useListFilters<
    T extends Record<string, string | number | null>,
>(url: () => string, initial: T) {
    const filters = reactive({ ...initial }) as T;

    watchDebounced(
        () => ({ ...filters }),
        (value) => {
            const query = Object.fromEntries(
                Object.entries(value).filter(
                    ([, v]) => v !== null && v !== '' && v !== undefined,
                ),
            );

            router.get(url(), query, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            });
        },
        { debounce: 300, deep: true },
    );

    return filters;
}
