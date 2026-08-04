<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import SekolahLayout from '@/Layouts/SekolahLayout.vue';
import { ref, computed } from 'vue';
import { GOL_OPTIONS, GOL_ORDER, golLabel } from '@/golongan';

const props = defineProps({
    kontingen: { type: Object, default: null },
    alokasi: { type: Array, default: () => [] },
    lombasTersedia: { type: Array, default: () => [] },
    siswas: { type: Array, default: () => [] },
    pendampings: { type: Array, default: () => [] },
});

const MAX = 10;
const KATEGORI_LABEL = { PA: 'Putra', PI: 'Putri' };
const KATEGORI_ICON = { PA: '👦', PI: '👧' };

const statusLabel = { draft: 'Draft', siap: 'Siap', selesai: 'Selesai' };
const statusClass = {
    draft: 'bg-gray-100 text-gray-700 border-gray-300',
    siap: 'bg-green-100 text-green-700 border-green-300',
    selesai: 'bg-blue-100 text-blue-700 border-blue-300',
};

// ===== mode layar: 'list' (riwayat) | 'pilih' (grid lomba) =====
const mode = ref('list');

// ===== modal detail =====
const detailLomba = ref(null);
const openDetail = (l) => { detailLomba.value = l; };
const closeDetail = () => { detailLomba.value = null; };

// ===== modal join (create & edit pakai ini) =====
const joinLomba = ref(null);
const editingAlokasiId = ref(null);
const chosenGolongan = ref('');
const chosenKategori = ref('');
const selectedIds = ref([]);
const pendampingId = ref('');

const joinForm = useForm({
    lomba_id: '', golongan: '', kategori: '', pendamping_id: '', siswa_ids: [],
});

const joinGolonganOptions = computed(() => golonganOf(joinLomba.value));
const jkWanted = computed(() => chosenKategori.value === 'PA' ? 'L' : chosenKategori.value === 'PI' ? 'P' : null);
const matchedSiswa = computed(() =>
    props.siswas.filter((s) =>
        chosenGolongan.value && jkWanted.value &&
        s.golongan_pramuka === chosenGolongan.value &&
        s.jenis_kelamin === jkWanted.value
    )
);
const sisaSlot = computed(() => MAX - selectedIds.value.length);
const canSubmitJoin = computed(() =>
    chosenGolongan.value && chosenKategori.value && pendampingId.value &&
    selectedIds.value.length > 0 && !joinForm.processing
);

function openJoin(lomba, alokasi = null) {
    joinLomba.value = lomba;
    editingAlokasiId.value = alokasi?.id ?? null;
    chosenGolongan.value = alokasi?.golongan ?? '';
    chosenKategori.value = alokasi?.kategori ?? '';
    selectedIds.value = alokasi?.siswas ? alokasi.siswas.map((s) => s.id) : [];
    pendampingId.value = alokasi?.pendamping_id ?? '';
    joinForm.clearErrors();
}
function closeJoin() {
    joinLomba.value = null;
    editingAlokasiId.value = null;
    joinForm.clearErrors();
}
function toggleSiswa(id) {
    const i = selectedIds.value.indexOf(id);
    if (i === -1) { if (selectedIds.value.length < MAX) selectedIds.value.push(id); }
    else selectedIds.value.splice(i, 1);
}
function submitJoin() {
    joinForm.lomba_id = joinLomba.value.id;
    joinForm.golongan = chosenGolongan.value;
    joinForm.kategori = chosenKategori.value;
    joinForm.pendamping_id = pendampingId.value;
    joinForm.siswa_ids = [...selectedIds.value];
    const opts = {
        preserveState: true, preserveScroll: true,
        onSuccess: () => { closeJoin(); mode.value = 'list'; },
    };
    if (editingAlokasiId.value) {
        joinForm.put(route('sekolah.alokasi.update', { kontingen: props.kontingen.id, alokasi: editingAlokasiId.value }), opts);
    } else {
        joinForm.post(route('sekolah.alokasi.store', { kontingen: props.kontingen.id }), opts);
    }
}
function joinFromDetail() {
    const l = detailLomba.value;
    closeDetail();
    openJoin(l);
}

// ===== helpers =====
function golonganOf(l) {
    if (!l) return [];
    return [...new Set((l.kriteria_komponens || []).filter((k) => k.is_active).map((k) => k.golongan))]
        .sort((a, b) => GOL_ORDER.indexOf(a) - GOL_ORDER.indexOf(b));
}
function komponenOf(l, gol = null) {
    if (!l) return [];
    return (l.kriteria_komponens || []).filter((k) => k.is_active && (!gol || k.golongan === gol));
}
function lombaCanJoin(l) {
    return golonganOf(l).length > 0;
}
function editAlokasi(a) {
    // pakai lomba dari alokasi (sudah bawa kriteria_komponens)
    openJoin(a.lomba, a);
}
const hapus = (a) => {
    if (!confirm(`Hapus alokasi "${a.lomba?.nama}" (${golLabel(a.golongan)} · ${KATEGORI_LABEL[a.kategori] ?? '-'})?`)) return;
    router.delete(route('sekolah.alokasi.destroy', { kontingen: props.kontingen.id, alokasi: a.id }), { preserveScroll: true });
};
</script>

<template>
    <SekolahLayout header="Alokasi Lomba">
        <Head title="Alokasi Lomba" />
        <div class="relative px-2 sm:px-4 md:px-0 space-y-6">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-48 bg-[radial-gradient(60%_100%_at_50%_0%,theme(colors.forest/5%),transparent)]"></div>

            <!-- Info + nav -->
            <div class="relative bg-white rounded-xl border border-line shadow-sm p-4 sm:p-6 overflow-hidden">
                <span class="pointer-events-none absolute left-0 top-0 bottom-0 w-1.5 bg-gradient-to-b from-forest to-gold"></span>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pl-2">
                    <div class="min-w-0">
                        <h2 class="font-display text-lg sm:text-xl font-extrabold text-forest tracking-tight">{{ kontingen?.event?.nama ?? '-' }}</h2>
                        <p class="text-xs sm:text-sm text-ink/60 mt-0.5">Kontingen: {{ kontingen?.nama_kontingen ?? '-' }}</p>
                    </div>
                    <div class="flex gap-2 flex-shrink-0">
                        <Link :href="route('sekolah.pendaftaran.index')" class="px-3 py-1.5 border border-line rounded-lg text-xs sm:text-sm hover:bg-parchment transition-colors">← Pendaftaran</Link>
                        <Link :href="route('sekolah.siswa.index', { kontingen: kontingen.id })" class="px-3 py-1.5 bg-forest text-parchment rounded-lg text-xs sm:text-sm hover:bg-forest/90 transition-colors">🧑‍🎓 Biodata</Link>
                    </div>
                </div>
            </div>

            <!-- ================= LAYAR 1: RIWAYAT ALOKASI ================= -->
            <template v-if="mode === 'list'">
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-lg sm:text-xl font-extrabold text-forest tracking-tight">
                        Lomba yang Diikuti <span class="text-ink/40 text-base font-semibold">({{ alokasi.length }})</span>
                    </h3>
                    <button @click="mode = 'pilih'"
                        class="group inline-flex items-center gap-1.5 px-4 py-2 bg-gold text-ink font-semibold rounded-lg hover:opacity-90 active:scale-95 text-sm sm:text-base transition-all shadow-sm">
                        <span class="transition-transform duration-300 group-hover:rotate-90">＋</span> Tambah Alokasi
                    </button>
                </div>

                <div v-if="alokasi.length === 0" class="bg-white rounded-xl border border-line shadow-sm p-8 sm:p-12 text-center text-ink/50">
                    <div class="text-5xl mb-3">🏅</div>
                    <p class="font-semibold">Belum ada lomba yang dialokasikan.</p>
                    <p class="text-xs sm:text-sm mt-1">Tekan <strong>Tambah Alokasi</strong> untuk memilih lomba.</p>
                </div>

                <div v-else class="space-y-3">
                    <div v-for="a in alokasi" :key="a.id"
                        class="group bg-white rounded-xl border border-line shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5 hover:border-gold/40 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="min-w-0">
                            <div class="font-semibold text-forest text-base sm:text-lg group-hover:text-gold transition-colors">{{ a.lomba?.nama ?? '-' }}</div>
                            <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                                <span class="text-[10px] sm:text-xs bg-forest/10 text-forest px-2 py-0.5 rounded-full font-medium">{{ golLabel(a.golongan) }}</span>
                                <span class="text-[10px] sm:text-xs bg-gold/15 text-gold px-2 py-0.5 rounded-full font-medium">{{ KATEGORI_ICON[a.kategori] }} {{ KATEGORI_LABEL[a.kategori] ?? '-' }}</span>
                                <span class="text-[10px] sm:text-xs text-ink/50">👥 {{ a.siswas?.length ?? 0 }}/{{ MAX }} siswa</span>
                                <span class="text-[10px] sm:text-xs text-ink/50">🧑‍🏫 {{ a.pendamping?.nama ?? 'belum ditunjuk' }}</span>
                                <span v-if="a.nomor_urut_tampil" class="text-[10px] sm:text-xs text-ink/50">No. Urut <span class="font-mono font-semibold">{{ a.nomor_urut_tampil }}</span></span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                            <span :class="['px-3 py-1 rounded-full border text-[10px] sm:text-xs font-semibold whitespace-nowrap', statusClass[a.status]]">{{ statusLabel[a.status] }}</span>
                            <template v-if="a.status === 'draft' && !a.nomor_urut_tampil">
                                <button @click="editAlokasi(a)" class="px-3 py-1 bg-khaki text-white rounded-lg text-[10px] sm:text-xs hover:opacity-90 active:scale-95 transition-all">✏️</button>
                                <button @click="hapus(a)" class="px-3 py-1 bg-red-600 text-white rounded-lg text-[10px] sm:text-xs hover:bg-red-700 active:scale-95 transition-all">🗑️</button>
                            </template>
                            <span v-else class="text-[10px] sm:text-xs text-ink/40">🔒 Terkunci</span>
                        </div>
                    </div>
                </div>
            </template>

            <!-- ================= LAYAR 2: GRID PILIH LOMBA ================= -->
            <template v-else>
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-lg sm:text-xl font-extrabold text-forest tracking-tight">🏅 Pilih Lomba untuk Diikuti</h3>
                    <button @click="mode = 'list'" class="px-3 py-1.5 border border-line rounded-lg text-xs sm:text-sm hover:bg-parchment transition-colors">← Kembali</button>
                </div>
                <p class="text-xs sm:text-sm text-ink/60 -mt-3">Ketuk <strong>Detail</strong> untuk melihat komponen penilaian, atau <strong>Join Lomba</strong> untuk menyusun regu (golongan + kategori + peserta).</p>

                <div v-if="lombasTersedia.length === 0" class="bg-white rounded-xl border border-line shadow-sm p-8 sm:p-12 text-center text-ink/50">
                    <div class="text-5xl mb-3">📭</div>
                    <p class="font-semibold">Tidak ada lomba aktif untuk event ini.</p>
                </div>

                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                    <div v-for="(l, i) in lombasTersedia" :key="l.id"
                        :style="{ animationDelay: i * 50 + 'ms' }"
                        class="reveal group relative bg-white rounded-xl border border-line shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 hover:border-gold/60 p-4 sm:p-5 flex flex-col overflow-hidden">
                        <span class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-forest to-gold opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        <h4 class="font-display font-bold text-forest text-base sm:text-lg group-hover:text-gold transition-colors leading-tight">{{ l.nama }}</h4>
                        <p v-if="l.deskripsi" class="mt-1.5 text-[11px] sm:text-xs text-ink/55 line-clamp-2 flex-1">{{ l.deskripsi }}</p>
                        <div v-else class="flex-1"></div>

                        <!-- chip golongan tersedia -->
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            <span v-for="g in golonganOf(l)" :key="g"
                                class="text-[10px] sm:text-xs bg-forest/10 text-forest px-2 py-0.5 rounded-full font-medium">{{ golLabel(g) }}</span>
                            <span class="text-[10px] sm:text-xs bg-parchment text-ink/55 px-2 py-0.5 rounded-full border border-line/60">🧩 {{ komponenOf(l).length }} komponen</span>
                        </div>

                        <!-- aksi -->
                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <button @click="openDetail(l)"
                                class="px-3 py-2 bg-forest/5 text-forest font-semibold rounded-lg hover:bg-forest hover:text-parchment active:scale-95 transition-all text-xs sm:text-sm">👁 Detail</button>
                            <button @click="openJoin(l)" :disabled="!lombaCanJoin(l)"
                                :class="['px-3 py-2 rounded-lg font-semibold active:scale-95 transition-all text-xs sm:text-sm',
                                    lombaCanJoin(l) ? 'bg-gold text-ink hover:opacity-90' : 'bg-gray-100 text-ink/40 cursor-not-allowed']"
                                :title="lombaCanJoin(l) ? 'Susun regu untuk lomba ini' : 'Komponen penilaian belum diatur admin'">
                                ➕ Join
                            </button>
                        </div>
                        <p v-if="!lombaCanJoin(l)" class="mt-2 text-[10px] text-amber-600">⚠️ Komponen penilaian belum diatur admin untuk lomba ini.</p>
                    </div>
                </div>
            </template>
        </div>

        <!-- ================= MODAL DETAIL ================= -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="detailLomba" class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4 bg-black/50 backdrop-blur-sm" @click.self="closeDetail">
                    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto modal-card">
                        <div class="sticky top-0 bg-white/95 backdrop-blur-sm border-b border-line px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between rounded-t-2xl z-10">
                            <h3 class="font-display text-base sm:text-xl font-extrabold text-forest flex items-center gap-2"><span>🏅</span> Detail Lomba</h3>
                            <button @click="closeDetail" class="p-1.5 sm:p-2 hover:bg-parchment rounded-full transition-colors">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        <div class="px-4 sm:px-6 py-4 sm:py-6 space-y-4">
                            <div class="bg-parchment/30 rounded-xl p-3 sm:p-4 border border-line/50">
                                <label class="text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider">Nama Lomba</label>
                                <p class="text-forest font-bold text-base sm:text-lg mt-1">{{ detailLomba.nama }}</p>
                            </div>
                            <div class="bg-parchment/30 rounded-xl p-3 sm:p-4 border border-line/50">
                                <label class="text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider">Deskripsi</label>
                                <p class="text-ink/80 text-xs sm:text-sm leading-relaxed mt-1 whitespace-pre-wrap">{{ detailLomba.deskripsi || '-' }}</p>
                            </div>
                            <div v-for="g in golonganOf(detailLomba)" :key="g" class="rounded-xl border border-line/60 p-3 sm:p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="w-2 h-2 rounded-full bg-forest"></span>
                                    <span class="text-xs font-display font-bold uppercase tracking-wider text-forest">{{ golLabel(g) }}</span>
                                    <span class="text-[10px] text-ink/40">— komponen penilaian</span>
                                </div>
                                <div class="space-y-1.5">
                                    <div v-for="(k, ki) in komponenOf(detailLomba, g)" :key="k.id"
                                        class="flex items-center gap-2 bg-white rounded-lg border border-line/60 px-3 py-2">
                                        <span class="text-ink/40 font-mono text-xs">{{ ki + 1 }}.</span>
                                        <span class="text-xs sm:text-sm text-forest">{{ k.nama_komponen }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="sticky bottom-0 bg-white/95 backdrop-blur-sm border-t border-line px-4 sm:px-6 py-3 sm:py-4 flex gap-2 sm:gap-3 rounded-b-2xl">
                            <button @click="closeDetail" class="flex-1 px-4 py-2 sm:py-2.5 border border-line rounded-lg hover:bg-parchment transition-colors font-medium text-sm sm:text-base">Tutup</button>
                            <button @click="joinFromDetail" :disabled="!lombaCanJoin(detailLomba)"
                                :class="['flex-1 px-4 py-2 sm:py-2.5 rounded-lg font-semibold transition-all text-sm sm:text-base',
                                    lombaCanJoin(detailLomba) ? 'bg-forest text-parchment hover:bg-forest/90 active:scale-95' : 'bg-gray-100 text-ink/40 cursor-not-allowed']">
                                ➕ Join Lomba Ini
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ================= MODAL JOIN (susun regu) ================= -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="joinLomba" class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4 bg-black/50 backdrop-blur-sm" @click.self="closeJoin">
                    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[92vh] overflow-y-auto modal-card">
                        <div class="sticky top-0 bg-white/95 backdrop-blur-sm border-b border-line px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between rounded-t-2xl z-10">
                            <div class="min-w-0">
                                <h3 class="font-display text-base sm:text-xl font-extrabold text-forest truncate">{{ editingAlokasiId ? '✏️ Edit Regu' : '➕ Join Lomba' }}</h3>
                                <p class="text-[11px] sm:text-xs text-ink/55 truncate">{{ joinLomba.nama }}</p>
                            </div>
                            <button @click="closeJoin" class="p-1.5 sm:p-2 hover:bg-parchment rounded-full transition-colors flex-shrink-0">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <div class="px-4 sm:px-6 py-4 sm:py-6 space-y-5">
                            <!-- golongan + kategori -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-semibold mb-1 text-sm">Golongan Regu</label>
                                    <select v-model="chosenGolongan" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold">
                                        <option value="">Pilih golongan</option>
                                        <option v-for="g in joinGolonganOptions" :key="g" :value="g">{{ golLabel(g) }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-semibold mb-1 text-sm">Kategori Regu</label>
                                    <select v-model="chosenKategori" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold">
                                        <option value="">Pilih kategori</option>
                                        <option value="PA">👦 Putra (PA)</option>
                                        <option value="PI">👧 Putri (PI)</option>
                                    </select>
                                </div>
                            </div>
                            <p v-if="chosenGolongan && chosenKategori" class="text-[11px] text-ink/50 -mt-2">
                                Menampilkan siswa <strong>{{ golLabel(chosenGolongan) }}</strong> berjenis kelamin <strong>{{ KATEGORI_LABEL[chosenKategori] }}</strong>. Siswa tanpa golongan pramuka tidak muncul — lengkapi biodata dulu.
                            </p>

                            <!-- daftar siswa match -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block font-semibold text-sm">Centang Peserta (maks {{ MAX }})</label>
                                    <span :class="['text-xs font-semibold px-2 py-0.5 rounded-full', sisaSlot === 0 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700']">
                                        {{ selectedIds.length }}/{{ MAX }} · sisa {{ sisaSlot }}
                                    </span>
                                </div>

                                <div v-if="!chosenGolongan || !chosenKategori" class="text-sm text-ink/50 bg-parchment/40 rounded-lg p-4 text-center">
                                    Pilih golongan & kategori dulu untuk memunculkan daftar siswa.
                                </div>
                                <div v-else-if="matchedSiswa.length === 0" class="text-sm text-ink/50 bg-amber-50 border border-amber-200 rounded-lg p-4 text-center">
                                    Tidak ada siswa {{ golLabel(chosenGolongan) }} {{ KATEGORI_LABEL[chosenKategori] }} yang lolos verifikasi. Lengkapi biodata & golongan pramuka siswa dulu.
                                </div>
                                <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-72 overflow-y-auto pr-1">
                                    <label v-for="s in matchedSiswa" :key="s.id"
                                        :class="['flex items-center gap-2 p-2.5 rounded-lg border cursor-pointer transition',
                                            selectedIds.includes(s.id) ? 'border-forest bg-forest/5' : 'border-line hover:bg-parchment/40',
                                            !selectedIds.includes(s.id) && sisaSlot === 0 ? 'opacity-40 cursor-not-allowed' : '']">
                                        <input type="checkbox" :checked="selectedIds.includes(s.id)"
                                            :disabled="!selectedIds.includes(s.id) && sisaSlot === 0"
                                            @change="toggleSiswa(s.id)" class="rounded border-line text-forest focus:ring-gold" />
                                        <span class="text-sm truncate">{{ s.nama }}</span>
                                    </label>
                                </div>
                                <div v-if="joinForm.errors.siswa_ids" class="text-red-600 text-sm mt-1">{{ joinForm.errors.siswa_ids }}</div>
                            </div>

                            <!-- pendamping -->
                            <div>
                                <label class="block font-semibold mb-1 text-sm">Pendamping / Pembina Regu (1 orang)</label>
                                <select v-model="pendampingId" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold">
                                    <option value="">Pilih pendamping</option>
                                    <option v-for="p in pendampings" :key="p.id" :value="p.id">{{ p.nama }} — {{ p.jabatan || '-' }}</option>
                                </select>
                                <div v-if="joinForm.errors.pendamping_id" class="text-red-600 text-sm mt-1">{{ joinForm.errors.pendamping_id }}</div>
                            </div>

                            <!-- error global -->
                            <div v-if="joinForm.errors.golongan" class="text-red-600 text-sm">{{ joinForm.errors.golongan }}</div>
                            <div v-if="joinForm.errors.kategori" class="text-red-600 text-sm">{{ joinForm.errors.kategori }}</div>
                        </div>

                        <div class="sticky bottom-0 bg-white/95 backdrop-blur-sm border-t border-line px-4 sm:px-6 py-3 sm:py-4 flex flex-col sm:flex-row gap-2 sm:gap-3 rounded-b-2xl">
                            <button @click="closeJoin" class="w-full sm:flex-1 px-4 py-2 sm:py-2.5 border border-line rounded-lg hover:bg-parchment transition-colors font-medium text-sm sm:text-base">Batal</button>
                            <button @click="submitJoin" :disabled="!canSubmitJoin"
                                class="w-full sm:flex-1 px-4 py-2 sm:py-2.5 bg-forest text-parchment rounded-lg font-semibold hover:bg-forest/90 disabled:opacity-40 active:scale-95 transition-all text-sm sm:text-base">
                                {{ joinForm.processing ? 'Menyimpan…' : (editingAlokasiId ? 'Update Regu' : 'Simpan Regu') }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </SekolahLayout>
</template>

<style scoped>
.reveal { opacity: 0; animation: reveal 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
@keyframes reveal { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.modal-enter-active, .modal-leave-active { transition: opacity 0.25s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-card { animation: modalPop 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
@keyframes modalPop { from { opacity: 0; transform: translateY(16px) scale(0.96); } to { opacity: 1; transform: translateY(0) scale(1); } }
</style>