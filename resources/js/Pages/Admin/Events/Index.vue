// File: Index.vue
<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    events: {
        type: Array,
        default: () => [],
    },
});

const statusLabel = {
    draft: 'Draft',
    aktif: 'Aktif',
    selesai: 'Selesai',
};

const statusClass = {
    draft: 'bg-gray-100 text-gray-700 border-gray-300',
    aktif: 'bg-green-100 text-green-700 border-green-300',
    selesai: 'bg-blue-100 text-blue-700 border-blue-300',
};

const statusIcon = {
    draft: '📝',
    aktif: '✅',
    selesai: '🏁',
};

const formatDate = (value) => {
    if (!value) return '-';
    return String(value).slice(0, 10).split('-').reverse().join('/');
};

const formatDateLong = (value) => {
    if (!value) return '-';
    const date = new Date(value);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

const deleteEvent = (event) => {
    if (!confirm(`Hapus event "${event.nama}"?`)) return;
    router.delete(route('admin.events.destroy', event.id), {
        preserveScroll: true,
    });
};

// Modal state
const selectedEvent = ref(null);
const isModalOpen = ref(false);

const openModal = (event) => {
    selectedEvent.value = event;
    isModalOpen.value = true;
    document.body.style.overflow = 'hidden';
};

const closeModal = () => {
    isModalOpen.value = false;
    document.body.style.overflow = 'auto';
    setTimeout(() => {
        selectedEvent.value = null;
    }, 300);
};
</script>

<template>
    <AdminLayout header="Manajemen Event">
        <Head title="Manajemen Event" />

        <div class="px-2 sm:px-4 md:px-0">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h2 class="font-display text-xl sm:text-2xl font-semibold text-forest">
                        Daftar Event
                    </h2>
                    <p class="text-xs sm:text-sm text-ink/60 mt-1">
                        Kelola event lomba kepramukaan.
                    </p>
                </div>

                <Link
                    :href="route('admin.events.create')"
                    class="self-start sm:self-auto px-4 sm:px-5 py-2 sm:py-2.5 bg-gold text-ink font-semibold rounded-lg hover:opacity-90 text-center transition-all duration-200 hover:scale-105 shadow-sm text-sm sm:text-base"
                >
                    ✨ Tambah Event
                </Link>
            </div>

            <!-- Grid Cards -->
            <div class="grid grid-cols-1 xs:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
                <div v-if="props.events.length === 0" class="col-span-full">
                    <div class="bg-white rounded-xl border border-line shadow-sm p-8 sm:p-12 text-center text-ink/50">
                        <div class="text-4xl sm:text-6xl mb-4">📭</div>
                        <p class="text-base sm:text-lg font-semibold">Belum ada event</p>
                        <p class="text-xs sm:text-sm">Mulai buat event pertama kamu sekarang!</p>
                    </div>
                </div>

                <div
                    v-for="event in props.events"
                    :key="event.id"
                    class="group bg-white rounded-xl border border-line shadow-sm hover:shadow-lg transition-all duration-300 hover:scale-[1.02] hover:border-gold/50 overflow-hidden"
                >
                    <!-- Card Header -->
                    <div class="p-3 sm:p-4 md:p-5 pb-2 sm:pb-3">
                        <div class="flex items-start justify-between gap-2 sm:gap-3">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-forest text-sm sm:text-base md:text-lg truncate group-hover:text-gold transition-colors duration-300">
                                    {{ event.nama }}
                                </h3>
                                <div class="flex items-center gap-2 mt-0.5 sm:mt-1">
                                    <span class="text-[10px] sm:text-xs text-ink/50 truncate">
                                        {{ event.slug }}
                                    </span>
                                </div>
                            </div>
                            <span
                                :class="[
                                    'px-2 sm:px-3 py-0.5 sm:py-1 rounded-full border text-[10px] sm:text-xs font-semibold whitespace-nowrap flex-shrink-0 transition-all duration-300 group-hover:scale-105',
                                    statusClass[event.status],
                                ]"
                            >
                                <span class="hidden xs:inline">{{ statusIcon[event.status] }}</span>
                                {{ statusLabel[event.status] }}
                            </span>
                        </div>
                    </div>

                    <!-- Card Body - Quick Info -->
                    <div class="px-3 sm:px-4 md:px-5 pb-2 sm:pb-3">
                        <div class="text-[10px] sm:text-xs text-ink/60 space-y-1">
                            <div class="flex items-center gap-1 sm:gap-2">
                                <span>📅</span>
                                <span class="truncate">Pendaftaran: {{ formatDate(event.periode_pendaftaran_mulai) }} - {{ formatDate(event.periode_pendaftaran_selesai) }}</span>
                            </div>
                            <div class="flex items-center gap-1 sm:gap-2">
                                <span>🏃</span>
                                <span class="truncate">Pelaksanaan: {{ formatDate(event.tanggal_pelaksanaan_mulai) }} - {{ formatDate(event.tanggal_pelaksanaan_selesai) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card Actions -->
                    <div class="px-3 sm:px-4 md:px-5 pb-3 sm:pb-4 md:pb-5 pt-2 border-t border-line/50">
                        <div class="flex gap-1.5 sm:gap-2">
                            <button
                                @click="openModal(event)"
                                class="flex-1 px-2 sm:px-3 py-1.5 sm:py-2 bg-forest/5 text-forest font-medium rounded-lg hover:bg-forest hover:text-parchment transition-all duration-200 text-[10px] sm:text-sm whitespace-nowrap"
                            >
                                <span class="hidden xs:inline">👁️ </span>Detail
                            </button>
                            <Link
                                :href="route('admin.events.edit', event.id)"
                                class="px-2 sm:px-3 py-1.5 sm:py-2 bg-khaki text-white rounded-lg hover:opacity-90 transition-all duration-200 text-[10px] sm:text-sm"
                            >
                                ✏️
                            </Link>
                            <button
                                @click="deleteEvent(event)"
                                class="px-2 sm:px-3 py-1.5 sm:py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-all duration-200 text-[10px] sm:text-sm"
                            >
                                🗑️
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Popup -->
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
                                <span>📋</span>
                                <span class="hidden xs:inline">Detail Event</span>
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
                        <div v-if="selectedEvent" class="px-4 sm:px-6 py-4 sm:py-6 space-y-3 sm:space-y-5">
                            <!-- Status Badge -->
                            <div class="flex items-center justify-between">
                                <span
                                    :class="[
                                        'px-3 sm:px-4 py-1 sm:py-1.5 rounded-full border text-xs sm:text-sm font-semibold',
                                        statusClass[selectedEvent.status],
                                    ]"
                                >
                                    {{ statusIcon[selectedEvent.status] }} {{ statusLabel[selectedEvent.status] }}
                                </span>
                                <span class="text-[10px] sm:text-xs text-ink/40">
                                    ID: #{{ selectedEvent.id }}
                                </span>
                            </div>

                            <!-- Nama Event -->
                            <div class="bg-parchment/30 rounded-xl p-3 sm:p-4 border border-line/50">
                                <label class="text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider">Nama Event</label>
                                <p class="text-forest font-semibold text-base sm:text-lg mt-1">
                                    {{ selectedEvent.nama }}
                                </p>
                            </div>

                            <!-- Slug -->
                            <div class="bg-parchment/30 rounded-xl p-3 sm:p-4 border border-line/50">
                                <label class="text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider">Slug</label>
                                <p class="text-ink font-mono text-xs sm:text-sm mt-1 break-all">
                                    {{ selectedEvent.slug }}
                                </p>
                            </div>

                            <!-- Deskripsi -->
                            <div class="bg-parchment/30 rounded-xl p-3 sm:p-4 border border-line/50">
                                <label class="text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider">Deskripsi</label>
                                <p class="text-ink/90 text-xs sm:text-sm leading-relaxed mt-1 whitespace-pre-wrap break-words">
                                    {{ selectedEvent.deskripsi || '-' }}
                                </p>
                            </div>

                            <!-- Periode Pendaftaran -->
                            <div class="bg-parchment/30 rounded-xl p-3 sm:p-4 border border-line/50">
                                <label class="text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider">Periode Pendaftaran</label>
                                <div class="flex flex-col xs:flex-row items-start xs:items-center gap-2 xs:gap-3 mt-2">
                                    <div class="w-full xs:flex-1">
                                        <div class="text-[10px] sm:text-xs text-ink/40">Mulai</div>
                                        <div class="font-semibold text-forest text-sm sm:text-base">
                                            {{ formatDateLong(selectedEvent.periode_pendaftaran_mulai) }}
                                        </div>
                                    </div>
                                    <div class="text-ink/30 hidden xs:block">→</div>
                                    <div class="w-full xs:flex-1">
                                        <div class="text-[10px] sm:text-xs text-ink/40">Selesai</div>
                                        <div class="font-semibold text-forest text-sm sm:text-base">
                                            {{ formatDateLong(selectedEvent.periode_pendaftaran_selesai) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tanggal Pelaksanaan -->
                            <div class="bg-parchment/30 rounded-xl p-3 sm:p-4 border border-line/50">
                                <label class="text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider">Tanggal Pelaksanaan</label>
                                <div class="flex flex-col xs:flex-row items-start xs:items-center gap-2 xs:gap-3 mt-2">
                                    <div class="w-full xs:flex-1">
                                        <div class="text-[10px] sm:text-xs text-ink/40">Mulai</div>
                                        <div class="font-semibold text-forest text-sm sm:text-base">
                                            {{ formatDateLong(selectedEvent.tanggal_pelaksanaan_mulai) }}
                                        </div>
                                    </div>
                                    <div class="text-ink/30 hidden xs:block">→</div>
                                    <div class="w-full xs:flex-1">
                                        <div class="text-[10px] sm:text-xs text-ink/40">Selesai</div>
                                        <div class="font-semibold text-forest text-sm sm:text-base">
                                            {{ formatDateLong(selectedEvent.tanggal_pelaksanaan_selesai) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Created At -->
                            <div class="text-[10px] sm:text-xs text-ink/40 text-right border-t border-line pt-3">
                                Dibuat: {{ formatDateLong(selectedEvent.created_at) }}
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
                                v-if="selectedEvent"
                                :href="route('admin.events.edit', selectedEvent.id)"
                                class="w-full xs:flex-1 px-4 py-2 sm:py-2.5 bg-forest text-parchment rounded-lg hover:bg-forest/90 transition-all duration-200 font-medium text-center text-sm sm:text-base order-1 xs:order-2"
                                @click="closeModal"
                            >
                                ✏️ Edit Event
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
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.animate-fadeIn {
    animation: fadeIn 0.3s ease-out;
}

.animate-slideUp {
    animation: slideUp 0.3s ease-out;
}

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