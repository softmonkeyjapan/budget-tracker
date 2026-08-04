<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CategoryIcon from '@/Components/CategoryIcon.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { CATEGORY_ICONS } from '@/utils/categoryIcons';
import { Head, router, useForm } from '@inertiajs/vue3';

defineProps({
    categories: {
        type: Array,
        required: true,
    },
});

const SWATCHES = ['#FF8A66', '#2F80ED', '#23C48E', '#FF5B62', '#8A5CF6'];

const editingCategoryId = ref(null);

const form = useForm({
    name: '',
    color: null,
    icon: null,
    parent_id: null,
});

function startCreate(parentId = null) {
    editingCategoryId.value = null;
    form.reset();
    form.clearErrors();
    form.parent_id = parentId;
}

function startEdit(category) {
    editingCategoryId.value = category.id;
    form.clearErrors();
    form.name = category.name;
    form.color = category.color;
    form.icon = category.icon;
    form.parent_id = category.parent_id;
}

function cancel() {
    startCreate(null);
}

function submit() {
    if (editingCategoryId.value) {
        form.put(route('categories.update', editingCategoryId.value), {
            onSuccess: () => cancel(),
        });
    } else {
        form.post(route('categories.store'), {
            onSuccess: () => cancel(),
        });
    }
}

const categoryPendingDeletion = ref(null);

function confirmDestroy(category) {
    categoryPendingDeletion.value = category;
}

function cancelDestroy() {
    categoryPendingDeletion.value = null;
}

function destroy() {
    router.delete(route('categories.destroy', categoryPendingDeletion.value.id), {
        onFinish: () => {
            categoryPendingDeletion.value = null;
        },
    });
}
</script>

<template>
    <Head title="Catégories" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-extrabold leading-tight text-ink">
                        Catégories
                    </h2>
                    <p class="mt-0.5 text-sm text-muted">Hiérarchie racine → enfant</p>
                </div>
                <PrimaryButton type="button" @click="startCreate()">
                    + Nouvelle catégorie
                </PrimaryButton>
            </div>
        </template>

        <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                <div
                    v-for="root in categories"
                    :key="root.id"
                    class="rounded-card bg-surface p-5 shadow-soft"
                >
                    <div class="flex flex-wrap items-center gap-3">
                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-control text-lg"
                            :style="{
                                backgroundColor: (root.resolved_color ?? '#8A90A2') + '22',
                                color: root.resolved_color ?? '#8A90A2',
                            }"
                        >
                            <CategoryIcon :icon="root.icon" class="h-5 w-5" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold text-ink">{{ root.name }}</p>
                            <p class="text-sm text-muted">{{ root.children_count }} enfant(s)</p>
                        </div>

                        <div class="flex shrink-0 items-center gap-1">
                            <button
                                type="button"
                                class="rounded-control px-3 py-1.5 text-sm font-semibold text-nav hover:bg-app"
                                @click="startCreate(root.id)"
                            >
                                + Enfant
                            </button>
                            <button
                                type="button"
                                class="rounded-control px-3 py-1.5 text-sm text-muted hover:bg-app hover:text-ink"
                                @click="startEdit(root)"
                            >
                                Modifier
                            </button>
                            <button
                                type="button"
                                class="rounded-control px-3 py-1.5 text-sm text-expense hover:bg-app"
                                @click="confirmDestroy(root)"
                            >
                                Supprimer
                            </button>
                        </div>
                    </div>

                    <div
                        v-if="root.children.length"
                        class="ms-5 mt-4 space-y-2 border-l border-line ps-5"
                    >
                        <div
                            v-for="child in root.children"
                            :key="child.id"
                            class="flex flex-wrap items-center justify-between gap-2 rounded-control bg-surface-secondary px-4 py-3"
                        >
                            <div class="flex min-w-0 items-center gap-3">
                                <span
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-control text-sm"
                                    :style="{
                                        backgroundColor: (child.resolved_color ?? '#8A90A2') + '22',
                                        color: child.resolved_color ?? '#8A90A2',
                                    }"
                                >
                                    <CategoryIcon :icon="child.resolved_icon" class="h-4 w-4" />
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-ink">
                                        {{ child.name }}
                                    </p>
                                    <p class="text-xs text-muted">
                                        {{
                                            child.color_inherited
                                                ? `Hérite de ${root.name}`
                                                : 'Couleur propre'
                                        }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex shrink-0 items-center gap-3">
                                <button
                                    type="button"
                                    class="text-sm text-muted hover:text-ink"
                                    @click="startEdit(child)"
                                >
                                    Modifier
                                </button>
                                <button
                                    type="button"
                                    class="text-sm text-expense hover:text-expense/80"
                                    @click="confirmDestroy(child)"
                                >
                                    Supprimer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="h-fit rounded-card bg-surface p-6 shadow-soft">
                <h3 class="mb-4 text-sm font-semibold text-ink">
                    <template v-if="editingCategoryId">Modifier la catégorie</template>
                    <template v-else>Nouvelle catégorie</template>
                </h3>

                <form @submit.prevent="submit" class="space-y-4">
                    <div v-if="!editingCategoryId">
                        <InputLabel for="parent_id" value="Parent" />
                        <select
                            id="parent_id"
                            v-model="form.parent_id"
                            class="mt-1 block w-full rounded-control border-line bg-surface text-ink shadow-sm focus:border-nav focus:ring-nav"
                        >
                            <option :value="null">Aucun (nouvelle catégorie racine)</option>
                            <option v-for="root in categories" :key="root.id" :value="root.id">
                                {{ root.name }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.parent_id" />
                    </div>

                    <div>
                        <InputLabel for="name" value="Nom" />
                        <TextInput
                            id="name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.name"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div>
                        <InputLabel value="Icône" />
                        <div class="mt-2 flex flex-wrap gap-2">
                            <button
                                v-for="key in CATEGORY_ICONS"
                                :key="key"
                                type="button"
                                class="flex h-9 w-9 items-center justify-center rounded-control border-2 bg-app text-ink"
                                :class="form.icon === key ? 'border-nav' : 'border-transparent'"
                                @click="form.icon = key"
                            >
                                <CategoryIcon :icon="key" class="h-5 w-5" />
                            </button>
                        </div>
                        <InputError class="mt-2" :message="form.errors.icon" />
                    </div>

                    <div>
                        <InputLabel value="Couleur" />
                        <div class="mt-2 flex flex-wrap gap-2">
                            <button
                                v-for="swatch in SWATCHES"
                                :key="swatch"
                                type="button"
                                class="h-8 w-8 rounded-control border-2"
                                :class="form.color === swatch ? 'border-ink' : 'border-transparent'"
                                :style="{ backgroundColor: swatch }"
                                @click="form.color = swatch"
                            />
                            <button
                                type="button"
                                class="rounded-control border border-line px-2 text-xs text-muted"
                                @click="form.color = null"
                            >
                                Aucune
                            </button>
                        </div>
                        <InputError class="mt-2" :message="form.errors.color" />
                    </div>

                    <p
                        v-if="form.parent_id"
                        class="rounded-control bg-peach/20 p-3 text-xs text-ink"
                    >
                        Sans couleur ni icône propres, cette catégorie hérite de celles de sa
                        catégorie racine.
                    </p>

                    <div class="flex justify-end gap-2">
                        <SecondaryButton type="button" @click="cancel">
                            Annuler
                        </SecondaryButton>
                        <PrimaryButton :disabled="form.processing">
                            Enregistrer
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>

        <Modal :show="categoryPendingDeletion !== null" @close="cancelDestroy">
            <div class="p-6" v-if="categoryPendingDeletion">
                <h2 class="text-lg font-semibold text-ink">
                    Supprimer "{{ categoryPendingDeletion.name }}" ?
                </h2>
                <p class="mt-1 text-sm text-muted">
                    Cette action est irréversible.
                </p>

                <div class="mt-6 flex justify-end gap-2">
                    <SecondaryButton @click="cancelDestroy">Annuler</SecondaryButton>
                    <DangerButton @click="destroy">Supprimer</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
