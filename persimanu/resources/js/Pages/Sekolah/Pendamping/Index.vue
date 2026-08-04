<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import SekolahLayout from '@/Layouts/SekolahLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    kontingen: { type: Object, default: null },
    slotsPutra: { type: Array, default: () => [] },
    slotsPutri: { type: Array, default: () => [] },
    kuotaPutra: { type: Number, default: 0 },
    kuotaPutri: { type: Number, default: 0 },
});

const statusLabel = { pending: 'Menunggu Verifikasi', approved: 'Disetujui', rejected: 'Ditolak' };
const statusClass = {
    pending: 'bg-amber-100 text-amber-700 border-amber-300',
    approved: 'bg-green-100 text-green-700 border-green-300',
    rejected: 'bg-red-100 text-red-700 border-red-300',
};

const filledPutra = computed(() => props.slotsPutra.filter((s) => s.filled).length);
const filledPutri = computed(() => props.slotsPutri.filter((s) => s.filled).length);
const pct = (filled, total) => (total > 0 ? Math.min(100, Math.round((filled / total) * 100)) : 0);
const isFull = (filled, total) => total > 0 && filled >= total;
const sisa = (filled, total) => Math.max(0, total - filled);

const deletePendamping = (p) => {
    if (!confirm(`Hapus data pendamping "${p.nama}"?`)) return;
    router.delete(route('sekolah.pendamping.destroy', { kontingen: props.kontingen.id, pendamping: p.id }), { preserveScroll: true });
};
</script>

<template>
    <SekolahLayout header="Biodata Pendamping">
        <Head title="Biodata Pendamping" />

        <div class="relative px-2 sm:px-4 md:px-0 space-y-7">
            <!-- ambient tipis -->
            <div class="pointer-events-none absolute inset-x-0 top-0 h-48 bg-[radial-gradient(60%_100%_at_50%_0%,theme(colors.forest/5%),transparent)]"></div>

            <!-- Info + navigasi -->
            <div class="relative bg-white rounded-xl border border-line shadow-sm p-4 sm:p-6 overflow-hidden">
                <span class="pointer-events-none absolute left-0 top-0 bottom-0 w-1.5 bg-gradient-to-b from-forest to-gold"></span>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pl-2">
                    <div class="min-w-0">
                        <h2 class="font-display text-lg sm:text-xl font-extrabold text-forest tracking-tight">
                            {{ kontingen?.event?.nama ?? '-' }}
                        </h2>
                        <p class="text-xs sm:text-sm text-ink/60 mt-0.5">Kontingen: {{ kontingen?.nama_kontingen ?? '-' }}</p>
                    </div>
                    <div class="flex gap-2 flex-shrink-0">
                        <Link :href="route('sekolah.pendaftaran.index')"
                            class="px-3 py-1.5 border border-line rounded-lg text-xs sm:text-sm hover:bg-parchment transition-colors">← Pendaftaran</Link>
                        <Link :href="route('sekolah.siswa.index', { kontingen: kontingen.id })"
                            class="px-3 py-1.5 bg-forest text-parchment rounded-lg text-xs sm:text-sm hover:bg-forest/90 transition-colors">🧑‍🎓 Siswa</Link>
                    </div>
                </div>
            </div>

            <!-- Ringkasan kuota -->
            <div class="relative bg-white rounded-xl border border-line shadow-sm p-4 sm:p-6">
                <h3 class="font-display text-base sm:text-lg font-extrabold text-forest mb-4 tracking-tight">Kuota Pendamping</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- putra -->
                    <div class="rounded-xl p-4 border transition-colors" :class="isFull(filledPutra, kuotaPutra) ? 'bg-green-50/60 border-green-200' : 'bg-parchment/40 border-line/60'">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-[10px] uppercase tracking-wider text-ink/50 font-semibold">👨 Pendamping Putra</span>
                            <span v-if="kuotaPutra > 0 && isFull(filledPutra, kuotaPutra)" class="text-[10px] font-bold text-green-600 bg-green-100 px-2 py-0.5 rounded-full">✅ Terpenuhi</span>
                            <span v-else-if="kuotaPutra > 0" class="text-[10px] font-bold text-amber-600 bg-amber-100 px-2 py-0.5 rounded-full">⬜ {{ sisa(filledPutra, kuotaPutra) }} kosong</span>
                        </div>
                        <div class="font-display text-3xl font-extrabold text-forest tabular-nums leading-none">
                            {{ filledPutra }}<span class="text-ink/30 text-xl"> / {{ kuotaPutra }}</span>
                        </div>
                        <div class="mt-3 h-2 rounded-full bg-line overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-forest to-gold transition-all duration-700 ease-out" :style="{ width: pct(filledPutra, kuotaPutra) + '%' }"></div>
                        </div>
                    </div>
                    <!-- putri -->
                    <div class="rounded-xl p-4 border transition-colors" :class="isFull(filledPutri, kuotaPutri) ? 'bg-green-50/60 border-green-200' : 'bg-parchment/40 border-line/60'">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-[10px] uppercase tracking-wider text-ink/50 font-semibold">👩 Pendamping Putri</span>
                            <span v-if="kuotaPutri > 0 && isFull(filledPutri, kuotaPutri)" class="text-[10px] font-bold text-green-600 bg-green-100 px-2 py-0.5 rounded-full">✅ Terpenuhi</span>
                            <span v-else-if="kuotaPutri > 0" class="text-[10px] font-bold text-amber-600 bg-amber-100 px-2 py-0.5 rounded-full">⬜ {{ sisa(filledPutri, kuotaPutri) }} kosong</span>
                        </div>
                        <div class="font-display text-3xl font-extrabold text-forest tabular-nums leading-none">
                            {{ filledPutri }}<span class="text-ink/30 text-xl"> / {{ kuotaPutri }}</span>
                        </div>
                        <div class="mt-3 h-2 rounded-full bg-line overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-forest to-gold transition-all duration-700 ease-out" :style="{ width: pct(filledPutri, kuotaPutri) + '%' }"></div>
                        </div>
                    </div>
                </div>
                <p class="text-[11px] text-ink/45 mt-3">Isi biodata lewat tombol <strong>+ Isi Biodata</strong> pada slot kosong. Jika kuota suatu kategori sudah penuh, slot tambah tidak tersedia.</p>
            </div>

            <!-- Slot Putra -->
            <section v-if="kuotaPutra > 0">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-display text-lg sm:text-xl font-extrabold text-forest tracking-tight">👨 Pendamping Putra <span class="text-ink/40 text-base font-semibold">({{ kuotaPutra }} slot)</span></h3>
                    <span v-if="isFull(filledPutra, kuotaPutra)" class="text-[10px] sm:text-xs font-bold text-green-700 bg-green-100 border border-green-300 px-2.5 py-1 rounded-full">🔒 Kuota Terkunci</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <div v-for="s in slotsPutra" :key="'L-' + s.slot"
                        class="group rounded-xl border p-4 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md"
                        :class="s.filled ? 'bg-white border-green-300 shadow-sm hover:border-green-400' : 'bg-parchment/30 border-dashed border-line hover:border-gold/60'">
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-ink/50">
                                <span class="w-6 h-6 rounded-md bg-forest/10 text-forest flex items-center justify-center text-[11px] font-extrabold tabular-nums">{{ s.slot }}</span>
                                Slot #{{ s.slot }}
                            </span>
                            <span v-if="s.filled" class="text-[10px] font-semibold text-green-600 bg-green-100 px-2 py-0.5 rounded-full">✅ Terisi</span>
                            <span v-else class="text-[10px] font-semibold text-amber-600 bg-amber-100 px-2 py-0.5 rounded-full">⬜ Kosong</span>
                        </div>

                        <div v-if="s.filled" class="space-y-2">
                            <div class="font-semibold text-forest text-base leading-tight">{{ s.pendamping.nama }}</div>
                            <div class="text-xs text-ink/60 space-y-0.5">
                                <div>{{ s.pendamping.jabatan || '-' }}</div>
                                <div v-if="s.pendamping.asal_instansi" class="truncate">{{ s.pendamping.asal_instansi }}</div>
                            </div>
                            <span :class="['inline-block px-2.5 py-0.5 rounded-full border text-[10px] font-semibold', statusClass[s.pendamping.status_verifikasi]]">
                                {{ statusLabel[s.pendamping.status_verifikasi] }}
                            </span>
                            <div v-if="s.pendamping.status_verifikasi === 'rejected' && s.pendamping.catatan_verifikasi" class="text-[11px] text-red-600">Catatan: {{ s.pendamping.catatan_verifikasi }}</div>
                            <div v-if="s.pendamping.status_verifikasi !== 'approved'" class="flex items-center gap-2 pt-1">
                                <Link :href="route('sekolah.pendamping.edit', { kontingen: kontingen.id, pendamping: s.pendamping.id })"
                                    class="px-3 py-1 bg-khaki text-white rounded-lg text-[11px] font-semibold hover:opacity-90 active:scale-95 transition-all">✏️ Edit</Link>
                                <button @click="deletePendamping(s.pendamping)"
                                    class="px-3 py-1 bg-red-600 text-white rounded-lg text-[11px] font-semibold hover:bg-red-700 active:scale-95 transition-all">🗑️ Hapus</button>
                            </div>
                            <span v-else class="inline-block text-[11px] text-ink/40 pt-1">🔒 Terkunci (sudah disetujui)</span>
                        </div>

                        <div v-else class="flex flex-col items-start gap-2">
                            <p class="text-sm text-ink/40">Slot belum terisi.</p>
                            <Link :href="route('sekolah.pendamping.create', { kontingen: kontingen.id, slot: s.slot, jk: 'L' })"
                                class="inline-flex items-center gap-1 px-4 py-2 bg-forest text-parchment rounded-lg text-xs font-semibold hover:bg-forest/90 active:scale-95 transition-all">+ Isi Biodata</Link>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Slot Putri -->
            <section v-if="kuotaPutri > 0">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-display text-lg sm:text-xl font-extrabold text-forest tracking-tight">👩 Pendamping Putri <span class="text-ink/40 text-base font-semibold">({{ kuotaPutri }} slot)</span></h3>
                    <span v-if="isFull(filledPutri, kuotaPutri)" class="text-[10px] sm:text-xs font-bold text-green-700 bg-green-100 border border-green-300 px-2.5 py-1 rounded-full">🔒 Kuota Terkunci</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <div v-for="s in slotsPutri" :key="'P-' + s.slot"
                        class="group rounded-xl border p-4 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md"
                        :class="s.filled ? 'bg-white border-green-300 shadow-sm hover:border-green-400' : 'bg-parchment/30 border-dashed border-line hover:border-gold/60'">
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-ink/50">
                                <span class="w-6 h-6 rounded-md bg-forest/10 text-forest flex items-center justify-center text-[11px] font-extrabold tabular-nums">{{ s.slot }}</span>
                                Slot #{{ s.slot }}
                            </span>
                            <span v-if="s.filled" class="text-[10px] font-semibold text-green-600 bg-green-100 px-2 py-0.5 rounded-full">✅ Terisi</span>
                            <span v-else class="text-[10px] font-semibold text-amber-600 bg-amber-100 px-2 py-0.5 rounded-full">⬜ Kosong</span>
                        </div>

                        <div v-if="s.filled" class="space-y-2">
                            <div class="font-semibold text-forest text-base leading-tight">{{ s.pendamping.nama }}</div>
                            <div class="text-xs text-ink/60 space-y-0.5">
                                <div>{{ s.pendamping.jabatan || '-' }}</div>
                                <div v-if="s.pendamping.asal_instansi" class="truncate">{{ s.pendamping.asal_instansi }}</div>
                            </div>
                            <span :class="['inline-block px-2.5 py-0.5 rounded-full border text-[10px] font-semibold', statusClass[s.pendamping.status_verifikasi]]">
                                {{ statusLabel[s.pendamping.status_verifikasi] }}
                            </span>
                            <div v-if="s.pendamping.status_verifikasi === 'rejected' && s.pendamping.catatan_verifikasi" class="text-[11px] text-red-600">Catatan: {{ s.pendamping.catatan_verifikasi }}</div>
                            <div v-if="s.pendamping.status_verifikasi !== 'approved'" class="flex items-center gap-2 pt-1">
                                <Link :href="route('sekolah.pendamping.edit', { kontingen: kontingen.id, pendamping: s.pendamping.id })"
                                    class="px-3 py-1 bg-khaki text-white rounded-lg text-[11px] font-semibold hover:opacity-90 active:scale-95 transition-all">✏️ Edit</Link>
                                <button @click="deletePendamping(s.pendamping)"
                                    class="px-3 py-1 bg-red-600 text-white rounded-lg text-[11px] font-semibold hover:bg-red-700 active:scale-95 transition-all">🗑️ Hapus</button>
                            </div>
                            <span v-else class="inline-block text-[11px] text-ink/40 pt-1">🔒 Terkunci (sudah disetujui)</span>
                        </div>

                        <div v-else class="flex flex-col items-start gap-2">
                            <p class="text-sm text-ink/40">Slot belum terisi.</p>
                            <Link :href="route('sekolah.pendamping.create', { kontingen: kontingen.id, slot: s.slot, jk: 'P' })"
                                class="inline-flex items-center gap-1 px-4 py-2 bg-forest text-parchment rounded-lg text-xs font-semibold hover:bg-forest/90 active:scale-95 transition-all">+ Isi Biodata</Link>
                        </div>
                    </div>
                </div>
            </section>

            <!-- kuota 0 semua -->
            <div v-if="kuotaPutra === 0 && kuotaPutri === 0" class="relative bg-white rounded-xl border border-line shadow-sm p-10 text-center text-ink/50">
                <div class="text-5xl mb-3">🧑‍🏫</div>
                <p class="font-semibold">Belum ada kuota pendamping yang ditentukan.</p>
                <p class="text-xs mt-1">Isi Formulir Kesediaan (C.01) terlebih dahulu.</p>
            </div>
        </div>
    </SekolahLayout>
</template>