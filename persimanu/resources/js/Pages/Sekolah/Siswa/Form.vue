<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import SekolahLayout from '@/Layouts/SekolahLayout.vue';
import { GOL_OPTIONS } from '@/golongan';

const props = defineProps({
    kontingen: { type: Object, default: null },
    siswa: { type: Object, default: null },
    existingDoc: { type: Object, default: null },
    slot: { type: Number, default: null },
    jk: { type: String, default: 'L' },
});

const isEditing = computed(() => Boolean(props.siswa?.id));

const form = useForm({
    nama: props.siswa?.nama ?? '',
    nisn: props.siswa?.nisn ?? '',
    tempat_lahir: props.siswa?.tempat_lahir ?? '',
    tanggal_lahir: props.siswa?.tanggal_lahir ? String(props.siswa.tanggal_lahir).slice(0, 10) : '',
    nama_orang_tua: props.siswa?.nama_orang_tua ?? '',
    alamat: props.siswa?.alamat ?? '',
    no_telp: props.siswa?.no_telp ?? '',
    golongan_pramuka: props.siswa?.golongan_pramuka ?? '',
    jenjang_pendidikan: props.siswa?.jenjang_pendidikan ?? '',
    golongan_darah: props.siswa?.golongan_darah ?? '',
    surat_kesehatan: null,
    slot_index: props.siswa?.slot_index ?? props.slot ?? 1,
    jenis_kelamin: props.siswa?.jenis_kelamin ?? props.jk ?? 'L',
});

const onFile = (e) => { form.surat_kesehatan = e.target.files[0] || null; };
const newFileName = computed(() => form.surat_kesehatan?.name ?? null);
const isImage = (mime) => mime?.startsWith('image/');

const submit = () => {
    const hasFile = form.surat_kesehatan instanceof File;
    const opts = { preserveScroll: true, ...(hasFile ? { forceFormData: true } : {}) };
    if (isEditing.value) {
        form.put(route('sekolah.siswa.update', { kontingen: props.kontingen.id, siswa: props.siswa.id }), opts);
    } else {
        form.post(route('sekolah.siswa.store', { kontingen: props.kontingen.id }), opts);
    }
};
</script>

<template>
    <SekolahLayout :header="isEditing ? 'Edit Siswa' : 'Tambah Siswa'">
        <Head :title="isEditing ? 'Edit Siswa' : 'Tambah Siswa'" />
        <div class="max-w-3xl mx-auto px-2 sm:px-0">
            <div class="mb-6"><Link :href="route('sekolah.siswa.index', { kontingen: kontingen.id })" class="text-sm text-forest hover:underline">← Kembali ke daftar siswa</Link></div>
            <form @submit.prevent="submit" class="bg-white rounded-xl border border-line shadow-sm p-4 sm:p-6 space-y-6">
                <input type="hidden" v-model="form.slot_index" />
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><label class="block font-semibold mb-1">Nama Lengkap</label><input v-model="form.nama" type="text" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold" /><div v-if="form.errors.nama" class="text-red-600 text-sm mt-1">{{ form.errors.nama }}</div></div>
                    <div><label class="block font-semibold mb-1">NISN</label><input v-model="form.nisn" type="text" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold" placeholder="Opsional" /><div v-if="form.errors.nisn" class="text-red-600 text-sm mt-1">{{ form.errors.nisn }}</div></div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold mb-1">Jenis Kelamin</label>
                        <select v-model="form.jenis_kelamin" disabled class="w-full border border-line rounded-lg px-4 py-2 bg-gray-100 text-ink/50 cursor-not-allowed"><option value="L">Laki-laki</option><option value="P">Perempuan</option></select>
                        <p class="text-[11px] text-ink/45 mt-1">Jenis kelamin ditentukan oleh slot kuota.</p>
                        <div v-if="form.errors.jenis_kelamin" class="text-red-600 text-sm mt-1">{{ form.errors.jenis_kelamin }}</div>
                    </div>
                    <div><label class="block font-semibold mb-1">Golongan Pramuka</label><select v-model="form.golongan_pramuka" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold"><option value="">Pilih</option><option v-for="g in GOL_OPTIONS" :key="g.value" :value="g.value">{{ g.label }}</option></select><div v-if="form.errors.golongan_pramuka" class="text-red-600 text-sm mt-1">{{ form.errors.golongan_pramuka }}</div></div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><label class="block font-semibold mb-1">Tempat Lahir</label><input v-model="form.tempat_lahir" type="text" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold" placeholder="Opsional" /></div>
                    <div><label class="block font-semibold mb-1">Tanggal Lahir</label><input v-model="form.tanggal_lahir" type="date" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold" /><div v-if="form.errors.tanggal_lahir" class="text-red-600 text-sm mt-1">{{ form.errors.tanggal_lahir }}</div></div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><label class="block font-semibold mb-1">Nama Orang Tua / Wali</label><input v-model="form.nama_orang_tua" type="text" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold" placeholder="Opsional" /></div>
                    <div><label class="block font-semibold mb-1">Jenjang Pendidikan</label><select v-model="form.jenjang_pendidikan" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold"><option value="">Pilih</option><option value="SD">SD</option><option value="MI">MI</option><option value="SMP">SMP</option><option value="MTs">MTs</option><option value="SMA">SMA</option><option value="MA">MA</option><option value="SMK">SMK</option></select></div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><label class="block font-semibold mb-1">Golongan Darah</label><select v-model="form.golongan_darah" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold"><option value="">Pilih</option><option value="A">A</option><option value="B">B</option><option value="AB">AB</option><option value="O">O</option></select></div>
                    <div><label class="block font-semibold mb-1">No. Telepon</label><input v-model="form.no_telp" type="text" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold" placeholder="Opsional" /></div>
                </div>
                <div><label class="block font-semibold mb-1">Alamat</label><textarea v-model="form.alamat" rows="2" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold" placeholder="Opsional" /></div>

                <!-- ✅ LABEL BARU: Kelengkapan Persyaratan (tetap PDF, layout sama) -->
                <div class="border-t border-line pt-5">
                    <label class="block font-semibold mb-1">📋 Kelengkapan Persyaratan</label>
                    <p class="text-xs text-ink/50 mb-2">Unggah kelengkapan persyaratan dalam format PDF (atau foto JPG/PNG). Maks 2 MB. Opsional saat simpan, tapi Admin dapat meminta dokumen ini.</p>
                    <input type="file" accept=".jpg,.jpeg,.png,.pdf" @change="onFile" class="w-full text-sm text-ink/70 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-forest file:text-parchment file:font-semibold hover:file:bg-forest/90 file:cursor-pointer" />
                    <div v-if="form.errors.surat_kesehatan" class="text-red-600 text-sm mt-1">{{ form.errors.surat_kesehatan }}</div>
                    <div v-if="newFileName" class="mt-3 flex items-center gap-2 text-sm bg-green-50 border border-green-200 rounded-lg px-3 py-2"><span>🆕</span><span class="truncate">File baru: <strong>{{ newFileName }}</strong></span></div>
                    <div v-else-if="existingDoc" class="mt-3 bg-parchment/40 rounded-lg border border-line/60 p-3">
                        <p class="text-xs font-semibold text-ink/60 mb-2">File saat ini: {{ existingDoc.name }}</p>
                        <img v-if="isImage(existingDoc.mime)" :src="existingDoc.url" class="max-h-48 rounded-lg border border-line" alt="Kelengkapan persyaratan" />
                        <a v-else :href="existingDoc.url" target="_blank" class="text-forest underline text-sm">📄 Lihat PDF</a>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 pt-4 border-t border-line">
                    <button type="submit" :disabled="form.processing" class="w-full sm:w-auto px-5 py-2 bg-forest text-parchment rounded-lg font-semibold hover:bg-forest/90 disabled:opacity-50 text-sm sm:text-base">{{ isEditing ? 'Update Siswa' : 'Simpan Siswa' }}</button>
                    <Link :href="route('sekolah.siswa.index', { kontingen: kontingen.id })" class="w-full sm:w-auto text-center px-5 py-2 border border-line rounded-lg hover:bg-parchment text-sm sm:text-base">Batal</Link>
                </div>
            </form>
        </div>
    </SekolahLayout>
</template>