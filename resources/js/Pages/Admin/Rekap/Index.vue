<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    events: { type: Array, default: () => [] },
    lombas: { type: Array, default: () => [] },
    selectedEventId: { type: [Number, null], default: null },
});

const eventStatusClass = {
    draft: 'bg-gray-100 text-gray-700 border-gray-300',
    aktif: 'bg-green-100 text-green-700 border-green-300',
    selesai: 'bg-blue-100 text-blue-700 border-blue-300',
};
const fmt = (v) => (v ? String(v).slice(0, 10).split('-').reverse().join('/') : '-');

const switchEvent = (id) => {
    router.get(route('admin.rekap.index'), { event_id: id || undefined }, { preserveState: false });
};
</script>

<template>
    <AdminLayout header="Rekap & Juara">
        <Head title="Rekap & Juara" />

        <div class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-64 bg-[radial-gradient(60%_100%_at_50%_0%,theme(colors.gold/10%),transparent)]"></div>
            <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-gold via-forest to-khaki"></div>

            <div class="relative px-2 sm:px-4 md:px-0 pt-6 space-y-6">
                <header class="space-y-3">
                    <span class="inline-block text-[10px] sm:text-xs font-display font-bold tracking-[0.25em] uppercase text-gold">
                        Modul 7 • Papan Hasil
                    </span>
                    <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-forest leading-none">
                        Rekap &amp; Juara
                    </h2>
                    <p class="text-sm text-ink/60 max-w-2xl">
                        Lihat rekap nilai per lomba, podium juara 1-2-3, lalu bekukan hasilnya.
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
                            :href="route('admin.rekap.index', { event_id: e.id })"
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
                                <span class="text-forest font-semibold">Lihat rekap</span>
                                <span class="text-gold transition-transform duration-300 group-hover:translate-x-1">→</span>
                            </div>
                        </Link>
                    </div>
                </section>

                <!-- pilih lomba -->
                <section v-else class="space-y-5">
                    <div class="bg-white rounded-xl border border-line shadow-sm p-3 sm:p-4 flex flex-col sm:flex-row sm:items-center gap-3">
                        <div class="flex items-center gap-2 flex-1 min-w-0">
                            <span class="text-ink/50 text-sm flex-shrink-0">Event:</span>
                            <select :value="selectedEventId" @change="switchEvent($event.target.value)" class="flex-1 min-w-0 border border-line rounded-lg px-3 py-2 text-sm font-semibold text-forest focus:outline-none focus:ring-2 focus:ring-gold">
                                <option v-for="e in events" :key="e.id" :value="e.id">{{ e.nama }}</option>
                            </select>
                        </div>
                        <Link :href="route('admin.rekap.index')" class="text-xs sm:text-sm text-forest hover:underline whitespace-nowrap">← Semua event</Link>
                    </div>

                    <div v-if="lombas.length === 0" class="bg-white rounded-xl border border-line shadow-sm p-10 text-center text-ink/50">
                        <div class="text-5xl mb-3">🏅</div>
                        <p>Belum ada lomba di event ini.</p>
                    </div>

                    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                        <Link
                            v-for="(l, i) in lombas"
                            :key="l.id"
                            :href="route('admin.rekap.show', l.id)"
                            :style="{ animationDelay: i * 60 + 'ms' }"
                            class="reveal group relative bg-white rounded-xl border border-line shadow-sm p-4 sm:p-5 flex flex-col overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-gold/60"
                        >
                            <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-forest to-gold opacity-0 group-hover:opacity-100 transition-opacity"></span>
                            <h4 class="font-display text-lg font-bold text-forest group-hover:text-gold transition-colors leading-tight">{{ l.nama }}</h4>
                            <div class="mt-3 flex items-center gap-2 text-sm flex-1">
                                <span class="w-2 h-2 rounded-full" :class="l.penilaians_count > 0 ? 'bg-green-500' : 'bg-gray-300'"></span>
                                <span class="font-mono font-bold text-forest tabular-nums">{{ l.penilaians_count }}</span>
                                <span class="text-ink/50">penilaian masuk</span>
                            </div>
                            <div class="mt-4 flex items-center justify-between text-sm">
                                <span class="text-forest font-semibold">Buka rekap &amp; podium</span>
                                <span class="text-gold transition-transform duration-300 group-hover:translate-x-1">→</span>
                            </div>
                        </Link>
                    </div>
                </section>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.reveal { opacity: 0; animation: reveal 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
@keyframes reveal { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
</style>