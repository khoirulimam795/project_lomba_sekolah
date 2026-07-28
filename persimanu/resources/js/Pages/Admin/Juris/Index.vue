// File: Index.vue
<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    juris: {
        type: Array,
        default: () => [],
    },
});

const statusIcon = {
    active: '✅',
    inactive: '⛔',
};

const deleteJuri = (juri) => {
    if (!confirm(`Hapus akun juri "${juri.name}"?`)) return;
    router.delete(route('admin.juris.destroy', juri.id), {
        preserveScroll: true,
    });
};

// Modal state
const selectedJuri = ref(null);
const isModalOpen = ref(false);

const openModal = (juri) => {
    selectedJuri.value = juri;
    isModalOpen.value = true;
    document.body.style.overflow = 'hidden';
};

const closeModal = () => {
    isModalOpen.value = false;
    document.body.style.overflow = 'auto';
    setTimeout(() => {
        selectedJuri.value = null;
    }, 300);
};
</script>

<template>
    <AdminLayout header="Manajemen Juri">
        <Head title="Manajemen Juri" />

        <div class="px-2 sm:px-4 md:px-0">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h2 class="font-display text-xl sm:text-2xl font-semibold text-forest">
                        Daftar Juri
                    </h2>
                    <p class="text-xs sm:text-sm text-ink/60 mt-1">
                        Akun juri hanya dapat dibuat oleh Admin.
                    </p>
                </div>

                <Link
                    :href="route('admin.juris.create')"
                    class="self-start sm:self-auto px-4 sm:px-5 py-2 sm:py-2.5 bg-gold text-ink font-semibold rounded-lg hover:opacity-90 text-center transition-all duration-200 hover:scale-105 shadow-sm text-sm sm:text-base"
                >
                    ✨ Tambah Juri
                </Link>
            </div>

            <!-- Grid Cards -->
            <div class="grid grid-cols-1 xs:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
                <div v-if="props.juris.length === 0" class="col-span-full">
                    <div class="bg-white rounded-xl border border-line shadow-sm p-8 sm:p-12 text-center text-ink/50">
                        <div class="text-4xl sm:text-6xl mb-4">👤</div>
                        <p class="text-base sm:text-lg font-semibold">Belum ada juri</p>
                        <p class="text-xs sm:text-sm">Mulai tambahkan juri untuk event kamu!</p>
                    </div>
                </div>

                <div
                    v-for="juri in props.juris"
                    :key="juri.id"
                    class="group bg-white rounded-xl border border-line shadow-sm hover:shadow-lg transition-all duration-300 hover:scale-[1.02] hover:border-gold/50 overflow-hidden"
                >
                    <!-- Card Header -->
                    <div class="p-3 sm:p-4 md:p-5 pb-2 sm:pb-3">
                        <div class="flex items-start justify-between gap-2 sm:gap-3">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-forest text-sm sm:text-base md:text-lg truncate group-hover:text-gold transition-colors duration-300">
                                    {{ juri.name }}
                                </h3>
                                <div class="flex items-center gap-2 mt-0.5 sm:mt-1">
                                    <span class="text-[10px] sm:text-xs text-ink/50 truncate">
                                        {{ juri.email }}
                                    </span>
                                </div>
                            </div>
                            <span
                                :class="[
                                    'px-2 sm:px-3 py-0.5 sm:py-1 rounded-full border text-[10px] sm:text-xs font-semibold whitespace-nowrap flex-shrink-0 transition-all duration-300 group-hover:scale-105',
                                    juri.is_active 
                                        ? 'bg-green-100 text-green-700 border-green-300'
                                        : 'bg-gray-100 text-gray-700 border-gray-300'
                                ]"
                            >
                                <span class="hidden xs:inline">{{ statusIcon[juri.is_active ? 'active' : 'inactive'] }}</span>
                                {{ juri.is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                    </div>

                    <!-- Card Body - Quick Info -->
                    <div class="px-3 sm:px-4 md:px-5 pb-2 sm:pb-3">
                        <div class="text-[10px] sm:text-xs text-ink/60 space-y-1">
                            <div class="flex items-center gap-1 sm:gap-2">
                                <span>📱</span>
                                <span class="truncate">{{ juri.no_hp || 'No HP tidak tersedia' }}</span>
                            </div>
                            <div class="flex items-center gap-1 sm:gap-2">
                                <span>📧</span>
                                <span class="truncate">{{ juri.email }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card Actions -->
                    <div class="px-3 sm:px-4 md:px-5 pb-3 sm:pb-4 md:pb-5 pt-2 border-t border-line/50">
                        <div class="flex gap-1.5 sm:gap-2">
                            <button
                                @click="openModal(juri)"
                                class="flex-1 px-2 sm:px-3 py-1.5 sm:py-2 bg-forest/5 text-forest font-medium rounded-lg hover:bg-forest hover:text-parchment transition-all duration-200 text-[10px] sm:text-sm whitespace-nowrap"
                            >
                                <span class="hidden xs:inline">👁️ </span>Detail
                            </button>
                            <Link
                                :href="route('admin.juris.edit', juri.id)"
                                class="px-2 sm:px-3 py-1.5 sm:py-2 bg-khaki text-white rounded-lg hover:opacity-90 transition-all duration-200 text-[10px] sm:text-sm"
                            >
                                ✏️
                            </Link>
                            <button
                                @click="deleteJuri(juri)"
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
                                <span>👤</span>
                                <span class="hidden xs:inline">Detail Juri</span>
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
                        <div v-if="selectedJuri" class="px-4 sm:px-6 py-4 sm:py-6 space-y-3 sm:space-y-5">
                            <!-- Status Badge -->
                            <div class="flex items-center justify-between">
                                <span
                                    :class="[
                                        'px-3 sm:px-4 py-1 sm:py-1.5 rounded-full border text-xs sm:text-sm font-semibold',
                                        selectedJuri.is_active 
                                            ? 'bg-green-100 text-green-700 border-green-300'
                                            : 'bg-gray-100 text-gray-700 border-gray-300'
                                    ]"
                                >
                                    {{ statusIcon[selectedJuri.is_active ? 'active' : 'inactive'] }} 
                                    {{ selectedJuri.is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                                <span class="text-[10px] sm:text-xs text-ink/40">
                                    ID: #{{ selectedJuri.id }}
                                </span>
                            </div>

                            <!-- Nama Lengkap -->
                            <div class="bg-parchment/30 rounded-xl p-3 sm:p-4 border border-line/50">
                                <label class="text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider">Nama Lengkap</label>
                                <p class="text-forest font-semibold text-base sm:text-lg mt-1">
                                    {{ selectedJuri.name }}
                                </p>
                            </div>

                            <!-- Email -->
                            <div class="bg-parchment/30 rounded-xl p-3 sm:p-4 border border-line/50">
                                <label class="text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider">Email</label>
                                <p class="text-ink text-sm sm:text-base mt-1 break-all">
                                    {{ selectedJuri.email }}
                                </p>
                            </div>

                            <!-- No HP -->
                            <div class="bg-parchment/30 rounded-xl p-3 sm:p-4 border border-line/50">
                                <label class="text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider">No. HP</label>
                                <p class="text-ink text-sm sm:text-base mt-1">
                                    {{ selectedJuri.no_hp || '-' }}
                                </p>
                            </div>

                            <!-- Role/Level -->
                            <div class="bg-parchment/30 rounded-xl p-3 sm:p-4 border border-line/50">
                                <label class="text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider">Role</label>
                                <p class="text-ink text-sm sm:text-base mt-1">
                                    <span class="px-3 py-1 bg-forest/10 text-forest rounded-full text-xs font-semibold">
                                        Juri
                                    </span>
                                </p>
                            </div>

                            <!-- Created At -->
                            <div class="text-[10px] sm:text-xs text-ink/40 text-right border-t border-line pt-3">
                                Bergabung: {{ new Date(selectedJuri.created_at).toLocaleDateString('id-ID', {
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
                                v-if="selectedJuri"
                                :href="route('admin.juris.edit', selectedJuri.id)"
                                class="w-full xs:flex-1 px-4 py-2 sm:py-2.5 bg-forest text-parchment rounded-lg hover:bg-forest/90 transition-all duration-200 font-medium text-center text-sm sm:text-base order-1 xs:order-2"
                                @click="closeModal"
                            >
                                ✏️ Edit Juri
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