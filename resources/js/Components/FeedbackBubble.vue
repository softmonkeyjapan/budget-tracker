<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import { Button } from '@/Components/ui/button';

const open = ref(false);
const sent = ref(false);

const form = useForm({
    message: '',
    page_url: '',
});

const openModal = () => {
    sent.value = false;
    form.clearErrors();
    open.value = true;
};

const closeModal = () => {
    open.value = false;
};

const submit = () => {
    form.page_url = window.location.pathname;

    form.post(route('feedback.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('message');
            sent.value = true;
            setTimeout(closeModal, 1500);
        },
    });
};
</script>

<template>
    <button
        type="button"
        @click="openModal"
        class="fixed bottom-6 right-6 z-40 flex h-12 w-12 items-center justify-center rounded-full bg-primary text-white shadow-primary transition duration-150 ease-in-out hover:bg-primary/90"
        aria-label="Donner un avis"
    >
        <svg
            class="h-5 w-5"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" />
        </svg>
    </button>

    <Modal :show="open" @close="closeModal">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-foreground">Un avis, un bug ?</h2>

            <p v-if="sent" class="mt-4 text-sm text-income">
                Merci, votre retour a bien été envoyé !
            </p>

            <form v-else @submit.prevent="submit">
                <textarea
                    v-model="form.message"
                    rows="4"
                    class="mt-4 w-full rounded-lg border-border bg-card text-foreground shadow-sm focus:border-ring focus:ring-ring"
                    placeholder="Dites-nous ce qui ne va pas ou ce que vous aimeriez voir…"
                    autofocus
                />
                <InputError class="mt-2" :message="form.errors.message" />

                <div class="mt-4 flex justify-end gap-3">
                    <button
                        type="button"
                        @click="closeModal"
                        class="rounded-lg px-4 py-2 text-sm text-muted-foreground hover:text-foreground"
                    >
                        Annuler
                    </button>
                    <Button variant="default" :disabled="form.processing || !form.message.trim()">
                        Envoyer
                    </Button>
                </div>
            </form>
        </div>
    </Modal>
</template>
