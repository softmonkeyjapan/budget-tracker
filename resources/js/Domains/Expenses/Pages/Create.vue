<script setup>
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Shared/Layouts/AuthenticatedLayout.vue';
import CategoryIcon from '@/Shared/Components/CategoryIcon.vue';
import { Button } from '@/Shared/Components/ui/button';
import TextInput from '@/Shared/Components/TextInput.vue';
import InputLabel from '@/Shared/Components/InputLabel.vue';
import InputError from '@/Shared/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    categories: {
        type: Array,
        required: true,
    },
    month: {
        type: String,
        required: true,
    },
});

function defaultDate() {
    const today = new Date().toISOString().slice(0, 10);

    return today.slice(0, 7) === props.month ? today : `${props.month}-01`;
}

const selectedRootId = ref(props.categories[0]?.id ?? null);
const selectedRoot = computed(
    () => props.categories.find((root) => root.id === selectedRootId.value) ?? null,
);
const selectedChild = computed(
    () => selectedRoot.value?.children.find((child) => child.id === form.category_id) ?? null,
);

const form = useForm({
    category_id: null,
    amount: '',
    date: defaultDate(),
    description: '',
});

function selectRoot(root) {
    selectedRootId.value = root.id;
    form.category_id = null;
}

function submit() {
    form.transform((data) => ({
        ...data,
        amount: Math.round(Number(data.amount)),
    })).post(route('expenses.store'));
}
</script>

<template>
    <Head title="Nouvelle dépense" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-heading font-extrabold leading-tight text-foreground">Nouvelle dépense</h2>
            <p class="mt-0.5 text-sm text-muted-foreground">Enregistrer un mouvement réel</p>
        </template>

        <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-3">
            <div class="rounded-xl bg-card p-6 shadow-soft lg:col-span-2">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <div class="flex items-center gap-3">
                            <span
                                class="flex h-7 w-7 items-center justify-center rounded-full bg-background text-sm font-bold text-primary"
                            >
                                1
                            </span>
                            <div>
                                <h3 class="font-semibold text-foreground">Catégorie racine</h3>
                                <p class="text-sm text-muted-foreground">Choisissez d'abord une famille</p>
                            </div>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3">
                            <button
                                v-for="root in categories"
                                :key="root.id"
                                type="button"
                                class="flex items-center gap-2 rounded-lg border-2 p-3 text-left"
                                :class="
                                    selectedRootId === root.id
                                        ? 'border-primary bg-background'
                                        : 'border-border'
                                "
                                @click="selectRoot(root)"
                            >
                                <span
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-base"
                                    :style="{
                                        backgroundColor: (root.resolved_color ?? '#676E80') + '22',
                                        color: root.resolved_color ?? '#676E80',
                                    }"
                                >
                                    <CategoryIcon :icon="root.icon" class="h-4 w-4" />
                                </span>
                                <span class="truncate text-sm font-semibold text-foreground">
                                    {{ root.name }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <div v-if="selectedRoot">
                        <div class="flex items-center gap-3">
                            <span
                                class="flex h-7 w-7 items-center justify-center rounded-full bg-background text-sm font-bold text-primary"
                            >
                                2
                            </span>
                            <div>
                                <h3 class="font-semibold text-foreground">Catégorie enfant</h3>
                                <p class="text-sm text-muted-foreground">
                                    La dépense est toujours rattachée à un enfant
                                </p>
                            </div>
                        </div>

                        <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <button
                                v-for="child in selectedRoot.children"
                                :key="child.id"
                                type="button"
                                class="flex items-center gap-2 rounded-lg border-2 p-3 text-left"
                                :class="
                                    form.category_id === child.id
                                        ? 'border-primary bg-background'
                                        : 'border-border'
                                "
                                @click="form.category_id = child.id"
                            >
                                <span
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-base"
                                    :style="{
                                        backgroundColor:
                                            (child.resolved_color ?? '#676E80') + '22',
                                        color: child.resolved_color ?? '#676E80',
                                    }"
                                >
                                    <CategoryIcon :icon="child.resolved_icon" class="h-4 w-4" />
                                </span>
                                <span class="truncate text-sm font-semibold text-foreground">
                                    {{ child.name }}
                                </span>
                            </button>
                        </div>
                        <InputError class="mt-2" :message="form.errors.category_id" />
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel for="amount" value="Montant (FCFP)" />
                            <TextInput
                                id="amount"
                                type="number"
                                min="1"
                                class="mt-1 block w-full"
                                v-model="form.amount"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.amount" />
                        </div>

                        <div>
                            <InputLabel for="date" value="Date" />
                            <TextInput
                                id="date"
                                type="date"
                                class="mt-1 block w-full"
                                v-model="form.date"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.date" />
                        </div>

                        <div class="sm:col-span-2">
                            <InputLabel for="description" value="Description (optionnelle)" />
                            <TextInput
                                id="description"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.description"
                            />
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>
                    </div>

                    <div class="flex justify-end gap-2">
                        <Link
                            :href="route('expenses.index', { month })"
                            class="rounded-lg border border-border bg-card px-5 py-2.5 text-sm font-semibold text-foreground shadow-pill hover:bg-accent"
                        >
                            Annuler
                        </Link>
                        <Button variant="default" :disabled="form.processing || !form.category_id">
                            Enregistrer la dépense
                        </Button>
                    </div>
                </form>
            </div>

            <div class="h-fit rounded-xl bg-card p-6 shadow-soft">
                <h3 class="mb-3 text-sm font-semibold text-foreground">Résumé</h3>
                <div v-if="selectedChild" class="flex items-center gap-3 rounded-lg bg-muted p-3">
                    <span
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-lg"
                        :style="{
                            backgroundColor: (selectedChild.resolved_color ?? '#676E80') + '22',
                            color: selectedChild.resolved_color ?? '#676E80',
                        }"
                    >
                        <CategoryIcon :icon="selectedChild.resolved_icon" class="h-4 w-4" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs text-muted-foreground">{{ selectedRoot.name }}</p>
                        <p class="truncate text-sm font-semibold text-foreground">
                            {{ selectedChild.name }}
                        </p>
                    </div>
                </div>
                <p v-else class="text-sm text-muted-foreground">Sélectionnez une catégorie enfant.</p>

                <h3 class="mb-2 mt-6 text-sm font-semibold text-foreground">Saisie rétroactive</h3>
                <p class="text-sm text-muted-foreground">
                    La date peut appartenir à n'importe quel mois passé. Le mouvement apparaîtra
                    automatiquement dans ce mois.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
