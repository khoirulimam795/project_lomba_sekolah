<script setup>
import { Head } from '@inertiajs/vue3';
import JuriLayout from '@/Layouts/JuriLayout.vue';
import { computed } from 'vue';
import { golLabel } from '@/golongan';

const props = defineProps({
    grouped: { type: Array, default: () => [] },
});

const KATEGORI_LABEL = { PA: 'Putra', PI: 'Putri' };
const KATEGORI_ICON  = { PA: '👦', PI: '👧' };

const nilaiClass = (v) =>
    v >= 80 ? 'text-forest bg-green-100 border-green-300'
    : v >= 50 ? 'text-gold bg-amber-100 border-amber-300'
    : 'text-khaki bg-parchment border-line';

// ===== ringkasan (biar halaman "bercerita", bukan cuma daftar) =====
const totalLomba = computed(() => props.grouped.length);
const totalPenilaian = computed(() =>
    props.grouped.reduce((n, g) => n + (g.items?.length ?? 0), 0)
);
const rataNilai = computed(() => {
    const all = props.grouped
        .flatMap((g) => (g.items ?? []).map((it) => Number(it.nilai_akhir_juri)))
        .filter((v) => !isNaN(v));
    return all.length ? (all.reduce((a, b) => a + b, 0) / all.length).toFixed(1) : '–';
});
</script>

<template>
    <JuriLayout header="Rekap Penilaian Saya">
        <Head title="Rekap Penilaian" />

        <div class="relative">
            <!-- ambient on-theme (konsisten dgn halaman juri lain) -->
            <div class="pointer-events-none absolute inset-x-0 top-0 h-56 bg-[radial-gradient(60%_100%_at_50%_0%,theme(colors.forest/8%),transparent)]"></div>
            <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-forest via-gold to-khaki"></div>

            <div class="relative px-2 sm:px-4 md:px-0 pt-4 space-y-6">
                <header class="space-y-2">
                    <span class="inline-block text-[10px] sm:text-xs font-display font-bold tracking-[0.25em] uppercase text-gold">Read-only</span>
                    <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-forest leading-none">Rekap Nilai Anda</h2>
                    <p class="text-sm text-ink/60 max-w-2xl">Seluruh penilaian yang telah Anda kunci, lengkap golongan &amp; kategori regu. Tidak dapat diubah dari sini.</p>
                </header>

                <!-- chip ringkasan -->
                <div v-if="grouped.length > 0" class="grid grid-cols-3 gap-2 sm:gap-3">
                    <div class="bg-white rounded-xl border border-line shadow-sm p-3 sm:p-4">
                        <div class="text-[9px] sm:text-[10px] uppercase tracking-wider text-ink/45 font-semibold">Lomba Dinilai</div>
                        <div class="font-display text-xl sm:text-2xl md:text-3xl font-extrabold text-forest tabular-nums">{{ totalLomba }}</div>
                    </div>
                    <div class="bg-white rounded-xl border border-line shadow-sm p-3 sm:p-4">
                        <div class="text-[9px] sm:text-[10px] uppercase tracking-wider text-ink/45 font-semibold">Total Penilaian</div>
                        <div class="font-display text-xl sm:text-2xl md:text-3xl font-extrabold text-gold tabular-nums">{{ totalPenilaian }}</div>
                    </div>
                    <div class="bg-white rounded-xl border border-line shadow-sm p-3 sm:p-4">
                        <div class="text-[9px] sm:text-[10px] uppercase tracking-wider text-ink/45 font-semibold">Rata-rata Nilai</div>
                        <div class="font-display text-xl sm:text-2xl md:text-3xl font-extrabold text-khaki tabular-nums">{{ rataNilai }}</div>
                    </div>
                </div>

                <div v-if="grouped.length === 0" class="bg-white rounded-xl border border-line shadow-sm p-10 text-center text-ink/50">
                    <div class="text-5xl mb-3">🗂️</div>
                    <p class="font-semibold">Belum ada penilaian yang Anda lakukan.</p>
                    <p class="text-xs sm:text-sm mt-1">Mulai menilai dari menu <strong>Penilaian</strong>.</p>
                </div>

                <div
                    v-for="(g, i) in grouped"
                    :key="g.lomba?.id ?? i"
                    :style="{ animationDelay: i * 70 + 'ms' }"
                    class="reveal bg-white rounded-xl border border-line shadow-sm overflow-hidden transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md hover:border-gold/40"
                >
                    <div class="px-4 sm:px-6 py-4 border-b border-line bg-parchment/30 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="font-display text-lg sm:text-xl font-extrabold text-forest truncate">{{ g.lomba?.nama ?? '-' }}</h3>
                            <p class="text-[11px] sm:text-xs text-ink/50 mt-0.5 truncate">🏆 {{ g.lomba?.event?.nama ?? '-' }}</p>
                        </div>
                        <span class="flex-shrink-0 text-[10px] sm:text-xs font-semibold text-ink/55 bg-white border border-line/70 px-2.5 py-1 rounded-full">{{ g.items.length }} penilaian</span>
                    </div>

                    <div class="divide-y divide-line/60">
                        <div
                            v-for="(it, j) in g.items"
                            :key="j"
                            class="group/row px-4 sm:px-6 py-3 flex items-center justify-between gap-3 hover:bg-parchment/40 transition-colors"
                        >
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="font-mono font-extrabold text-forest text-lg sm:text-xl w-8 sm:w-9 text-center flex-shrink-0 tabular-nums">{{ it.nomor_urut_tampil ?? '–' }}</span>
                                <div class="min-w-0">
                                    <div class="font-semibold text-forest text-sm sm:text-base truncate">{{ it.team_name }}</div>
                                    <!-- ✅ meta: golongan (label) + KATEGORI (Putra/Putri) + waktu -->
                                    <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                        <span class="inline-flex items-center text-[10px] sm:text-[11px] bg-forest/10 text-forest px-2 py-0.5 rounded-full font-medium">
                                            {{ golLabel(it.golongan) ?? it.golongan }}
                                        </span>
                                        <span
                                            v-if="it.kategori"
                                            class="inline-flex items-center gap-1 text-[10px] sm:text-[11px] bg-gold/15 text-gold px-2 py-0.5 rounded-full font-medium"
                                        >
                                            {{ KATEGORI_ICON[it.kategori] }} {{ KATEGORI_LABEL[it.kategori] }}
                                        </span>
                                        <span class="text-[10px] sm:text-[11px] text-ink/45">• {{ it.submitted_at }}</span>
                                    </div>
                                </div>
                            </div>
                            <span :class="['font-mono font-extrabold text-base sm:text-lg px-3 py-1 rounded-lg border tabular-nums flex-shrink-0 transition-transform group-hover/row:scale-105', nilaiClass(Number(it.nilai_akhir_juri))]">
                                {{ it.nilai_akhir_juri }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </JuriLayout>
</template>

<style scoped>
.reveal { opacity: 0; animation: reveal 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
@keyframes reveal { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
</style>