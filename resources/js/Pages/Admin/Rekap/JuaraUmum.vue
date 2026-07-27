<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    events: { type: Array, default: () => [] },
    selectedEventId: { type: [Number, null], default: null },
    ranking: { type: Array, default: () => [] },
    totalMedal: { type: Object, default: () => ({ emas: 0, perak: 0, perunggu: 0 }) },
    lombaDinilai: { type: Number, default: 0 },
});

const expandedId = ref(null);
const toggle = (id) => { expandedId.value = expandedId.value === id ? null : id; };

const switchEvent = (id) => {
    expandedId.value = null;
    router.get(route('admin.rekap.juara-umum'), { event_id: id || undefined }, { preserveState: false });
};

const maxPoin = computed(() => props.ranking.length ? Math.max(...props.ranking.map((r) => Number(r.poin))) : 1);

const initials = (name) => (name || '?').split(' ').slice(0, 2).map((n) => n[0]).join('').toUpperCase();

// tema medali (konsisten sama podium 7A)
const medal = {
    emas:     { emoji: '🥇', chip: 'bg-gold/15 text-gold border-gold/40',        bar: 'bg-gold',      text: 'text-gold' },
    perak:    { emoji: '🥈', chip: 'bg-slate-100 text-slate-500 border-slate-300', bar: 'bg-slate-300', text: 'text-slate-500' },
    perunggu: { emoji: '🥉', chip: 'bg-khaki/15 text-khaki border-khaki/40',      bar: 'bg-khaki',     text: 'text-khaki' },
};

const podiumMeta = {
    1: { emoji: '🥇', height: 'h-44 sm:h-52', glow: 'from-gold/30 to-gold/5', ring: 'ring-gold' },
    2: { emoji: '🥈', height: 'h-32 sm:h-40', glow: 'from-slate-200/70 to-slate-100/30', ring: 'ring-slate-300' },
    3: { emoji: '🥉', height: 'h-24 sm:h-32', glow: 'from-khaki/30 to-khaki/5', ring: 'ring-khaki' },
};

const eventStatusClass = {
    draft: 'bg-gray-100 text-gray-700 border-gray-300',
    aktif: 'bg-green-100 text-green-700 border-green-300',
    selesai: 'bg-blue-100 text-blue-700 border-blue-300',
};
const fmt = (v) => (v ? String(v).slice(0, 10).split('-').reverse().join('/') : '-');

// lebar segmen komposisi medali di dalam bar (proporsi terhadap total medali pangkalan)
const segWidth = (row, key) => {
    const total = row.emas + row.perak + row.perunggu;
    return total ? (row[key] / total) * 100 : 0;
};
</script>

<template>
    <AdminLayout header="Juara Umum">
        <Head title="Juara Umum" />

        <div class="relative overflow-hidden">
            <!-- ambient on-theme (radial tipis, bukan blob) -->
            <div class="pointer-events-none absolute inset-x-0 top-0 h-72 bg-[radial-gradient(55%_100%_at_50%_0%,theme(colors.gold/12%),transparent)]"></div>
            <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-gold via-forest to-khaki"></div>

            <div class="relative px-2 sm:px-4 md:px-0 pt-6 pb-12 space-y-6">
                <!-- header -->
                <header class="space-y-3">
                    <span class="inline-block text-[10px] sm:text-xs font-display font-bold tracking-[0.25em] uppercase text-gold">
                        Modul 7 • Puncak Klasemen
                    </span>
                    <h2 class="font-display text-3xl sm:text-5xl font-extrabold text-forest leading-none">
                        Juara Umum
                    </h2>
                    <p class="text-sm text-ink/60 max-w-2xl">
                        Klasemen akhir pangkalan lintas seluruh lomba dalam satu event.
                        Sistem poin Olimpiade: <strong class="text-gold">emas 3</strong> ·
                        <strong class="text-slate-500">perak 2</strong> ·
                        <strong class="text-khaki">perunggu 1</strong>.
                    </p>
                </header>

                <!-- pilih event -->
                <section v-if="!selectedEventId" class="space-y-4">
                    <h3 class="font-display text-lg sm:text-xl font-semibold text-forest">Pilih Event</h3>
                    <div v-if="events.length === 0" class="bg-white rounded-xl border border-line shadow-sm p-10 text-center text-ink/50">
                        <div class="text-5xl mb-3">🗓️</div>
                        <p>Belum ada event.</p>
                    </div>
                    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                        <Link
                            v-for="(e, i) in events"
                            :key="e.id"
                            :href="route('admin.rekap.juara-umum', { event_id: e.id })"
                            :style="{ animationDelay: i * 60 + 'ms' }"
                            class="reveal group relative bg-white rounded-xl border border-line shadow-sm p-4 sm:p-5 flex flex-col overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-gold/60"
                        >
                            <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-gold to-forest opacity-0 group-hover:opacity-100 transition-opacity"></span>
                            <div class="flex items-start justify-between gap-2">
                                <h4 class="font-display text-lg font-bold text-forest group-hover:text-gold transition-colors leading-tight">{{ e.nama }}</h4>
                                <span :class="['px-2 py-0.5 rounded-full border text-[10px] font-semibold whitespace-nowrap', eventStatusClass[e.status]]">{{ e.status }}</span>
                            </div>
                            <div class="mt-3 text-xs text-ink/55 flex-1">📍 {{ fmt(e.tanggal_pelaksanaan_mulai) }} – {{ fmt(e.tanggal_pelaksanaan_selesai) }}</div>
                            <div class="mt-4 flex items-center justify-between text-sm">
                                <span class="text-forest font-semibold">Lihat klasemen</span>
                                <span class="text-gold transition-transform duration-300 group-hover:translate-x-1">→</span>
                            </div>
                        </Link>
                    </div>
                </section>

                <!-- klasemen -->
                <section v-else class="space-y-6">
                    <!-- toolbar -->
                    <div class="bg-white rounded-xl border border-line shadow-sm p-3 sm:p-4 flex flex-col sm:flex-row sm:items-center gap-3">
                        <div class="flex items-center gap-2 flex-1 min-w-0">
                            <span class="text-ink/50 text-sm flex-shrink-0">Event:</span>
                            <select :value="selectedEventId" @change="switchEvent($event.target.value)" class="flex-1 min-w-0 border border-line rounded-lg px-3 py-2 text-sm font-semibold text-forest focus:outline-none focus:ring-2 focus:ring-gold">
                                <option v-for="e in events" :key="e.id" :value="e.id">{{ e.nama }}</option>
                            </select>
                        </div>
                        <Link :href="route('admin.rekap.index', { event_id: selectedEventId })" class="text-xs sm:text-sm text-forest hover:underline whitespace-nowrap">📊 Rekap per lomba</Link>
                    </div>

                    <!-- stat chips -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="bg-white rounded-xl border border-line shadow-sm p-3 sm:p-4">
                            <div class="text-[10px] uppercase tracking-wider text-ink/45 font-semibold">Pangkalan</div>
                            <div class="font-display text-2xl sm:text-3xl font-extrabold text-forest tabular-nums">{{ ranking.length }}</div>
                        </div>
                        <div class="bg-white rounded-xl border border-line shadow-sm p-3 sm:p-4">
                            <div class="text-[10px] uppercase tracking-wider text-ink/45 font-semibold flex items-center gap-1">🥇 Emas</div>
                            <div class="font-display text-2xl sm:text-3xl font-extrabold text-gold tabular-nums">{{ totalMedal.emas }}</div>
                        </div>
                        <div class="bg-white rounded-xl border border-line shadow-sm p-3 sm:p-4">
                            <div class="text-[10px] uppercase tracking-wider text-ink/45 font-semibold flex items-center gap-1">🥈 Perak</div>
                            <div class="font-display text-2xl sm:text-3xl font-extrabold text-slate-500 tabular-nums">{{ totalMedal.perak }}</div>
                        </div>
                        <div class="bg-white rounded-xl border border-line shadow-sm p-3 sm:p-4">
                            <div class="text-[10px] uppercase tracking-wider text-ink/45 font-semibold flex items-center gap-1">🥉 Perunggu</div>
                            <div class="font-display text-2xl sm:text-3xl font-extrabold text-khaki tabular-nums">{{ totalMedal.perunggu }}</div>
                        </div>
                    </div>

                    <!-- kosong -->
                    <div v-if="ranking.length === 0" class="bg-white rounded-xl border border-line shadow-sm p-10 text-center text-ink/50">
                        <div class="text-5xl mb-3">🏆</div>
                        <p class="font-semibold text-ink/60">Belum ada juara yang dibekukan di event ini.</p>
                        <p class="text-xs sm:text-sm mt-1">Buka <strong>Rekap per lomba</strong>, lalu pencet <strong>🏆 Hitung &amp; Simpan Juara</strong> di tiap lomba.</p>
                    </div>

                    <template v-else>
                        <!-- PODIUM JUARA UMUM -->
                        <div class="bg-white rounded-2xl border border-line shadow-sm p-4 sm:p-8">
                            <h3 class="font-display text-lg sm:text-xl font-extrabold text-forest mb-6 text-center">🏆 Podium Juara Umum</h3>
                            <div class="flex items-end justify-center gap-2 sm:gap-4 max-w-2xl mx-auto">
                                <div
                                    v-for="rank in [2, 1, 3]"
                                    :key="rank"
                                    class="flex-1 flex flex-col items-center"
                                    :style="{ animationDelay: (rank === 1 ? 0 : rank === 2 ? 120 : 240) + 'ms' }"
                                >
                                    <template v-if="ranking[rank - 1]">
                                        <div class="text-4xl sm:text-5xl mb-2 drop-shadow-sm podium-pop">{{ podiumMeta[rank].emoji }}</div>
                                        <div class="text-center mb-3 px-1">
                                            <div class="font-display font-bold text-forest text-sm sm:text-base leading-tight line-clamp-2">{{ ranking[rank - 1].team_name }}</div>
                                            <div class="font-mono font-extrabold text-xl sm:text-2xl mt-1 tabular-nums text-forest">{{ ranking[rank - 1].poin }} <span class="text-[10px] font-sans font-semibold text-ink/40">poin</span></div>
                                            <div class="flex items-center justify-center gap-1 mt-1 text-[11px] font-mono tabular-nums">
                                                <span class="text-gold">{{ ranking[rank - 1].emas }}</span>·<span class="text-slate-400">{{ ranking[rank - 1].perak }}</span>·<span class="text-khaki">{{ ranking[rank - 1].perunggu }}</span>
                                            </div>
                                        </div>
                                        <div :class="['w-full rounded-t-xl border border-line flex flex-col items-center justify-start pt-3 transition-all duration-300 hover:-translate-y-1 bg-gradient-to-b', podiumMeta[rank].glow, podiumMeta[rank].height]">
                                            <span class="font-display font-extrabold text-3xl sm:text-4xl text-ink/70">{{ rank }}</span>
                                        </div>
                                    </template>
                                    <div v-else class="w-full" :class="podiumMeta[rank].height"></div>
                                </div>
                            </div>
                        </div>

                        <!-- RANKING LENGKAP -->
                        <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
                            <div class="px-4 sm:px-6 py-4 border-b border-line bg-parchment/30 flex items-center justify-between">
                                <h3 class="font-display text-lg sm:text-xl font-extrabold text-forest">Klasemen Lengkap</h3>
                                <span class="text-xs sm:text-sm text-ink/50">{{ lombaDinilai }} lomba dinilai</span>
                            </div>

                            <div class="divide-y divide-line/60">
                                <div v-for="(r, i) in ranking" :key="r.kontingen_id">
                                    <!-- baris utama -->
                                    <div
                                        :style="{ animationDelay: i * 40 + 'ms' }"
                                        @click="toggle(r.kontingen_id)"
                                        class="reveal-row px-4 sm:px-6 py-3 sm:py-4 flex items-center gap-3 sm:gap-4 cursor-pointer hover:bg-parchment/30 transition-colors"
                                    >
                                        <!-- rank -->
                                        <div class="w-9 sm:w-10 flex-shrink-0 text-center">
                                            <span v-if="r.rank <= 3" class="text-2xl sm:text-3xl">{{ podiumMeta[r.rank].emoji }}</span>
                                            <span v-else class="font-mono font-bold text-ink/40 text-lg sm:text-xl tabular-nums">{{ r.rank }}</span>
                                        </div>

                                        <!-- avatar + nama -->
                                        <div class="flex items-center gap-3 min-w-0 w-40 sm:w-52 flex-shrink-0">
                                            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-forest/10 text-forest flex items-center justify-center font-display font-bold text-xs sm:text-sm flex-shrink-0">{{ initials(r.team_name) }}</div>
                                            <div class="min-w-0">
                                                <div class="font-semibold text-forest text-sm sm:text-base truncate">{{ r.team_name }}</div>
                                                <div class="text-[10px] sm:text-xs text-ink/45 truncate">{{ r.jenjang || '-' }}</div>
                                            </div>
                                        </div>

                                        <!-- chip medali -->
                                        <div class="hidden sm:flex items-center gap-1.5 flex-shrink-0">
                                            <span :class="['inline-flex items-center gap-1 px-2 py-0.5 rounded-full border text-xs font-mono font-bold tabular-nums transition-transform hover:scale-110', medal.emas.chip]">🥇 {{ r.emas }}</span>
                                            <span :class="['inline-flex items-center gap-1 px-2 py-0.5 rounded-full border text-xs font-mono font-bold tabular-nums transition-transform hover:scale-110', medal.perak.chip]">🥈 {{ r.perak }}</span>
                                            <span :class="['inline-flex items-center gap-1 px-2 py-0.5 rounded-full border text-xs font-mono font-bold tabular-nums transition-transform hover:scale-110', medal.perunggu.chip]">🥉 {{ r.perunggu }}</span>
                                        </div>

                                        <!-- bar komposisi + dominasi -->
                                        <div class="flex-1 min-w-0">
                                            <div class="h-3 rounded-full bg-line overflow-hidden flex ring-1 ring-inset ring-line/60" :style="{ width: Math.max(8, (Number(r.poin) / maxPoin) * 100) + '%' }">
                                                <div class="h-full transition-all duration-700 ease-out bg-gold" :style="{ width: segWidth(r, 'emas') + '%' }"></div>
                                                <div class="h-full transition-all duration-700 ease-out bg-slate-300" :style="{ width: segWidth(r, 'perak') + '%' }"></div>
                                                <div class="h-full transition-all duration-700 ease-out bg-khaki" :style="{ width: segWidth(r, 'perunggu') + '%' }"></div>
                                            </div>
                                        </div>

                                        <!-- poin -->
                                        <div class="text-right flex-shrink-0 w-16 sm:w-20">
                                            <div class="font-mono font-extrabold text-xl sm:text-2xl tabular-nums" :class="r.rank <= 3 ? podiumMeta[r.rank].ring.replace('ring-', 'text-') : 'text-forest'">{{ r.poin }}</div>
                                            <div class="text-[9px] uppercase tracking-wider text-ink/40 font-semibold">poin</div>
                                        </div>

                                        <!-- chevron -->
                                        <svg :class="['w-4 h-4 text-ink/40 transition-transform duration-300 flex-shrink-0', expandedId === r.kontingen_id ? 'rotate-90' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </div>

                                    <!-- detail per lomba (expand) -->
                                    <Transition name="expand">
                                        <div v-if="expandedId === r.kontingen_id" class="px-4 sm:px-6 pb-4 bg-parchment/20 border-t border-line/40">
                                            <div class="pt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                <div
                                                    v-for="(d, di) in r.details"
                                                    :key="di"
                                                    class="flex items-center justify-between gap-2 bg-white rounded-lg border border-line/60 px-3 py-2"
                                                >
                                                    <div class="min-w-0">
                                                        <div class="text-sm font-semibold text-forest truncate">{{ d.lomba_nama }}</div>
                                                        <div class="text-[10px] sm:text-xs text-ink/50">{{ d.golongan }}</div>
                                                    </div>
                                                    <div class="flex items-center gap-2 flex-shrink-0">
                                                        <span class="font-mono text-xs text-ink/50 tabular-nums">{{ d.nilai }}</span>
                                                        <span class="text-lg">{{ medal[d.medali].emoji }}</span>
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