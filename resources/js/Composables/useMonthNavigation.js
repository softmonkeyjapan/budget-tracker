import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { formatMonth, parseMonth } from '@/utils/month';

export function useMonthNavigation(month, routeName) {
    const monthLabel = computed(() => formatMonth(month.value));

    function shiftMonth(delta) {
        const date = parseMonth(month.value);
        date.setMonth(date.getMonth() + delta);
        const target = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;

        router.get(route(routeName, { month: target }));
    }

    return { monthLabel, shiftMonth };
}
