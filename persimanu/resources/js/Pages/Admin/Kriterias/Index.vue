<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, computed } from 'vue';
import { golLabel } from '@/golongan';

const props = defineProps({
    lombas: { type: Array, default: () => [] },
});

// ✅ Filter state
const selectedLombaId = ref('');

const filteredLombas = computed(() => {
    if (!selectedLombaId.value) return props.lombas;
    return props.lombas.filter((l) => l.id == selectedLombaId.value);
});

const filterByLomba = (e) => {
    selectedLombaId.value = e.target.value;
};

const deleteKriteria = (lombaId, komponenId) => {
    if (!confirm('Hapus komponen kriteria ini?')) return;
    router.delete(route('admin.kriterias.destroy', komponenId), {
        preserveScroll: true,
    });
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
    setTimeout(() => { selectedKriteria.value = null; }, 300);
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
            </div>

            <!-- Filter lomba -->
            <div class="mb-4 max-w-full sm:max-w-xs">
                <select @change="filterByLomba" v-model="selectedLombaId"
                    class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold text-sm sm:text-base">
                    <option value="">📋 Semua Lomba</option>
                    <option v-for="l in props.lombas" :key="l.id" :value="l.id">{{ l.nama }}</option>
                </select>
            </div>

            <!-- Grid Cards per Lomba -->
            <div v-if="filteredLombas.length === 0" class="bg-white rounded-xl border border-line shadow-sm p-8 sm:p-12 text-center text-ink/50">
                <div class="text-4xl sm:text-6xl mb-4">📋</div>
                <p class="text-base sm:text-lg font-semibold">Belum ada lomba</p>
                <p class="text-xs sm:text-sm">Buat lomba dulu di menu Lomba.</p>
            </div>

            <div v-for="lomba in filteredLombas" :key="lomba.id" class="mb-6">
                <!-- Header per lomba -->
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-display text-lg font-bold text-forest">{{ lomba.nama }}</h3>
                    <!-- ✅ FIX: lomba_id dari loop variable, bukan "lomba" yang undefined -->
                    <Link :href="route('admin.kriterias.create', { lomba_id: lomba.id })"
                        class="px-4 py-2 bg-forest text-parchment rounded-lg text-xs sm:text-sm font-semibold hover:bg-forest/90 transition">
                        + Tambah Kriteria
                    </Link>
                </div>

                <!-- Komponen kriteria untuk lomba ini -->
                <div v-if="!lomba.kriteria_komponens || lomba.kriteria_komponens.length === 0"
                    class="bg-parchment/30 rounded-xl border border-line/60 p-6 text-center text-ink/40 text-sm">
                    Belum ada komponen kriteria. Klik "+ Tambah Kriteria" untuk menambahkan.
                </div>

                <div v-else class="grid grid-cols-1 xs:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
                    <div v-for="k in lomba.kriteria_komponens" :key="k.id"
                        class="group bg-white rounded-xl border border-line shadow-sm hover:shadow-lg transition-all duration-300 hover:scale-[1.02] hover:border-gold/50 overflow-hidden">
                        <div class="p-3 sm:p-4 md:p-5">
                            <div class="flex items-start justify-between gap-2 sm:gap-3">
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-forest text-base sm:text-lg truncate group-hover:text-gold transition-colors">
                                        {{ k.nama_komponen }}
                                    </h3>
                                    <div class="flex flex-wrap items-center gap-1 sm:gap-2 mt-1.5">
                                        <!-- ✅ FIX: pakai golLabel() dari @/golongan, bukan getGolonganLabel lama -->
                                        <span class="text-[10px] sm:text-xs bg-forest/10 text-forest px-2 py-0.5 rounded-full font-medium">
                                            {{ golLabel(k.golongan) }}
                                        </span>
                                        <span class="text-[10px] sm:text-xs text-ink/50 font-mono">#{{ k.urutan }}</span>
                                    </div>
                                </div>
                                <span :class="[
                                    'px-2 sm:px-3 py-0.5 sm:py-1 rounded-full border text-[10px] sm:text-xs font-semibold whitespace-nowrap flex-shrink-0',
                                    k.is_active
                                        ? 'bg-green-100 text-green-700 border-green-300'
                                        : 'bg-gray-100 text-gray-700 border-gray-300',
                                ]">
                                    {{ k.is_active ? '✅ Aktif' : '⛔ Nonaktif' }}
                                </span>
                            </div>
                        </div>
                        <div class="px-3 sm:px-4 md:px-5 pb-3 sm:pb-4 md:pb-5 pt-2 border-t border-line/50">
                            <div class="flex gap-1.5 sm:gap-2">
                                <button @click="openModal(k)"
                                    class="flex-1 px-2 sm:px-3 py-1.5 sm:py-2 bg-forest/5 text-forest font-medium rounded-lg hover:bg-forest hover:text-parchment transition-all text-[10px] sm:text-sm whitespace-nowrap">
                                    👁️ Detail
                                </button>
                                <!-- ✅ FIX: route edit pakai {lomba} bukan {k.id} -->
                                <Link :href="route('admin.kriterias.edit', lomba.id)"
                                    class="px-2 sm:px-3 py-1.5 sm:py-2 bg-khaki text-white rounded-lg hover:opacity-90 transition-all text-[10px] sm:text-sm">
                                    ✏️
                                </Link>
                                <button @click="deleteKriteria(lomba.id, k.id)"
                                    class="px-2 sm:px-3 py-1.5 sm:py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-all text-[10px] sm:text-sm">
                                    🗑️
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Popup -->
            <Teleport to="body">
                <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4 bg-black/50 backdrop-blur-sm animate-fadeIn" @click.self="closeModal">
                    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto animate-slideUp mx-2 sm:mx-0" @click.stop>
                        <div class="sticky top-0 bg-white/95 backdrop-blur-sm border-b border-line px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between rounded-t-2xl">
                            <h3 class="font-display text-base sm:text-xl font-semibold text-forest flex items-center gap-2">
                                <span>📋</span> Detail Kriteria
                            </h3>
                            <button @click="closeModal" class="p-1.5 sm:p-2 hover:bg-parchment rounded-full transition-colors">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div v-if="selectedKriteria" class="px-4 sm:px-6 py-4 sm:py-6 space-y-3 sm:space-y-5">
                            <div class="flex items-center justify-between">
                                <span :class="[
                                    'px-3 sm:px-4 py-1 sm:py-1.5 rounded-full border text-xs sm:text-sm font-semibold',
                                    selectedKriteria.is_active ? 'bg-green-100 text-green-700 border-green-300' : 'bg-gray-100 text-gray-700 border-gray-300',
                                ]">
                                    {{ selectedKriteria.is_active ? '✅ Aktif' : '⛔ Nonaktif' }}
                                </span>
                                <span class="text-[10px] sm:text-xs text-ink/40">ID: #{{ selectedKriteria.id }}</span>
                            </div>
                            <div class="bg-parchment/30 rounded-xl p-3 sm:p-4 border border-line/50">
                                <label class="text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider">Nama Komponen</label>
                                <p class="text-forest font-semibold text-base sm:text-lg mt-1">{{ selectedKriteria.nama_komponen }}</p>
                            </div>
                            <div class="grid grid-cols-1 xs:grid-cols-2 gap-3">
                                <div class="bg-parchment/30 rounded-xl p-3 sm:p-4 border border-line/50">
                                    <label class="text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider">Golongan</label>
                                    <p class="text-forest font-semibold text-sm sm:text-base mt-1">{{ golLabel(selectedKriteria.golongan) }}</p>
                                </div>
                                <div class="bg-parchment/30 rounded-xl p-3 sm:p-4 border border-line/50">
                                    <label class="text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider">Urutan</label>
                                    <p class="text-forest font-semibold text-sm sm:text-base mt-1 font-mono">#{{ selectedKriteria.urutan }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="sticky bottom-0 bg-white/95 backdrop-blur-sm border-t border-line px-4 sm:px-6 py-3 sm:py-4 flex gap-2 rounded-b-2xl">
                            <button @click="closeModal" class="flex-1 px-4 py-2 sm:py-2.5 border border-line rounded-lg hover:bg-parchment transition-colors font-medium text-sm sm:text-base">Tutup</button>
                        </div>
                    </div>
                </div>
            </Teleport>
        </div>
    </AdminLayout>
</template>

<style scoped>
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideUp { from { opacity: 0; transform: translateY(20px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }
.animate-fadeIn { animation: fadeIn 0.3s ease-out; }
.animate-slideUp { animation: slideUp 0.3s ease-out; }
@media (min-width: 320px) { .xs\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
</style>