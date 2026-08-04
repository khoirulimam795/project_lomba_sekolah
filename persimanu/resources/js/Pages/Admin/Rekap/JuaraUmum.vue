<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, computed } from 'vue';
import { GOL_OPTIONS } from '@/golongan';

const props = defineProps({
    events: { type: Array, default: () => [] },
    selectedEventId: { type: [Number, null], default: null },
    perGolongan: { type: Object, default: () => ({}) },
});

// tab golongan: pakai 3 golongan baku
const tabs = GOL_OPTIONS; // [{value,label}]
const selectedGol = ref(tabs[0]?.value ?? 'penggalang_ramu');

const current = computed(() => props.perGolongan[selectedGol.value] || {
    ranking: [], totalMedal: { emas: 0, perak: 0, perunggu: 0 }, lombaDinilai: 0,
});

// bulletproof: sort olimpiade di sisi vue juga
const sortedRanking = computed(() => {
    const rows = (current.value.ranking || []).map((r) => ({
        ...r,
        emas: Number(r.emas) || 0, perak: Number(r.perak) || 0, perunggu: Number(r.perunggu) || 0,
    }));
    rows.sort((a, b) => (b.emas - a.emas) || (b.perak - a.perak) || (b.perunggu - a.perunggu) || (a.kontingen_id - b.kontingen_id));
    return rows.map((r, i) => ({ ...r, rank: i + 1 }));
});

const expandedId = ref(null);
const toggle = (id) => { expandedId.value = expandedId.value === id ? null : id; };
const switchEvent = (id) => { expandedId.value = null; router.get(route('admin.rekap.juara-umum'), { event_id: id || undefined }, { preserveState: false }); };

const maxTotal = computed(() => sortedRanking.value.length ? Math.max(...sortedRanking.value.map((r) => r.emas + r.perak + r.perunggu)) : 1);
const initials = (name) => (name || '?').split(' ').slice(0, 2).map((n) => n[0]).join('').toUpperCase();

const medal = {
    emas:     { emoji: '🥇', chip: 'bg-gold/15 text-gold border-gold/40', bar: 'bg-gold', text: 'text-gold' },
    perak:    { emoji: '🥈', chip: 'bg-slate-100 text-slate-500 border-slate-300', bar: 'bg-slate-300', text: 'text-slate-500' },
    perunggu: { emoji: '🥉', chip: 'bg-khaki/15 text-khaki border-khaki/40', bar: 'bg-khaki', text: 'text-khaki' },
};
const podiumMeta = {
    1: { emoji: '🥇', height: 'h-36 sm:h-44 md:h-52', glow: 'from-gold/30 to-gold/5', ring: 'ring-gold' },
    2: { emoji: '🥈', height: 'h-28 sm:h-32 md:h-40', glow: 'from-slate-200/70 to-slate-100/30', ring: 'ring-slate-300' },
    3: { emoji: '🥉', height: 'h-20 sm:h-24 md:h-32', glow: 'from-khaki/30 to-khaki/5', ring: 'ring-khaki' },
};
const eventStatusClass = {
    draft: 'bg-gray-100 text-gray-700 border-gray-300',
    aktif: 'bg-green-100 text-green-700 border-green-300',
    selesai: 'bg-blue-100 text-blue-700 border-blue-300',
};
const fmt = (v) => (v ? String(v).slice(0, 10).split('-').reverse().join('/') : '-');
const segWidth = (row, key) => { const total = row.emas + row.perak + row.perunggu; return total ? (row[key] / total) * 100 : 0; };
const rowTotal = (row) => (row.emas || 0) + (row.perak || 0) + (row.perunggu || 0);
</script>

<template>
    <AdminLayout header="Juara Umum">
        <Head title="Juara Umum" />
        <div class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-72 bg-[radial-gradient(55%_100%_at_50%_0%,theme(colors.gold/12%),transparent)]"></div>
            <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-gold via-forest to-khaki"></div>

            <div class="relative px-3 sm:px-4 md:px-6 lg:px-0 pt-6 pb-12 space-y-6">
                <header class="space-y-2 sm:space-y-3">
                    <span class="inline-block text-[10px] sm:text-xs font-display font-bold tracking-[0.2em] sm:tracking-[0.25em] uppercase text-gold">Modul 7 • Puncak Klasemen</span>
                    <h2 class="font-display text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold text-forest leading-none">Juara Umum</h2>
                    <p class="text-xs sm:text-sm text-ink/60 max-w-2xl">
                        Klasemen akhir pangkalan, <strong class="text-forest">dipisah per golongan</strong>.
                        Ranking berdasarkan <strong class="text-gold">medali emas terbanyak</strong>, disusul
                        <strong class="text-slate-500">perak</strong>, lalu <strong class="text-khaki">perunggu</strong>.
                    </p>
                </header>

                <!-- pilih event -->
                <section v-if="!selectedEventId" class="space-y-4">
                    <h3 class="font-display text-base sm:text-lg md:text-xl font-semibold text-forest">Pilih Event</h3>
                    <div v-if="events.length === 0" class="bg-white rounded-xl border border-line shadow-sm p-8 sm:p-10 text-center text-ink/50">
                        <div class="text-4xl sm:text-5xl mb-3">🗓️</div><p class="text-sm sm:text-base">Belum ada event.</p>
                    </div>
                    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                        <Link v-for="(e, i) in events" :key="e.id" :href="route('admin.rekap.juara-umum', { event_id: e.id })"
                            :style="{ animationDelay: i * 60 + 'ms' }"
                            class="reveal group relative bg-white rounded-xl border border-line shadow-sm p-4 sm:p-5 flex flex-col overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-gold/60">
                            <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-gold to-forest opacity-0 group-hover:opacity-100 transition-opacity"></span>
                            <div class="flex items-start justify-between gap-2">
                                <h4 class="font-display text-base sm:text-lg font-bold text-forest group-hover:text-gold transition-colors leading-tight">{{ e.nama }}</h4>
                                <span :class="['px-2 py-0.5 rounded-full border text-[8px] sm:text-[10px] font-semibold whitespace-nowrap', eventStatusClass[e.status]]">{{ e.status }}</span>
                            </div>
                            <div class="mt-2 sm:mt-3 text-[10px] sm:text-xs text-ink/55 flex-1">📍 {{ fmt(e.tanggal_pelaksanaan_mulai) }} – {{ fmt(e.tanggal_pelaksanaan_selesai) }}</div>
                            <div class="mt-3 sm:mt-4 flex items-center justify-between text-xs sm:text-sm">
                                <span class="text-forest font-semibold">Lihat klasemen</span>
                                <span class="text-gold transition-transform duration-300 group-hover:translate-x-1">→</span>
                            </div>
                        </Link>
                    </div>
                </section>

                <!-- klasemen per golongan -->
                <section v-else class="space-y-5 sm:space-y-6">
                    <!-- toolbar -->
                    <div class="bg-white rounded-xl border border-line shadow-sm p-3 sm:p-4 flex flex-col sm:flex-row sm:items-center gap-3">
                        <div class="flex items-center gap-2 flex-1 min-w-0">
                            <span class="text-ink/50 text-xs sm:text-sm flex-shrink-0">Event:</span>
                            <select :value="selectedEventId" @change="switchEvent($event.target.value)" class="flex-1 min-w-0 border border-line rounded-lg px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm font-semibold text-forest focus:outline-none focus:ring-2 focus:ring-gold">
                                <option v-for="e in events" :key="e.id" :value="e.id">{{ e.nama }}</option>
                            </select>
                        </div>
                        <Link :href="route('admin.rekap.index', { event_id: selectedEventId })" class="text-[10px] sm:text-xs md:text-sm text-forest hover:underline whitespace-nowrap">📊 Rekap per lomba</Link>
                    </div>

                    <!-- ✅ TAB GOLONGAN -->
                    <div class="flex flex-wrap gap-2">
                        <button v-for="t in tabs" :key="t.value" @click="selectedGol = t.value"
                            :class="['px-4 sm:px-5 py-2 rounded-full border font-display font-bold text-sm sm:text-base tracking-wide transition-all duration-200 active:scale-95',
                                selectedGol === t.value ? 'bg-forest text-parchment border-forest shadow-md' : 'bg-white text-forest border-line hover:border-gold hover:bg-parchment/40']">
                            {{ t.label }}
                        </button>
                    </div>

                    <!-- stat chips (per golongan terpilih) -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3">
                        <div class="bg-white rounded-xl border border-line shadow-sm p-2.5 sm:p-3 md:p-4">
                            <div class="text-[8px] sm:text-[10px] uppercase tracking-wider text-ink/45 font-semibold">Pangkalan</div>
                            <div class="font-display text-xl sm:text-2xl md:text-3xl font-extrabold text-forest tabular-nums">{{ sortedRanking.length }}</div>
                        </div>
                        <div class="bg-white rounded-xl border border-line shadow-sm p-2.5 sm:p-3 md:p-4">
                            <div class="text-[8px] sm:text-[10px] uppercase tracking-wider text-ink/45 font-semibold flex items-center gap-1">🥇 Emas</div>
                            <div class="font-display text-xl sm:text-2xl md:text-3xl font-extrabold text-gold tabular-nums">{{ current.totalMedal.emas }}</div>
                        </div>
                        <div class="bg-white rounded-xl border border-line shadow-sm p-2.5 sm:p-3 md:p-4">
                            <div class="text-[8px] sm:text-[10px] uppercase tracking-wider text-ink/45 font-semibold flex items-center gap-1">🥈 Perak</div>
                            <div class="font-display text-xl sm:text-2xl md:text-3xl font-extrabold text-slate-500 tabular-nums">{{ current.totalMedal.perak }}</div>
                        </div>
                        <div class="bg-white rounded-xl border border-line shadow-sm p-2.5 sm:p-3 md:p-4">
                            <div class="text-[8px] sm:text-[10px] uppercase tracking-wider text-ink/45 font-semibold flex items-center gap-1">🥉 Perunggu</div>
                            <div class="font-display text-xl sm:text-2xl md:text-3xl font-extrabold text-khaki tabular-nums">{{ current.totalMedal.perunggu }}</div>
                        </div>
                    </div>

                    <!-- kosong -->
                    <div v-if="sortedRanking.length === 0" class="bg-white rounded-xl border border-line shadow-sm p-8 sm:p-10 text-center text-ink/50">
                        <div class="text-4xl sm:text-5xl mb-3">🏆</div>
                        <p class="font-semibold text-ink/60 text-sm sm:text-base">Belum ada juara golongan ini yang dibekukan.</p>
                        <p class="text-[10px] sm:text-xs md:text-sm mt-1">Buka <strong>Rekap per lomba</strong>, lalu pencet <strong>🏆 Hitung &amp; Simpan Juara</strong>.</p>
                    </div>

                    <template v-else>
                        <!-- PODIUM -->
                        <div class="bg-white rounded-2xl border border-line shadow-sm p-4 sm:p-6 md:p-8">
                            <h3 class="font-display text-base sm:text-lg md:text-xl font-extrabold text-forest mb-4 sm:mb-6 text-center">🏆 Podium — {{ current.label }}</h3>
                            <div class="flex items-end justify-center gap-2 sm:gap-4 max-w-2xl mx-auto">
                                <div v-for="rank in [2, 1, 3]" :key="rank" class="flex-1 flex flex-col items-center"
                                    :style="{ animationDelay: (rank === 1 ? 0 : rank === 2 ? 120 : 240) + 'ms' }">
                                    <template v-if="sortedRanking[rank - 1]">
                                        <div class="text-3xl sm:text-4xl md:text-5xl mb-1 sm:mb-2 drop-shadow-sm podium-pop">{{ podiumMeta[rank].emoji }}</div>
                                        <div class="text-center mb-2 sm:mb-3 px-1">
                                            <div class="font-display font-bold text-forest text-xs sm:text-sm md:text-base leading-tight line-clamp-2 max-w-[80px] sm:max-w-[120px] md:max-w-none">{{ sortedRanking[rank - 1].team_name }}</div>
                                            <div class="font-mono font-extrabold text-lg sm:text-xl md:text-2xl mt-0.5 sm:mt-1 tabular-nums text-forest">
                                                {{ rowTotal(sortedRanking[rank - 1]) }} <span class="text-[8px] sm:text-[10px] font-sans font-semibold text-ink/40">medali</span>
                                            </div>
                                            <div class="flex items-center justify-center gap-1 mt-0.5 sm:mt-1 text-[9px] sm:text-[11px] font-mono tabular-nums">
                                                <span class="text-gold">{{ sortedRanking[rank - 1].emas }}</span>·<span class="text-slate-400">{{ sortedRanking[rank - 1].perak }}</span>·<span class="text-khaki">{{ sortedRanking[rank - 1].perunggu }}</span>
                                            </div>
                                        </div>
                                        <div :class="['w-full rounded-t-xl border border-line flex flex-col items-center justify-start pt-3 transition-all duration-300 hover:-translate-y-1 bg-gradient-to-b', podiumMeta[rank].glow, podiumMeta[rank].height]">
                                            <span class="font-display font-extrabold text-2xl sm:text-3xl md:text-4xl text-ink/70">{{ rank }}</span>
                                        </div>
                                    </template>
                                    <div v-else class="w-full" :class="podiumMeta[rank].height"></div>
                                </div>
                            </div>
                        </div>

                        <!-- RANKING LENGKAP -->
                        <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
                            <div class="px-3 sm:px-4 md:px-6 py-3 sm:py-4 border-b border-line bg-parchment/30 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <h3 class="font-display text-base sm:text-lg md:text-xl font-extrabold text-forest">Klasemen Lengkap — {{ current.label }}</h3>
                                <span class="text-[10px] sm:text-xs md:text-sm text-ink/50">{{ current.lombaDinilai }} lomba dinilai</span>
                            </div>
                            <div class="divide-y divide-line/60">
                                <div v-for="(r, i) in sortedRanking" :key="r.kontingen_id">
                                    <div :style="{ animationDelay: i * 40 + 'ms' }" @click="toggle(r.kontingen_id)"
                                        class="reveal-row px-3 sm:px-4 md:px-6 py-2.5 sm:py-3 md:py-4 flex items-center gap-2 sm:gap-3 md:gap-4 cursor-pointer hover:bg-parchment/30 transition-colors">
                                        <div class="w-7 sm:w-8 md:w-9 lg:w-10 flex-shrink-0 text-center">
                                            <span v-if="r.rank <= 3" class="text-xl sm:text-2xl md:text-3xl">{{ podiumMeta[r.rank].emoji }}</span>
                                            <span v-else class="font-mono font-bold text-ink/40 text-base sm:text-lg md:text-xl tabular-nums">{{ r.rank }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1 sm:flex-none sm:w-36 md:w-44 lg:w-52">
                                            <div class="w-7 h-7 sm:w-8 sm:h-8 md:w-9 md:h-9 lg:w-10 lg:h-10 rounded-full bg-forest/10 text-forest flex items-center justify-center font-display font-bold text-[10px] sm:text-xs md:text-sm flex-shrink-0">{{ initials(r.team_name) }}</div>
                                            <div class="min-w-0">
                                                <div class="font-semibold text-forest text-xs sm:text-sm md:text-base truncate">{{ r.team_name }}</div>
                                                <div class="text-[8px] sm:text-[10px] md:text-xs text-ink/45 truncate">{{ r.jenjang || '-' }}</div>
                                            </div>
                                        </div>
                                        <div class="hidden lg:flex items-center gap-1 flex-shrink-0">
                                            <span :class="['inline-flex items-center gap-1 px-1.5 sm:px-2 py-0.5 rounded-full border text-[9px] sm:text-xs font-mono font-bold tabular-nums transition-transform hover:scale-110', medal.emas.chip]">🥇 {{ r.emas }}</span>
                                            <span :class="['inline-flex items-center gap-1 px-1.5 sm:px-2 py-0.5 rounded-full border text-[9px] sm:text-xs font-mono font-bold tabular-nums transition-transform hover:scale-110', medal.perak.chip]">🥈 {{ r.perak }}</span>
                                            <span :class="['inline-flex items-center gap-1 px-1.5 sm:px-2 py-0.5 rounded-full border text-[9px] sm:text-xs font-mono font-bold tabular-nums transition-transform hover:scale-110', medal.perunggu.chip]">🥉 {{ r.perunggu }}</span>
                                        </div>
                                        <div class="flex-1 min-w-[30px] sm:min-w-[50px]">
                                            <div class="h-2 sm:h-2.5 md:h-3 rounded-full bg-line overflow-hidden flex ring-1 ring-inset ring-line/60" :style="{ width: Math.max(8, (rowTotal(r) / maxTotal) * 100) + '%' }">
                                                <div class="h-full transition-all duration-700 ease-out bg-gold" :style="{ width: segWidth(r, 'emas') + '%' }"></div>
                                                <div class="h-full transition-all duration-700 ease-out bg-slate-300" :style="{ width: segWidth(r, 'perak') + '%' }"></div>
                                                <div class="h-full transition-all duration-700 ease-out bg-khaki" :style="{ width: segWidth(r, 'perunggu') + '%' }"></div>
                                            </div>
                                        </div>
                                        <div class="text-right flex-shrink-0 w-12 sm:w-14 md:w-16 lg:w-20">
                                            <div class="font-mono font-extrabold text-base sm:text-lg md:text-xl lg:text-2xl tabular-nums" :class="r.rank <= 3 ? podiumMeta[r.rank].ring.replace('ring-', 'text-') : 'text-forest'">{{ rowTotal(r) }}</div>
                                            <div class="text-[7px] sm:text-[8px] md:text-[9px] uppercase tracking-wider text-ink/40 font-semibold">medali</div>
                                        </div>
                                        <svg :class="['w-3 h-3 sm:w-3.5 sm:h-3.5 md:w-4 md:h-4 text-ink/40 transition-transform duration-300 flex-shrink-0', expandedId === r.kontingen_id ? 'rotate-90' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </div>
                                    <Transition name="expand">
                                        <div v-if="expandedId === r.kontingen_id" class="px-3 sm:px-4 md:px-6 pb-3 sm:pb-4 bg-parchment/20 border-t border-line/40">
                                            <div class="pt-2 sm:pt-3 grid grid-cols-1 sm:grid-cols-2 gap-1.5 sm:gap-2">
                                                <div v-for="(d, di) in r.details" :key="di" class="flex items-center justify-between gap-2 bg-white rounded-lg border border-line/60 px-2 sm:px-3 py-1.5 sm:py-2">
                                                    <div class="min-w-0">
                                                        <div class="text-[10px] sm:text-xs md:text-sm font-semibold text-forest truncate">{{ d.lomba_nama }}</div>
                                                        <div class="text-[8px] sm:text-[10px] md:text-xs text-ink/50">{{ d.golongan }}</div>
                                                    </div>
                                                    <div class="flex items-center gap-1 sm:gap-2 flex-shrink-0">
                                                        <span class="font-mono text-[10px] sm:text-xs text-ink/50 tabular-nums">{{ d.nilai }}</span>
                                                        <span class="text-base sm:text-lg">{{ medal[d.medali].emoji }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </Transition>
                                </div>
                            </div>
                        </div>
                    </template>
                </section>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.reveal { opacity: 0; animation: reveal 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
@keyframes reveal { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
.reveal-row { opacity: 0; animation: revealRow 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
@keyframes revealRow { from { opacity: 0; transform: translateX(-10px); } to { opacity: 1; transform: translateX(0); } }
.podium-pop { animation: podiumPop 0.5s cubic-bezier(0.16, 1, 0.3, 1) both; }
@keyframes podiumPop { 0% { opacity: 0; transform: scale(0.4) translateY(10px); } 60% { transform: scale(1.15); } 100% { opacity: 1; transform: scale(1) translateY(0); } }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.expand-enter-active, .expand-leave-active { transition: all 0.3s ease; overflow: hidden; }
.expand-enter-from, .expand-leave-to { opacity: 0; max-height: 0; }
.expand-enter-to, .expand-leave-from { opacity: 1; max-height: 600px; }
</style>