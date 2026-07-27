<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    kontingen: { type: Object, default: null },
    siswas: { type: Array, default: () => [] },
    pendampings: { type: Array, default: () => [] },
});

const statusMeta = {
    pending:  { label: 'Menunggu',  cls: 'bg-amber-100 text-amber-700 border-amber-300',  dot: 'bg-amber-500' },
    approved: { label: 'Disetujui', cls: 'bg-green-100 text-green-700 border-green-300',  dot: 'bg-green-500' },
    rejected: { label: 'Ditolak',   cls: 'bg-red-100 text-red-700 border-red-300',        dot: 'bg-red-500' },
};

const fmtTgl  = (v) => (v ? String(v).slice(0, 10).split('-').reverse().join('/') : '-');
const jkLabel = (v) => (v === 'L' ? 'Laki-laki' : v === 'P' ? 'Perempuan' : '-');
const isImage = (mime) => mime?.startsWith('image/');
const initials = (name) => (name || '?').split(' ').slice(0, 2).map((n) => n[0]).join('').toUpperCase();

// ===== Modal detail =====
const detailSiswa      = ref(null);
const detailPendamping = ref(null);
const openDetailSiswa      = (s) => { detailSiswa.value = s; };
const openDetailPendamping = (p) => { detailPendamping.value = p; };
const closeDetailSiswa     = () => { detailSiswa.value = null; };
const closeDetailPendamping= () => { detailPendamping.value = null; };

// Field detail siswa (semua kolom, dirakit biar template rapi)
const siswaFields = computed(() => {
    const s = detailSiswa.value;
    if (!s) return [];
    return [
        { l: 'NISN', v: s.nisn || '-' },
        { l: 'Jenis Kelamin', v: jkLabel(s.jenis_kelamin) },
        { l: 'Tempat, Tanggal Lahir', v: `${s.tempat_lahir || '-'}, ${fmtTgl(s.tanggal_lahir)}` },
        { l: 'Nama Orang Tua / Wali', v: s.nama_orang_tua || '-' },
        { l: 'Golongan Pramuka', v: s.golongan_pramuka || '-' },
        { l: 'Jenjang Pendidikan', v: s.jenjang_pendidikan || '-' },
        { l: 'Golongan Darah', v: s.golongan_darah || '-' },
        { l: 'No. Telepon', v: s.no_telp || '-' },
        { l: 'Alamat', v: s.alamat || '-', full: true },
    ];
});

const pendampingFields = computed(() => {
    const p = detailPendamping.value;
    if (!p) return [];
    return [
        { l: 'Jenis Kelamin', v: jkLabel(p.jenis_kelamin) },
        { l: 'Jabatan', v: p.jabatan || '-' },
        { l: 'Pekerjaan', v: p.pekerjaan || '-' },
        { l: 'Asal Instansi', v: p.asal_instansi || '-' },
        { l: 'Golongan Binaan', v: p.golongan_binaan || '-' },
        { l: 'Tempat, Tanggal Lahir', v: `${p.tempat_lahir || '-'}, ${fmtTgl(p.tanggal_lahir)}` },
        { l: 'Kota', v: p.kota || '-' },
        { l: 'Golongan Darah', v: p.golongan_darah || '-' },
        { l: 'No. Telepon', v: p.no_telp || '-' },
        { l: 'Alamat', v: p.alamat || '-', full: true },
    ];
});

// ===== Approve (langsung, dari card atau popup) =====
const approveSiswa = (s) => {
    if (!confirm(`Setujui siswa "${s.nama}"?`)) return;
    router.post(route('admin.verifikasi.siswa.approve', { siswa: s.id }), {}, {
        preserveScroll: true,
        onSuccess: () => { detailSiswa.value = null; },
    });
};
const approvePendamping = (p) => {
    if (!confirm(`Setujui pendamping "${p.nama}"?`)) return;
    router.post(route('admin.verifikasi.pendamping.approve', { pendamping: p.id }), {}, {
        preserveScroll: true,
        onSuccess: () => { detailPendamping.value = null; },
    });
};

// ===== Reject (modal catatan — bisa numpuk di atas popup detail) =====
const rejecting  = ref(null); // { tipe, item }
const rejectForm = useForm({ catatan_verifikasi: '' });

const openReject = (tipe, item) => {
    rejecting.value = { tipe, item };
    rejectForm.catatan_verifikasi = '';
};
const closeReject = () => { rejecting.value = null; rejectForm.reset(); };

const submitReject = () => {
    const { tipe, item } = rejecting.value;
    rejectForm.post(route(`admin.verifikasi.${tipe}.reject`, { [tipe]: item.id }), {
        preserveScroll: true,
        onSuccess: () => {
            rejecting.value = null;
            if (tipe === 'siswa') detailSiswa.value = null;
            else detailPendamping.value = null;
        },
    });
};

// Kunci scroll badan kalau ada modal terbuka
const anyOpen = computed(() => !!detailSiswa.value || !!detailPendamping.value || !!rejecting.value);
watch(anyOpen, (open) => { document.body.style.overflow = open ? 'hidden' : 'auto'; });
</script>

<template>
    <AdminLayout header="Detail Verifikasi">
        <Head title="Detail Verifikasi" />

        <div class="px-2 sm:px-4 md:px-0 space-y-6">
            <!-- Info kontingen -->
            <div class="relative overflow-hidden bg-white rounded-xl border border-line shadow-sm p-4 sm:p-6">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-forest via-gold to-khaki"></div>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h2 class="font-display text-lg sm:text-xl font-semibold text-forest">{{ kontingen?.team?.name }}</h2>
                        <p class="text-xs sm:text-sm text-ink/60 mt-1">{{ kontingen?.event?.nama }} • {{ kontingen?.nama_kontingen }}</p>
                    </div>
                    <Link :href="route('admin.verifikasi.index')" class="self-start px-3 py-1.5 border border-line rounded-lg text-xs sm:text-sm hover:bg-parchment transition">← Kembali</Link>
                </div>
            </div>

            <!-- ===== SISWA ===== -->
            <section>
                <h3 class="font-display text-lg sm:text-xl font-semibold text-forest mb-3">🧑🎓 Siswa ({{ siswas.length }})</h3>
                <div v-if="siswas.length === 0" class="bg-white rounded-xl border border-line shadow-sm p-6 text-center text-ink/50 text-sm">Belum ada data siswa.</div>
                <div v-else class="space-y-3">
                    <div
                        v-for="s in siswas"
                        :key="s.id"
                        @click="openDetailSiswa(s)"
                        class="group relative bg-white rounded-xl border border-line shadow-sm p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 cursor-pointer transition-all duration-200 hover:shadow-lg hover:border-gold/60 hover:-translate-y-0.5"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-forest/10 text-forest flex items-center justify-center font-display font-bold text-sm sm:text-base flex-shrink-0 group-hover:bg-forest group-hover:text-parchment transition-colors">
                                {{ initials(s.nama) }}
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-forest text-base sm:text-lg group-hover:text-gold transition-colors truncate">{{ s.nama }}</span>
                                    <span v-if="s.has_doc" class="text-[10px] px-1.5 py-0.5 rounded bg-forest/10 text-forest font-semibold flex-shrink-0" title="Ada surat sehat">📎</span>
                                </div>
                                <div class="text-xs sm:text-sm text-ink/60 mt-0.5 truncate">
                                    {{ jkLabel(s.jenis_kelamin) }}
                                    <span v-if="s.golongan_pramuka"> • {{ s.golongan_pramuka }}</span>
                                    <span v-if="s.nisn"> • NISN: {{ s.nisn }}</span>
                                </div>
                                <div v-if="s.status_verifikasi === 'rejected' && s.catatan_verifikasi" class="text-xs text-red-600 mt-1 truncate">📝 {{ s.catatan_verifikasi }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span :class="['inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-[10px] sm:text-xs font-semibold whitespace-nowrap', statusMeta[s.status_verifikasi].cls]">
                                <span :class="['w-1.5 h-1.5 rounded-full', statusMeta[s.status_verifikasi].dot]"></span>
                                {{ statusMeta[s.status_verifikasi].label }}
                            </span>
                            <button @click.stop="openDetailSiswa(s)" class="px-3 py-1 bg-forest/5 text-forest rounded-lg text-[10px] sm:text-xs hover:bg-forest hover:text-parchment transition" title="Lihat detail">👁️</button>
                            <button @click.stop="approveSiswa(s)" :disabled="s.status_verifikasi === 'approved'" class="px-3 py-1 bg-green-600 text-white rounded-lg text-[10px] sm:text-xs hover:bg-green-700 disabled:opacity-40 transition" title="Setujui">✅</button>
                            <button @click.stop="openReject('siswa', s)" class="px-3 py-1 bg-red-600 text-white rounded-lg text-[10px] sm:text-xs hover:bg-red-700 transition" title="Tolak">❌</button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ===== PENDAMPING ===== -->
            <section>
                <h3 class="font-display text-lg sm:text-xl font-semibold text-forest mb-3">🧑 Pendamping ({{ pendampings.length }})</h3>
                <div v-if="pendampings.length === 0" class="bg-white rounded-xl border border-line shadow-sm p-6 text-center text-ink/50 text-sm">Belum ada data pendamping.</div>
                <div v-else class="space-y-3">
                    <div
                        v-for="p in pendampings"
                        :key="p.id"
                        @click="openDetailPendamping(p)"
                        class="group relative bg-white rounded-xl border border-line shadow-sm p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 cursor-pointer transition-all duration-200 hover:shadow-lg hover:border-gold/60 hover:-translate-y-0.5"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-khaki/20 text-khaki flex items-center justify-center font-display font-bold text-sm sm:text-base flex-shrink-0 group-hover:bg-khaki group-hover:text-white transition-colors">
                                {{ initials(p.nama) }}
                            </div>
                            <div class="min-w-0">
                                <div class="font-semibold text-forest text-base sm:text-lg group-hover:text-gold transition-colors truncate">{{ p.nama }}</div>
                                <div class="text-xs sm:text-sm text-ink/60 mt-0.5 truncate">
                                    {{ p.jabatan || '-' }}
                                    <span v-if="p.golongan_binaan"> • Binaan: {{ p.golongan_binaan }}</span>
                                    <span v-if="p.asal_instansi"> • {{ p.asal_instansi }}</span>
                                </div>
                                <div v-if="p.status_verifikasi === 'rejected' && p.catatan_verifikasi" class="text-xs text-red-600 mt-1 truncate">📝 {{ p.catatan_verifikasi }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span :class="['inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-[10px] sm:text-xs font-semibold whitespace-nowrap', statusMeta[p.status_verifikasi].cls]">
                                <span :class="['w-1.5 h-1.5 rounded-full', statusMeta[p.status_verifikasi].dot]"></span>
                                {{ statusMeta[p.status_verifikasi].label }}
                            </span>
                            <button @click.stop="openDetailPendamping(p)" class="px-3 py-1 bg-forest/5 text-forest rounded-lg text-[10px] sm:text-xs hover:bg-forest hover:text-parchment transition" title="Lihat detail">👁️</button>
                            <button @click.stop="approvePendamping(p)" :disabled="p.status_verifikasi === 'approved'" class="px-3 py-1 bg-green-600 text-white rounded-lg text-[10px] sm:text-xs hover:bg-green-700 disabled:opacity-40 transition" title="Setujui">✅</button>
                            <button @click.stop="openReject('pendamping', p)" class="px-3 py-1 bg-red-600 text-white rounded-lg text-[10px] sm:text-xs hover:bg-red-700 transition" title="Tolak">❌</button>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- ========================================================= -->
        <!-- POPUP DETAIL SISWA                                         -->
        <!-- ========================================================= -->
        <Teleport to="body">
            <Transition name="pop">
                <div
                    v-if="detailSiswa"
                    class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4"
                    @click.self="closeDetailSiswa"
                >
                    <!-- backdrop berlapis: hitam blur + gradient forest halus (ambient, on-theme) -->
                    <div class="absolute inset-0 bg-ink/60 backdrop-blur-md"></div>
                    <div class="absolute inset-0 bg-gradient-to-b from-forest/15 via-transparent to-transparent pointer-events-none"></div>

                    <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[92vh] overflow-y-auto border-t-4 border-gold" @click.stop>
                        <!-- header sticky -->
                        <div class="sticky top-0 z-10 bg-white/95 backdrop-blur-sm border-b border-line px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-forest text-parchment flex items-center justify-center font-display font-bold text-base sm:text-lg flex-shrink-0">
                                    {{ initials(detailSiswa.nama) }}
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-display text-lg sm:text-2xl font-bold text-forest truncate">{{ detailSiswa.nama }}</h3>
                                    <p class="text-[10px] sm:text-xs text-ink/40">Detail Biodata Siswa • ID #{{ detailSiswa.id }}</p>
                                </div>
                            </div>
                            <button @click="closeDetailSiswa" class="p-1.5 sm:p-2 hover:bg-parchment hover:rotate-90 rounded-full transition-all duration-300 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <!-- body -->
                        <div class="px-4 sm:px-6 py-4 sm:py-6 space-y-4">
                            <!-- status -->
                            <div class="flex items-center justify-between gap-3">
                                <span :class="['inline-flex items-center gap-1.5 px-3 sm:px-4 py-1 sm:py-1.5 rounded-full border text-xs sm:text-sm font-semibold', statusMeta[detailSiswa.status_verifikasi].cls]">
                                    <span :class="['w-2 h-2 rounded-full', statusMeta[detailSiswa.status_verifikasi].dot]"></span>
                                    {{ statusMeta[detailSiswa.status_verifikasi].label }}
                                </span>
                            </div>
                            <div v-if="detailSiswa.status_verifikasi === 'rejected' && detailSiswa.catatan_verifikasi" class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-3 sm:px-4 py-2 text-xs sm:text-sm">
                                📝 Catatan penolakan: {{ detailSiswa.catatan_verifikasi }}
                            </div>

                            <!-- semua field -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div
                                    v-for="f in siswaFields"
                                    :key="f.l"
                                    :class="['bg-parchment/40 rounded-xl p-3 sm:p-4 border border-line/60 transition-colors hover:border-gold/40', f.full ? 'sm:col-span-2' : '']"
                                >
                                    <label class="text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider">{{ f.l }}</label>
                                    <p class="text-forest font-medium text-sm sm:text-base mt-1 break-words">{{ f.v }}</p>
                                </div>
                            </div>

                            <!-- surat kesehatan -->
                            <div class="bg-parchment/40 rounded-xl p-3 sm:p-4 border border-line/60">
                                <label class="text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider">📄 Surat Keterangan Sehat</label>

                                <div v-if="!detailSiswa.surat_kesehatan" class="mt-2 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-3 flex items-center gap-2">
                                    <span class="text-lg">⚠️</span>
                                    <span>Belum ada dokumen yang diupload operator.</span>
                                </div>

                                <div v-else class="mt-2">
                                    <div class="overflow-hidden rounded-lg border border-line bg-white">
                                        <img
                                            v-if="isImage(detailSiswa.surat_kesehatan.mime)"
                                            :src="detailSiswa.surat_kesehatan.url"
                                            class="w-full max-h-80 object-contain transition-transform duration-300 hover:scale-[1.03]"
                                            alt="Surat sehat"
                                        />
                                        <iframe v-else :src="detailSiswa.surat_kesehatan.url" class="w-full h-72" />
                                    </div>
                                    <a :href="detailSiswa.surat_kesehatan.url" target="_blank" class="inline-flex items-center gap-1 mt-2 text-sm text-forest underline hover:text-gold transition-colors">
                                        📎 Buka di tab baru ({{ detailSiswa.surat_kesehatan.name }})
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- footer aksi sticky -->
                        <div class="sticky bottom-0 z-10 bg-white/95 backdrop-blur-sm border-t border-line px-4 sm:px-6 py-3 sm:py-4 flex flex-col sm:flex-row gap-2 sm:gap-3">
                            <button @click="closeDetailSiswa" class="w-full sm:flex-1 px-4 py-2 sm:py-2.5 border border-line rounded-lg hover:bg-parchment transition font-medium text-sm sm:text-base order-3 sm:order-1">Tutup</button>
                            <button @click="openReject('siswa', detailSiswa)" class="w-full sm:flex-1 px-4 py-2 sm:py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-semibold text-sm sm:text-base order-1 sm:order-2">❌ Tolak</button>
                            <button @click="approveSiswa(detailSiswa)" :disabled="detailSiswa.status_verifikasi === 'approved'" class="w-full sm:flex-1 px-4 py-2 sm:py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-40 transition font-semibold text-sm sm:text-base order-2 sm:order-3">✅ Setujui</button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ========================================================= -->
        <!-- POPUP DETAIL PENDAMPING                                    -->
        <!-- ========================================================= -->
        <Teleport to="body">
            <Transition name="pop">
                <div
                    v-if="detailPendamping"
                    class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4"
                    @click.self="closeDetailPendamping"
                >
                    <div class="absolute inset-0 bg-ink/60 backdrop-blur-md"></div>
                    <div class="absolute inset-0 bg-gradient-to-b from-khaki/15 via-transparent to-transparent pointer-events-none"></div>

                    <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[92vh] overflow-y-auto border-t-4 border-khaki" @click.stop>
                        <div class="sticky top-0 z-10 bg-white/95 backdrop-blur-sm border-b border-line px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-khaki text-white flex items-center justify-center font-display font-bold text-base sm:text-lg flex-shrink-0">
                                    {{ initials(detailPendamping.nama) }}
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-display text-lg sm:text-2xl font-bold text-forest truncate">{{ detailPendamping.nama }}</h3>
                                    <p class="text-[10px] sm:text-xs text-ink/40">Detail Biodata Pendamping • ID #{{ detailPendamping.id }}</p>
                                </div>
                            </div>
                            <button @click="closeDetailPendamping" class="p-1.5 sm:p-2 hover:bg-parchment hover:rotate-90 rounded-full transition-all duration-300 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <div class="px-4 sm:px-6 py-4 sm:py-6 space-y-4">
                            <div class="flex items-center justify-between gap-3">
                                <span :class="['inline-flex items-center gap-1.5 px-3 sm:px-4 py-1 sm:py-1.5 rounded-full border text-xs sm:text-sm font-semibold', statusMeta[detailPendamping.status_verifikasi].cls]">
                                    <span :class="['w-2 h-2 rounded-full', statusMeta[detailPendamping.status_verifikasi].dot]"></span>
                                    {{ statusMeta[detailPendamping.status_verifikasi].label }}
                                </span>
                            </div>
                            <div v-if="detailPendamping.status_verifikasi === 'rejected' && detailPendamping.catatan_verifikasi" class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-3 sm:px-4 py-2 text-xs sm:text-sm">
                                📝 Catatan penolakan: {{ detailPendamping.catatan_verifikasi }}
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div
                                    v-for="f in pendampingFields"
                                    :key="f.l"
                                    :class="['bg-parchment/40 rounded-xl p-3 sm:p-4 border border-line/60 transition-colors hover:border-gold/40', f.full ? 'sm:col-span-2' : '']"
                                >
                                    <label class="text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider">{{ f.l }}</label>
                                    <p class="text-forest font-medium text-sm sm:text-base mt-1 break-words">{{ f.v }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="sticky bottom-0 z-10 bg-white/95 backdrop-blur-sm border-t border-line px-4 sm:px-6 py-3 sm:py-4 flex flex-col sm:flex-row gap-2 sm:gap-3">
                            <button @click="closeDetailPendamping" class="w-full sm:flex-1 px-4 py-2 sm:py-2.5 border border-line rounded-lg hover:bg-parchment transition font-medium text-sm sm:text-base order-3 sm:order-1">Tutup</button>
                            <button @click="openReject('pendamping', detailPendamping)" class="w-full sm:flex-1 px-4 py-2 sm:py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-semibold text-sm sm:text-base order-1 sm:order-2">❌ Tolak</button>
                            <button @click="approvePendamping(detailPendamping)" :disabled="detailPendamping.status_verifikasi === 'approved'" class="w-full sm:flex-1 px-4 py-2 sm:py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-40 transition font-semibold text-sm sm:text-base order-2 sm:order-3">✅ Setujui</button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ========================================================= -->
        <!-- MODAL CATATAN REJECT (z lebih tinggi — numpuk di popup)    -->
        <!-- ========================================================= -->
        <Teleport to="body">
            <Transition name="pop">
                <div
                    v-if="rejecting"
                    class="fixed inset-0 z-[60] flex items-center justify-center p-2 sm:p-4 bg-ink/40 backdrop-blur-sm"
                    @click.self="closeReject"
                >
                    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full mx-2 sm:mx-0 border-t-4 border-red-500" @click.stop>
                        <div class="px-4 sm:px-6 py-3 sm:py-4">
                            <h3 class="font-display text-base sm:text-xl font-bold text-forest">❌ Tolak {{ rejecting.tipe === 'siswa' ? 'Siswa' : 'Pendamping' }}</h3>
                            <p class="text-xs sm:text-sm text-ink/60 mt-1">{{ rejecting.item.nama }}</p>
                        </div>
                        <div class="px-4 sm:px-6 pb-4 sm:pb-6">
                            <label class="block font-semibold mb-1 text-sm">Catatan Penolakan (wajib)</label>
                            <textarea v-model="rejectForm.catatan_verifikasi" rows="3" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-400 text-sm" placeholder="Contoh: NISN tidak sesuai / surat sehat buram / dokumen kurang..." />
                            <div v-if="rejectForm.errors.catatan_verifikasi" class="text-red-600 text-sm mt-1">{{ rejectForm.errors.catatan_verifikasi }}</div>
                        </div>
                        <div class="border-t border-line px-4 sm:px-6 py-3 sm:py-4 flex flex-col sm:flex-row gap-2 sm:gap-3">
                            <button @click="closeReject" class="w-full sm:flex-1 px-4 py-2 border border-line rounded-lg hover:bg-parchment font-medium text-sm sm:text-base order-2 sm:order-1">Batal</button>
                            <button @click="submitReject" :disabled="rejectForm.processing || !rejectForm.catatan_verifikasi" class="w-full sm:flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 font-semibold text-sm sm:text-base order-1 sm:order-2">Tolak</button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AdminLayout>
</template>

<style scoped>
/* transisi popup: fade backdrop + scale/slide panel */
.pop-enter-active,
.pop-leave-active {
    transition: opacity 0.25s ease;
}
.pop-enter-active > div:not(.absolute),
.pop-leave-active > div:not(.absolute) {
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease;
}
.pop-enter-from,
.pop-leave-to {
    opacity: 0;
}
.pop-enter-from > div:not(.absolute),
.pop-leave-to > div:not(.absolute) {
    opacity: 0;
    transform: translateY(16px) scale(0.96);
}
</style>