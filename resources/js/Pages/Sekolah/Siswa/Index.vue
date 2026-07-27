<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import SekolahLayout from "@/Layouts/SekolahLayout.vue";

const props = defineProps({
    kontingen: { type: Object, default: null },
    siswas: { type: Array, default: () => [] },
});

const statusLabel = {
    pending: "Menunggu Verifikasi",
    approved: "Disetujui",
    rejected: "Ditolak",
};
const statusClass = {
    pending: "bg-amber-100 text-amber-700 border-amber-300",
    approved: "bg-green-100 text-green-700 border-green-300",
    rejected: "bg-red-100 text-red-700 border-red-300",
};

const deleteSiswa = (s) => {
    if (!confirm(`Hapus data siswa "${s.nama}"?`)) return;
    router.delete(
        route("sekolah.siswa.destroy", {
            kontingen: props.kontingen.id,
            siswa: s.id,
        }),
        {
            preserveScroll: true,
        },
    );
};
</script>

<template>
    <SekolahLayout header="Biodata Siswa">
        <Head title="Biodata Siswa" />

        <div class="px-2 sm:px-4 md:px-0 space-y-6">
            <!-- Info kontingen + nav -->
            <div
                class="bg-white rounded-xl border border-line shadow-sm p-4 sm:p-6"
            >
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                >
                    <div>
                        <h2
                            class="font-display text-lg sm:text-xl font-semibold text-forest"
                        >
                            {{ kontingen?.event?.nama }}
                        </h2>
                        <p class="text-xs sm:text-sm text-ink/60 mt-1">
                            Kontingen: {{ kontingen?.nama_kontingen }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <Link
                            :href="route('sekolah.pendaftaran.index')"
                            class="px-3 py-1.5 border border-line rounded-lg text-xs sm:text-sm hover:bg-parchment"
                        >
                            ← Pendaftaran
                        </Link>
                        <Link
                            :href="
                                route('sekolah.pendamping.index', {
                                    kontingen: kontingen.id,
                                })
                            "
                            class="px-3 py-1.5 bg-khaki text-white rounded-lg text-xs sm:text-sm hover:opacity-90"
                        >
                            Pendamping
                        </Link>
                        <!-- <Link :href="route('sekolah.pendamping.index', { kontingen: kontingen.id })" class="px-3 py-1.5 bg-khaki text-white rounded-lg text-xs sm:text-sm">Pendamping</Link> -->
                    </div>
                </div>
            </div>

            <!-- Header + tombol tambah -->
            <div class="flex items-center justify-between">
                <h3
                    class="font-display text-lg sm:text-xl font-semibold text-forest"
                >
                    Daftar Siswa ({{ siswas.length }})
                </h3>
                <Link
                    :href="
                        route('sekolah.siswa.create', {
                            kontingen: kontingen.id,
                        })
                    "
                    class="px-4 py-2 bg-gold text-ink font-semibold rounded-lg hover:opacity-90 text-sm sm:text-base"
                >
                    ✨ Tambah Siswa
                </Link>
            </div>

            <!-- List siswa -->
            <div
                v-if="siswas.length === 0"
                class="bg-white rounded-xl border border-line shadow-sm p-8 text-center text-ink/50"
            >
                <div class="text-4xl mb-3">🧑‍🎓</div>
                <p>Belum ada data siswa.</p>
            </div>

            <div v-else class="space-y-3">
                <div
                    v-for="s in siswas"
                    :key="s.id"
                    class="bg-white rounded-xl border border-line shadow-sm p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                >
                    <div class="min-w-0">
                        <div
                            class="font-semibold text-forest text-base sm:text-lg"
                        >
                            {{ s.nama }}
                        </div>
                        <div
                            class="text-xs sm:text-sm text-ink/60 mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1"
                        >
                            <span>{{
                                s.jenis_kelamin === "L"
                                    ? "Laki-laki"
                                    : "Perempuan"
                            }}</span>
                            <span v-if="s.golongan_pramuka"
                                >• {{ s.golongan_pramuka }}</span
                            >
                            <span v-if="s.nisn">• NISN: {{ s.nisn }}</span>
                            <span
                                v-if="s.doc_count > 0"
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-forest/10 text-forest text-[10px] font-semibold"
                                title="Surat sehat sudah diupload"
                            >
                                📎 Surat sehat
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-[10px] font-semibold"
                                title="Surat sehat belum diupload"
                            >
                                ⚠️ Belum ada surat sehat
                            </span>
                        </div>
                        <div
                            v-if="
                                s.status_verifikasi === 'rejected' &&
                                s.catatan_verifikasi
                            "
                            class="text-xs text-red-600 mt-1"
                        >
                            Catatan admin: {{ s.catatan_verifikasi }}
                        </div>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                        <span
                            :class="[
                                'px-3 py-1 rounded-full border text-[10px] sm:text-xs font-semibold whitespace-nowrap',
                                statusClass[s.status_verifikasi],
                            ]"
                        >
                            {{ statusLabel[s.status_verifikasi] }}
                        </span>

                        <template v-if="s.status_verifikasi !== 'approved'">
                            <Link
                                :href="
                                    route('sekolah.siswa.edit', {
                                        kontingen: kontingen.id,
                                        siswa: s.id,
                                    })
                                "
                                class="px-3 py-1 bg-khaki text-white rounded-lg text-[10px] sm:text-xs hover:opacity-90"
                            >
                                ✏️
                            </Link>
                            <button
                                @click="deleteSiswa(s)"
                                class="px-3 py-1 bg-red-600 text-white rounded-lg text-[10px] sm:text-xs hover:bg-red-700"
                            >
                                🗑️
                            </button>
                        </template>
                        <span v-else class="text-[10px] sm:text-xs text-ink/40"
                            >🔒 Terkunci</span
                        >
                    </div>
                </div>
            </div>
        </div>
    </SekolahLayout>
</template>
