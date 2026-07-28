<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    lombas: { type: Array, default: () => [] },
    events: { type: Array, default: () => [] },
});

const filterByEvent = (e) => {
    router.get(
        route('admin.penugasan-juri.index'),
        { event_id: e.target.value || undefined },
        { preserveState: true }
    );
};

// Modal state
const selectedLomba = ref(null);
const isModalOpen = ref(false);

const openModal = (lomba) => {
    selectedLomba.value = lomba;
    isModalOpen.value = true;
    document.body.style.overflow = 'hidden';
};

const closeModal = () => {
    isModalOpen.value = false;
    document.body.style.overflow = 'auto';
    setTimeout(() => {
        selectedLomba.value = null;
    }, 300);
};

const statusLabel = { draft: 'Draft', aktif: 'Aktif', selesai: 'Selesai' };
const statusClass = {
    draft: 'bg-gray-100 text-gray-700 border-gray-300',
    aktif: 'bg-green-100 text-green-700 border-green-300',
    selesai: 'bg-blue-100 text-blue-700 border-blue-300',
};

const getInitials = (name) => {
    if (!name) return '?';
    return name
        .split(' ')
        .slice(0, 2)
        .map((n) => n[0])
        .join('')
        .toUpperCase();
};
</script>

<template>
    <AdminLayout header="Penugasan Juri">
        <Head title="Penugasan Juri" />

        <div class="px-2 sm:px-4 md:px-0">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h2 class="font-display text-xl sm:text-2xl font-semibold text-forest">
                        Penugasan Juri per Lomba
                    </h2>
                    <p class="text-xs sm:text-sm text-ink/60 mt-1">
                        Tentukan juri yang menilai tiap lomba (bisa multi-juri).
                    </p>
                </div>
            </div>

            <!-- Filter event -->
            <div class="mb-4 max-w-full sm:max-w-xs">
                <select
                    @change="filterByEvent"
                    class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold text-sm sm:text-base"
                >
                    <option value="">📋 Semua Event</option>
                    <option v-for="ev in props.events" :key="ev.id" :value="ev.id">{{ ev.nama }}</option>
                </select>
            </div>

            <!-- Grid Cards -->
            <div class="grid grid-cols-1 xs:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
                <!-- Empty state -->
                <div v-if="props.lombas.length === 0" class="col-span-full">
                    <div class="bg-white rounded-xl border border-line shadow-sm p-8 sm:p-12 text-center text-ink/50">
                        <div class="text-4xl sm:text-6xl mb-4">🏆</div>
                        <p class="text-base sm:text-lg font-semibold">Belum ada lomba</p>
                        <p class="text-xs sm:text-sm">Buat lomba dulu di menu Lomba.</p>
                    </div>
                </div>

                <!-- Card lomba -->
                <div
                    v-for="l in props.lombas"
                    :key="l.id"
                    class="group bg-white rounded-xl border border-line shadow-sm hover:shadow-lg transition-all duration-300 hover:scale-[1.02] hover:border-gold/50 overflow-hidden"
                >
                    <div class="p-3 sm:p-4 md:p-5">
                        <div class="flex items-start justify-between gap-2 sm:gap-3">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-forest text-sm sm:text-base md:text-lg truncate group-hover:text-gold transition-colors duration-300">
                                    {{ l.nama }}
                                </h3>
                                <div class="flex flex-wrap items-center gap-1 sm:gap-2 mt-0.5 sm:mt-1">
                                    <span class="text-[10px] sm:text-xs bg-forest/10 text-forest px-2 py-0.5 rounded-full font-medium truncate max-w-[120px] sm:max-w-[140px]">
                                        {{ l.event?.nama ?? '-' }}
                                    </span>
                                    <span class="text-[10px] sm:text-xs text-ink/30 hidden xs:inline">•</span>
                                    <span class="text-[10px] sm:text-xs text-ink/50 truncate">
                                        {{ l.slug }}
                                    </span>
                                </div>
                            </div>

                            <span
                                :class="[
                                    'px-2 sm:px-3 py-0.5 sm:py-1 rounded-full border text-[10px] sm:text-xs font-semibold whitespace-nowrap flex-shrink-0 transition-all duration-300 group-hover:scale-105',
                                    statusClass[l.status],
                                ]"
                            >
                                {{ statusLabel[l.status] }}
                            </span>
                        </div>

                        <!-- Jumlah juri -->
                        <div class="mt-3">
                            <span
                                :class="[
                                    'inline-flex items-center gap-1 px-2 sm:px-3 py-0.5 sm:py-1 rounded-full border text-[10px] sm:text-xs font-semibold transition-all duration-300 group-hover:scale-105',
                                    l.juri && l.juri.length > 0
                                        ? 'bg-green-100 text-green-700 border-green-300'
                                        : 'bg-red-100 text-red-700 border-red-300',
                                ]"
                            >
                                👨‍⚖️ {{ l.juri ? l.juri.length : 0 }} Juri
                                <span v-if="!l.juri || l.juri.length === 0" class="hidden sm:inline">— belum ditugaskan</span>
                            </span>
                        </div>
                    </div>

                    <!-- Card Actions -->
                    <div class="px-3 sm:px-4 md:px-5 pb-3 sm:pb-4 md:pb-5 pt-2 border-t border-line/50">
                        <div class="flex gap-1.5 sm:gap-2">
                            <button
                                @click="openModal(l)"
                                class="flex-1 px-2 sm:px-3 py-1.5 sm:py-2 bg-forest/5 text-forest font-medium rounded-lg hover:bg-forest hover:text-parchment transition-all duration-200 text-[10px] sm:text-sm whitespace-nowrap"
                            >
                                <span class="hidden xs:inline">👁️ </span>Detail
                            </button>
                            <Link
                                :href="route('admin.penugasan-juri.edit', l.id)"
                                class="flex-1 px-2 sm:px-3 py-1.5 sm:py-2 bg-gold text-ink font-semibold rounded-lg hover:opacity-90 transition-all duration-200 text-[10px] sm:text-sm text-center whitespace-nowrap"
                            >
                                ⚙️ Atur Juri
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Detail -->
            <Teleport to="body">
                <div
                    v-if="isModalOpen"
                    class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4 bg-black/50 backdrop-blur-sm animate-fadeIn"
                    @click.self="closeModal"
                >
                    <div
                        class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto animate-slideUp mx-2 sm:mx-0"
                        @click.stop
                    >
                        <!-- Modal Header -->
                        <div class="sticky top-0 bg-white/95 backdrop-blur-sm border-b border-line px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between rounded-t-2xl">
                            <h3 class="font-display text-base sm:text-xl font-semibold text-forest flex items-center gap-2">
                                <span>👨‍⚖️</span>
                                <span class="hidden xs:inline">Detail Penugasan Juri</span>
                                <span class="xs:hidden">Detail</span>
                            </h3>
                            <button
                                @click="closeModal"
                                class="p-1.5 sm:p-2 hover:bg-parchment rounded-full transition-colors duration-200"
                            >
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Modal Body -->
                        <div v-if="selectedLomba" class="px-4 sm:px-6 py-4 sm:py-6 space-y-3 sm:space-y-5">
                            <!-- Status + ID -->
                            <div class="flex items-center justify-between">
                                <span
                                    :class="[
                                        'px-3 sm:px-4 py-1 sm:py-1.5 rounded-full border text-xs sm:text-sm font-semibold',
                                        statusClass[selectedLomba.status],
                                    ]"
                                >
                                    {{ statusLabel[selectedLomba.status] }}
                                </span>
                                <span class="text-[10px] sm:text-xs text-ink/40">ID: #{{ selectedLomba.id }}</span>
                            </div>

                            <!-- Nama Lomba -->
                            <div class="bg-parchment/30 rounded-xl p-3 sm:p-4 border border-line/50">
                                <label class="text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider">Nama Lomba</label>
                                <p class="text-forest font-semibold text-base sm:text-lg mt-1">{{ selectedLomba.nama }}</p>
                                <p class="text-xs text-ink/50 mt-0.5">{{ selectedLomba.slug }}</p>
                            </div>

                            <!-- Event -->
                            <div class="bg-parchment/30 rounded-xl p-3 sm:p-4 border border-line/50">
                                <label class="text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider">Event</label>
                                <p class="text-forest font-medium text-sm sm:text-base mt-1">
                                    {{ selectedLomba.event?.nama ?? '-' }}
                                </p>
                                <p v-if="selectedLomba.event" class="text-xs text-ink/50 mt-0.5">
                                    {{ selectedLomba.event.slug }}
                                </p>
                            </div>

                            <!-- Daftar Juri -->
                            <div class="bg-parchment/30 rounded-xl p-3 sm:p-4 border border-line/50">
                                <label class="text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider">
                                    Juri Ditugaskan ({{ selectedLomba.juri ? selectedLomba.juri.length : 0 }})
                                </label>

                                <div v-if="!selectedLomba.juri || selectedLomba.juri.length === 0" class="mt-2 text-xs sm:text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                                    ⚠️ Belum ada juri ditugaskan untuk lomba ini.
                                </div>

                                <div v-else class="mt-2 space-y-2">
                                    <div
                                        v-for="j in selectedLomba.juri"
                                        :key="j.id"
                                        class="flex items-center gap-3 bg-white rounded-lg border border-line px-3 py-2 hover:border-gold/50 transition-colors duration-200"
                                    >
                                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-forest text-parchment flex items-center justify-center text-xs sm:text-sm font-semibold flex-shrink-0">
                                            {{ getInitials(j.name) }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="font-semibold text-forest text-sm sm:text-base truncate">{{ j.name }}</div>
                                            <div class="text-[10px] sm:text-xs text-ink/50 truncate">{{ j.email }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Created At -->
                            <div class="text-[10px] sm:text-xs text-ink/40 text-right border-t border-line pt-3">
                                Dibuat: {{ new Date(selectedLomba.created_at).toLocaleDateString('id-ID', {
                                    day: 'numeric', month: 'long', year: 'numeric'
                                }) }}
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="sticky bottom-0 bg-white/95 backdrop-blur-sm border-t border-line px-4 sm:px-6 py-3 sm:py-4 flex flex-col xs:flex-row gap-2 xs:gap-3 rounded-b-2xl">
                            <button
                                @click="closeModal"
                                class="w-full xs:flex-1 px-4 py-2 sm:py-2.5 border border-line rounded-lg hover:bg-parchment transition-colors duration-200 font-medium text-sm sm:text-base order-2 xs:order-1"
                            >
                                Tutup
                            </button>
                            <Link
                                v-if="selectedLomba"
                                :href="route('admin.penugasan-juri.edit', selectedLomba.id)"
                                class="w-full xs:flex-1 px-4 py-2 sm:py-2.5 bg-gold text-ink rounded-lg hover:opacity-90 transition-all duration-200 font-semibold text-center text-sm sm:text-base order-1 xs:order-2"
                                @click="closeModal"
                            >
                                ⚙️ Atur Juri
                            </Link>
                        </div>
                    </div>
                </div>
            </Teleport>
        </div>
    </AdminLayout>
</template>

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
.animate-fadeIn { animation: fadeIn 0.3s ease-out; }
.animate-slideUp { animation: slideUp 0.3s ease-out; }

/* Extra small devices (phones, 320px and up) */
@media (min-width: 320px) {
    .xs\:grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

/* Custom breakpoint for extra small screens */
@media (max-width: 480px) {
    .grid {
        grid-template-columns: 1fr;
    }
}
</style>