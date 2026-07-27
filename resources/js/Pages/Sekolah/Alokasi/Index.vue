<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import SekolahLayout from '@/Layouts/SekolahLayout.vue';

const props = defineProps({
    kontingen: { type: Object, default: null },
    alokasi: { type: Array, default: () => [] },
});

const statusLabel = { draft: 'Draft', siap: 'Siap', selesai: 'Selesai' };
const statusClass = {
    draft: 'bg-gray-100 text-gray-700 border-gray-300',
    siap: 'bg-green-100 text-green-700 border-green-300',
    selesai: 'bg-blue-100 text-blue-700 border-blue-300',
};

const hapus = (a) => {
    if (!confirm(`Hapus alokasi "${a.lomba?.nama}" (${a.golongan})?`)) return;
    router.delete(route('sekolah.alokasi.destroy', { kontingen: props.kontingen.id, alokasi: a.id }), { preserveScroll: true });
};
</script>

<template>
    <SekolahLayout header="Alokasi Lomba">
        <Head title="Alokasi Lomba" />

        <div class="px-2 sm:px-4 md:px-0 space-y-6">
            <!-- Info + nav -->
            <div class="bg-white rounded-xl border border-line shadow-sm p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h2 class="font-display text-lg sm:text-xl font-semibold text-forest">{{ kontingen?.event?.nama }}</h2>
                        <p class="text-xs sm:text-sm text-ink/60 mt-1">Kontingen: {{ kontingen?.nama_kontingen }}</p>
                    </div>
                    <div class="flex gap-2">
                        <Link :href="route('sekolah.pendaftaran.index')" class="px-3 py-1.5 border border-line rounded-lg text-xs sm:text-sm hover:bg-parchment">← Pendaftaran</Link>
                        <Link :href="route('sekolah.siswa.index', { kontingen: kontingen.id })" class="px-3 py-1.5 bg-forest text-parchment rounded-lg text-xs sm:text-sm hover:bg-forest/90">🧑🎓 Biodata</Link>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <h3 class="font-display text-lg sm:text-xl font-semibold text-forest">Lomba yang Diikuti ({{ alokasi.length }})</h3>
                <Link :href="route('sekolah.alokasi.create', { kontingen: kontingen.id })" class="px-4 py-2 bg-gold text-ink font-semibold rounded-lg hover:opacity-90 text-sm sm:text-base">✨ Tambah Alokasi</Link>
            </div>

            <div v-if="alokasi.length === 0" class="bg-white rounded-xl border border-line shadow-sm p-8 text-center text-ink/50">
                <div class="text-4xl mb-3">🏅</div>
                <p>Belum ada lomba yang dialokasikan.</p>
            </div>

            <div v-else class="space-y-3">
                <div v-for="a in alokasi" :key="a.id" class="bg-white rounded-xl border border-line shadow-sm p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-semibold text-forest text-base sm:text-lg">{{ a.lomba?.nama ?? '-' }}</div>
                        <div class="text-xs sm:text-sm text-ink/60 mt-0.5">
                            Golongan <span class="font-semibold">{{ a.golongan }}</span>
                            • 👥 {{ a.siswas?.length ?? 0 }}/10 siswa
                            • 🧑🏫 {{ a.pendamping?.nama ?? 'belum ditunjuk' }}
                            <span v-if="a.nomor_urut_tampil"> • No. Urut <span class="font-mono font-semibold">{{ a.nomor_urut_tampil }}</span></span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                        <span :class="['px-3 py-1 rounded-full border text-[10px] sm:text-xs font-semibold whitespace-nowrap', statusClass[a.status]]">{{ statusLabel[a.status] }}</span>
                        <template v-if="a.status === 'draft' && !a.nomor_urut_tampil">
                            <Link :href="route('sekolah.alokasi.edit', { kontingen: kontingen.id, alokasi: a.id })" class="px-3 py-1 bg-khaki text-white rounded-lg text-[10px] sm:text-xs hover:opacity-90">✏️</Link>
                            <button @click="hapus(a)" class="px-3 py-1 bg-red-600 text-white rounded-lg text-[10px] sm:text-xs hover:bg-red-700">🗑️</button>
                        </template>
                        <span v-else class="text-[10px] sm:text-xs text-ink/40">🔒 Terkunci</span>
                    </div>
                </div>
            </div>
        </div>
    </SekolahLayout>
</template>