<script setup>
import { Head, Link } from '@inertiajs/vue3';
import JuriLayout from '@/Layouts/JuriLayout.vue';
import { golLabel } from '@/golongan';

const props = defineProps({
    lombas: { type: Array, default: () => [] },
});

const statusClass = {
    draft: 'bg-gray-100 text-gray-700 border-gray-300',
    aktif: 'bg-green-100 text-green-700 border-green-300',
    selesai: 'bg-blue-100 text-blue-700 border-blue-300',
};

const kategoriLabel = { PA: 'Putra', PI: 'Putri' };
const kategoriIcon = { PA: '👦', PI: '👧' };
</script>

<template>
    <JuriLayout header="Penilaian">
        <Head title="Penilaian" />

        <div class="relative">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-56 bg-[radial-gradient(60%_100%_at_50%_0%,theme(colors.forest/8%),transparent)]"></div>
            <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-forest via-gold to-khaki"></div>

            <div class="relative px-2 sm:px-4 md:px-0 pt-4 space-y-6">
                <header class="space-y-2">
                    <span class="inline-block text-[10px] sm:text-xs font-display font-bold tracking-[0.25em] uppercase text-gold">
                        Modul 6 • Meja Juri
                    </span>
                    <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-forest leading-none">
                        Lomba yang Anda Nilai
                    </h2>
                    <p class="text-sm text-ink/60 max-w-2xl">
                        Pilih lomba yang ditugaskan kepada Anda untuk mulai menilai regu peserta.
                    </p>
                </header>

                <div v-if="lombas.length === 0" class="bg-white rounded-xl border border-line shadow-sm p-10 text-center text-ink/50">
                    <div class="text-5xl mb-3">📭</div>
                    <p class="font-semibold text-ink/60">Belum ada lomba yang ditugaskan.</p>
                    <p class="text-xs sm:text-sm mt-1">Hubungi Admin untuk penugasan lomba.</p>
                </div>

                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                    <Link
                        v-for="(l, i) in lombas"
                        :key="l.id"
                        :href="route('juri.penilaian.show', l.id)"
                        :style="{ animationDelay: i * 60 + 'ms' }"
                        class="reveal group relative bg-white rounded-xl border border-line shadow-sm p-4 sm:p-5 flex flex-col overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-gold/60"
                    >
                        <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-forest to-gold opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="font-display text-lg font-bold text-forest group-hover:text-gold transition-colors leading-tight">
                                {{ l.nama }}
                            </h3>
                            <span :class="['px-2 py-0.5 rounded-full border text-[10px] font-semibold whitespace-nowrap', statusClass[l.status]]">
                                {{ l.status }}
                            </span>
                        </div>

                        <p class="text-xs text-ink/55 mt-2">🏆 {{ l.event?.nama ?? '-' }}</p>

                        <!-- ✅ BADGE GOLONGAN + KATEGORI (seperti admin) -->
                        <div class="flex flex-wrap items-center gap-1.5 mt-2">
                            <span v-if="l.golongan" class="text-[10px] sm:text-xs bg-forest/10 text-forest px-2 py-0.5 rounded-full font-medium">
                                {{ golLabel(l.golongan) }}
                            </span>
                            <span v-if="l.kategori" class="text-[10px] sm:text-xs bg-gold/15 text-gold px-2 py-0.5 rounded-full font-medium">
                                {{ kategoriIcon[l.kategori] }} {{ kategoriLabel[l.kategori] }}
                            </span>
                        </div>

                        <!-- ✅ STATUS PENILAIAN -->
                        <div class="mt-3 flex items-center gap-2">
                            <span v-if="l.sudah_dinilai"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-green-100 text-green-700 border border-green-300 text-[10px] sm:text-xs font-semibold">
                                ✅ Sudah Dinilai
                            </span>
                            <span v-else
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 border border-amber-300 text-[10px] sm:text-xs font-semibold">
                                ⏳ Belum Dinilai
                            </span>
                        </div>

                        <div class="mt-4 flex items-center justify-between">
                            <span class="inline-flex items-center gap-1.5 text-sm">
                                <span class="w-2 h-2 rounded-full bg-gold"></span>
                                <span class="font-mono font-bold text-forest">{{ l.regu_siap }}</span>
                                <span class="text-ink/50">regu siap dinilai</span>
                            </span>
                            <span class="text-gold transition-transform duration-300 group-hover:translate-x-1">→</span>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </JuriLayout>
</template>

<style scoped>
.reveal { opacity: 0; animation: reveal 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
@keyframes reveal { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
</style>