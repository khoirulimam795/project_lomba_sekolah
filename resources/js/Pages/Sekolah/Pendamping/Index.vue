<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import SekolahLayout from '@/Layouts/SekolahLayout.vue';

const props = defineProps({
    kontingen: { type: Object, default: null },
    pendampings: { type: Array, default: () => [] },
});

const statusLabel = { pending: 'Menunggu Verifikasi', approved: 'Disetujui', rejected: 'Ditolak' };
const statusClass = {
    pending: 'bg-amber-100 text-amber-700 border-amber-300',
    approved: 'bg-green-100 text-green-700 border-green-300',
    rejected: 'bg-red-100 text-red-700 border-red-300',
};

const deletePendamping = (p) => {
    if (!confirm(`Hapus data pendamping "${p.nama}"?`)) return;
    router.delete(route('sekolah.pendamping.destroy', { kontingen: props.kontingen.id, pendamping: p.id }), {
        preserveScroll: true,
    });
};
</script>

<template>
    <SekolahLayout header="Biodata Pendamping">
        <Head title="Biodata Pendamping" />

        <div class="px-2 sm:px-4 md:px-0 space-y-6">
            <!-- Info + navigasi -->
            <div class="bg-white rounded-xl border border-line shadow-sm p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h2 class="font-display text-lg sm:text-xl font-semibold text-forest">
                            {{ kontingen?.event?.nama }}
                        </h2>
                        <p class="text-xs sm:text-sm text-ink/60 mt-1">
                            Kontingen: {{ kontingen?.nama_kontingen }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <Link
                            :href="route('sekolah.pendaftaran.index')"
                            class="px-3 py-1.5 border border-line rounded-lg text-xs sm:text-sm hover:bg-parchment"
                        >
                            ← Pendaftaran
                        </Link>
                        <Link
                            :href="route('sekolah.siswa.index', { kontingen: kontingen.id })"
                            class="px-3 py-1.5 bg-forest text-parchment rounded-lg text-xs sm:text-sm hover:bg-forest/90"
                        >
                            🧑🎓 Siswa
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Header + tombol tambah -->
            <div class="flex items-center justify-between">
                <h3 class="font-display text-lg sm:text-xl font-semibold text-forest">
                    Daftar Pendamping ({{ pendampings.length }})
                </h3>
                <Link
                    :href="route('sekolah.pendamping.create', { kontingen: kontingen.id })"
                    class="px-4 py-2 bg-gold text-ink font-semibold rounded-lg hover:opacity-90 text-sm sm:text-base"
                >
                    ✨ Tambah Pendamping
                </Link>
            </div>

            <!-- List -->
            <div v-if="pendampings.length === 0" class="bg-white rounded-xl border border-line shadow-sm p-8 text-center text-ink/50">
                <div class="text-4xl mb-3">🧑‍</div>
                <p>Belum ada data pendamping.</p>
            </div>

            <div v-else class="space-y-3">
                <div
                    v-for="p in pendampings"
                    :key="p.id"
                    class="bg-white rounded-xl border border-line shadow-sm p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                >
                    <div class="min-w-0">
                        <div class="font-semibold text-forest text-base sm:text-lg">{{ p.nama }}</div>
                        <div class="text-xs sm:text-sm text-ink/60 mt-0.5">
                            {{ p.jabatan || '-' }}
                            <span v-if="p.asal_instansi"> • {{ p.asal_instansi }}</span>
                            <span v-if="p.golongan_binaan"> • Binaan: {{ p.golongan_binaan }}</span>
                        </div>
                        <div v-if="p.status_verifikasi === 'rejected' && p.catatan_verifikasi" class="text-xs text-red-600 mt-1">
                            Catatan admin: {{ p.catatan_verifikasi }}
                        </div>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                        <span :class="['px-3 py-1 rounded-full border text-[10px] sm:text-xs font-semibold whitespace-nowrap', statusClass[p.status_verifikasi]]">
                            {{ statusLabel[p.status_verifikasi] }}
                        </span>

                        <template v-if="p.status_verifikasi !== 'approved'">
                            <Link
                                :href="route('sekolah.pendamping.edit', { kontingen: kontingen.id, pendamping: p.id })"
                                class="px-3 py-1 bg-khaki text-white rounded-lg text-[10px] sm:text-xs hover:opacity-90"
                            >
                                ✏️
                            </Link>
                            <button
                                @click="deletePendamping(p)"
                                class="px-3 py-1 bg-red-600 text-white rounded-lg text-[10px] sm:text-xs hover:bg-red-700"
                            >
                                🗑️
                            </button>
                        </template>
                        <span v-else class="text-[10px] sm:text-xs text-ink/40">🔒 Terkunci</span>
                    </div>
                </div>
            </div>
        </div>
    </SekolahLayout>
</template>