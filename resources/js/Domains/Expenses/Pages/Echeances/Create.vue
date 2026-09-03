<script setup>
import { computed, ref, watch } from 'vue';
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
});

const frequencyOptions = [
    { value: 'mensuelle', label: 'Mensuelle', months: 1 },
    { value: 'trimestrielle', label: 'Trimestrielle', months: 3 },
    { value: 'annuelle', label: 'Annuelle', months: 12 },
];

function addMonthsNoOverflow(dateString, months) {
    const [year, month, day] = dateString.split('-').map(Number);
    const target = new Date(Date.UTC(year, month - 1 + months, 1));
    const lastDayOfTargetMonth = new Date(Date.UTC(target.getUTCFullYear(), target.getUTCMonth() + 1, 0)).getUTCDate();
    target.setUTCDate(Math.min(day, lastDayOfTargetMonth));

    return target.toISOString().slice(0, 10);
}

const selectedRootId = ref(props.categories[0]?.id ?? null);
const selectedRoot = computed(
    () => props.categories.find((root) => root.id === selectedRootId.value) ?? null,
);
const selectedChild = computed(
    () => selectedRoot.value?.children.find((child) => child.id === form.category_id) ?? null,
);

function selectRoot(root) {
    selectedRootId.value = root.id;
    form.category_id = null;
}

const isUnlimited = ref(true);
const firstDate = ref(new Date().toISOString().slice(0, 10));
const baseAmount = ref('');
const occurrencesCount = ref(1);

const form = useForm({
    category_id: null,
    description: '',
    frequency: 'mensuelle',
    occurrences_total: null,
    occurrences: [{ date: firstDate.value, amount: '' }],
});

function targetOccurrenceCount() {
    if (isUnlimited.value) {
        return 1;
    }

    return Math.max(1, Math.min(60, Number(occurrencesCount.value) || 1));
}

function syncOccurrenceLines() {
    const months = frequencyOptions.find((option) => option.value === form.frequency)?.months ?? 1;
    const count = targetOccurrenceCount();

    form.occurrences_total = isUnlimited.value ? null : count;

    if (count > form.occurrences.length) {
        for (let index = form.occurrences.length; index < count; index++) {
            form.occurrences.push({
                date: index === 0 ? firstDate.value : addMonthsNoOverflow(firstDate.value, index * months),
                amount: baseAmount.value,
            });
        }
    } else if (count < form.occurrences.length) {
        form.occurrences.splice(count);
    }
}

watch([isUnlimited, occurrencesCount], syncOccurrenceLines);

watch(baseAmount, (value) => {
    form.occurrences.forEach((occurrence) => {
        occurrence.amount = value;
    });
});

function submit() {
    form.transform((data) => ({
        ...data,
        occurrences: data.occurrences.map((occurrence) => ({
            date: occurrence.date,
            amount: Math.round(Number(occurrence.amount)),
        })),
    })).post(route('echeances.store'));
}
</script>

<template>
    <Head title="Nouvel échéancier" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-heading font-extrabold leading-tight text-foreground">Nouvel échéancier</h2>
            <p class="mt-0.5 text-sm text-muted-foreground">Programmer une dépense qui se répète dans le temps</p>
        </template>

        <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-3">
            <div class="rounded-xl bg-card p-6 shadow-soft lg:col-span-2">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel for="description" value="Description" />
                        <TextInput
                            id="description"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.description"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.description" />
                    </div>

                    <div>
                        <h3 class="font-semibold text-foreground">Catégorie racine</h3>
                        <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3">
                            <button
                                v-for="root in categories"
                                :key="root.id"
                                type="button"
                                class="flex items-center gap-2 rounded-lg border-2 p-3 text-left"
                                :class="selectedRootId === root.id ? 'border-primary bg-background' : 'border-border'"
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
                                <span class="truncate text-sm font-semibold text-foreground">{{ root.name }}</span>
                            </button>
                        </div>
                    </div>

                    <div v-if="selectedRoot">
                        <h3 class="font-semibold text-foreground">Catégorie enfant</h3>
                        <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <button
                                v-for="child in selectedRoot.children"
                                :key="child.id"
                                type="button"
                                class="flex items-center gap-2 rounded-lg border-2 p-3 text-left"
                                :class="form.category_id === child.id ? 'border-primary bg-background' : 'border-border'"
                                @click="form.category_id = child.id"
                            >
                                <span
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-base"
                                    :style="{
                                        backgroundColor: (child.resolved_color ?? '#676E80') + '22',
                                        color: child.resolved_color ?? '#676E80',
                                    }"
                                >
                                    <CategoryIcon :icon="child.resolved_icon" class="h-4 w-4" />
                                </span>
                                <span class="truncate text-sm font-semibold text-foreground">{{ child.name }}</span>
                            </button>
                        </div>
                        <InputError class="mt-2" :message="form.errors.category_id" />
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel for="frequency" value="Fréquence" />
                            <select
                                id="frequency"
                                v-model="form.frequency"
                                class="mt-1 block w-full rounded-lg border-border bg-card text-foreground shadow-sm focus:border-ring focus:ring-ring"
                            >
                                <option v-for="option in frequencyOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.frequency" />
                        </div>

                        <div>
                            <InputLabel value="Nombre d'échéances" />
                            <div class="mt-1 flex items-center gap-3">
                                <label class="flex items-center gap-1.5 text-sm text-foreground">
                                    <input type="radio" :checked="isUnlimited" @change="isUnlimited = true" />
                                    Illimité
                                </label>
                                <label class="flex items-center gap-1.5 text-sm text-foreground">
                                    <input type="radio" :checked="!isUnlimited" @change="isUnlimited = false" />
                                    Connu
                                </label>
                            </div>
                        </div>

                        <div v-if="!isUnlimited">
                            <InputLabel for="occurrences-count" value="Nombre d'occurrences" />
                            <TextInput
                                id="occurrences-count"
                                type="number"
                                min="1"
                                max="60"
                                class="mt-1 block w-full"
                                v-model="occurrencesCount"
                            />
                        </div>

                        <div>
                            <InputLabel for="first-date" value="Date de la 1ère échéance" />
                            <TextInput
                                id="first-date"
                                type="date"
                                class="mt-1 block w-full"
                                v-model="firstDate"
                            />
                        </div>

                        <div>
                            <InputLabel for="base-amount" value="Montant (FCFP)" />
                            <TextInput
                                id="base-amount"
                                type="number"
                                min="1"
                                class="mt-1 block w-full"
                                v-model="baseAmount"
                            />
                        </div>
                    </div>

                    <div v-if="form.occurrences.length > 0" class="space-y-2">
                        <h3 class="font-semibold text-foreground">
                            {{ isUnlimited ? 'Première occurrence' : 'Occurrences' }}
                        </h3>
                        <p class="text-sm text-muted-foreground">
                            Chaque montant peut être ajusté individuellement (ex : chèques d'un montant différent).
                        </p>
                        <div
                            v-for="(occurrence, index) in form.occurrences"
                            :key="index"
                            class="flex items-center gap-3 rounded-lg border border-border p-3"
                        >
                            <span class="w-6 text-sm text-muted-foreground">{{ index + 1 }}</span>
                            <TextInput type="date" class="block" v-model="occurrence.date" />
                            <TextInput type="number" min="1" class="block w-32" v-model="occurrence.amount" />
                        </div>
                        <InputError class="mt-2" :message="form.errors.occurrences" />
                    </div>

                    <div class="flex justify-end gap-2">
                        <Link
                            :href="route('echeances.index')"
                            class="rounded-lg border border-border bg-card px-5 py-2.5 text-sm font-semibold text-foreground shadow-pill hover:bg-accent"
                        >
                            Annuler
                        </Link>
                        <Button variant="default" :disabled="form.processing || !form.category_id">
                            Créer l'échéancier
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
                        <p class="truncate text-sm font-semibold text-foreground">{{ selectedChild.name }}</p>
                    </div>
                </div>
                <p v-else class="text-sm text-muted-foreground">Sélectionnez une catégorie enfant.</p>

                <h3 class="mb-2 mt-6 text-sm font-semibold text-foreground">Comment ça marche</h3>
                <p class="text-sm text-muted-foreground">
                    Chaque occurrence apparaîtra automatiquement dans vos dépenses, dans la bonne catégorie, le jour
                    de son échéance — rien ne s'affiche avant.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
