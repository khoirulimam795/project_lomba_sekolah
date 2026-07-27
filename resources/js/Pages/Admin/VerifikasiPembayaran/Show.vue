<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    kontingen: { type: Object, default: null },
    bukti: { type: Object, default: null },
});

const rejectForm = useForm({
    catatan_pembayaran: '',
});

const approve = () => {
    if (!confirm('Setujui pembayaran kontingen ini?')) return;
    router.post(route('admin.verifikasi-pembayaran.approve', props.kontingen.id), {}, { preserveScroll: true });
};

const reject = () => {
    rejectForm.post(route('admin.verifikasi-pembayaran.reject', props.kontingen.id), { preserveScroll: true });
};

const isImage = (mime) => mime?.startsWith('image/');
</script>

<template>
    <AdminLayout header="Detail Verifikasi Pembayaran">
        <Head title="Detail Verifikasi Pembayaran" />

        <div class="max-w-4xl mx-auto px-2 sm:px-0">
            <div class="mb-6">
                <Link :href="route('admin.verifikasi-pembayaran.index')" class="text-sm text-forest hover:underline">
                    ← Kembali ke daftar
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                <!-- Detail kontingen -->
                <div class="bg-white rounded-xl border border-line shadow-sm p-4 sm:p-6 space-y-3">
                    <h2 class="font-display text-lg sm:text-xl font-semibold text-forest">Info Kontingen</h2>

                    <div class="space-y-2 text-sm">
                        <div><span class="text-ink/40">Event:</span> <span class="font-medium">{{ kontingen?.event?.nama }}</span></div>
                        <div><span class="text-ink/40">Pangkalan:</span> <span class="font-medium">{{ kontingen?.team?.name }}</span></div>
                        <div><span class="text-ink/40">Jenjang:</span> {{ kontingen?.team?.jenjang ?? '-' }}</div>
                        <div><span class="text-ink/40">Nama Kontingen:</span> {{ kontingen?.nama_kontingen }}</div>
                        <div><span class="text-ink/40">Contact Person:</span> {{ kontingen?.contact_person ?? '-' }}</div>
                        <div><span class="text-ink/40">No. Telp:</span> {{ kontingen?.contact_phone ?? '-' }}</div>
                        <div>
                            <span class="text-ink/40">Status:</span>
                            <span class="ml-1 px-2 py-0.5 rounded-full border text-xs font-semibold bg-amber-100 text-amber-700 border-amber-300">
                                {{ kontingen?.status }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Preview bukti -->
                <div class="bg-white rounded-xl border border-line shadow-sm p-4 sm:p-6">
                    <h2 class="font-display text-lg sm:text-xl font-semibold text-forest mb-3">Bukti Pembayaran</h2>

                    <div v-if="!bukti" class="text-ink/50 text-sm">
                        ⚠️ Tidak ada file bukti pembayaran.
                    </div>

                    <template v-else>
                        <img
                            v-if="isImage(bukti.mime)"
                            :src="bukti.url"
                            class="w-full max-h-96 object-contain rounded-lg border border-line"
                            alt="Bukti pembayaran"
                        />
                        <iframe
                            v-else
                            :src="bukti.url"
                            class="w-full h-96 rounded-lg border border-line"
                        />
                        <a
                            :href="bukti.url"
                            target="_blank"
                            class="inline-block mt-3 text-sm text-forest underline"
                        >
                            📎 Buka file di tab baru ({{ bukti.name }})
                        </a>
                    </template>
                </div>
            </div>

            <!-- Aksi approve / reject -->
            <div class="bg-white rounded-xl border border-line shadow-sm p-4 sm:p-6 mt-4 sm:mt-6 space-y-4">
                <div class="flex flex-col sm:flex-row gap-3">
                    <button
                        @click="approve"
                        class="flex-1 px-5 py-2.5 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 text-sm sm:text-base"
                    >
                        ✅ Setujui Pembayaran
                    </button>
                </div>

                <div class="border-t border-line pt-4">
                    <label class="block font-semibold mb-1 text-sm">Tolak dengan catatan</label>
                    <textarea
                        v-model="rejectForm.catatan_pembayaran"
                        rows="3"
                        class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-400 text-sm"
                        placeholder="Tulis alasan penolakan (wajib)..."
                    />
                    <div v-if="rejectForm.errors.catatan_pembayaran" class="text-red-600 text-sm mt-1">
                        {{ rejectForm.errors.catatan_pembayaran }}
                    </div>
                    <button
                        @click="reject"
                        :disabled="rejectForm.processing || !rejectForm.catatan_pembayaran"
                        class="mt-3 px-5 py-2.5 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 disabled:opacity-50 text-sm sm:text-base"
                    >
                        ❌ Tolak Pembayaran
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>