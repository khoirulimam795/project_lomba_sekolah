<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    event: {
        type: Object,
        default: null,
    },
});

console.log('event data:', props.event);

const isEditing = computed(() => Boolean(props.event?.id));

const formatDateValue = (value) => {
    return value ? String(value).slice(0, 10) : '';
};

const form = useForm({
    nama: props.event?.nama ?? '',
    deskripsi: props.event?.deskripsi ?? '',
    periode_pendaftaran_mulai: formatDateValue(props.event?.periode_pendaftaran_mulai),
    periode_pendaftaran_selesai: formatDateValue(props.event?.periode_pendaftaran_selesai),
    tanggal_pelaksanaan_mulai: formatDateValue(props.event?.tanggal_pelaksanaan_mulai),
    tanggal_pelaksanaan_selesai: formatDateValue(props.event?.tanggal_pelaksanaan_selesai),
    status: props.event?.status ?? 'draft',
});

const submit = () => {
    if (isEditing.value) {
        form.put(route('admin.events.update', props.event.id), {
            preserveScroll: true,
        });
    } else {
        form.post(route('admin.events.store'), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AdminLayout :header="isEditing ? 'Edit Event' : 'Tambah Event'">
        <Head :title="isEditing ? 'Edit Event' : 'Tambah Event'" />

        <div class="max-w-3xl mx-auto px-4 sm:px-0">
            <div class="mb-6">
                <Link
                    :href="route('admin.events.index')"
                    class="text-sm text-forest hover:underline inline-flex items-center"
                >
                    ← Kembali ke daftar event
                </Link>
            </div>

            <form
                @submit.prevent="submit"
                class="bg-white rounded-xl border border-line shadow-sm p-4 sm:p-6 space-y-6"
            >
                <!-- Nama -->
                <div>
                    <label class="block font-semibold mb-1">
                        Nama Event
                    </label>

                    <input
                        v-model="form.nama"
                        type="text"
                        class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold"
                        placeholder="Contoh: Persimanu Championship 2026"
                    />

                    <div
                        v-if="form.errors.nama"
                        class="text-red-600 text-sm mt-1"
                    >
                        {{ form.errors.nama }}
                    </div>
                </div>

                <!-- Deskripsi -->
                <div>
                    <label class="block font-semibold mb-1">
                        Deskripsi
                    </label>

                    <textarea
                        v-model="form.deskripsi"
                        rows="4"
                        class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold"
                        placeholder="Deskripsi singkat event"
                    />

                    <div
                        v-if="form.errors.deskripsi"
                        class="text-red-600 text-sm mt-1"
                    >
                        {{ form.errors.deskripsi }}
                    </div>
                </div>

                <!-- Periode Pendaftaran -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold mb-1">
                            Pendaftaran Mulai
                        </label>

                        <input
                            v-model="form.periode_pendaftaran_mulai"
                            type="date"
                            class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold"
                        />

                        <div
                            v-if="form.errors.periode_pendaftaran_mulai"
                            class="text-red-600 text-sm mt-1"
                        >
                            {{ form.errors.periode_pendaftaran_mulai }}
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold mb-1">
                            Pendaftaran Selesai
                        </label>

                        <input
                            v-model="form.periode_pendaftaran_selesai"
                            type="date"
                            class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold"
                        />

                        <div
                            v-if="form.errors.periode_pendaftaran_selesai"
                            class="text-red-600 text-sm mt-1"
                        >
                            {{ form.errors.periode_pendaftaran_selesai }}
                        </div>
                    </div>
                </div>

                <!-- Tanggal Pelaksanaan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold mb-1">
                            Pelaksanaan Mulai
                        </label>

                        <input
                            v-model="form.tanggal_pelaksanaan_mulai"
                            type="date"
                            class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold"
                        />

                        <div
                            v-if="form.errors.tanggal_pelaksanaan_mulai"
                            class="text-red-600 text-sm mt-1"
                        >
                            {{ form.errors.tanggal_pelaksanaan_mulai }}
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold mb-1">
                            Pelaksanaan Selesai
                        </label>

                        <input
                            v-model="form.tanggal_pelaksanaan_selesai"
                            type="date"
                            class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold"
                        />

                        <div
                            v-if="form.errors.tanggal_pelaksanaan_selesai"
                            class="text-red-600 text-sm mt-1"
                        >
                            {{ form.errors.tanggal_pelaksanaan_selesai }}
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div>
                    <label class="block font-semibold mb-1">
                        Status
                    </label>

                    <select
                        v-model="form.status"
                        class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold"
                    >
                        <option value="draft">Draft</option>
                        <option value="aktif">Aktif</option>
                        <option value="selesai">Selesai</option>
                    </select>

                    <div
                        v-if="form.errors.status"
                        class="text-red-600 text-sm mt-1"
                    >
                        {{ form.errors.status }}
                    </div>
                </div>

                <!-- Action -->
                <div class="flex flex-col sm:flex-row items-center gap-3 pt-4 border-t border-line">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full sm:w-auto px-5 py-2 bg-forest text-parchment rounded-lg font-semibold hover:bg-forest/90 disabled:opacity-50"
                    >
                        {{ isEditing ? 'Update Event' : 'Simpan Event' }}
                    </button>

                    <Link
                        :href="route('admin.events.index')"
                        class="w-full sm:w-auto text-center px-5 py-2 border border-line rounded-lg hover:bg-parchment"
                    >
                        Batal
                    </Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>