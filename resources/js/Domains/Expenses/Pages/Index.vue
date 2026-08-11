<script setup>
import { computed, ref, toRef } from 'vue';
import AuthenticatedLayout from '@/Shared/Layouts/AuthenticatedLayout.vue';
import Amount from '@/Shared/Components/Amount.vue';
import CategoryIcon from '@/Shared/Components/CategoryIcon.vue';
import CategoryMultiSelect from '@/Domains/Expenses/Components/CategoryMultiSelect.vue';
import SubcategoryBarChart from '@/Domains/Expenses/Components/SubcategoryBarChart.vue';
import Modal from '@/Shared/Components/Modal.vue';
import Pagination from '@/Shared/Components/Pagination.vue';
import { Button } from '@/Shared/Components/ui/button';
import TextInput from '@/Shared/Components/TextInput.vue';
import InputLabel from '@/Shared/Components/InputLabel.vue';
import InputError from '@/Shared/Components/InputError.vue';
import { useMonthNavigation } from '@/Shared/Composables/useMonthNavigation';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    expenses: {
        type: Object,
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

function normalizeCategoryIds(value) {
    if (Array.isArray(value)) {
        return value.map(Number);
    }

    return value === null || value === undefined || value === '' ? [] : [Number(value)];
}

const search = ref(props.filters.search ?? '');
const categoryIds = ref(normalizeCategoryIds(props.filters.category_id));
const date = ref(props.filters.date ?? '');

function currentQuery(overrides = {}) {
    return {
        month: props.month,
        category_id: categoryIds.value.length > 0 ? categoryIds.value : undefined,
        search: search.value || undefined,
        date: date.value || undefined,
        sort: props.filters.sort,
        direction: props.filters.direction,
        per_page: props.expenses.meta.per_page !== 20 ? props.expenses.meta.per_page : undefined,
        ...overrides,
    };
}

function onCategoryIdsChange(ids) {
    categoryIds.value = ids;
    navigate();
}

const distinctSelectedRootCount = computed(() => {
    if (categoryIds.value.length === 0) {
        return 0;
    }

    const rootIds = new Set();

    for (const root of props.categories) {
        if (root.children.some((child) => categoryIds.value.includes(child.id))) {
            rootIds.add(root.id);
        }
    }

    return rootIds.size;
});

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
    categoryIds.value = [];
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
                    <h2 class="text-xl font-heading font-extrabold leading-tight text-foreground">Dépenses</h2>
                    <p class="mt-0.5 text-sm capitalize text-muted-foreground">{{ monthLabel }}</p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1 rounded-lg border border-border px-2 py-1">
                        <button
                            type="button"
                            class="rounded-lg px-2 py-1 text-muted-foreground hover:bg-accent hover:text-foreground"
                            @click="shiftMonth(-1)"
                        >
                            ‹
                        </button>
                        <span class="px-2 text-sm font-semibold capitalize text-foreground">
                            {{ monthLabel }}
                        </span>
                        <button
                            type="button"
                            class="rounded-lg px-2 py-1 text-muted-foreground hover:bg-accent hover:text-foreground"
                            @click="shiftMonth(1)"
                        >
                            ›
                        </button>
                    </div>

                    <Link :href="route('expenses.create', { month })">
                        <Button variant="default" type="button">+ Nouvelle dépense</Button>
                    </Link>
                </div>
            </div>
        </template>

        <div class="p-6">
            <div class="mb-4 flex flex-wrap items-center gap-3 rounded-xl bg-card p-4 shadow-soft">
                <CategoryMultiSelect
                    :categories="categories"
                    :model-value="categoryIds"
                    @update:model-value="onCategoryIdsChange"
                />

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

                <Button variant="secondary" type="button" @click="resetFilters">
                    Réinitialiser
                </Button>
            </div>

            <SubcategoryBarChart
                :general="categoryTotals"
                :detail="subcategoryTotals"
                :show-general-tab="distinctSelectedRootCount !== 1"
                default-tab="general"
                class="mb-4"
            />

            <div class="overflow-hidden rounded-xl bg-card shadow-soft">
                <div
                    class="grid grid-cols-[100px_1fr_1fr_140px_170px] gap-2 border-b border-border px-5 py-3 text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                >
                    <button type="button" class="flex items-center gap-1 text-left hover:text-foreground" @click="toggleSort('date')">
                        Date
                        <span v-if="filters.sort === 'date'">{{ filters.direction === 'asc' ? '▲' : '▼' }}</span>
                    </button>
                    <button type="button" class="flex items-center gap-1 text-left hover:text-foreground" @click="toggleSort('category')">
                        Catégorie
                        <span v-if="filters.sort === 'category'">{{ filters.direction === 'asc' ? '▲' : '▼' }}</span>
                    </button>
                    <button type="button" class="flex items-center gap-1 text-left hover:text-foreground" @click="toggleSort('description')">
                        Description
                        <span v-if="filters.sort === 'description'">{{ filters.direction === 'asc' ? '▲' : '▼' }}</span>
                    </button>
                    <button type="button" class="flex items-center justify-end gap-1 text-right hover:text-foreground" @click="toggleSort('amount')">
                        Montant
                        <span v-if="filters.sort === 'amount'">{{ filters.direction === 'asc' ? '▲' : '▼' }}</span>
                    </button>
                    <span></span>
                </div>

                <p v-if="expenses.data.length === 0" class="p-5 text-sm text-muted-foreground">
                    Aucune dépense pour ce mois ou ces filtres.
                </p>

                <div
                    v-for="expense in expenses.data"
                    :key="expense.id"
                    class="grid grid-cols-[100px_1fr_1fr_140px_170px] items-center gap-2 border-b border-border px-5 py-3 last:border-b-0"
                >
                    <span class="text-sm text-muted-foreground">
                        {{ new Date(expense.date).toLocaleDateString('fr-FR') }}
                    </span>
                    <span class="flex min-w-0 items-center gap-2">
                        <span
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-sm"
                            :style="{
                                backgroundColor: (expense.category.resolved_color ?? '#676E80') + '22',
                                color: expense.category.resolved_color ?? '#676E80',
                            }"
                        >
                            <CategoryIcon :icon="expense.category.resolved_icon" class="h-4 w-4" />
                        </span>
                        <span class="truncate text-sm font-medium text-foreground">
                            {{ expense.category.name }}
                        </span>
                    </span>
                    <span class="truncate text-sm text-muted-foreground">
                        {{ expense.description ?? '—' }}
                    </span>
                    <strong class="whitespace-nowrap text-right text-sm text-expense">
                        <Amount :value="expense.amount" prefix="−" />
                    </strong>
                    <span class="flex justify-end gap-3 whitespace-nowrap text-sm">
                        <button
                            type="button"
                            class="text-muted-foreground hover:text-foreground"
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

                <Pagination
                    :meta="expenses.meta"
                    @update:page="(page) => navigate({ page })"
                    @update:per-page="(perPage) => navigate({ per_page: perPage, page: undefined })"
                />
            </div>
        </div>

        <Modal :show="editingExpense !== null" @close="cancelEdit">
            <div class="p-6" v-if="editingExpense">
                <h2 class="text-lg font-semibold text-foreground">Modifier la dépense</h2>

                <form @submit.prevent="submitEdit" class="mt-4 space-y-4">
                    <div>
                        <InputLabel for="edit-category" value="Catégorie" />
                        <select
                            id="edit-category"
                            v-model.number="editForm.category_id"
                            class="mt-1 block w-full rounded-lg border-border bg-card text-foreground shadow-sm focus:border-ring focus:ring-ring"
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
                        <Button variant="secondary" type="button" @click="cancelEdit">Annuler</Button>
                        <Button variant="default" :disabled="editForm.processing">Enregistrer</Button>
                    </div>
                </form>
            </div>
        </Modal>

        <Modal :show="expensePendingDeletion !== null" @close="cancelDestroy">
            <div class="p-6" v-if="expensePendingDeletion">
                <h2 class="text-lg font-semibold text-foreground">Supprimer cette dépense ?</h2>
                <p class="mt-1 text-sm text-muted-foreground">Cette action est irréversible.</p>

                <div class="mt-6 flex justify-end gap-2">
                    <Button variant="secondary" @click="cancelDestroy">Annuler</Button>
                    <Button variant="destructive" @click="destroy">Supprimer</Button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
