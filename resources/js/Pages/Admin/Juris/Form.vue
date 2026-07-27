<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    juri: {
        type: Object,
        default: null,
    },
});

const isEditing = computed(() => Boolean(props.juri?.id));

const form = useForm({
    name: props.juri?.name ?? '',
    email: props.juri?.email ?? '',
    password: '',
    no_hp: props.juri?.no_hp ?? '',
    is_active: props.juri?.is_active ?? true,
});

const submit = () => {
    if (isEditing.value) {
        form.put(route('admin.juris.update', props.juri.id), {
            preserveScroll: true,
        });
    } else {
        form.post(route('admin.juris.store'), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AdminLayout :header="isEditing ? 'Edit Juri' : 'Tambah Juri'">
        <Head :title="isEditing ? 'Edit Juri' : 'Tambah Juri'" />

        <div class="max-w-3xl">
            <div class="mb-6">
                <Link
                    :href="route('admin.juris.index')"
                    class="text-sm text-forest hover:underline"
                >
                    ← Kembali ke daftar juri
                </Link>
            </div>

            <form
                @submit.prevent="submit"
                class="bg-white rounded-xl border border-line shadow-sm p-6 space-y-6"
            >
                <div>
                    <label class="block font-semibold mb-1">Nama Juri</label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold"
                        placeholder="Nama lengkap juri"
                    />
                    <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">
                        {{ form.errors.name }}
                    </div>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold"
                        placeholder="email@juri.com"
                    />
                    <div v-if="form.errors.email" class="text-red-600 text-sm mt-1">
                        {{ form.errors.email }}
                    </div>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Password</label>
                    <input
                        v-model="form.password"
                        type="password"
                        class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold"
                        :placeholder="isEditing ? 'Kosongkan jika tidak diubah' : 'Minimal 8 karakter'"
                    />
                    <div v-if="form.errors.password" class="text-red-600 text-sm mt-1">
                        {{ form.errors.password }}
                    </div>
                </div>

                <div>
                    <label class="block font-semibold mb-1">No. HP</label>
                    <input
                        v-model="form.no_hp"
                        type="text"
                        class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold"
                        placeholder="Opsional"
                    />
                    <div v-if="form.errors.no_hp" class="text-red-600 text-sm mt-1">
                        {{ form.errors.no_hp }}
                    </div>
                </div>

                <div>
                    <label class="flex items-center gap-2">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            class="rounded border-line text-forest focus:ring-gold"
                        />
                        <span class="font-semibold">Akun aktif</span>
                    </label>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-line">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-5 py-2 bg-forest text-parchment rounded-lg font-semibold hover:bg-forest/90 disabled:opacity-50"
                    >
                        {{ isEditing ? 'Update Juri' : 'Simpan Juri' }}
                    </button>

                    <Link
                        :href="route('admin.juris.index')"
                        class="px-5 py-2 border border-line rounded-lg hover:bg-parchment"
                    >
                        Batal
                    </Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>