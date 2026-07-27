<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import SekolahLayout from '@/Layouts/SekolahLayout.vue';

const props = defineProps({
    kontingen: { type: Object, default: null },
    pendamping: { type: Object, default: null },
});

const isEditing = computed(() => Boolean(props.pendamping?.id));

const form = useForm({
    nama: props.pendamping?.nama ?? '',
    jenis_kelamin: props.pendamping?.jenis_kelamin ?? '',
    jabatan: props.pendamping?.jabatan ?? '',
    pekerjaan: props.pendamping?.pekerjaan ?? '',
    asal_instansi: props.pendamping?.asal_instansi ?? '',
    golongan_binaan: props.pendamping?.golongan_binaan ?? '',
    tempat_lahir: props.pendamping?.tempat_lahir ?? '',
    tanggal_lahir: props.pendamping?.tanggal_lahir ? String(props.pendamping.tanggal_lahir).slice(0, 10) : '',
    alamat: props.pendamping?.alamat ?? '',
    no_telp: props.pendamping?.no_telp ?? '',
    kota: props.pendamping?.kota ?? '',
    golongan_darah: props.pendamping?.golongan_darah ?? '',
});

const submit = () => {
    if (isEditing.value) {
        form.put(route('sekolah.pendamping.update', { kontingen: props.kontingen.id, pendamping: props.pendamping.id }), { preserveScroll: true });
    } else {
        form.post(route('sekolah.pendamping.store', { kontingen: props.kontingen.id }), { preserveScroll: true });
    }
};
</script>

<template>
    <SekolahLayout :header="isEditing ? 'Edit Pendamping' : 'Tambah Pendamping'">
        <Head :title="isEditing ? 'Edit Pendamping' : 'Tambah Pendamping'" />

        <div class="max-w-3xl mx-auto px-2 sm:px-0">
            <div class="mb-6">
                <Link :href="route('sekolah.pendamping.index', { kontingen: kontingen.id })" class="text-sm text-forest hover:underline">
                    ← Kembali ke daftar pendamping
                </Link>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-xl border border-line shadow-sm p-4 sm:p-6 space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold mb-1">Nama Lengkap</label>
                        <input v-model="form.nama" type="text" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold" />
                        <div v-if="form.errors.nama" class="text-red-600 text-sm mt-1">{{ form.errors.nama }}</div>
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Jenis Kelamin</label>
                        <select v-model="form.jenis_kelamin" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold">
                            <option value="">Pilih</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                        <div v-if="form.errors.jenis_kelamin" class="text-red-600 text-sm mt-1">{{ form.errors.jenis_kelamin }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold mb-1">Jabatan</label>
                        <input v-model="form.jabatan" type="text" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold" placeholder="Ketua Kontingen / Pembina / Official" />
                        <div v-if="form.errors.jabatan" class="text-red-600 text-sm mt-1">{{ form.errors.jabatan }}</div>
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Pekerjaan</label>
                        <input v-model="form.pekerjaan" type="text" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold" placeholder="Opsional" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold mb-1">Asal Instansi</label>
                        <input v-model="form.asal_instansi" type="text" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold" placeholder="Opsional (jika beda dari pangkalan)" />
                        <div v-if="form.errors.asal_instansi" class="text-red-600 text-sm mt-1">{{ form.errors.asal_instansi }}</div>
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Golongan Binaan</label>
                        <select v-model="form.golongan_binaan" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold">
                            <option value="">Pilih (untuk pembina)</option>
                            <option value="siaga">Siaga</option>
                            <option value="penggalang">Penggalang</option>
                            <option value="penegak">Penegak</option>
                            <option value="pandega">Pandega</option>
                        </select>
                        <div v-if="form.errors.golongan_binaan" class="text-red-600 text-sm mt-1">{{ form.errors.golongan_binaan }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold mb-1">Tempat Lahir</label>
                        <input v-model="form.tempat_lahir" type="text" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold" placeholder="Opsional" />
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Tanggal Lahir</label>
                        <input v-model="form.tanggal_lahir" type="date" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold" />
                        <div v-if="form.errors.tanggal_lahir" class="text-red-600 text-sm mt-1">{{ form.errors.tanggal_lahir }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold mb-1">Kota</label>
                        <input v-model="form.kota" type="text" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold" placeholder="Opsional" />
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Golongan Darah</label>
                        <select v-model="form.golongan_darah" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold">
                            <option value="">Pilih</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="AB">AB</option>
                            <option value="O">O</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Alamat</label>
                    <textarea v-model="form.alamat" rows="2" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold" placeholder="Opsional" />
                </div>

                <div>
                    <label class="block font-semibold mb-1">No. Telepon</label>
                    <input v-model="form.no_telp" type="text" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold" placeholder="Opsional" />
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 pt-4 border-t border-line">
                    <button type="submit" :disabled="form.processing" class="w-full sm:w-auto px-5 py-2 bg-forest text-parchment rounded-lg font-semibold hover:bg-forest/90 disabled:opacity-50 text-sm sm:text-base">
                        {{ isEditing ? 'Update Pendamping' : 'Simpan Pendamping' }}
                    </button>
                    <Link :href="route('sekolah.pendamping.index', { kontingen: kontingen.id })" class="w-full sm:w-auto text-center px-5 py-2 border border-line rounded-lg hover:bg-parchment text-sm sm:text-base">
                        Batal
                    </Link>
                </div>
            </form>
        </div>
    </SekolahLayout>
</template>