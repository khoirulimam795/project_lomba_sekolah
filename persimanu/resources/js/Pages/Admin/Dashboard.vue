<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    stats: { type: Object, default: () => ({}) },
});

const cards = [
    { key: 'events', label: 'Event', icon: '🗓️', color: 'text-forest', bg: 'bg-forest/10' },
    { key: 'lombas', label: 'Lomba', icon: '🏅', color: 'text-gold', bg: 'bg-gold/10' },
    { key: 'juris', label: 'Juri', icon: '⚖️', color: 'text-blue-600', bg: 'bg-blue-100' },
    { key: 'kontingens', label: 'Kontingen', icon: '🏫', color: 'text-emerald-600', bg: 'bg-emerald-100' },
    { key: 'peserta', label: 'Peserta', icon: '👥', color: 'text-purple-600', bg: 'bg-purple-100' },
    { key: 'pendamping', label: 'Pendamping', icon: '🧑‍🏫', color: 'text-orange-600', bg: 'bg-orange-100' },
    { key: 'terverifikasi', label: 'Terverifikasi', icon: '✅', color: 'text-green-600', bg: 'bg-green-100' },
    { key: 'menunggu_pembayaran', label: 'Menunggu Bayar', icon: '💰', color: 'text-amber-600', bg: 'bg-amber-100' },
];
</script>

<template>
    <AdminLayout header="Dashboard">
        <Head title="Dashboard Admin" />
        <div class="px-2 sm:px-4 md:px-0 space-y-6">
            <div>
                <h2 class="font-display text-2xl sm:text-3xl font-extrabold text-forest">Dashboard Admin</h2>
                <p class="text-sm text-ink/60 mt-1">Ringkasan data sistem SAKOMA.</p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
                <div v-for="card in cards" :key="card.key"
                    class="bg-white rounded-xl border border-line shadow-sm p-4 sm:p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3 mb-3">
                        <span :class="['w-10 h-10 rounded-lg flex items-center justify-center text-xl', card.bg]">{{ card.icon }}</span>
                        <span class="text-xs sm:text-sm font-semibold text-ink/60 uppercase tracking-wider">{{ card.label }}</span>
                    </div>
                    <div :class="['font-display text-3xl sm:text-4xl font-extrabold tabular-nums', card.color]">
                        {{ stats[card.key] ?? 0 }}
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>