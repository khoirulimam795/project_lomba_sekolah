<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';
import { golLabel } from '@/golongan';

const props = defineProps({
    lombas: { type: Array, default: () => [] },
    events: { type: Array, default: () => [] },
});

const statusLabel = { draft: 'Draft', aktif: 'Aktif', selesai: 'Selesai' };
const statusClass = {
    draft: 'bg-gray-100 text-gray-700 border-gray-300',
    aktif: 'bg-green-100 text-green-700 border-green-300',
    selesai: 'bg-blue-100 text-blue-700 border-blue-300',
};
const statusIcon = { draft: '📝', aktif: '✅', selesai: '🏁' };
const kategoriLabel = { PA: 'Putra', PI: 'Putri' };

const filterByEvent = (e) => {
    router.get(route('admin.lombas.index'), { event_id: e.target.value || undefined }, { preserveState: true });
};
const deleteLomba = (lomba) => {
    if (!confirm(`Hapus lomba "${lomba.nama}"?`)) return;
    router.delete(route('admin.lombas.destroy', lomba.id), { preserveScroll: true });
};
const duplikatLomba = (lomba) => {
    router.get(route('admin.lombas.duplicate', lomba.id));
};

const selectedLomba = ref(null);
const isModalOpen = ref(false);
const openModal = (lomba) => { selectedLomba.value = lomba; isModalOpen.value = true; document.body.style.overflow = 'hidden'; };
const closeModal = () => { isModalOpen.value = false; document.body.style.overflow = 'auto'; setTimeout(() => (selectedLomba.value = null), 300); };

const komponenOf = (lomba) => lomba.kriteria_komponens ?? [];
const truncateText = (t, n = 70) => (!t ? '-' : t.length > n ? t.substring(0, n) + '…' : t);
</script>

<template>
    <AdminLayout header="Manajemen Lomba">
        <Head title="Manajemen Lomba" />
        <div class="px-2 sm:px-4 md:px-0">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h2 class="font-display text-xl sm:text-2xl font-semibold text-forest">Daftar Lomba</h2>
                    <p class="text-xs sm:text-sm text-ink/60 mt-1">Kelola lomba per event — golongan, kategori & komponen penilaian menyatu di sini.</p>
                </div>
                <Link :href="route('admin.lombas.create')"
                    class="self-start sm:self-auto px-4 sm:px-5 py-2 sm:py-2.5 bg-gold text-ink font-semibold rounded-lg hover:opacity-90 text-center transition-all duration-200 hover:scale-105 shadow-sm text-sm sm:text-base">
                    ✨ Tambah Lomba
                </Link>
            </div>

            <div class="mb-4 max-w-full sm:max-w-xs">
                <select @change="filterByEvent" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold text-sm sm:text-base">
                    <option value="">📋 Semua Event</option>
                    <option v-for="ev in events" :key="ev.id" :value="ev.id">{{ ev.nama }}</option>
                </select>
            </div>

            <div class="grid grid-cols-1 xs:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
                <div v-if="lombas.length === 0" class="col-span-full">
                    <div class="bg-white rounded-xl border border-line shadow-sm p-8 sm:p-12 text-center text-ink/50">
                        <div class="text-4xl sm:text-6xl mb-4">🏆</div>
                        <p class="text-base sm:text-lg font-semibold">Belum ada lomba</p>
                        <p class="text-xs sm:text-sm">Mulai tambahkan lomba untuk event kamu!</p>
                    </div>
                </div>

                <div v-for="lomba in lombas" :key="lomba.id"
                    class="group bg-white rounded-xl border border-line shadow-sm hover:shadow-lg transition-all duration-300 hover:scale-[1.02] hover:border-gold/50 overflow-hidden flex flex-col">
                    <div class="p-3 sm:p-4 md:p-5 pb-2 sm:pb-3">
                        <div class="flex items-start justify-between gap-2 sm:gap-3">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-forest text-sm sm:text-base md:text-lg truncate group-hover:text-gold transition-colors duration-300">{{ lomba.nama }}</h3>
                                <div class="text-[10px] sm:text-xs text-ink/50 truncate mt-0.5">{{ lomba.event?.nama ?? '-' }}</div>
                            </div>
                            <span :class="['px-2 sm:px-3 py-0.5 sm:py-1 rounded-full border text-[10px] sm:text-xs font-semibold whitespace-nowrap flex-shrink-0', statusClass[lomba.status]]">
                                {{ statusIcon[lomba.status] }} {{ statusLabel[lomba.status] }}
                            </span>
                        </div>
                        <!-- badge golongan + kategori + jumlah komponen -->
                        <div class="flex flex-wrap items-center gap-1.5 mt-2">
                            <span v-if="lomba.golongan" class="text-[10px] sm:text-xs bg-forest/10 text-forest px-2 py-0.5 rounded-full font-medium">{{ golLabel(lomba.golongan) }}</span>
                            <span v-if="lomba.kategori" class="text-[10px] sm:text-xs bg-gold/15 text-gold px-2 py-0.5 rounded-full font-medium">{{ kategoriLabel[lomba.kategori] }}</span>
                            <span class="text-[10px] sm:text-xs bg-parchment text-ink/60 px-2 py-0.5 rounded-full border border-line/60">🧩 {{ komponenOf(lomba).length }} komponen</span>
                        </div>
                    </div>

                    <div class="px-3 sm:px-4 md:px-5 pb-2 sm:pb-3 flex-1">
                        <div class="text-[10px] sm:text-xs text-ink/60 flex items-start gap-1 sm:gap-2">
                            <span class="mt-0.5">📝</span>
                            <span class="line-clamp-2 break-words">{{ truncateText(lomba.deskripsi) }}</span>
                        </div>
                    </div>

                    <div class="px-3 sm:px-4 md:px-5 pb-3 sm:pb-4 md:pb-5 pt-2 border-t border-line/50">
                        <div class="grid grid-cols-4 gap-1.5 sm:gap-2">
                            <button @click="openModal(lomba)" class="px-2 py-1.5 sm:py-2 bg-forest/5 text-forest font-medium rounded-lg hover:bg-forest hover:text-parchment transition-all duration-200 text-[10px] sm:text-sm" title="Detail">👁️</button>
                            <Link :href="route('admin.lombas.edit', lomba.id)" class="px-2 py-1.5 sm:py-2 bg-khaki text-white rounded-lg hover:opacity-90 transition-all duration-200 text-[10px] sm:text-sm text-center" title="Edit">✏️</Link>
                            <button @click="duplikatLomba(lomba)" class="px-2 py-1.5 sm:py-2 bg-gold/15 text-gold font-semibold rounded-lg hover:bg-gold hover:text-ink transition-all duration-200 text-[10px] sm:text-sm" title="Duplikat">⧉</button>
                            <button @click="deleteLomba(lomba)" class="px-2 py-1.5 sm:py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-all duration-200 text-[10px] sm:text-sm" title="Hapus">🗑️</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Detail -->
            <Teleport to="body">
                <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4 bg-black/50 backdrop-blur-sm animate-fadeIn" @click.self="closeModal">
                    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto animate-slideUp mx-2 sm:mx-0" @click.stop>
                        <div class="sticky top-0 bg-white/95 backdrop-blur-sm border-b border-line px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between rounded-t-2xl">
                            <h3 class="font-display text-base sm:text-xl font-semibold text-forest flex items-center gap-2"><span>🏆</span> Detail Lomba</h3>
                            <button @click="closeModal" class="p-1.5 sm:p-2 hover:bg-parchment rounded-full transition-colors">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        <div v-if="selectedLomba" class="px-4 sm:px-6 py-4 sm:py-6 space-y-3 sm:space-y-5">
                            <div class="flex flex-wrap items-center gap-2">
                                <span :class="['px-3 py-1 rounded-full border text-xs sm:text-sm font-semibold', statusClass[selectedLomba.status]]">{{ statusIcon[selectedLomba.status] }} {{ statusLabel[selectedLomba.status] }}</span>
                                <span v-if="selectedLomba.golongan" class="px-3 py-1 rounded-full border border-forest/30 bg-forest/10 text-forest text-xs sm:text-sm font-semibold">{{ golLabel(selectedLomba.golongan) }}</span>
                                <span v-if="selectedLomba.kategori" class="px-3 py-1 rounded-full border border-gold/40 bg-gold/15 text-gold text-xs sm:text-sm font-semibold">{{ kategoriLabel[selectedLomba.kategori] }}</span>
                            </div>
                            <div class="bg-parchment/30 rounded-xl p-3 sm:p-4 border border-line/50">
                                <label class="text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider">Nama Lomba</label>
                                <p class="text-forest font-semibold text-base sm:text-lg mt-1">{{ selectedLomba.nama }}</p>
                                <p class="text-xs text-ink/50 mt-1">{{ selectedLomba.event?.nama ?? '-' }}</p>
                            </div>
                            <div class="bg-parchment/30 rounded-xl p-3 sm:p-4 border border-line/50">
                                <label class="text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider">Deskripsi</label>
                                <p class="text-ink/90 text-xs sm:text-sm leading-relaxed mt-1 whitespace-pre-wrap break-words">{{ selectedLomba.deskripsi || '-' }}</p>
                            </div>
                            <div class="bg-parchment/30 rounded-xl p-3 sm:p-4 border border-line/50">
                                <label class="text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider">Komponen Penilaian ({{ komponenOf(selectedLomba).length }})</label>
                                <div v-if="komponenOf(selectedLomba).length" class="mt-2 space-y-1.5">
                                    <div v-for="(k, i) in komponenOf(selectedLomba)" :key="k.id" class="flex items-center justify-between gap-2 bg-white rounded-lg border border-line/60 px-3 py-2">
                                        <span class="text-xs sm:text-sm text-forest"><span class="text-ink/40 font-mono mr-1">{{ i + 1 }}.</span>{{ k.nama_komponen }}</span>
                                        <span :class="['text-[10px] px-1.5 py-0.5 rounded-full', k.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500']">{{ k.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                    </div>
                                </div>
                                <p v-else class="text-xs text-ink/40 mt-1">Belum ada komponen penilaian.</p>
                            </div>
                        </div>
                        <div class="sticky bottom-0 bg-white/95 backdrop-blur-sm border-t border-line px-4 sm:px-6 py-3 sm:py-4 flex flex-col xs:flex-row gap-2 xs:gap-3 rounded-b-2xl">
                            <button @click="closeModal" class="w-full xs:flex-1 px-4 py-2 sm:py-2.5 border border-line rounded-lg hover:bg-parchment transition-colors font-medium text-sm sm:text-base order-2 xs:order-1">Tutup</button>
                            <Link v-if="selectedLomba" :href="route('admin.lombas.edit', selectedLomba.id)" @click="closeModal"
                                class="w-full xs:flex-1 px-4 py-2 sm:py-2.5 bg-forest text-parchment rounded-lg hover:bg-forest/90 transition-all font-medium text-center text-sm sm:text-base order-1 xs:order-2">✏️ Edit Lomba</Link>
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
@media (max-width: 480px) { .grid { grid-template-columns: 1fr; } }
</style>