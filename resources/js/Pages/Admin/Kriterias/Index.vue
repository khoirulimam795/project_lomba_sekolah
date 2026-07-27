<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    kriterias: { type: Array, default: () => [] },
    lombas: { type: Array, default: () => [] },
});

const filterByLomba = (e) => {
    router.get(
        route('admin.kriterias.index'),
        { lomba_id: e.target.value || undefined },
        { preserveState: true }
    );
};

const deleteKriteria = (k) => {
    if (!confirm(`Hapus kriteria "${k.nama_komponen}"?`)) return;
    router.delete(route('admin.kriterias.destroy', k.id), { preserveScroll: true });
};

// Modal state
const selectedKriteria = ref(null);
const isModalOpen = ref(false);

const openModal = (kriteria) => {
    selectedKriteria.value = kriteria;
    isModalOpen.value = true;
    document.body.style.overflow = 'hidden';
};

const closeModal = () => {
    isModalOpen.value = false;
    document.body.style.overflow = 'auto';
    setTimeout(() => {
        selectedKriteria.value = null;
    }, 300);
};

// Get golongan label
const getGolonganLabel = (golongan) => {
    const labels = {
        'sd': 'SD',
        'smp': 'SMP', 
        'sma': 'SMA',
        'umum': 'Umum'
    };
    return labels[golongan] || golongan;
};
</script>

<template>
    <AdminLayout header="Kriteria Penilaian">
        <Head title="Kriteria Penilaian" />

        <div class="px-2 sm:px-4 md:px-0">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h2 class="font-display text-xl sm:text-2xl font-semibold text-forest">Daftar Kriteria</h2>
                    <p class="text-xs sm:text-sm text-ink/60 mt-1">
                        Komponen penilaian per lomba per golongan (skala 1-100, tanpa bobot).
                    </p>
                </div>

                <Link
                    :href="route('admin.kriterias.create')"
                    class="self-start sm:self-auto px-4 sm:px-5 py-2 sm:py-2.5 bg-gold text-ink font-semibold rounded-lg hover:opacity-90 text-center transition-all duration-200 hover:scale-105 shadow-sm text-sm sm:text-base"
                >
                    ✨ Tambah Kriteria
                </Link>
            </div>

            <!-- Filter lomba -->
            <div class="mb-4 max-w-full sm:max-w-xs">
                <select
                    @change="filterByLomba"
                    class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold text-sm sm:text-base"
                >
                    <option value="">📋 Semua Lomba</option>
                    <option v-for="l in props.lombas" :key="l.id" :value="l.id">{{ l.nama }}</option>
                </select>
            </div>

            <!-- Grid Cards -->
            <div class="grid grid-cols-1 xs:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
                <div v-if="props.kriterias.length === 0" class="col-span-full">
                    <div class="bg-white rounded-xl border border-line shadow-sm p-8 sm:p-12 text-center text-ink/50">
                        <div class="text-4xl sm:text-6xl mb-4">📋</div>
                        <p class="text-base sm:text-lg font-semibold">Belum ada kriteria</p>
                        <p class="text-xs sm:text-sm">Mulai tambahkan kriteria penilaian untuk lomba!</p>
                    </div>
                </div>

                <div
                    v-for="k in props.kriterias"
                    :key="k.id"
                    class="group bg-white rounded-xl border border-line shadow-sm hover:shadow-lg transition-all duration-300 hover:scale-[1.02] hover:border-gold/50 overflow-hidden"
                >
                    <!-- Card Content -->
                    <div class="p-3 sm:p-4 md:p-5">
                        <div class="flex items-start justify-between gap-2 sm:gap-3">
                            <div class="flex-1 min-w-0">
                                <!-- Nama Komponen - lebih besar dan jelas -->
                                <h3 class="font-semibold text-forest text-base sm:text-lg md:text-xl truncate group-hover:text-gold transition-colors duration-300">
                                    {{ k.lomba?.nama ?? '-' }}
                                </h3>
                                <div class="flex flex-wrap items-center gap-1 sm:gap-2 mt-1 sm:mt-1.5">
                                    <span class="text-[10px] sm:text-xs text-ink/30 hidden xs:inline">•</span>
                                    <span class="text-[10px] sm:text-xs bg-forest/10 text-forest px-2 py-0.5 rounded-full font-medium">
                                        {{ getGolonganLabel(k.golongan) }}
                                    </span>
                                    <span class="text-[10px] sm:text-xs text-ink/30 hidden xs:inline">•</span>
                                    <span class="text-[10px] sm:text-xs text-ink/50 font-mono">
                                        #{{ k.urutan }}
                                    </span>
                                </div>
                            </div>
                            <span
                                :class="[
                                    'px-2 sm:px-3 py-0.5 sm:py-1 rounded-full border text-[10px] sm:text-xs font-semibold whitespace-nowrap flex-shrink-0 transition-all duration-300 group-hover:scale-105',
                                    k.is_active
                                        ? 'bg-green-100 text-green-700 border-green-300'
                                        : 'bg-gray-100 text-gray-700 border-gray-300',
                                ]"
                            >
                                <span class="hidden xs:inline">{{ k.is_active ? '✅' : '⛔' }}</span>
                                {{ k.is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                    </div>

                    <!-- Card Actions -->
                    <div class="px-3 sm:px-4 md:px-5 pb-3 sm:pb-4 md:pb-5 pt-2 border-t border-line/50">
                        <div class="flex gap-1.5 sm:gap-2">
                            <button
                                @click="openModal(k)"
                                class="flex-1 px-2 sm:px-3 py-1.5 sm:py-2 bg-forest/5 text-forest font-medium rounded-lg hover:bg-forest hover:text-parchment transition-all duration-200 text-[10px] sm:text-sm whitespace-nowrap"
                            >
                                <span class="hidden xs:inline">👁️ </span>Detail
                            </button>
                            <Link
                                :href="route('admin.kriterias.edit', k.id)"
                                class="px-2 sm:px-3 py-1.5 sm:py-2 bg-khaki text-white rounded-lg hover:opacity-90 transition-all duration-200 text-[10px] sm:text-sm"
                            >
                                ✏️
                            </Link>
                            <button
                                @click="deleteKriteria(k)"
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
                                <span class="hidden xs:inline">Detail Kriteria</span>
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
                        <div v-if="selectedKriteria" class="px-4 sm:px-6 py-4 sm:py-6 space-y-3 sm:space-y-5">
                            <!-- Status Badge -->
                            <div class="flex items-center justify-between">
                                <span
                                    :class="[
                                        'px-3 sm:px-4 py-1 sm:py-1.5 rounded-full border text-xs sm:text-sm font-semibold',
                                        selectedKriteria.is_active
                                            ? 'bg-green-100 text-green-700 border-green-300'
                                            : 'bg-gray-100 text-gray-700 border-gray-300',
                                    ]"
                                >
                                    {{ selectedKriteria.is_active ? '✅' : '⛔' }} 
                                    {{ selectedKriteria.is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                                <span class="text-[10px] sm:text-xs text-ink/40">
                                    ID: #{{ selectedKriteria.id }}
                                </span>
                            </div>

                            <!-- Nama Komponen -->
                            <div class="bg-parchment/30 rounded-xl p-3 sm:p-4 border border-line/50">
                                <label class="text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider">Nama Komponen</label>
                                <p class="text-forest font-semibold text-base sm:text-lg mt-1">
                                    {{ selectedKriteria.nama_komponen }}
                                </p>
                            </div>

                            <!-- Komponen (deskripsi lengkap) -->
                            <div class="bg-parchment/30 rounded-xl p-3 sm:p-4 border border-line/50">
                                <label class="text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider">Komponen</label>
                                <p class="text-ink/90 text-sm sm:text-base leading-relaxed mt-1 whitespace-pre-wrap break-words">
                                    {{ selectedKriteria.komponen || '-' }}
                                </p>
                            </div>

                            <!-- Lomba -->
                            <div class="bg-parchment/30 rounded-xl p-3 sm:p-4 border border-line/50">
                                <label class="text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider">Lomba Terkait</label>
                                <p class="text-forest font-medium text-sm sm:text-base mt-1">
                                    {{ selectedKriteria.lomba?.nama ?? '-' }}
                                </p>
                                <p v-if="selectedKriteria.lomba" class="text-xs text-ink/50 mt-0.5">
                                    {{ selectedKriteria.lomba.slug }}
                                </p>
                            </div>

                            <!-- Golongan & Urutan -->
                            <div class="grid grid-cols-1 xs:grid-cols-2 gap-3">
                                <div class="bg-parchment/30 rounded-xl p-3 sm:p-4 border border-line/50">
                                    <label class="text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider">Golongan</label>
                                    <p class="text-forest font-semibold text-sm sm:text-base mt-1">
                                        {{ getGolonganLabel(selectedKriteria.golongan) }}
                                    </p>
                                </div>
                                <div class="bg-parchment/30 rounded-xl p-3 sm:p-4 border border-line/50">
                                    <label class="text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider">Urutan Penilaian</label>
                                    <p class="text-forest font-semibold text-sm sm:text-base mt-1 font-mono">
                                        #{{ selectedKriteria.urutan }}
                                    </p>
                                </div>
                            </div>

                            <!-- Created At -->
                            <div class="text-[10px] sm:text-xs text-ink/40 text-right border-t border-line pt-3">
                                Dibuat: {{ new Date(selectedKriteria.created_at).toLocaleDateString('id-ID', {
                                    day: 'numeric',
                                    month: 'long',
                                    year: 'numeric'
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
                                v-if="selectedKriteria"
                                :href="route('admin.kriterias.edit', selectedKriteria.id)"
                                class="w-full xs:flex-1 px-4 py-2 sm:py-2.5 bg-forest text-parchment rounded-lg hover:bg-forest/90 transition-all duration-200 font-medium text-center text-sm sm:text-base order-1 xs:order-2"
                                @click="closeModal"
                            >
                                ✏️ Edit Kriteria
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