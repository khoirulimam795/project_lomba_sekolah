<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    kontingens: { type: Array, default: () => [] },
});

const formatDate = (v) => {
    if (!v) return '-';
    return new Date(v).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};
</script>

<template>
    <AdminLayout header="Verifikasi Pembayaran">
        <Head title="Verifikasi Pembayaran" />

        <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2">
            <!-- Header Section -->
            <div class="mb-6 sm:mb-8">
                <h2 class="font-display text-xl sm:text-2xl lg:text-3xl font-bold text-forest tracking-tight">
                    Menunggu Approval Pembayaran
                </h2>
                <p class="text-xs sm:text-sm text-ink/60 mt-1 leading-relaxed">
                    Periksa bukti bayar kontingen, lalu setujui atau tolak.
                </p>
            </div>

            <!-- Empty State -->
            <div 
                v-if="props.kontingens.length === 0" 
                class="bg-white rounded-2xl border border-line/60 shadow-sm p-8 sm:p-12 text-center"
            >
                <div class="text-5xl sm:text-6xl mb-4 animate-bounce">✅</div>
                <h3 class="text-base sm:text-lg font-semibold text-forest">Tidak ada pembayaran menunggu approval</h3>
                <p class="text-xs sm:text-sm text-ink/50 mt-1">Semua verifikasi pembayaran sudah diselesaikan.</p>
            </div>

            <!-- Grid Cards -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                <div
                    v-for="k in props.kontingens"
                    :key="k.id"
                    class="group bg-white rounded-2xl border border-line/80 shadow-sm hover:shadow-md sm:hover:scale-[1.01] hover:border-gold/60 transition-all duration-200 p-4 sm:p-5 flex flex-col justify-between"
                >
                    <!-- Card Top Content -->
                    <div>
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <h3 class="font-bold text-forest text-base sm:text-lg group-hover:text-gold transition-colors line-clamp-2 leading-snug">
                                {{ k.event?.nama ?? '-' }}
                            </h3>
                            <span class="shrink-0 bg-gold/10 text-gold-dark text-[10px] sm:text-xs font-semibold px-2.5 py-1 rounded-full">
                                Pending
                            </span>
                        </div>

                        <div class="space-y-2 text-xs sm:text-sm text-ink/70">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="shrink-0">🏫</span>
                                <span class="truncate font-medium">{{ k.team?.name ?? '-' }}</span>
                            </div>
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="shrink-0">📛</span>
                                <span class="truncate">{{ k.nama_kontingen ?? '-' }}</span>
                            </div>
                            <div class="flex items-center gap-2 min-w-0 text-ink/50">
                                <span class="shrink-0">🗓️</span>
                                <span>{{ formatDate(k.updated_at) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="mt-5 pt-3 border-t border-line/40">
                        <Link
                            :href="route('admin.verifikasi-pembayaran.show', k.id)"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gold text-ink font-semibold rounded-xl hover:bg-gold/90 active:scale-[0.98] transition-all text-xs sm:text-sm shadow-sm"
                        >
                            <span>🔍</span>
                            <span>Periksa Bukti</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>