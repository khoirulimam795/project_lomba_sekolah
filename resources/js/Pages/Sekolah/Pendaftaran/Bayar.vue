<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import SekolahLayout from '@/Layouts/SekolahLayout.vue';

const props = defineProps({
    kontingen: { type: Object, default: null },
    bukti: { type: Object, default: null },
});

const form = useForm({
    bukti_pembayaran: null,
});

const onFileChange = (e) => {
    form.bukti_pembayaran = e.target.files[0];
};

const submit = () => {
    form.post(route('sekolah.pendaftaran.bayar.upload', props.kontingen.id), {
        preserveScroll: true,
    });
};

const isImage = (mime) => mime?.startsWith('image/');
</script>

<template>
    <SekolahLayout header="Upload Bukti Pembayaran">
        <Head title="Upload Bukti Pembayaran" />

        <div class="max-w-3xl mx-auto px-2 sm:px-0">
            <div class="mb-6">
                <Link :href="route('sekolah.pendaftaran.index')" class="text-sm text-forest hover:underline">
                    ← Kembali ke pendaftaran
                </Link>
            </div>

            <!-- Info kontingen -->
            <div class="bg-white rounded-xl border border-line shadow-sm p-4 sm:p-6 mb-4">
                <h2 class="font-display text-lg sm:text-xl font-semibold text-forest">
                    {{ kontingen?.event?.nama }}
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-2 text-xs sm:text-sm text-ink/70">
                    <div><span class="text-ink/40">Kontingen:</span> {{ kontingen?.nama_kontingen }}</div>
                    <div><span class="text-ink/40">Status:</span> {{ kontingen?.status }}</div>
                </div>
            </div>

            <!-- Alert kalau ditolak -->
            <div
                v-if="kontingen?.status === 'pembayaran_ditolak'"
                class="bg-red-50 border border-red-300 text-red-800 rounded-xl px-4 py-3 mb-4 text-sm"
            >
                ⚠️ Pembayaran Anda ditolak.
                <span v-if="kontingen?.catatan_pembayaran">
                    Catatan Admin: <strong>{{ kontingen.catatan_pembayaran }}</strong>
                </span>
                Silakan upload ulang bukti pembayaran yang benar.
            </div>

            <!-- Preview bukti lama (kalau ada) -->
            <div v-if="bukti" class="bg-parchment/40 rounded-xl border border-line/60 p-4 mb-4">
                <p class="text-xs sm:text-sm font-semibold text-ink/60 mb-2">Bukti saat ini: {{ bukti.name }}</p>
                <img
                    v-if="isImage(bukti.mime)"
                    :src="bukti.url"
                    class="max-h-64 rounded-lg border border-line"
                    alt="Bukti pembayaran"
                />
                <a
                    v-else
                    :href="bukti.url"
                    target="_blank"
                    class="text-forest underline text-sm"
                >
                    📄 Lihat file PDF
                </a>
            </div>

            <!-- Form upload -->
            <form @submit.prevent="submit" class="bg-white rounded-xl border border-line shadow-sm p-4 sm:p-6 space-y-6">
                <div>
                    <label class="block font-semibold mb-1">Bukti Pembayaran</label>
                    <input
                        type="file"
                        accept=".jpg,.jpeg,.png,.pdf"
                        @change="onFileChange"
                        class="w-full text-sm text-ink/70 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-forest file:text-parchment file:font-semibold hover:file:bg-forest/90"
                    />
                    <p class="text-xs text-ink/50 mt-1">Format: JPG, PNG, atau PDF. Maksimal 2 MB.</p>
                    <div v-if="form.errors.bukti_pembayaran" class="text-red-600 text-sm mt-1">
                        {{ form.errors.bukti_pembayaran }}
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 pt-4 border-t border-line">
                    <button
                        type="submit"
                        :disabled="form.processing || !form.bukti_pembayaran"
                        class="w-full sm:w-auto px-5 py-2 bg-gold text-ink rounded-lg font-semibold hover:opacity-90 disabled:opacity-50 text-sm sm:text-base"
                    >
                        📤 Upload & Kirim untuk Approval
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