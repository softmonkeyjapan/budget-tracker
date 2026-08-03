import { ref } from 'vue';
import { applyPrivacy, currentPrivacy } from '@/utils/privacy';

const hidden = ref(currentPrivacy());

export function usePrivacy() {
    function toggle() {
        hidden.value = !hidden.value;
        applyPrivacy(hidden.value);
    }

    return { hidden, toggle };
}
