<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import SekolahLayout from '@/Layouts/SekolahLayout.vue';

const props = defineProps({
    kontingen: { type: Object, default: null },
    alokasi: { type: Object, default: null },
    lombas: { type: Array, default: () => [] },
    siswas: { type: Array, default: () => [] },
    pendampings: { type: Array, default: () => [] },
});

const MAX = 10;
const isEditing = computed(() => Boolean(props.alokasi?.id));

const form = useForm({
    lomba_id: props.alokasi?.lomba_id ?? '',
    golongan: props.alokasi?.golongan ?? '',
    pendamping_id: props.alokasi?.pendamping_id ?? '',
    siswa_ids: props.alokasi?.siswas ? props.alokasi.siswas.map((s) => s.id) : [],
});

const sisa = computed(() => MAX - form.siswa_ids.length);

const toggleSiswa = (id) => {
    const i = form.siswa_ids.indexOf(id);
    if (i === -1) {
        if (form.siswa_ids.length < MAX) form.siswa_ids.push(id);
    } else {
        form.siswa_ids.splice(i, 1);
    }
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('sekolah.alokasi.update', { kontingen: props.kontingen.id, alokasi: props.alokasi.id }), { preserveScroll: true });
    } else {
        form.post(route('sekolah.alokasi.store', { kontingen: props.kontingen.id }), { preserveScroll: true });
    }
};
</script>

<template>
    <SekolahLayout :header="isEditing ? 'Edit Alokasi Lomba' : 'Tambah Alokasi Lomba'">
        <Head :title="isEditing ? 'Edit Alokasi' : 'Tambah Alokasi'" />

        <div class="max-w-3xl mx-auto px-2 sm:px-0">
            <div class="mb-6">
                <Link :href="route('sekolah.alokasi.index', { kontingen: kontingen.id })" class="text-sm text-forest hover:underline">← Kembali ke alokasi</Link>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-xl border border-line shadow-sm p-4 sm:p-6 space-y-6">
                <!-- Lomba + Golongan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold mb-1">Lomba</label>
                        <select v-model="form.lomba_id" :disabled="isEditing" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold disabled:bg-gray-100">
                            <option value="">Pilih Lomba</option>
                            <option v-for="l in lombas" :key="l.id" :value="l.id">{{ l.nama }}</option>
                        </select>
                        <div v-if="form.errors.lomba_id" class="text-red-600 text-sm mt-1">{{ form.errors.lomba_id }}</div>
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Golongan Regu</label>
                        <select v-model="form.golongan" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold">
                            <option value="">Pilih Golongan</option>
                            <option value="siaga">Siaga</option>
                            <option value="penggalang">Penggalang</option>
                            <option value="penegak">Penegak</option>
                            <option value="pandega">Pandega</option>
                        </select>
                        <div v-if="form.errors.golongan" class="text-red-600 text-sm mt-1">{{ form.errors.golongan }}</div>
                    </div>
                </div>

                <!-- Pendamping (tepat 1) -->
                <div>
                    <label class="block font-semibold mb-1">Pendamping / Pembina Regu (1 orang)</label>
                    <select v-model="form.pendamping_id" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold">
                        <option value="">Pilih Pendamping</option>
                        <option v-for="p in pendampings" :key="p.id" :value="p.id">{{ p.nama }} — {{ p.jabatan || '-' }}</option>
                    </select>
                    <div v-if="form.errors.pendamping_id" class="text-red-600 text-sm mt-1">{{ form.errors.pendamping_id }}</div>
                </div>

                <!-- Siswa (max 10) -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block font-semibold">Siswa Peserta (maks {{ MAX }})</label>
                        <span :class="['text-xs sm:text-sm font-semibold px-2 py-0.5 rounded-full', sisa === 0 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700']">
                            {{ form.siswa_ids.length }}/{{ MAX }} (sisa {{ sisa }})
                        </span>
                    </div>

                    <div v-if="siswas.length === 0" class="text-sm text-ink/50 bg-parchment/40 rounded-lg p-3">
                        Belum ada siswa. Isi biodata siswa dulu.
                    </div>

                    <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-72 overflow-y-auto pr-1">
                        <label
                            v-for="s in siswas"
                            :key="s.id"
                            :class="[
                                'flex items-center gap-2 p-2.5 rounded-lg border cursor-pointer transition',
                                form.siswa_ids.includes(s.id) ? 'border-forest bg-forest/5' : 'border-line hover:bg-parchment/40',
                                !form.siswa_ids.includes(s.id) && sisa === 0 ? 'opacity-40 cursor-not-allowed' : '',
                            ]"
                        >
                            <input
                                type="checkbox"
                                :checked="form.siswa_ids.includes(s.id)"
                                :disabled="!form.siswa_ids.includes(s.id) && sisa === 0"
                                @change="toggleSiswa(s.id)"
                                class="rounded border-line text-forest focus:ring-gold"
                            />
                            <span class="text-sm truncate">{{ s.nama }}</span>
                        </label>
                    </div>
                    <div v-if="form.errors.siswa_ids" class="text-red-600 text-sm mt-1">{{ form.errors.siswa_ids }}</div>
                </div>

                <!-- Action -->
                <div class="flex flex-col sm:flex-row items-center gap-3 pt-4 border-t border-line">
                    <button type="submit" :disabled="form.processing" class="w-full sm:w-auto px-5 py-2 bg-forest text-parchment rounded-lg font-semibold hover:bg-forest/90 disabled:opacity-50 text-sm sm:text-base">
                        {{ isEditing ? 'Update Alokasi' : 'Simpan Alokasi' }}
                    </button>
                    <Link :href="route('sekolah.alokasi.index', { kontingen: kontingen.id })" class="w-full sm:w-auto text-center px-5 py-2 border border-line rounded-lg hover:bg-parchment text-sm sm:text-base">Batal</Link>
                </div>
            </form>
        </div>
    </SekolahLayout>
</template>