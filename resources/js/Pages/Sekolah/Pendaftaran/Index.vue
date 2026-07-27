<script setup>
import { Head, Link } from "@inertiajs/vue3";
import SekolahLayout from "@/Layouts/SekolahLayout.vue";

const props = defineProps({
    availableEvents: { type: Array, default: () => [] },
    myKontingens: { type: Array, default: () => [] },
    pangkalan: { type: Object, default: null },
});

const statusLabel = {
    draft: "Menunggu Upload Bayar",
    menunggu_approval_pembayaran: "Menunggu Approval Bayar",
    pembayaran_ditolak: "Pembayaran Ditolak",
    menunggu_verifikasi_dokumen: "Menunggu Verifikasi Dokumen",
    verifikasi_ditolak: "Verifikasi Ditolak",
    terverifikasi: "Terverifikasi",
};

const statusClass = {
    draft: "bg-gray-100 text-gray-700 border-gray-300",
    menunggu_approval_pembayaran:
        "bg-amber-100 text-amber-700 border-amber-300",
    pembayaran_ditolak: "bg-red-100 text-red-700 border-red-300",
    menunggu_verifikasi_dokumen: "bg-blue-100 text-blue-700 border-blue-300",
    verifikasi_ditolak: "bg-red-100 text-red-700 border-red-300",
    terverifikasi: "bg-green-100 text-green-700 border-green-300",
};

const nextStep = {
    draft: "Selanjutnya: upload bukti pembayaran.",
    menunggu_approval_pembayaran: "Menunggu admin menyetujui pembayaran.",
    pembayaran_ditolak: "Silakan upload ulang bukti pembayaran.",
    menunggu_verifikasi_dokumen: "Lengkapi biodata siswa & pendamping.",
    verifikasi_ditolak: "Perbaiki dokumen yang ditolak.",
    terverifikasi: "Selesai — siap daftar ulang di lokasi.",
};

const formatDate = (v) =>
    v ? String(v).slice(0, 10).split("-").reverse().join("/") : "-";
</script>

<template>
    <SekolahLayout header="Pendaftaran Kontingen">
        <Head title="Pendaftaran Kontingen" />

        <div class="px-2 sm:px-4 md:px-0 space-y-8">
            <!-- Info Pangkalan -->
            <div
                class="bg-white rounded-xl border border-line shadow-sm p-4 sm:p-6"
            >
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-xl">🏫</span>
                    <h2
                        class="font-display text-lg sm:text-xl font-semibold text-forest"
                    >
                        {{ pangkalan?.name ?? "Pangkalan" }}
                    </h2>
                </div>
                <div
                    class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs sm:text-sm text-ink/70"
                >
                    <div>
                        <span class="text-ink/40">Jenjang:</span>
                        {{ pangkalan?.jenjang ?? "-" }}
                    </div>
                    <div>
                        <span class="text-ink/40">NPSN:</span>
                        {{ pangkalan?.npsn ?? "-" }}
                    </div>
                    <div>
                        <span class="text-ink/40">No. Telp:</span>
                        {{ pangkalan?.no_telp ?? "-" }}
                    </div>
                </div>
            </div>

            <!-- Event Tersedia -->
            <section>
                <h3
                    class="font-display text-lg sm:text-xl font-semibold text-forest mb-3"
                >
                    📋 Event yang Membuka Pendaftaran
                </h3>

                <div
                    v-if="props.availableEvents.length === 0"
                    class="bg-white rounded-xl border border-line shadow-sm p-8 text-center text-ink/50"
                >
                    <div class="text-4xl mb-3">📭</div>
                    <p class="text-sm sm:text-base">
                        Tidak ada event yang sedang membuka pendaftaran.
                    </p>
                </div>

                <div
                    v-else
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4"
                >
                    <div
                        v-for="ev in props.availableEvents"
                        :key="ev.id"
                        class="group bg-white rounded-xl border border-line shadow-sm hover:shadow-lg transition-all duration-300 hover:scale-[1.02] hover:border-gold/50 p-4 sm:p-5 flex flex-col"
                    >
                        <h4
                            class="font-semibold text-forest text-base sm:text-lg group-hover:text-gold transition-colors"
                        >
                            {{ ev.nama }}
                        </h4>
                        <div
                            class="mt-2 space-y-1 text-xs sm:text-sm text-ink/60 flex-1"
                        >
                            <div>
                                🗓️ Daftar:
                                {{ formatDate(ev.periode_pendaftaran_mulai) }} –
                                {{ formatDate(ev.periode_pendaftaran_selesai) }}
                            </div>
                            <div>
                                📍 Pelaksanaan:
                                {{ formatDate(ev.tanggal_pelaksanaan_mulai) }} –
                                {{ formatDate(ev.tanggal_pelaksanaan_selesai) }}
                            </div>
                        </div>
                        <Link
                            :href="
                                route('sekolah.pendaftaran.create', {
                                    event_id: ev.id,
                                })
                            "
                            class="mt-4 px-4 py-2 bg-gold text-ink font-semibold rounded-lg hover:opacity-90 text-center text-sm sm:text-base transition-all"
                        >
                            ✨ Daftar
                        </Link>
                    </div>
                </div>
            </section>

            <!-- Riwayat Pendaftaran -->
            <section>
                <h3
                    class="font-display text-lg sm:text-xl font-semibold text-forest mb-3"
                >
                    📝 Riwayat Pendaftaran Saya
                </h3>

                <div
                    v-if="props.myKontingens.length === 0"
                    class="bg-white rounded-xl border border-line shadow-sm p-8 text-center text-ink/50"
                >
                    <div class="text-4xl mb-3">🗂️</div>
                    <p class="text-sm sm:text-base">Belum ada pendaftaran.</p>
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="k in props.myKontingens"
                        :key="k.id"
                        class="bg-white rounded-xl border border-line shadow-sm p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                    >
                        <div class="min-w-0">
                            <div
                                class="font-semibold text-forest text-base sm:text-lg"
                            >
                                {{ k.event?.nama ?? "-" }}
                            </div>
                            <div class="text-xs sm:text-sm text-ink/60 mt-0.5">
                                {{ nextStep[k.status] }}
                            </div>
                            <div
                                v-if="
                                    k.status === 'pembayaran_ditolak' &&
                                    k.catatan_pembayaran
                                "
                                class="text-xs text-red-600 mt-1"
                            >
                                Catatan admin: {{ k.catatan_pembayaran }}
                            </div>
                        </div>

                        <div
                            class="flex items-center gap-2 sm:gap-3 flex-shrink-0"
                        >
                            <span
                                :class="[
                                    'px-3 py-1 rounded-full border text-[10px] sm:text-xs font-semibold whitespace-nowrap',
                                    statusClass[k.status],
                                ]"
                            >
                                {{ statusLabel[k.status] }}
                            </span>

                            <!-- Tombol upload bayar untuk draft & ditolak -->
                            <Link
                                v-if="
                                    k.status === 'draft' ||
                                    k.status === 'pembayaran_ditolak'
                                "
                                :href="route('sekolah.pendaftaran.bayar', k.id)"
                                class="px-3 py-1 bg-gold text-ink rounded-lg text-[10px] sm:text-xs font-semibold hover:opacity-90 whitespace-nowrap"
                            >
                                📤 Upload Bayar
                            </Link>
                            <Link
                                v-if="
                                    k.status ===
                                        'menunggu_verifikasi_dokumen' ||
                                    k.status === 'verifikasi_ditolak'
                                "
                                :href="
                                    route('sekolah.siswa.index', {
                                        kontingen: k.id,
                                    })
                                "
                                class="px-3 py-1 bg-forest text-parchment rounded-lg text-[10px] sm:text-xs font-semibold hover:bg-forest/90 whitespace-nowrap"
                            >
                                📋 Isi Biodata
                            </Link>
                            <!-- Tombol alokasi lomba: sama gate-nya dengan isi biodata (v1) -->
                            <Link
                                v-if="
                                    k.status ===
                                        'menunggu_verifikasi_dokumen' ||
                                    k.status === 'verifikasi_ditolak' ||
                                    k.status === 'terverifikasi'
                                "
                                :href="
                                    route('sekolah.alokasi.index', {
                                        kontingen: k.id,
                                    })
                                "
                                class="px-3 py-1 bg-gold text-ink rounded-lg text-[10px] sm:text-xs font-semibold hover:opacity-90 whitespace-nowrap"
                            >
                                🏅 Alokasi Lomba
                            </Link>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </SekolahLayout>
</template>
