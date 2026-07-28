<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import JuriLayout from '@/Layouts/JuriLayout.vue';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    lomba: { type: Object, default: null },
    golonganAktif: { type: Array, default: () => [] },
    kriteriaByGolongan: { type: Object, default: () => ({}) },
    selected: { type: [String, null], default: null },
    regus: { type: Array, default: () => [] },
});

const activeRegu = ref(null);

const kriteriaOf = computed(() => props.kriteriaByGolongan[props.selected] || []);

const form = useForm({
    golongan: props.selected,
    nilai: {},
});

const isLocked = computed(() => !!activeRegu.value?.penilaian?.is_locked);

const valOf = (kid) => form.nilai[kid];
const numOf = (kid) => {
    const v = Number(form.nilai[kid]);
    return Number.isFinite(v) ? v : 0;
};
const isValid = (kid) => {
    const v = Number(form.nilai[kid]);
    return form.nilai[kid] !== '' && form.nilai[kid] != null && Number.isFinite(v) && v >= 1 && v <= 100;
};

const filledCount = computed(() => kriteriaOf.value.filter((k) => isValid(k.id)).length);
const totalKomponen = computed(() => kriteriaOf.value.length);
const runningAvg = computed(() => {
    const vals = kriteriaOf.value.filter((k) => isValid(k.id)).map((k) => numOf(k.id));
    if (!vals.length) return 0;
    return Math.round(vals.reduce((a, b) => a + b, 0) / vals.length);
});
const canSubmit = computed(() => !isLocked.value && filledCount.value === totalKomponen.value && totalKomponen.value > 0 && !form.processing);

// warna bar on-theme: khaki (rendah) → gold (sedang) → forest (tinggi)
const barClass = (kid) => {
    const v = numOf(kid);
    if (v >= 80) return 'from-gold to-forest';
    if (v >= 50) return 'from-khaki to-gold';
    return 'from-khaki/50 to-khaki';
};

const openInput = (regu) => {
    activeRegu.value = regu;
    const preset = {};
    kriteriaOf.value.forEach((k) => {
        const ex = regu.penilaian?.details?.find((d) => d.kriteria_komponen_id === k.id);
        preset[k.id] = ex ? String(ex.nilai) : '';
    });
    form.nilai = preset;
    form.golongan = props.selected;
    document.body.style.overflow = 'hidden';
};

const closeModal = () => {
    activeRegu.value = null;
    form.reset();
    document.body.style.overflow = 'auto';
};

const setNilai = (kid, e) => {
    if (isLocked.value) return;
    const v = e.target.value;
    form.nilai[kid] = v === '' ? '' : v; // simpan string, parse di computed & backend cast
};

const submit = () => {
    form.post(route('juri.penilaian.store', { lomba: props.lomba.id, alokasi: activeRegu.value.id }), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};

const pickGolongan = (g) => {
    router.get(route('juri.penilaian.show', props.lomba.id), { golongan: g }, { preserveState: false });
};

watch(() => props.selected, () => { activeRegu.value = null; });

const statusDot = (regu) => (regu.penilaian?.is_locked ? 'bg-green-500' : 'bg-amber-500');
</script>

<template>
    <JuriLayout header="Penilaian">
        <Head :title="`Penilaian — ${lomba?.nama ?? ''}`" />

        <div class="relative">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-64 bg-[radial-gradient(60%_100%_at_50%_0%,theme(colors.forest/8%),transparent)]"></div>
            <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-forest via-gold to-khaki"></div>

            <div class="relative px-2 sm:px-4 md:px-0 pt-4 pb-10 space-y-6">
                <!-- header lomba -->
                <header class="space-y-2">
                    <Link :href="route('juri.penilaian.index')" class="text-xs sm:text-sm text-forest hover:underline inline-flex items-center">← Daftar lomba</Link>
                    <span class="block text-[10px] sm:text-xs font-display font-bold tracking-[0.25em] uppercase text-gold">Meja Penilaian</span>
                    <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-forest leading-none">{{ lomba?.nama }}</h2>
                    <p class="text-sm text-ink/60">🏆 {{ lomba?.event?.nama ?? '-' }}</p>
                </header>

                <!-- pemilih golongan (segmented chip) -->
                <section v-if="golonganAktif.length" class="space-y-3">
                    <h3 class="text-[11px] sm:text-xs font-display font-bold uppercase tracking-[0.2em] text-ink/50">Pilih Golongan</h3>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="g in golonganAktif"
                            :key="g"
                            @click="pickGolongan(g)"
                            :class="[
                                'px-4 sm:px-5 py-2 rounded-full border font-display font-bold text-sm sm:text-base uppercase tracking-wide transition-all duration-200 active:scale-95',
                                selected === g
                                    ? 'bg-forest text-parchment border-forest shadow-md'
                                    : 'bg-white text-forest border-line hover:border-gold hover:bg-parchment/40',
                            ]"
                        >
                            {{ g }}
                        </button>
                    </div>
                </section>

                <section v-else class="bg-white rounded-xl border border-line shadow-sm p-8 text-center text-ink/50">
                    <div class="text-4xl mb-2">📋</div>
                    <p>Lomba ini belum punya kriteria aktif. Minta Admin menambah kriteria.</p>
                </section>

                <!-- daftar regu -->
                <section v-if="selected" class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="font-display text-lg sm:text-xl font-semibold text-forest">
                            Regu Golongan <span class="text-gold">{{ selected }}</span>
                        </h3>
                        <span class="text-xs sm:text-sm text-ink/50">{{ regus.length }} regu siap</span>
                    </div>

                    <div v-if="regus.length === 0" class="bg-white rounded-xl border border-line shadow-sm p-8 text-center text-ink/50">
                        <div class="text-4xl mb-2">🏅</div>
                        <p>Belum ada regu terkunci di golongan ini.</p>
                    </div>

                    <div v-else class="space-y-2.5">
                        <div
                            v-for="(r, i) in regus"
                            :key="r.id"
                            :style="{ animationDelay: i * 50 + 'ms' }"
                            class="reveal group bg-white rounded-xl border border-line shadow-sm p-3 sm:p-4 flex items-center gap-3 sm:gap-4 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg hover:border-gold/60"
                        >
                            <!-- nomor urut besar ala papan undian -->
                            <div class="relative flex-shrink-0 w-14 h-14 sm:w-16 sm:h-16 rounded-xl bg-forest text-parchment flex flex-col items-center justify-center group-hover:bg-gold group-hover:text-ink transition-colors duration-300">
                                <span class="text-[8px] uppercase tracking-widest opacity-70 leading-none">No</span>
                                <span class="font-mono font-extrabold text-2xl sm:text-3xl leading-none">{{ r.nomor_urut ?? '–' }}</span>
                            </div>

                            <!-- identitas regu (level sekolah, tanpa nama siswa) -->
                            <div class="min-w-0 flex-1">
                                <div class="font-display font-bold text-forest text-base sm:text-lg truncate group-hover:text-gold transition-colors">
                                    {{ r.team_name }}
                                </div>
                                <div class="text-[11px] sm:text-xs text-ink/55">👥 {{ r.siswa_count }} siswa • golongan {{ selected }}</div>
                            </div>

                            <!-- status + aksi -->
                            <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                                <span
                                    v-if="r.penilaian?.is_locked"
                                    class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border text-[10px] sm:text-xs font-semibold bg-green-100 text-green-700 border-green-300 whitespace-nowrap"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                    Nilai {{ r.penilaian.nilai_akhir_juri }}
                                </span>
                                <span
                                    v-else
                                    class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border text-[10px] sm:text-xs font-semibold bg-amber-100 text-amber-700 border-amber-300 whitespace-nowrap"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Belum dinilai
                                </span>

                                <button
                                    v-if="r.penilaian?.is_locked"
                                    @click="openInput(r)"
                                    class="px-3 sm:px-4 py-2 border border-line text-forest rounded-lg text-[11px] sm:text-sm font-semibold hover:bg-parchment transition active:scale-95 whitespace-nowrap"
                                >
                                    👁️ Lihat
                                </button>
                                <button
                                    v-else
                                    @click="openInput(r)"
                                    class="px-3 sm:px-4 py-2 bg-gold text-ink rounded-lg text-[11px] sm:text-sm font-bold hover:opacity-90 transition active:scale-95 whitespace-nowrap"
                                >
                                    ✍️ Input Nilai
                                </button>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- MODAL INPUT / LIHAT NILAI                                  -->
        <!-- ========================================================= -->
        <Teleport to="body">
            <Transition name="pop">
                <div v-if="activeRegu" class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4" @click.self="closeModal">
                    <div class="absolute inset-0 bg-ink/60 backdrop-blur-md"></div>
                    <div class="absolute inset-0 bg-gradient-to-b from-gold/10 via-transparent to-transparent pointer-events-none"></div>

                    <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[92vh] overflow-y-auto border-t-4 border-gold" @click.stop>
                        <!-- header -->
                        <div class="sticky top-0 z-10 bg-white/95 backdrop-blur-sm border-b border-line px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-forest text-parchment flex flex-col items-center justify-center flex-shrink-0">
                                    <span class="text-[7px] uppercase tracking-widest opacity-70 leading-none">No</span>
                                    <span class="font-mono font-extrabold text-xl sm:text-2xl leading-none">{{ activeRegu.nomor_urut ?? '–' }}</span>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-display text-lg sm:text-2xl font-bold text-forest truncate">{{ activeRegu.team_name }}</h3>
                                    <p class="text-[10px] sm:text-xs text-ink/40">Golongan {{ selected }} • {{ totalKomponen }} komponen penilaian</p>
                                </div>
                            </div>
                            <button @click="closeModal" class="p-1.5 sm:p-2 hover:bg-parchment hover:rotate-90 rounded-full transition-all duration-300 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <!-- body: komponen penilaian -->
                        <div class="px-4 sm:px-6 py-4 sm:py-6 space-y-4">
                            <div v-if="isLocked" class="flex items-center gap-2 text-sm bg-green-50 border border-green-200 text-green-700 rounded-lg px-3 py-2">
                                🔒 Penilaian sudah terkunci. Mode baca.
                            </div>

                            <div v-for="k in kriteriaOf" :key="k.id" class="bg-parchment/40 rounded-xl border border-line/60 p-3 sm:p-4">
                                <div class="flex items-center justify-between gap-3 mb-2">
                                    <label class="font-semibold text-forest text-sm sm:text-base">{{ k.nama_komponen }}</label>
                                    <input
                                        type="number"
                                        min="1"
                                        max="100"
                                        :value="valOf(k.id)"
                                        :disabled="isLocked"
                                        @input="setNilai(k.id, $event)"
                                        placeholder="1–100"
                                        :class="[
                                            'w-20 text-center font-mono text-xl font-bold border-2 rounded-lg py-1 focus:outline-none transition-colors',
                                            isLocked ? 'bg-parchment/60 border-line text-ink/60 cursor-not-allowed' : 'border-line focus:border-gold focus:ring-2 focus:ring-gold/30',
                                        ]"
                                    />
                                </div>
                                <!-- bar nilai hidup -->
                                <div class="h-2.5 rounded-full bg-line overflow-hidden">
                                    <div
                                        class="h-full rounded-full bg-gradient-to-r transition-all duration-300 ease-out"
                                        :class="barClass(k.id)"
                                        :style="{ width: Math.min(100, numOf(k.id)) + '%' }"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <!-- footer: running average + aksi -->
                        <div class="sticky bottom-0 z-10 bg-white/95 backdrop-blur-sm border-t border-line px-4 sm:px-6 py-3 sm:py-4">
                            <!-- meter rata-rata sementara -->
                            <div class="flex items-center justify-between gap-3 mb-3">
                                <div>
                                    <div class="text-[10px] uppercase tracking-wider text-ink/45 font-semibold">Rata-rata Sementara</div>
                                    <div class="text-xs text-ink/55">{{ filledCount }} / {{ totalKomponen }} komponen terisi</div>
                                </div>
                                <div class="font-mono font-extrabold text-3xl sm:text-4xl tabular-nums transition-colors" :class="runningAvg >= 80 ? 'text-forest' : runningAvg >= 50 ? 'text-gold' : 'text-khaki'">
                                    {{ runningAvg }}
                                </div>
                            </div>

                            <div v-if="form.errors.nilai || form.errors.golongan" class="text-red-600 text-sm mb-3">
                                {{ form.errors.nilai || form.errors.golongan }}
                            </div>

                            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                                <button @click="closeModal" class="w-full sm:flex-1 px-4 py-2.5 border border-line rounded-lg hover:bg-parchment transition font-medium text-sm sm:text-base order-2 sm:order-1">
                                    {{ isLocked ? 'Tutup' : 'Batal' }}
                                </button>
                                <button
                                    v-if="!isLocked"
                                    @click="submit"
                                    :disabled="!canSubmit"
                                    class="w-full sm:flex-1 px-4 py-2.5 bg-forest text-parchment rounded-lg font-bold hover:bg-forest/90 disabled:opacity-40 transition active:scale-95 text-sm sm:text-base order-1 sm:order-2"
                                >
                                    🔒 Simpan & Kunci Nilai
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </JuriLayout>
</template>

<style scoped>
.reveal { opacity: 0; animation: reveal 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
@keyframes reveal { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
.pop-enter-active, .pop-leave-active { transition: opacity 0.25s ease; }
.pop-enter-active > div:not(.absolute), .pop-leave-active > div:not(.absolute) { transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease; }
.pop-enter-from, .pop-leave-to { opacity: 0; }
.pop-enter-from > div:not(.absolute), .pop-leave-to > div:not(.absolute) { opacity: 0; transform: translateY(16px) scale(0.96); }
</style>