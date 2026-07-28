<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { reactive, computed, ref } from 'vue';

const props = defineProps({
    lomba: { type: Object, default: null },
    kontingen: { type: Object, default: null },
    golongan: { type: String, default: '' },
    kriterias: { type: Array, default: () => [] },       // [{id, nama_komponen, urutan}]
    penilaians: { type: Array, default: () => [] },      // [{id, juri_name, nilai_akhir_juri, nilai:{kid:v}, ...}]
    audit: { type: Array, default: () => [] },           // [{who, when, desc, props}]
});

// draft per juri (deep copy dari nilai tersimpan) + segel "sedang dibuka"
const openId = ref(null);
const draft = reactive({});
props.penilaians.forEach((p) => {
    draft[p.id] = { ...p.nilai };
});

const kidList = computed(() => props.kriterias.map((k) => k.id));

const valOf = (pid, kid) => draft[pid]?.[kid];
const setVal = (pid, kid, e) => {
    const v = e.target.value;
    draft[pid][kid] = v === '' ? '' : parseInt(v, 10);
};
const num = (pid, kid) => { const v = Number(draft[pid]?.[kid]); return Number.isFinite(v) ? v : 0; };
const valid = (pid, kid) => { const v = Number(draft[pid]?.[kid]); return draft[pid]?.[kid] !== '' && draft[pid]?.[kid] != null && Number.isFinite(v) && v >= 1 && v <= 100; };

const runningAvg = (pid) => {
    const vals = kidList.value.filter((k) => valid(pid, k)).map((k) => num(pid, k));
    return vals.length ? Math.round(vals.reduce((a, b) => a + b, 0) / vals.length) : 0;
};
const savedAvg = (pid) => Number(props.penilaians.find((p) => p.id === pid)?.nilai_akhir_juri ?? 0);

// delta per komponen & per rata-rata (draft vs tersimpan)
const delta = (pid, kid) => {
    const orig = Number(props.penilaians.find((p) => p.id === pid)?.nilai?.[kid] ?? 0);
    if (!valid(pid, kid)) return 0;
    return num(pid, kid) - orig;
};
const avgDelta = (pid) => runningAvg(pid) - savedAvg(pid);

const isDirty = (pid) => {
    const orig = props.penilaians.find((p) => p.id === pid)?.nilai ?? {};
    return kidList.value.some((k) => Number(orig[k] ?? 0) !== num(pid, k));
};
const canSubmit = (pid) => isDirty(pid) && kidList.value.every((k) => valid(pid, k));

const barClass = (pid, kid) => {
    const v = num(pid, kid);
    if (v >= 80) return 'from-gold to-forest';
    if (v >= 50) return 'from-khaki to-gold';
    return 'from-khaki/50 to-khaki';
};

const saveJuri = (pid) => {
    if (!canSubmit(pid)) return;
    router.put(route('admin.rekap.update-nilai', { lomba: props.lomba.id, penilaian: pid }),
        { nilai: draft[pid] }, { preserveScroll: true });
};

const toggle = (pid) => { openId.value = openId.value === pid ? null : pid; };

const initials = (n) => (n || '?').split(' ').slice(0, 2).map((x) => x[0]).join('').toUpperCase();
</script>

<template>
    <AdminLayout header="Revisi Nilai">
        <Head title="Revisi Nilai" />

        <div class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-72 bg-[radial-gradient(55%_100%_at_50%_0%,theme(colors.forest/8%),transparent)]"></div>
            <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-forest via-gold to-khaki"></div>

            <div class="relative px-2 sm:px-4 md:px-0 pt-4 pb-12 space-y-6">
                <!-- header -->
                <header class="space-y-3">
                    <Link :href="route('admin.rekap.show', lomba.id)" class="text-xs sm:text-sm text-forest hover:underline inline-flex items-center">← Kembali ke rekap</Link>
                    <span class="block text-[10px] sm:text-xs font-display font-bold tracking-[0.25em] uppercase text-gold">Meja Wasit • Revisi Terlacak</span>
                    <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-forest leading-none">Revisi Nilai Juri</h2>
                    <p class="text-sm text-ink/60 max-w-2xl">
                        Satu-satunya jalan mengubah nilai setelah juri mengunci. Buka segel juri,
                        koreksi komponen, simpan — <strong>setiap perubahan tercatat otomatis di audit log</strong>.
                    </p>
                </header>

                <!-- info regu -->
                <div class="bg-white rounded-xl border border-line shadow-sm p-4 sm:p-6 flex items-center gap-4">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl bg-forest text-parchment flex items-center justify-center font-display font-bold text-lg sm:text-xl flex-shrink-0">
                        {{ initials(kontingen?.team?.name) }}
                    </div>
                    <div class="min-w-0">
                        <div class="font-display font-bold text-forest text-lg sm:text-xl truncate">{{ kontingen?.team?.name }}</div>
                        <div class="text-xs sm:text-sm text-ink/55">🏆 {{ lomba?.nama }} • golongan <span class="font-semibold text-gold uppercase">{{ golongan }}</span> • {{ penilaians.length }} juri menilai</div>
                    </div>
                </div>

                <!-- per juri -->
                <div v-if="penilaians.length === 0" class="bg-white rounded-xl border border-line shadow-sm p-10 text-center text-ink/50">
                    <div class="text-5xl mb-3">📭</div>
                    <p>Belum ada juri yang menilai regu ini.</p>
                </div>

                <div v-else class="space-y-4">
                    <div v-for="(p, i) in penilaians" :key="p.id" :style="{ animationDelay: i * 60 + 'ms' }"
                        class="reveal bg-white rounded-2xl border border-line shadow-sm overflow-hidden transition-all duration-300"
                        :class="openId === p.id ? 'border-gold/60 shadow-lg' : ''">
                        <!-- header juri (segel) -->
                        <button @click="toggle(p.id)" class="w-full px-4 sm:px-6 py-4 flex items-center gap-3 sm:gap-4 text-left hover:bg-parchment/30 transition-colors">
                            <div class="w-11 h-11 rounded-full bg-khaki/15 text-khaki flex items-center justify-center font-display font-bold flex-shrink-0">{{ initials(p.juri_name) }}</div>
                            <div class="min-w-0 flex-1">
                                <div class="font-semibold text-forest text-sm sm:text-base truncate">{{ p.juri_name }}</div>
                                <div class="text-[11px] sm:text-xs text-ink/50">{{ p.submitted_at }}</div>
                            </div>
                            <!-- rata-rata juri + delta -->
                            <div class="text-right flex-shrink-0">
                                <div class="font-mono font-extrabold text-xl sm:text-2xl tabular-nums text-forest">{{ openId === p.id ? runningAvg(p.id) : p.nilai_akhir_juri }}</div>
                                <div v-if="openId === p.id && avgDelta(p.id) !== 0" :class="['text-[10px] font-mono font-bold tabular-nums', avgDelta(p.id) > 0 ? 'text-green-600' : 'text-red-600']">
                                    {{ avgDelta(p.id) > 0 ? '▲' : '▼' }} {{ Math.abs(avgDelta(p.id)) }}
                                </div>
                            </div>
                            <!-- segel -->
                            <span v-if="openId !== p.id" class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border text-[10px] sm:text-xs font-semibold bg-green-100 text-green-700 border-green-300 whitespace-nowrap">
                                🔒 Terkunci
                            </span>
                            <span v-else class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border text-[10px] sm:text-xs font-semibold bg-gold/15 text-gold border-gold/40 whitespace-nowrap">
                                🔓 Direvisi
                            </span>
                            <svg :class="['w-4 h-4 text-ink/40 transition-transform duration-300 flex-shrink-0', openId === p.id ? 'rotate-90' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </button>

                        <!-- body revisi -->
                        <Transition name="expand">
                            <div v-if="openId === p.id" class="px-4 sm:px-6 pb-5 border-t border-line/50 bg-parchment/20 space-y-3 pt-4">
                                <div v-for="k in kriterias" :key="k.id" class="bg-white rounded-xl border border-line/60 p-3 sm:p-4">
                                    <div class="flex items-center justify-between gap-3 mb-2">
                                        <label class="font-semibold text-forest text-sm sm:text-base flex items-center gap-2">
                                            {{ k.nama_komponen }}
                                            <span v-if="delta(p.id, k.id) !== 0" :class="['text-[10px] font-mono font-bold px-1.5 py-0.5 rounded border tabular-nums', delta(p.id, k.id) > 0 ? 'text-green-600 bg-green-50 border-green-200' : 'text-red-600 bg-red-50 border-red-200']">
                                                {{ delta(p.id, k.id) > 0 ? '▲' : '▼' }} {{ Math.abs(delta(p.id, k.id)) }}
                                            </span>
                                        </label>
                                        <input type="number" min="1" max="100" :value="valOf(p.id, k.id)" @input="setVal(p.id, k.id, $event)"
                                            placeholder="1–100"
                                            class="w-20 text-center font-mono text-xl font-bold border-2 border-line rounded-lg py-1 focus:outline-none focus:border-gold focus:ring-2 focus:ring-gold/30 transition-colors" />
                                    </div>
                                    <div class="h-2.5 rounded-full bg-line overflow-hidden">
                                        <div class="h-full rounded-full bg-gradient-to-r transition-all duration-300 ease-out" :class="barClass(p.id, k.id)" :style="{ width: Math.min(100, num(p.id, k.id)) + '%' }"></div>
                                    </div>
                                </div>

                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
                                    <div class="text-xs text-ink/55">
                                        Rata-rata juri: <span class="font-mono font-bold text-forest">{{ runningAvg(p.id) }}</span>
                                        <span v-if="avgDelta(p.id) !== 0" :class="['font-mono font-bold', avgDelta(p.id) > 0 ? 'text-green-600' : 'text-red-600']">
                                            ({{ avgDelta(p.id) > 0 ? '+' : '' }}{{ avgDelta(p.id) }} dari tersimpan)
                                        </span>
                                    </div>
                                    <div class="flex gap-2">
                                        <button @click="toggle(p.id)" class="px-4 py-2 border border-line rounded-lg hover:bg-parchment font-medium text-sm transition">Tutup</button>
                                        <button @click="saveJuri(p.id)" :disabled="!canSubmit(p.id)"
                                            class="px-5 py-2 bg-forest text-parchment rounded-lg font-bold hover:bg-forest/90 disabled:opacity-40 transition active:scale-95 text-sm whitespace-nowrap">
                                            💾 Simpan Revisi
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>

                <!-- ===== TIMELINE AUDIT (7B-3) ===== -->
                <section class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
                    <div class="px-4 sm:px-6 py-4 border-b border-line bg-parchment/30 flex items-center gap-2">
                        <span class="text-lg">📜</span>
                        <h3 class="font-display text-lg sm:text-xl font-extrabold text-forest">Riwayat Revisi (Audit Log)</h3>
                    </div>
                    <div v-if="audit.length === 0" class="px-4 sm:px-6 py-8 text-center text-ink/50 text-sm">
                        Belum ada revisi. Nilai masih asli dari juri.
                    </div>
                    <ol v-else class="relative px-4 sm:px-6 py-5 space-y-5">
                        <span class="absolute left-[27px] sm:left-[31px] top-6 bottom-6 w-px bg-line"></span>
                        <li v-for="(a, i) in audit" :key="i" class="relative flex gap-3 sm:gap-4 reveal-row" :style="{ animationDelay: i * 40 + 'ms' }">
                            <span class="relative z-10 w-3 h-3 mt-1.5 rounded-full bg-gold ring-4 ring-gold/15 flex-shrink-0"></span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                    <span class="font-semibold text-forest text-sm">{{ a.who }}</span>
                                    <span class="text-[11px] text-ink/45">{{ a.when }}</span>
                                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-forest/10 text-forest font-semibold">{{ a.desc }}</span>
                                </div>
                                <div v-if="a.props && (a.props.old_avg !== undefined)" class="mt-1.5 flex flex-wrap items-center gap-2 text-xs">
                                    <span class="font-mono text-ink/50 line-through tabular-nums">{{ a.props.old_avg }}</span>
                                    <span class="text-ink/40">→</span>
                                    <span class="font-mono font-bold text-forest tabular-nums">{{ a.props.new_avg }}</span>
                                    <span class="text-ink/45">(rata-rata juri)</span>
                                </div>
                            </div>
                        </li>
                    </ol>
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
.expand-enter-active, .expand-leave-active { transition: all 0.3s ease; overflow: hidden; }
.expand-enter-from, .expand-leave-to { opacity: 0; max-height: 0; }
.expand-enter-to, .expand-leave-from { opacity: 1; max-height: 1200px; }
</style>