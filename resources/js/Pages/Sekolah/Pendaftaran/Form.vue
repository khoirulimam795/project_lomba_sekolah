<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import SekolahLayout from '@/Layouts/SekolahLayout.vue';

const props = defineProps({
    event: { type: Object, default: null },
    pangkalan: { type: Object, default: null },
});

const form = useForm({
    event_id: props.event?.id ?? '',
    nama_kontingen: props.pangkalan?.name ?? '',
    contact_person: '',
    contact_phone: props.pangkalan?.no_telp ?? '',
    setuju: false,
});

const submit = () => {
    form.post(route('sekolah.pendaftaran.store'), { preserveScroll: true });
};

const formatDate = (v) => (v ? String(v).slice(0, 10).split('-').reverse().join('/') : '-');
</script>

<template>
    <SekolahLayout header="Form Kesediaan (C.01)">
        <Head title="Form Kesediaan" />

        <div class="max-w-3xl mx-auto px-2 sm:px-0">
            <div class="mb-6">
                <Link :href="route('sekolah.pendaftaran.index')" class="text-sm text-forest hover:underline">
                    ← Kembali ke pendaftaran
                </Link>
            </div>

            <!-- Info Event (read-only) -->
            <div class="bg-white rounded-xl border border-line shadow-sm p-4 sm:p-6 mb-4">
                <h2 class="font-display text-lg sm:text-xl font-semibold text-forest">{{ event?.nama }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-2 text-xs sm:text-sm text-ink/70">
                    <div>🗓️ Pendaftaran: {{ formatDate(event?.periode_pendaftaran_mulai) }} – {{ formatDate(event?.periode_pendaftaran_selesai) }}</div>
                    <div>📍 Pelaksanaan: {{ formatDate(event?.tanggal_pelaksanaan_mulai) }} – {{ formatDate(event?.tanggal_pelaksanaan_selesai) }}</div>
                </div>
            </div>

            <!-- Info Pangkalan (read-only) -->
            <div class="bg-parchment/40 rounded-xl border border-line/60 p-4 sm:p-6 mb-4">
                <h3 class="font-semibold text-forest mb-2">Data Pangkalan</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs sm:text-sm text-ink/70">
                    <div><span class="text-ink/40">Nama:</span> {{ pangkalan?.name }}</div>
                    <div><span class="text-ink/40">Jenjang:</span> {{ pangkalan?.jenjang ?? '-' }}</div>
                    <div><span class="text-ink/40">NPSN:</span> {{ pangkalan?.npsn ?? '-' }}</div>
                    <div><span class="text-ink/40">Alamat:</span> {{ pangkalan?.alamat ?? '-' }}</div>
                </div>
            </div>

            <!-- Form Kesediaan -->
            <form @submit.prevent="submit" class="bg-white rounded-xl border border-line shadow-sm p-4 sm:p-6 space-y-6">
                <div>
                    <label class="block font-semibold mb-1">Nama Kontingen</label>
                    <input
                        v-model="form.nama_kontingen"
                        type="text"
                        class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold"
                        placeholder="Nama kontingen (default: nama pangkalan)"
                    />
                    <div v-if="form.errors.nama_kontingen" class="text-red-600 text-sm mt-1">{{ form.errors.nama_kontingen }}</div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold mb-1">Contact Person</label>
                        <input
                            v-model="form.contact_person"
                            type="text"
                            class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold"
                            placeholder="Nama penanggung jawab"
                        />
                        <div v-if="form.errors.contact_person" class="text-red-600 text-sm mt-1">{{ form.errors.contact_person }}</div>
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">No. Telepon</label>
                        <input
                            v-model="form.contact_phone"
                            type="text"
                            class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold"
                            placeholder="Contoh: 081234567890"
                        />
                        <div v-if="form.errors.contact_phone" class="text-red-600 text-sm mt-1">{{ form.errors.contact_phone }}</div>
                    </div>
                </div>

                <div class="bg-parchment/40 rounded-lg border border-line/60 p-3 sm:p-4">
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input
                            v-model="form.setuju"
                            type="checkbox"
                            class="rounded border-line text-forest focus:ring-gold mt-0.5"
                        />
                        <span class="text-xs sm:text-sm text-ink/70">
                            Saya menyatakan bahwa pangkalan ini <strong>bersedia mengikuti</strong> event tersebut
                            dan akan mematuhi seluruh ketentuan yang berlaku.
                        </span>
                    </label>
                    <div v-if="form.errors.setuju" class="text-red-600 text-sm mt-1">{{ form.errors.setuju }}</div>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 pt-4 border-t border-line">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full sm:w-auto px-5 py-2 bg-forest text-parchment rounded-lg font-semibold hover:bg-forest/90 disabled:opacity-50 text-sm sm:text-base"
                    >
                        💾 Simpan Kesediaan
                    </button>
                    <Link
                        :href="route('sekolah.pendaftaran.index')"
                        class="w-full sm:w-auto text-center px-5 py-2 border border-line rounded-lg hover:bg-parchment text-sm sm:text-base"
                    >
                        Batal
                    </Link>
                </div>
            </form>
        </div>
    </SekolahLayout>
</template>