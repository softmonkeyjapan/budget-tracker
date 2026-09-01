<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Shared/Layouts/AuthenticatedLayout.vue';
import Amount from '@/Shared/Components/Amount.vue';
import Modal from '@/Shared/Components/Modal.vue';
import { Button } from '@/Shared/Components/ui/button';
import TextInput from '@/Shared/Components/TextInput.vue';
import InputLabel from '@/Shared/Components/InputLabel.vue';
import InputError from '@/Shared/Components/InputError.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    expenses: {
        type: Array,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
});

const statusLabels = {
    brouillon: 'Brouillon',
    rejetee: 'Rejetée',
};

const validatingExpense = ref(null);

const validateForm = useForm({
    category_id: null,
    amount: '',
    date: '',
    description: '',
});

function startValidate(expense) {
    validatingExpense.value = expense;
    validateForm.clearErrors();
    validateForm.category_id = expense.category?.id ?? null;
    validateForm.amount = expense.amount ?? '';
    validateForm.date = expense.date;
    validateForm.description = expense.description ?? '';
}

function cancelValidate() {
    validatingExpense.value = null;
}

function submitValidate() {
    validateForm.transform((data) => ({
        ...data,
        amount: Math.round(Number(data.amount)),
    })).put(route('expenses.update', validatingExpense.value.id), {
        onSuccess: () => cancelValidate(),
    });
}

function reject(expense) {
    router.patch(route('pending-expenses.reject', expense.id));
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
    <Head title="À traiter" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-heading font-extrabold leading-tight text-foreground">À traiter</h2>
                <p class="mt-0.5 text-sm text-muted-foreground">
                    Dépenses détectées automatiquement, en attente de validation.
                </p>
            </div>
        </template>

        <div class="p-6">
            <div class="overflow-hidden rounded-xl bg-card shadow-soft">
                <div
                    v-for="expense in expenses"
                    :key="expense.id"
                    class="border-b border-border px-5 py-4 last:border-b-0"
                >
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span
                                class="shrink-0 rounded-full px-2 py-0.5 text-xs font-semibold"
                                :class="expense.status === 'rejetee'
                                    ? 'bg-expense/10 text-expense'
                                    : 'bg-amber-500/10 text-amber-600'"
                            >
                                {{ statusLabels[expense.status] }}
                            </span>
                            <span class="text-sm text-muted-foreground">
                                {{ new Date(expense.date).toLocaleDateString('fr-FR') }}
                            </span>
                            <span class="truncate text-sm font-medium text-foreground">
                                {{ expense.description ?? 'Notification non reconnue' }}
                            </span>
                        </div>

                        <div class="flex items-center gap-3">
                            <strong v-if="expense.amount !== null" class="whitespace-nowrap text-sm text-expense">
                                <Amount :value="expense.amount" prefix="−" />
                            </strong>
                            <Button variant="default" type="button" @click="startValidate(expense)">
                                Valider
                            </Button>
                            <Button
                                v-if="expense.status === 'brouillon'"
                                variant="secondary"
                                type="button"
                                @click="reject(expense)"
                            >
                                Rejeter
                            </Button>
                            <button
                                type="button"
                                class="text-sm text-expense hover:text-expense/80"
                                @click="confirmDestroy(expense)"
                            >
                                Supprimer
                            </button>
                        </div>
                    </div>

                    <pre
                        v-if="expense.raw_payload"
                        class="mt-3 whitespace-pre-wrap rounded-lg bg-accent p-3 text-xs text-muted-foreground"
                    >{{ expense.raw_payload }}</pre>
                </div>

                <p v-if="expenses.length === 0" class="p-5 text-sm text-muted-foreground">
                    Rien à traiter pour le moment.
                </p>
            </div>
        </div>

        <Modal :show="validatingExpense !== null" @close="cancelValidate">
            <div class="p-6" v-if="validatingExpense">
                <h2 class="text-lg font-semibold text-foreground">Valider la dépense</h2>

                <form @submit.prevent="submitValidate" class="mt-4 space-y-4">
                    <div>
                        <InputLabel for="validate-category" value="Catégorie" />
                        <select
                            id="validate-category"
                            v-model.number="validateForm.category_id"
                            class="mt-1 block w-full rounded-lg border-border bg-card text-foreground shadow-sm focus:border-ring focus:ring-ring"
                        >
                            <option :value="null" disabled>Choisir une catégorie</option>
                            <optgroup v-for="root in categories" :key="root.id" :label="root.name">
                                <option v-for="child in root.children" :key="child.id" :value="child.id">
                                    {{ child.name }}
                                </option>
                            </optgroup>
                        </select>
                        <InputError class="mt-2" :message="validateForm.errors.category_id" />
                    </div>

                    <div>
                        <InputLabel for="validate-amount" value="Montant (FCFP)" />
                        <TextInput
                            id="validate-amount"
                            type="number"
                            min="1"
                            class="mt-1 block w-full"
                            v-model="validateForm.amount"
                            required
                        />
                        <InputError class="mt-2" :message="validateForm.errors.amount" />
                    </div>

                    <div>
                        <InputLabel for="validate-date" value="Date" />
                        <TextInput
                            id="validate-date"
                            type="date"
                            class="mt-1 block w-full"
                            v-model="validateForm.date"
                            required
                        />
                        <InputError class="mt-2" :message="validateForm.errors.date" />
                    </div>

                    <div>
                        <InputLabel for="validate-description" value="Description" />
                        <TextInput
                            id="validate-description"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="validateForm.description"
                        />
                        <InputError class="mt-2" :message="validateForm.errors.description" />
                    </div>

                    <div class="flex justify-end gap-2">
                        <Button variant="secondary" type="button" @click="cancelValidate">Annuler</Button>
                        <Button variant="default" :disabled="validateForm.processing">Valider</Button>
                    </div>
                </form>
            </div>
        </Modal>

        <Modal :show="expensePendingDeletion !== null" @close="cancelDestroy">
            <div class="p-6" v-if="expensePendingDeletion">
                <h2 class="text-lg font-semibold text-foreground">Supprimer cette entrée ?</h2>
                <p class="mt-1 text-sm text-muted-foreground">Cette action est irréversible.</p>

                <div class="mt-6 flex justify-end gap-2">
                    <Button variant="secondary" @click="cancelDestroy">Annuler</Button>
                    <Button variant="destructive" @click="destroy">Supprimer</Button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
