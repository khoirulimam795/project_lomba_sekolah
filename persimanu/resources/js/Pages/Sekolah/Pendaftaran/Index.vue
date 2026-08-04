<script setup>
import { Head, Link } from '@inertiajs/vue3';
import SekolahLayout from '@/Layouts/SekolahLayout.vue';

const props = defineProps({
    availableEvents: { type: Array, default: () => [] },
    myKontingens: { type: Array, default: () => [] },
    pangkalan: { type: Object, default: null },
});

const statusLabel = {
    draft: 'Menunggu Upload Bayar',
    menunggu_approval_pembayaran: 'Menunggu Approval Bayar',
    pembayaran_ditolak: 'Pembayaran Ditolak',
    menunggu_verifikasi_dokumen: 'Menunggu Verifikasi Dokumen',
    verifikasi_ditolak: 'Verifikasi Ditolak',
    terverifikasi: 'Terverifikasi',
};
const statusClass = {
    draft: 'bg-gray-100 text-gray-700 border-gray-300',
    menunggu_approval_pembayaran: 'bg-amber-100 text-amber-700 border-amber-300',
    pembayaran_ditolak: 'bg-red-100 text-red-700 border-red-300',
    menunggu_verifikasi_dokumen: 'bg-blue-100 text-blue-700 border-blue-300',
    verifikasi_ditolak: 'bg-red-100 text-red-700 border-red-300',
    terverifikasi: 'bg-green-100 text-green-700 border-green-300',
};
const statusAccent = {
    draft: 'bg-gray-300', menunggu_approval_pembayaran: 'bg-amber-400', pembayaran_ditolak: 'bg-red-400',
    menunggu_verifikasi_dokumen: 'bg-blue-400', verifikasi_ditolak: 'bg-red-400', terverifikasi: 'bg-green-500',
};
const nextStep = {
    draft: 'Selanjutnya: upload bukti pembayaran.',
    menunggu_approval_pembayaran: 'Menunggu admin menyetujui pembayaran.',
    pembayaran_ditolak: 'Silakan upload ulang bukti pembayaran.',
    menunggu_verifikasi_dokumen: 'Lengkapi biodata siswa & pendamping.',
    verifikasi_ditolak: 'Perbaiki dokumen yang ditolak.',
    terverifikasi: 'Selesai — siap daftar ulang di lokasi.',
};
const formatDate = (v) => (v ? String(v).slice(0, 10).split('-').reverse().join('/') : '-');

const kuotaSiswa = (k) => (Number(k.peserta_putra) || 0) + (Number(k.peserta_putri) || 0);
const kuotaPendamping = (k) => (Number(k.pendamping_putra) || 0) + (Number(k.pendamping_putri) || 0);
const pct = (filled, total) => (total > 0 ? Math.min(100, Math.round((filled / total) * 100)) : 0);
const hasCounts = (k) => k.siswas_count !== undefined && k.pendampings_count !== undefined;
const isComplete = (filled, total) => total > 0 && filled >= total;

const gateBayar = (s) => s === 'draft' || s === 'pembayaran_ditolak';
const gateBiodata = (s) => s === 'menunggu_verifikasi_dokumen' || s === 'verifikasi_ditolak';
const gateAlokasi = (s) => gateBiodata(s) || s === 'terverifikasi';
</script>

<template>
    <SekolahLayout header="Pendaftaran Kontingen">
        <Head title="Pendaftaran Kontingen" />
        <div class="relative px-2 sm:px-4 md:px-0 space-y-8">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-48 bg-[radial-gradient(60%_100%_at_50%_0%,theme(colors.forest/5%),transparent)]"></div>

            <div class="relative bg-white rounded-xl border border-line shadow-sm p-4 sm:p-6 overflow-hidden">
                <span class="pointer-events-none absolute left-0 top-0 bottom-0 w-1.5 bg-gradient-to-b from-forest to-gold"></span>
                <div class="flex items-center gap-2 mb-2 pl-2">
                    <span class="text-xl">🏫</span>
                    <h2 class="font-display text-lg sm:text-xl font-extrabold text-forest tracking-tight">{{ pangkalan?.name ?? 'Pangkalan' }}</h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs sm:text-sm text-ink/70 pl-2">
                    <div><span class="text-ink/40">Jenjang:</span> {{ pangkalan?.jenjang ?? '-' }}</div>
                    <div><span class="text-ink/40">NPSN:</span> {{ pangkalan?.npsn ?? '-' }}</div>
                    <div><span class="text-ink/40">No. Telp:</span> {{ pangkalan?.no_telp ?? '-' }}</div>
                </div>
            </div>

            <section class="relative">
                <h3 class="font-display text-lg sm:text-xl font-extrabold text-forest mb-3 tracking-tight">📋 Event yang Membuka Pendaftaran</h3>
                <div v-if="props.availableEvents.length === 0" class="bg-white rounded-xl border border-line shadow-sm p-8 text-center text-ink/50">
                    <div class="text-4xl mb-3">📭</div><p class="text-sm sm:text-base">Tidak ada event yang sedang membuka pendaftaran.</p>
                </div>
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                    <div v-for="ev in props.availableEvents" :key="ev.id"
                        class="group relative bg-white rounded-xl border border-line shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 hover:border-gold/60 p-4 sm:p-5 flex flex-col overflow-hidden">
                        <span class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-forest to-gold opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        <h4 class="font-display font-bold text-forest text-base sm:text-lg group-hover:text-gold transition-colors">{{ ev.nama }}</h4>
                        <div class="mt-2 space-y-1 text-xs sm:text-sm text-ink/60 flex-1">
                            <div>🗓️ Daftar: {{ formatDate(ev.periode_pendaftaran_mulai) }} – {{ formatDate(ev.periode_pendaftaran_selesai) }}</div>
                            <div>📍 Pelaksanaan: {{ formatDate(ev.tanggal_pelaksanaan_mulai) }} – {{ formatDate(ev.tanggal_pelaksanaan_selesai) }}</div>
                        </div>
                        <Link :href="route('sekolah.pendaftaran.create', { event_id: ev.id })"
                            class="mt-4 px-4 py-2 bg-gold text-ink font-semibold rounded-lg hover:opacity-90 active:scale-95 text-center text-sm sm:text-base transition-all">✨ Daftar</Link>
                    </div>
                </div>
            </section>

            <section class="relative">
                <h3 class="font-display text-lg sm:text-xl font-extrabold text-forest mb-3 tracking-tight">📝 Riwayat Pendaftaran Saya</h3>
                <div v-if="props.myKontingens.length === 0" class="bg-white rounded-xl border border-line shadow-sm p-8 text-center text-ink/50">
                    <div class="text-4xl mb-3">🗂️</div><p class="text-sm sm:text-base">Belum ada pendaftaran.</p>
                </div>
                <div v-else class="space-y-3">
                    <div v-for="k in props.myKontingens" :key="k.id"
                        class="group relative bg-white rounded-xl border border-line shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5 hover:border-gold/40 p-4 sm:p-5 pl-5 sm:pl-6 flex flex-col gap-4 overflow-hidden">
                        <span class="pointer-events-none absolute left-0 top-0 bottom-0 w-1.5 rounded-l-xl transition-all duration-300 group-hover:w-2" :class="statusAccent[k.status] || 'bg-gray-300'"></span>

                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                            <div class="min-w-0">
                                <div class="font-display font-bold text-forest text-base sm:text-lg tracking-tight">{{ k.event?.nama ?? '-' }}</div>
                                <div class="text-xs sm:text-sm text-ink/60 mt-0.5">{{ nextStep[k.status] }}</div>
                                <div v-if="k.status === 'pembayaran_ditolak' && k.catatan_pembayaran" class="text-xs text-red-600 mt-1">Catatan admin: {{ k.catatan_pembayaran }}</div>
                            </div>
                            <span :class="['inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-[10px] sm:text-xs font-semibold whitespace-nowrap flex-shrink-0', statusClass[k.status]]">
                                <span class="w-1.5 h-1.5 rounded-full" :class="statusAccent[k.status] || 'bg-gray-400'"></span>{{ statusLabel[k.status] }}
                            </span>
                        </div>

                        <div v-if="hasCounts(k) && (kuotaSiswa(k) > 0 || kuotaPendamping(k) > 0)" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div v-if="kuotaSiswa(k) > 0" class="rounded-lg bg-parchment/40 border border-line/60 px-3 py-2">
                                <div class="flex items-center justify-between text-[11px] sm:text-xs font-semibold mb-1">
                                    <span class="text-ink/60">🧑‍🎓 Siswa</span>
                                    <span :class="isComplete(k.siswas_count, kuotaSiswa(k)) ? 'text-green-600' : 'text-forest'">{{ k.siswas_count }} / {{ kuotaSiswa(k) }}</span>
                                </div>
                                <div class="h-1.5 rounded-full bg-line overflow-hidden"><div class="h-full rounded-full bg-gradient-to-r from-forest to-gold transition-all duration-700 ease-out" :style="{ width: pct(k.siswas_count, kuotaSiswa(k)) + '%' }"></div></div>
                            </div>
                            <div v-if="kuotaPendamping(k) > 0" class="rounded-lg bg-parchment/40 border border-line/60 px-3 py-2">
                                <div class="flex items-center justify-between text-[11px] sm:text-xs font-semibold mb-1">
                                    <span class="text-ink/60">🧑‍🏫 Pendamping</span>
                                    <span :class="isComplete(k.pendampings_count, kuotaPendamping(k)) ? 'text-green-600' : 'text-forest'">{{ k.pendampings_count }} / {{ kuotaPendamping(k) }}</span>
                                </div>
                                <div class="h-1.5 rounded-full bg-line overflow-hidden"><div class="h-full rounded-full bg-gradient-to-r from-forest to-gold transition-all duration-700 ease-out" :style="{ width: pct(k.pendampings_count, kuotaPendamping(k)) + '%' }"></div></div>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
                            <Link v-if="gateBayar(k.status)" :href="route('sekolah.pendaftaran.bayar', k.id)"
                                class="px-3 py-1.5 bg-gold text-ink rounded-lg text-[11px] sm:text-xs font-semibold hover:opacity-90 active:scale-95 whitespace-nowrap transition-all">📤 Upload Bayar</Link>
                            <Link v-if="gateBiodata(k.status)" :href="route('sekolah.siswa.index', { kontingen: k.id })"
                                class="px-3 py-1.5 bg-forest text-parchment rounded-lg text-[11px] sm:text-xs font-semibold hover:bg-forest/90 active:scale-95 whitespace-nowrap transition-all">🧑‍🎓 Biodata Siswa</Link>
                            <!-- ✅ PINTU YANG DULU HILANG -->
                            <Link v-if="gateBiodata(k.status)" :href="route('sekolah.pendamping.index', { kontingen: k.id })"
                                class="px-3 py-1.5 bg-forest text-parchment rounded-lg text-[11px] sm:text-xs font-semibold hover:bg-forest/90 active:scale-95 whitespace-nowrap transition-all">🧑‍ Biodata Pendamping</Link>
                            <Link v-if="gateAlokasi(k.status)" :href="route('sekolah.alokasi.index', { kontingen: k.id })"
                                class="px-3 py-1.5 bg-gold text-ink rounded-lg text-[11px] sm:text-xs font-semibold hover:opacity-90 active:scale-95 whitespace-nowrap transition-all">🏅 Alokasi Lomba</Link>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </SekolahLayout>
</template>