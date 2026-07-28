<script setup>
import { Head } from '@inertiajs/vue3';
import JuriLayout from '@/Layouts/JuriLayout.vue';

const props = defineProps({
    grouped: { type: Array, default: () => [] },
});

const nilaiClass = (v) => (v >= 80 ? 'text-forest bg-green-100 border-green-300' : v >= 50 ? 'text-gold bg-amber-100 border-amber-300' : 'text-khaki bg-parchment border-line');
</script>

<template>
    <JuriLayout header="Rekap Penilaian Saya">
        <Head title="Rekap Penilaian" />

        <div class="px-2 sm:px-4 md:px-0 space-y-6">
            <header class="space-y-2">
                <span class="inline-block text-[10px] sm:text-xs font-display font-bold tracking-[0.25em] uppercase text-gold">Read-only</span>
                <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-forest leading-none">Rekap Nilai Anda</h2>
                <p class="text-sm text-ink/60">Seluruh penilaian yang telah Anda kunci. Tidak dapat diubah dari sini.</p>
            </header>

            <div v-if="grouped.length === 0" class="bg-white rounded-xl border border-line shadow-sm p-10 text-center text-ink/50">
                <div class="text-5xl mb-3">🗂️</div>
                <p>Belum ada penilaian yang Anda lakukan.</p>
            </div>

            <div v-for="(g, i) in grouped" :key="g.lomba?.id ?? i" class="bg-white rounded-xl border border-line shadow-sm overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-line bg-parchment/30">
                    <h3 class="font-display text-xl font-extrabold text-forest">{{ g.lomba?.nama ?? '-' }}</h3>
                    <p class="text-xs text-ink/50 mt-0.5">🏆 {{ g.lomba?.event?.nama ?? '-' }} • {{ g.items.length }} penilaian</p>
                </div>
                <div class="divide-y divide-line/60">
                    <div v-for="(it, j) in g.items" :key="j" class="px-4 sm:px-6 py-3 flex items-center justify-between gap-3 hover:bg-parchment/30 transition-colors">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="font-mono font-bold text-forest text-lg w-8 text-center flex-shrink-0">{{ it.nomor_urut_tampil ?? '–' }}</span>
                            <div class="min-w-0">
                                <div class="font-semibold text-forest text-sm sm:text-base truncate">{{ it.team_name }}</div>
                                <div class="text-[11px] text-ink/50">{{ it.golongan }} • {{ it.submitted_at }}</div>
                            </div>
                        </div>
                        <span :class="['font-mono font-extrabold text-lg px-3 py-1 rounded-lg border tabular-nums', nilaiClass(Number(it.nilai_akhir_juri))]">
                            {{ it.nilai_akhir_juri }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </JuriLayout>
</template>