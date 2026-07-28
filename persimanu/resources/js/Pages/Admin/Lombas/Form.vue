<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    lomba: { type: Object, default: null },
    events: { type: Array, default: () => [] },
});

const isEditing = computed(() => Boolean(props.lomba?.id));

const form = useForm({
    event_id: props.lomba?.event_id ?? '',
    nama: props.lomba?.nama ?? '',
    deskripsi: props.lomba?.deskripsi ?? '',
    status: props.lomba?.status ?? 'draft',
});

const submit = () => {
    if (isEditing.value) {
        form.put(route('admin.lombas.update', props.lomba.id), { preserveScroll: true });
    } else {
        form.post(route('admin.lombas.store'), { preserveScroll: true });
    }
};
</script>

<template>
    <AdminLayout :header="isEditing ? 'Edit Lomba' : 'Tambah Lomba'">
        <Head :title="isEditing ? 'Edit Lomba' : 'Tambah Lomba'" />

        <div class="max-w-3xl">
            <div class="mb-6">
                <Link :href="route('admin.lombas.index')" class="text-sm text-forest hover:underline">
                    ← Kembali ke daftar lomba
                </Link>
            </div>

            <form
                @submit.prevent="submit"
                class="bg-white rounded-xl border border-line shadow-sm p-6 space-y-6"
            >
                <div>
                    <label class="block font-semibold mb-1">Event</label>
                    <select
                        v-model="form.event_id"
                        class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold"
                    >
                        <option value="">Pilih Event</option>
                        <option v-for="ev in props.events" :key="ev.id" :value="ev.id">
                            {{ ev.nama }}
                        </option>
                    </select>
                    <div v-if="form.errors.event_id" class="text-red-600 text-sm mt-1">
                        {{ form.errors.event_id }}
                    </div>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Nama Lomba</label>
                    <input
                        v-model="form.nama"
                        type="text"
                        class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold"
                        placeholder="Contoh: Cerdas Cermat Pramuka"
                    />
                    <div v-if="form.errors.nama" class="text-red-600 text-sm mt-1">
                        {{ form.errors.nama }}
                    </div>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Deskripsi</label>
                    <textarea
                        v-model="form.deskripsi"
                        rows="4"
                        class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold"
                        placeholder="Deskripsi singkat lomba"
                    />
                    <div v-if="form.errors.deskripsi" class="text-red-600 text-sm mt-1">
                        {{ form.errors.deskripsi }}
                    </div>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Status</label>
                    <select
                        v-model="form.status"
                        class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold"
                    >
                        <option value="draft">Draft</option>
                        <option value="aktif">Aktif</option>
                        <option value="selesai">Selesai</option>
                    </select>
                    <div v-if="form.errors.status" class="text-red-600 text-sm mt-1">
                        {{ form.errors.status }}
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-line">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-5 py-2 bg-forest text-parchment rounded-lg font-semibold hover:bg-forest/90 disabled:opacity-50"
                    >
                        {{ isEditing ? 'Update Lomba' : 'Simpan Lomba' }}
                    </button>
                    <Link
                        :href="route('admin.lombas.index')"
                        class="px-5 py-2 border border-line rounded-lg hover:bg-parchment"
                    >
                        Batal
                    </Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>