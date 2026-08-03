import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

export function useMonthNavigation(month, routeName) {
    const monthLabel = computed(() => {
        const [year, m] = month.value.split('-').map(Number);

        return new Date(year, m - 1, 1).toLocaleDateString('fr-FR', {
            month: 'long',
            year: 'numeric',
        });
    });

    function shiftMonth(delta) {
        const [year, m] = month.value.split('-').map(Number);
        const date = new Date(year, m - 1 + delta, 1);
        const target = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;

        router.get(route(routeName, { month: target }));
    }

    return { monthLabel, shiftMonth };
}
