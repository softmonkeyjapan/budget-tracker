<script setup>
import { ref, toRef } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Amount from '@/Components/Amount.vue';
import CategoryIcon from '@/Components/CategoryIcon.vue';
import SubcategoryBarChart from '@/Components/SubcategoryBarChart.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { useMonthNavigation } from '@/Composables/useMonthNavigation';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    expenses: {
        type: Array,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
    categoryTotals: {
        type: Array,
        required: true,
    },
    subcategoryTotals: {
        type: Array,
        required: true,
    },
    month: {
        type: String,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
});

const search = ref(props.filters.search ?? '');
const categoryId = ref(props.filters.category_id ?? null);
const date = ref(props.filters.date ?? '');

function currentQuery(overrides = {}) {
    return {
        month: props.month,
        category_id: categoryId.value || undefined,
        search: search.value || undefined,
        date: date.value || undefined,
        sort: props.filters.sort,
        direction: props.filters.direction,
        ...overrides,
    };
}

function navigate(overrides = {}) {
    router.get(route('expenses.index', currentQuery(overrides)), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

const { monthLabel, shiftMonth } = useMonthNavigation(toRef(props, 'month'), 'expenses.index', currentQuery);

let searchDebounce = null;

function onSearchInput() {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(() => navigate(), 300);
}

function resetFilters() {
    search.value = '';
    categoryId.value = null;
    date.value = '';
    navigate({ category_id: undefined, search: undefined, date: undefined });
}

function toggleSort(column) {
    const direction = props.filters.sort === column && props.filters.direction === 'asc' ? 'desc' : 'asc';
    navigate({ sort: column, direction });
}

const editingExpense = ref(null);

const editForm = useForm({
    category_id: null,
    amount: '',
    date: '',
    description: '',
});

function startEdit(expense) {
    editingExpense.value = expense;
    editForm.clearErrors();
    editForm.category_id = expense.category.id;
    editForm.amount = expense.amount;
    editForm.date = expense.date;
    editForm.description = expense.description ?? '';
}

function cancelEdit() {
    editingExpense.value = null;
}

function submitEdit() {
    editForm.transform((data) => ({
        ...data,
        amount: Math.round(Number(data.amount)),
    })).put(route('expenses.update', editingExpense.value.id), {
        onSuccess: () => cancelEdit(),
    });
}

const expensePendingDeletion = ref(null);

function confirmDestroy(expense) {
    expensePendingDeletion.value = expense;
}

function cancelDestroy() {
    expensePendingDeletion.value = null;
}

function destroy() {
    router.delete(route('expenses.destroy', expensePendingDeletion.value.id), {
        onFinish: () => {
            expensePendingDeletion.value = null;
        },
    });
}
</script>

<template>
    <Head title="Dépenses" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-extrabold leading-tight text-ink">Dépenses</h2>
                    <p class="mt-0.5 text-sm capitalize text-muted">{{ monthLabel }}</p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1 rounded-control border border-line px-2 py-1">
                        <button
                            type="button"
                            class="rounded-control px-2 py-1 text-muted hover:bg-app hover:text-ink"
                            @click="shiftMonth(-1)"
                        >
                            ‹
                        </button>
                        <span class="px-2 text-sm font-semibold capitalize text-ink">
                            {{ monthLabel }}
                        </span>
                        <button
                            type="button"
                            class="rounded-control px-2 py-1 text-muted hover:bg-app hover:text-ink"
                            @click="shiftMonth(1)"
                        >
                            ›
                        </button>
                    </div>

                    <Link :href="route('expenses.create', { month })">
                        <PrimaryButton type="button">+ Nouvelle dépense</PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="p-6">
            <div class="mb-4 flex flex-wrap items-center gap-3 rounded-card bg-surface p-4 shadow-soft">
                <select
                    v-model="categoryId"
                    class="rounded-control border-line bg-surface text-sm text-ink shadow-sm focus:border-nav focus:ring-nav"
                    @change="navigate()"
                >
                    <option :value="null">Toutes les catégories</option>
                    <optgroup v-for="root in categories" :key="root.id" :label="root.name">
                        <option v-for="child in root.children" :key="child.id" :value="child.id">
                            {{ child.name }}
                        </option>
                    </optgroup>
                </select>

                <TextInput
                    type="text"
                    v-model="search"
                    placeholder="Rechercher dans la description"
                    class="min-w-[220px]"
                    @input="onSearchInput"
                />

                <TextInput
                    type="date"
                    v-model="date"
                    class="w-auto"
                    @change="navigate()"
                />

                <SecondaryButton type="button" @click="resetFilters">
                    Réinitialiser
                </SecondaryButton>
            </div>

            <SubcategoryBarChart :general="categoryTotals" :detail="subcategoryTotals" class="mb-4" />

            <div class="overflow-hidden rounded-card bg-surface shadow-soft">
                <div
                    class="grid grid-cols-[100px_1fr_1fr_140px_170px] gap-2 border-b border-line px-5 py-3 text-xs font-semibold uppercase tracking-wide text-muted"
                >
                    <button type="button" class="flex items-center gap-1 text-left hover:text-ink" @click="toggleSort('date')">
                        Date
                        <span v-if="filters.sort === 'date'">{{ filters.direction === 'asc' ? '▲' : '▼' }}</span>
                    </button>
                    <button type="button" class="flex items-center gap-1 text-left hover:text-ink" @click="toggleSort('category')">
                        Catégorie
                        <span v-if="filters.sort === 'category'">{{ filters.direction === 'asc' ? '▲' : '▼' }}</span>
                    </button>
                    <button type="button" class="flex items-center gap-1 text-left hover:text-ink" @click="toggleSort('description')">
                        Description
                        <span v-if="filters.sort === 'description'">{{ filters.direction === 'asc' ? '▲' : '▼' }}</span>
                    </button>
                    <button type="button" class="flex items-center justify-end gap-1 text-right hover:text-ink" @click="toggleSort('amount')">
                        Montant
                        <span v-if="filters.sort === 'amount'">{{ filters.direction === 'asc' ? '▲' : '▼' }}</span>
                    </button>
                    <span></span>
                </div>

                <p v-if="expenses.length === 0" class="p-5 text-sm text-muted">
                    Aucune dépense pour ce mois ou ces filtres.
                </p>

                <div
                    v-for="expense in expenses"
                    :key="expense.id"
                    class="grid grid-cols-[100px_1fr_1fr_140px_170px] items-center gap-2 border-b border-line px-5 py-3 last:border-b-0"
                >
                    <span class="text-sm text-muted">
                        {{ new Date(expense.date).toLocaleDateString('fr-FR') }}
                    </span>
                    <span class="flex min-w-0 items-center gap-2">
                        <span
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-control text-sm"
                            :style="{
                                backgroundColor: (expense.category.resolved_color ?? '#8A90A2') + '22',
                                color: expense.category.resolved_color ?? '#8A90A2',
                            }"
                        >
                            <CategoryIcon :icon="expense.category.resolved_icon" class="h-4 w-4" />
                        </span>
                        <span class="truncate text-sm font-medium text-ink">
                            {{ expense.category.name }}
                        </span>
                    </span>
                    <span class="truncate text-sm text-muted">
                        {{ expense.description ?? '—' }}
                    </span>
                    <strong class="whitespace-nowrap text-right text-sm text-expense">
                        <Amount :value="expense.amount" prefix="−" />
                    </strong>
                    <span class="flex justify-end gap-3 whitespace-nowrap text-sm">
                        <button
                            type="button"
                            class="text-muted hover:text-ink"
                            @click="startEdit(expense)"
                        >
                            Modifier
                        </button>
                        <button
                            type="button"
                            class="text-expense hover:text-expense/80"
                            @click="confirmDestroy(expense)"
                        >
                            Supprimer
                        </button>
                    </span>
                </div>
            </div>
        </div>

        <Modal :show="editingExpense !== null" @close="cancelEdit">
            <div class="p-6" v-if="editingExpense">
                <h2 class="text-lg font-semibold text-ink">Modifier la dépense</h2>

                <form @submit.prevent="submitEdit" class="mt-4 space-y-4">
                    <div>
                        <InputLabel for="edit-category" value="Catégorie" />
                        <select
                            id="edit-category"
                            v-model.number="editForm.category_id"
                            class="mt-1 block w-full rounded-control border-line bg-surface text-ink shadow-sm focus:border-nav focus:ring-nav"
                        >
                            <optgroup v-for="root in categories" :key="root.id" :label="root.name">
                                <option v-for="child in root.children" :key="child.id" :value="child.id">
                                    {{ child.name }}
                                </option>
                            </optgroup>
                        </select>
                        <InputError class="mt-2" :message="editForm.errors.category_id" />
                    </div>

                    <div>
                        <InputLabel for="edit-amount" value="Montant (FCFP)" />
                        <TextInput
                            id="edit-amount"
                            type="number"
                            min="1"
                            class="mt-1 block w-full"
                            v-model="editForm.amount"
                            required
                        />
                        <InputError class="mt-2" :message="editForm.errors.amount" />
                    </div>

                    <div>
                        <InputLabel for="edit-date" value="Date" />
                        <TextInput
                            id="edit-date"
                            type="date"
                            class="mt-1 block w-full"
                            v-model="editForm.date"
                            required
                        />
                        <InputError class="mt-2" :message="editForm.errors.date" />
                    </div>

                    <div>
                        <InputLabel for="edit-description" value="Description (optionnelle)" />
                        <TextInput
                            id="edit-description"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="editForm.description"
                        />
                        <InputError class="mt-2" :message="editForm.errors.description" />
                    </div>

                    <div class="flex justify-end gap-2">
                        <SecondaryButton type="button" @click="cancelEdit">Annuler</SecondaryButton>
                        <PrimaryButton :disabled="editForm.processing">Enregistrer</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <Modal :show="expensePendingDeletion !== null" @close="cancelDestroy">
            <div class="p-6" v-if="expensePendingDeletion">
                <h2 class="text-lg font-semibold text-ink">Supprimer cette dépense ?</h2>
                <p class="mt-1 text-sm text-muted">Cette action est irréversible.</p>

                <div class="mt-6 flex justify-end gap-2">
                    <SecondaryButton @click="cancelDestroy">Annuler</SecondaryButton>
                    <DangerButton @click="destroy">Supprimer</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
