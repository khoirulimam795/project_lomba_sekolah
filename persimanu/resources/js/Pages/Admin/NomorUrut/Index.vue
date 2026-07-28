<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { computed } from 'vue';
import { GOL_ORDER, GOL_DOT, golLabel } from '@/golongan';

const props = defineProps({
    events: { type: Array, default: () => [] },
    selectedEventId: { type: [Number, null], default: null },
    rows: { type: Array, default: () => [] },
});

const form = useForm({
    rows: props.rows.map((r) => ({ ...r, _orig: r.nomor_urut_tampil })),
});
// field declared dari lahir biar nomor_urut_tampil pasti ke-serialize
const lockForm = useForm({ nomor_urut_tampil: null });

const initials = (name) => (name || '?').split(' ').slice(0, 2).map((n) => n[0]).join('').toUpperCase();

const lombaMeta = computed(() => {
    const map = new Map();
    for (const r of form.rows) {
        if (!map.has(r.lomba_id)) map.set(r.lomba_id, { id: r.lomba_id, nama: r.lomba_nama, status: r.lomba_status });
    }
    return [...map.values()];
});

const golonganIn = (lombaId) =>
    [...new Set(form.rows.filter((r) => r.lomba_id === lombaId).map((r) => r.golongan))]
        .sort((a, b) => GOL_ORDER.indexOf(a) - GOL_ORDER.indexOf(b));

const rowsOf = (lombaId, golongan) => form.rows.filter((r) => r.lomba_id === lombaId && r.golongan === golongan);

const progressOf = (lombaId) => {
    const rs = form.rows.filter((r) => r.lomba_id === lombaId);
    const locked = rs.filter((r) => r.status === 'siap').length;
    return { locked, total: rs.length, pct: rs.length ? Math.round((locked / rs.length) * 100) : 0 };
};

const isDirty = (row) => (row.nomor_urut_tampil ?? null) !== (row._orig ?? null);
const dirtyCount = computed(() => form.rows.filter(isDirty).length);

const stat = computed(() => ({
    lomba: lombaMeta.value.length,
    regu: form.rows.length,
    terkunci: form.rows.filter((r) => r.status === 'siap').length,
    dirty: dirtyCount.value,
}));

const eventStatusClass = {
    draft: 'bg-gray-100 text-gray-700 border-gray-300',
    aktif: 'bg-green-100 text-green-700 border-green-300',
    selesai: 'bg-blue-100 text-blue-700 border-blue-300',
};
const fmt = (v) => (v ? String(v).slice(0, 10).split('-').reverse().join('/') : '-');

const setNomor = (row, e) => {
    const v = e.target.value;
    row.nomor_urut_tampil = v === '' ? null : parseInt(v, 10);
};
const switchEvent = (id) => router.get(route('admin.nomor-urut.index'), { event_id: id || undefined }, { preserveState: false });
const saveAll = () => form.put(route('admin.nomor-urut.save'), { preserveScroll: true });

const lockRow = (row) => {
    if (!row.nomor_urut_tampil) return;
    lockForm.nomor_urut_tampil = row.nomor_urut_tampil;
    lockForm.post(route('admin.nomor-urut.lock', row.id), { preserveScroll: true });
};
const unlockRow = (row) => {
    if (!confirm(`Buka kunci regu "${row.team_name}"? Operator bisa mengubah alokasi lagi.`)) return;
    router.post(route('admin.nomor-urut.unlock', row.id), {}, { preserveScroll: true });
};
</script>

<template>
    <AdminLayout header="Nomor Urut Tampil">
        <Head title="Nomor Urut Tampil" />
        <div class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-64 bg-[radial-gradient(60%_100%_at_50%_0%,theme(colors.forest/8%),transparent)]"></div>
            <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-forest via-gold to-khaki"></div>

            <div class="relative px-2 sm:px-4 md:px-0 pt-6 pb-28 space-y-6">
                <header class="space-y-3">
                    <span class="inline-block text-[10px] sm:text-xs font-display font-bold tracking-[0.25em] uppercase text-gold">Modul 5 • Meja Panitia</span>
                    <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-forest leading-none">Nomor Urut Tampil</h2>
                    <p class="text-sm text-ink/60 max-w-2xl">Tentukan urutan tampil tiap regu per lomba &amp; golongan, lalu <strong>kunci</strong> alokasi supaya siap dinilai juri. Regu yang terkunci tidak bisa diubah operator lagi.</p>
                </header>

                <section v-if="!selectedEventId" class="space-y-4">
                    <h3 class="font-display text-lg sm:text-xl font-semibold text-forest">Pilih Event</h3>
                    <div v-if="events.length === 0" class="bg-white rounded-xl border border-line shadow-sm p-10 text-center text-ink/50">
                        <div class="text-5xl mb-3">🗓️</div><p>Belum ada event. Buat dulu di menu Event.</p>
                    </div>
                    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                        <Link v-for="(e, i) in events" :key="e.id" :href="route('admin.nomor-urut.index', { event_id: e.id })" :style="{ animationDelay: i * 60 + 'ms' }"
                            class="reveal group relative bg-white rounded-xl border border-line shadow-sm p-4 sm:p-5 flex flex-col overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-gold/60">
                            <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-forest to-gold opacity-0 group-hover:opacity-100 transition-opacity"></span>
                            <div class="flex items-start justify-between gap-2">
                                <h4 class="font-display text-lg font-bold text-forest group-hover:text-gold transition-colors leading-tight">{{ e.nama }}</h4>
                                <span :class="['px-2 py-0.5 rounded-full border text-[10px] font-semibold whitespace-nowrap', eventStatusClass[e.status]]">{{ e.status }}</span>
                            </div>
                            <div class="mt-3 text-xs text-ink/55 space-y-1 flex-1">
                                <div>🗓️ Daftar: {{ fmt(e.periode_pendaftaran_mulai) }} – {{ fmt(e.periode_pendaftaran_selesai) }}</div>
                                <div>📍 Main: {{ fmt(e.tanggal_pelaksanaan_mulai) }} – {{ fmt(e.tanggal_pelaksanaan_selesai) }}</div>
                            </div>
                            <div class="mt-4 flex items-center justify-between text-sm">
                                <span class="text-forest font-semibold">Atur nomor urut</span>
                                <span class="text-gold transition-transform duration-300 group-hover:translate-x-1">→</span>
                            </div>
                        </Link>
                    </div>
                </section>

                <section v-else class="space-y-5">
                    <div class="bg-white rounded-xl border border-line shadow-sm p-3 sm:p-4 flex flex-col sm:flex-row sm:items-center gap-3">
                        <div class="flex items-center gap-2 flex-1 min-w-0">
                            <span class="text-ink/50 text-sm flex-shrink-0">Event:</span>
                            <select :value="selectedEventId" @change="switchEvent($event.target.value)" class="flex-1 min-w-0 border border-line rounded-lg px-3 py-2 text-sm font-semibold text-forest focus:outline-none focus:ring-2 focus:ring-gold">
                                <option v-for="e in events" :key="e.id" :value="e.id">{{ e.nama }}</option>
                            </select>
                        </div>
                        <Link :href="route('admin.nomor-urut.index')" class="text-xs sm:text-sm text-forest hover:underline whitespace-nowrap">← Semua event</Link>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="bg-white rounded-xl border border-line shadow-sm p-3 sm:p-4"><div class="text-[10px] uppercase tracking-wider text-ink/45 font-semibold">Lomba</div><div class="font-display text-2xl sm:text-3xl font-extrabold text-forest">{{ stat.lomba }}</div></div>
                        <div class="bg-white rounded-xl border border-line shadow-sm p-3 sm:p-4"><div class="text-[10px] uppercase tracking-wider text-ink/45 font-semibold">Regu</div><div class="font-display text-2xl sm:text-3xl font-extrabold text-forest">{{ stat.regu }}</div></div>
                        <div class="bg-white rounded-xl border border-line shadow-sm p-3 sm:p-4"><div class="text-[10px] uppercase tracking-wider text-ink/45 font-semibold">Terkunci</div><div class="font-display text-2xl sm:text-3xl font-extrabold text-green-600">{{ stat.terkunci }}</div></div>
                        <div class="bg-white rounded-xl border border-line shadow-sm p-3 sm:p-4"><div class="text-[10px] uppercase tracking-wider text-ink/45 font-semibold">Belum Disimpan</div><div class="font-display text-2xl sm:text-3xl font-extrabold" :class="stat.dirty ? 'text-amber-600' : 'text-ink/30'">{{ stat.dirty }}</div></div>
                    </div>

                    <div v-if="form.errors.rows" class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">⚠️ {{ form.errors.rows }}</div>
                    <div v-if="lockForm.errors.lock" class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">⚠️ {{ lockForm.errors.lock }}</div>

                    <div v-if="form.rows.length === 0" class="bg-white rounded-xl border border-line shadow-sm p-10 text-center text-ink/50">
                        <div class="text-5xl mb-3">🏅</div><p>Belum ada regu yang mendaftar di event ini.</p>
                    </div>

                    <div v-for="(lomba, li) in lombaMeta" :key="lomba.id" :style="{ animationDelay: li * 80 + 'ms' }" class="reveal bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
                        <div class="px-4 sm:px-6 py-4 border-b border-line bg-parchment/30">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="font-display text-xl sm:text-2xl font-extrabold text-forest truncate">{{ lomba.nama }}</h3>
                                    <p class="text-xs text-ink/50 mt-0.5">{{ progressOf(lomba.id).locked }} / {{ progressOf(lomba.id).total }} regu terkunci</p>
                                </div>
                                <span :class="['px-3 py-1 rounded-full border text-xs font-semibold whitespace-nowrap', eventStatusClass[lomba.status]]">Lomba: {{ lomba.status }}</span>
                            </div>
                            <div class="mt-3 h-2 rounded-full bg-line overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-forest to-gold rounded-full transition-all duration-500" :style="{ width: progressOf(lomba.id).pct + '%' }"></div>
                            </div>
                        </div>

                        <div v-for="gol in golonganIn(lomba.id)" :key="gol" class="px-4 sm:px-6 py-4 border-b border-line/60 last:border-0">
                            <div class="flex items-center gap-2 mb-3">
                                <span :class="['w-2.5 h-2.5 rounded-full', GOL_DOT[gol] || 'bg-ink/30']"></span>
                                <span class="text-[11px] sm:text-xs font-display font-bold uppercase tracking-[0.2em] text-ink/60">{{ golLabel(gol) }}</span>
                                <span class="text-[10px] text-ink/40">({{ rowsOf(lomba.id, gol).length }} regu)</span>
                            </div>
                            <div class="space-y-2">
                                <div v-for="row in rowsOf(lomba.id, gol)" :key="row.id" class="group flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 rounded-xl border border-line bg-white p-3 sm:p-4 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md hover:border-gold/50">
                                    <div class="flex items-center gap-3 min-w-0 flex-1">
                                        <div class="w-10 h-10 rounded-full bg-forest/10 text-forest flex items-center justify-center font-display font-bold text-sm flex-shrink-0 group-hover:bg-forest group-hover:text-parchment transition-colors">{{ initials(row.team_name) }}</div>
                                        <div class="min-w-0">
                                            <div class="font-semibold text-forest text-sm sm:text-base truncate">{{ row.team_name }}</div>
                                            <div class="text-[11px] sm:text-xs text-ink/55 truncate">
                                                👥 {{ row.siswa_count }} siswa
                                                <span v-if="row.pendamping_name"> • 🧑‍ {{ row.pendamping_name }}</span>
                                                <span v-else class="text-amber-600"> • ⚠️ belum ada pendamping</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0 sm:pl-2">
                                        <div class="relative flex items-center">
                                            <input type="number" min="1" :value="row.nomor_urut_tampil" @input="setNomor(row, $event)" :disabled="row.status === 'siap'" placeholder="–"
                                                :class="['w-16 sm:w-20 text-center font-mono text-xl sm:text-2xl font-bold border-2 rounded-lg py-1 focus:outline-none transition-colors',
                                                    row.status === 'siap' ? 'bg-parchment/60 border-line text-ink/50 cursor-not-allowed'
                                                    : isDirty(row) ? 'border-amber-400 bg-amber-50 focus:border-gold focus:ring-2 focus:ring-gold/30'
                                                    : 'border-line focus:border-gold focus:ring-2 focus:ring-gold/30']" />
                                            <span v-if="row.status !== 'siap' && isDirty(row)" class="absolute -top-1 -right-1 w-3 h-3 rounded-full bg-amber-500 border-2 border-white" title="Belum disimpan"></span>
                                        </div>
                                        <span v-if="row.status === 'siap'" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border text-[10px] sm:text-xs font-semibold bg-green-100 text-green-700 border-green-300 whitespace-nowrap"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Siap</span>
                                        <span v-else class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border text-[10px] sm:text-xs font-semibold bg-amber-100 text-amber-700 border-amber-300 whitespace-nowrap"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Draft</span>
                                        <button v-if="row.status !== 'siap'" @click="lockRow(row)" :disabled="!row.nomor_urut_tampil || lockForm.processing" class="px-3 py-1.5 bg-forest text-parchment rounded-lg text-[11px] sm:text-xs font-semibold hover:bg-forest/90 disabled:opacity-40 transition active:scale-95 whitespace-nowrap" title="Kunci regu dengan nomor ini">🔒 Kunci</button>
                                        <button v-else @click="unlockRow(row)" class="px-3 py-1.5 border border-line text-ink/70 rounded-lg text-[11px] sm:text-xs font-semibold hover:bg-parchment transition active:scale-95 whitespace-nowrap" title="Buka kunci">🔓 Buka</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <Transition name="bar">
                <div v-if="selectedEventId && form.rows.length > 0" class="sticky bottom-4 z-20 mx-2 sm:mx-4 md:mx-auto md:max-w-5xl">
                    <div class="bg-forest text-parchment rounded-2xl shadow-2xl border border-forest px-4 sm:px-6 py-3 sm:py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center gap-3 text-sm">
                            <span class="text-2xl">📋</span>
                            <div>
                                <div class="font-semibold">Simpan perubahan nomor urut</div>
                                <div class="text-parchment/70 text-xs">
                                    <template v-if="dirtyCount > 0">{{ dirtyCount }} perubahan belum disimpan.</template>
                                    <template v-else>Semua nomor urut sudah tersimpan.</template>
                                </div>
                            </div>
                        </div>
                        <button @click="saveAll" :disabled="dirtyCount === 0 || form.processing" class="px-5 py-2.5 bg-gold text-ink rounded-lg font-bold hover:opacity-90 disabled:opacity-40 transition active:scale-95 text-sm sm:text-base whitespace-nowrap">💾 Simpan Semua Nomor Urut</button>
                    </div>
                </div>
            </Transition>
        </div>
    </AdminLayout>
</template>

<style scoped>
.reveal { opacity: 0; animation: reveal 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
@keyframes reveal { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
.bar-enter-active, .bar-leave-active { transition: all 0.3s ease; }
.bar-enter-from, .bar-leave-to { opacity: 0; transform: translateY(12px); }
</style>