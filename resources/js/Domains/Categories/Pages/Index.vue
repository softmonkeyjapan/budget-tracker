<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Shared/Layouts/AuthenticatedLayout.vue';
import CategoryIcon from '@/Shared/Components/CategoryIcon.vue';
import Modal from '@/Shared/Components/Modal.vue';
import { Button } from '@/Shared/Components/ui/button';
import TextInput from '@/Shared/Components/TextInput.vue';
import InputLabel from '@/Shared/Components/InputLabel.vue';
import InputError from '@/Shared/Components/InputError.vue';
import { CATEGORY_ICONS } from '@/Domains/Categories/utils/categoryIcons';
import { Head, router, useForm } from '@inertiajs/vue3';

defineProps({
    categories: {
        type: Array,
        required: true,
    },
});

const SWATCHES = ['#FF8A66', '#2F80ED', '#23C48E', '#FF5B62', '#8A5CF6'];

const editingCategoryId = ref(null);
const openRootId = ref(null);

function toggleRoot(rootId) {
    openRootId.value = openRootId.value === rootId ? null : rootId;
}

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
                    <h2 class="text-xl font-heading font-extrabold leading-tight text-foreground">
                        Catégories
                    </h2>
                    <p class="mt-0.5 text-sm text-muted-foreground">Hiérarchie racine → enfant</p>
                </div>
                <Button variant="default" type="button" @click="startCreate()">
                    + Nouvelle catégorie
                </Button>
            </div>
        </template>

        <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                <div
                    v-for="root in categories"
                    :key="root.id"
                    class="rounded-xl bg-card p-5 shadow-soft"
                >
                    <div class="flex flex-wrap items-center gap-3">
                        <button
                            type="button"
                            class="flex min-w-0 flex-1 items-center gap-3 text-left"
                            :aria-expanded="openRootId === root.id"
                            @click="toggleRoot(root.id)"
                        >
                            <span
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-lg"
                                :style="{
                                    backgroundColor: (root.resolved_color ?? '#676E80') + '22',
                                    color: root.resolved_color ?? '#676E80',
                                }"
                            >
                                <CategoryIcon :icon="root.icon" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold text-foreground">{{ root.name }}</p>
                                <p class="text-sm text-muted-foreground">{{ root.children_count }} enfant(s)</p>
                            </div>
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                class="h-4 w-4 shrink-0 text-muted-foreground transition-transform"
                                :class="{ 'rotate-180': openRootId === root.id }"
                            >
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </button>

                        <div class="flex shrink-0 items-center gap-1">
                            <button
                                type="button"
                                class="rounded-lg px-3 py-1.5 text-sm font-semibold text-primary hover:bg-accent"
                                @click="startCreate(root.id)"
                            >
                                + Enfant
                            </button>
                            <button
                                type="button"
                                class="rounded-lg px-3 py-1.5 text-sm text-muted-foreground hover:bg-accent hover:text-foreground"
                                @click="startEdit(root)"
                            >
                                Modifier
                            </button>
                            <button
                                type="button"
                                class="rounded-lg px-3 py-1.5 text-sm text-expense hover:bg-accent"
                                @click="confirmDestroy(root)"
                            >
                                Supprimer
                            </button>
                        </div>
                    </div>

                    <div
                        v-if="openRootId === root.id && root.children.length"
                        class="ms-5 mt-4 space-y-2 border-l border-border ps-5"
                    >
                        <div
                            v-for="child in root.children"
                            :key="child.id"
                            class="flex flex-wrap items-center justify-between gap-2 rounded-lg bg-muted px-4 py-3"
                        >
                            <div class="flex min-w-0 items-center gap-3">
                                <span
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-sm"
                                    :style="{
                                        backgroundColor: (child.resolved_color ?? '#676E80') + '22',
                                        color: child.resolved_color ?? '#676E80',
                                    }"
                                >
                                    <CategoryIcon :icon="child.resolved_icon" class="h-4 w-4" />
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-foreground">
                                        {{ child.name }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
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
                                    class="text-sm text-muted-foreground hover:text-foreground"
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

            <div class="h-fit rounded-xl bg-card p-6 shadow-soft">
                <h3 class="mb-4 text-sm font-semibold text-foreground">
                    <template v-if="editingCategoryId">Modifier la catégorie</template>
                    <template v-else>Nouvelle catégorie</template>
                </h3>

                <form @submit.prevent="submit" class="space-y-4">
                    <div v-if="!editingCategoryId">
                        <InputLabel for="parent_id" value="Parent" />
                        <select
                            id="parent_id"
                            v-model="form.parent_id"
                            class="mt-1 block w-full rounded-lg border-border bg-card text-foreground shadow-sm focus:border-ring focus:ring-ring"
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
                                class="flex h-9 w-9 items-center justify-center rounded-lg border-2 bg-background text-foreground"
                                :class="form.icon === key ? 'border-primary' : 'border-transparent'"
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
                                class="h-8 w-8 rounded-lg border-2"
                                :class="form.color === swatch ? 'border-ink' : 'border-transparent'"
                                :style="{ backgroundColor: swatch }"
                                @click="form.color = swatch"
                            />
                            <button
                                type="button"
                                class="rounded-lg border border-border px-2 text-xs text-muted-foreground"
                                @click="form.color = null"
                            >
                                Aucune
                            </button>
                        </div>
                        <InputError class="mt-2" :message="form.errors.color" />
                    </div>

                    <p
                        v-if="form.parent_id"
                        class="rounded-lg bg-peach/20 p-3 text-xs text-foreground"
                    >
                        Sans couleur ni icône propres, cette catégorie hérite de celles de sa
                        catégorie racine.
                    </p>

                    <div class="flex justify-end gap-2">
                        <Button variant="secondary" type="button" @click="cancel">
                            Annuler
                        </Button>
                        <Button variant="default" :disabled="form.processing">
                            Enregistrer
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <Modal :show="categoryPendingDeletion !== null" @close="cancelDestroy">
            <div class="p-6" v-if="categoryPendingDeletion">
                <h2 class="text-lg font-semibold text-foreground">
                    Supprimer "{{ categoryPendingDeletion.name }}" ?
                </h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Cette action est irréversible.
                </p>

                <div class="mt-6 flex justify-end gap-2">
                    <Button variant="secondary" @click="cancelDestroy">Annuler</Button>
                    <Button variant="destructive" @click="destroy">Supprimer</Button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
