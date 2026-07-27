<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    kontingens: { type: Array, default: () => [] },
});

const statusLabel = {
    menunggu_verifikasi_dokumen: 'Menunggu Verifikasi',
    verifikasi_ditolak: 'Verifikasi Ditolak',
};
const statusClass = {
    menunggu_verifikasi_dokumen: 'bg-amber-100 text-amber-700 border-amber-300',
    verifikasi_ditolak: 'bg-red-100 text-red-700 border-red-300',
};
</script>

<template>
    <AdminLayout header="Verifikasi Dokumen">
        <Head title="Verifikasi Dokumen" />

        <div class="px-2 sm:px-4 md:px-0">
            <div class="mb-6">
                <h2 class="font-display text-xl sm:text-2xl font-semibold text-forest">Kontingen Siap Verifikasi</h2>
                <p class="text-xs sm:text-sm text-ink/60 mt-1">Periksa & verifikasi biodata siswa dan pendamping per item.</p>
            </div>

            <div v-if="kontingens.length === 0" class="bg-white rounded-xl border border-line shadow-sm p-8 sm:p-12 text-center text-ink/50">
                <div class="text-4xl sm:text-6xl mb-4">✅</div>
                <p class="text-base sm:text-lg font-semibold">Tidak ada kontingen menunggu verifikasi</p>
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                <div
                    v-for="k in kontingens"
                    :key="k.id"
                    class="group bg-white rounded-xl border border-line shadow-sm hover:shadow-lg transition-all duration-300 hover:scale-[1.02] hover:border-gold/50 p-4 sm:p-5 flex flex-col"
                >
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="font-semibold text-forest text-base sm:text-lg group-hover:text-gold transition-colors truncate">
                            {{ k.team?.name ?? '-' }}
                        </h3>
                        <span :class="['px-2 sm:px-3 py-0.5 sm:py-1 rounded-full border text-[10px] sm:text-xs font-semibold whitespace-nowrap', statusClass[k.status]]">
                            {{ statusLabel[k.status] }}
                        </span>
                    </div>

                    <div class="mt-2 space-y-1 text-xs sm:text-sm text-ink/60 flex-1">
                        <div>🏆 {{ k.event?.nama ?? '-' }}</div>
                        <div>📛 {{ k.nama_kontingen ?? '-' }}</div>
                        <div>🧑🎓 Siswa: <span class="font-semibold text-forest">{{ k.siswa_approved }}/{{ k.siswas_count }}</span> disetujui</div>
                        <div>🧑🏫 Pendamping: <span class="font-semibold text-forest">{{ k.pendamping_approved }}/{{ k.pendampings_count }}</span> disetujui</div>
                    </div>

                    <Link
                        :href="route('admin.verifikasi.show', k.id)"
                        class="mt-4 px-4 py-2 bg-gold text-ink font-semibold rounded-lg hover:opacity-90 text-center text-sm sm:text-base"
                    >
                        🔍 Periksa
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>