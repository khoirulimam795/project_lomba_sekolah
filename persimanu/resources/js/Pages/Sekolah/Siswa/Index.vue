<script setup>
import { Head, Link } from '@inertiajs/vue3';
import SekolahLayout from '@/Layouts/SekolahLayout.vue';

const props = defineProps({
    kontingen: { type: Object, default: null },
    slotsPutra: { type: Array, default: () => [] },
    slotsPutri: { type: Array, default: () => [] },
    kuotaPutra: { type: Number, default: 0 },
    kuotaPutri: { type: Number, default: 0 },
});

const filledPutra = props.slotsPutra.filter(s => s.filled).length;
const filledPutri = props.slotsPutri.filter(s => s.filled).length;
</script>

<template>
    <SekolahLayout header="Biodata Siswa">
        <Head title="Biodata Siswa" />
        <div class="px-2 sm:px-4 md:px-0 space-y-8">

            <!-- Ringkasan Kuota -->
            <div class="bg-white rounded-xl border border-line shadow-sm p-4 sm:p-6">
                <h2 class="font-display text-lg sm:text-xl font-semibold text-forest mb-4">Kuota Peserta</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-parchment/40 rounded-xl p-4 border border-line/60">
                        <div class="text-xs uppercase tracking-wider text-ink/50 font-semibold mb-1">Peserta Putra</div>
                        <div class="font-display text-2xl font-extrabold text-forest">{{ filledPutra }} / {{ kuotaPutra }}</div>
                        <div class="mt-2 h-2 rounded-full bg-line overflow-hidden">
                            <div class="h-full bg-forest rounded-full transition-all" :style="{ width: (kuotaPutra ? (filledPutra / kuotaPutra) * 100 : 0) + '%' }"></div>
                        </div>
                    </div>
                    <div class="bg-parchment/40 rounded-xl p-4 border border-line/60">
                        <div class="text-xs uppercase tracking-wider text-ink/50 font-semibold mb-1">Peserta Putri</div>
                        <div class="font-display text-2xl font-extrabold text-forest">{{ filledPutri }} / {{ kuotaPutri }}</div>
                        <div class="mt-2 h-2 rounded-full bg-line overflow-hidden">
                            <div class="h-full bg-forest rounded-full transition-all" :style="{ width: (kuotaPutri ? (filledPutri / kuotaPutri) * 100 : 0) + '%' }"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slot Putra -->
            <section v-if="kuotaPutra > 0">
                <h3 class="font-display text-lg sm:text-xl font-semibold text-forest mb-3">👦 Peserta Putra ({{ kuotaPutra }} Slot)</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <div v-for="s in slotsPutra" :key="'L-' + s.slot"
                        :class="['rounded-xl border p-4 transition-all', s.filled ? 'bg-white border-green-300 shadow-sm' : 'bg-parchment/30 border-dashed border-line']">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-ink/50">Slot #{{ s.slot }}</span>
                            <span v-if="s.filled" class="text-xs font-semibold text-green-600 bg-green-100 px-2 py-0.5 rounded-full">✅ Terisi</span>
                            <span v-else class="text-xs font-semibold text-amber-600 bg-amber-100 px-2 py-0.5 rounded-full">⬜ Kosong</span>
                        </div>
                        <div v-if="s.filled">
                            <div class="font-semibold text-forest">{{ s.siswa.nama }}</div>
                            <div class="text-xs text-ink/50 mt-0.5">NISN: {{ s.siswa.nisn || '-' }}</div>
                            <Link :href="route('sekolah.siswa.edit', { kontingen: kontingen.id, siswa: s.siswa.id })"
                                class="inline-block mt-2 text-xs font-semibold text-forest hover:text-gold">✏️ Edit</Link>
                        </div>
                        <div v-else>
                            <p class="text-sm text-ink/40 mb-2">Belum diisi</p>
                            <Link :href="route('sekolah.siswa.create', { kontingen: kontingen.id, slot: s.slot, jk: 'L' })"
                                class="inline-block px-4 py-2 bg-forest text-parchment rounded-lg text-xs font-semibold hover:bg-forest/90 transition">
                                + Isi Biodata
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Slot Putri -->
            <section v-if="kuotaPutri > 0">
                <h3 class="font-display text-lg sm:text-xl font-semibold text-forest mb-3">👧 Peserta Putri ({{ kuotaPutri }} Slot)</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <div v-for="s in slotsPutri" :key="'P-' + s.slot"
                        :class="['rounded-xl border p-4 transition-all', s.filled ? 'bg-white border-green-300 shadow-sm' : 'bg-parchment/30 border-dashed border-line']">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-ink/50">Slot #{{ s.slot }}</span>
                            <span v-if="s.filled" class="text-xs font-semibold text-green-600 bg-green-100 px-2 py-0.5 rounded-full">✅ Terisi</span>
                            <span v-else class="text-xs font-semibold text-amber-600 bg-amber-100 px-2 py-0.5 rounded-full">⬜ Kosong</span>
                        </div>
                        <div v-if="s.filled">
                            <div class="font-semibold text-forest">{{ s.siswa.nama }}</div>
                            <div class="text-xs text-ink/50 mt-0.5">NISN: {{ s.siswa.nisn || '-' }}</div>
                            <Link :href="route('sekolah.siswa.edit', { kontingen: kontingen.id, siswa: s.siswa.id })"
                                class="inline-block mt-2 text-xs font-semibold text-forest hover:text-gold">✏️ Edit</Link>
                        </div>
                        <div v-else>
                            <p class="text-sm text-ink/40 mb-2">Belum diisi</p>
                            <Link :href="route('sekolah.siswa.create', { kontingen: kontingen.id, slot: s.slot, jk: 'P' })"
                                class="inline-block px-4 py-2 bg-forest text-parchment rounded-lg text-xs font-semibold hover:bg-forest/90 transition">
                                + Isi Biodata
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Pesan jika kuota 0 semua -->
            <div v-if="kuotaPutra === 0 && kuotaPutri === 0" class="bg-white rounded-xl border border-line shadow-sm p-10 text-center text-ink/50">
                <div class="text-5xl mb-3">📋</div>
                <p class="font-semibold">Belum ada kuota peserta yang ditentukan.</p>
                <p class="text-xs mt-1">Isi Formulir Kesediaan (C.01) terlebih dahulu.</p>
            </div>
        </div>
    </SekolahLayout>
</template>