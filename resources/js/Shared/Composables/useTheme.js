import { ref } from 'vue';
import { applyTheme, currentTheme } from '@/Shared/utils/theme';

const theme = ref(currentTheme());

export function useTheme() {
    function toggle() {
        theme.value = theme.value === 'dark' ? 'light' : 'dark';
        applyTheme(theme.value);
    }

    return { theme, toggle };
}
