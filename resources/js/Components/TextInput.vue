<script setup>
import { onMounted, ref } from 'vue';
import { Input } from '@/Components/ui/input';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value?.$el?.hasAttribute('autofocus')) {
        input.value.$el.focus();
    }
});

defineExpose({ focus: () => input.value?.$el?.focus() });
</script>

<template>
    <Input
        v-model="model"
        ref="input"
        class="h-10 rounded-lg border-border bg-card py-2 text-foreground shadow-sm focus-visible:border-ring focus-visible:ring-ring"
    />
</template>
