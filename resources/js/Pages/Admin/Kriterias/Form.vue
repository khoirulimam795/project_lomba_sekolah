<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    kriteria: { type: Object, default: null },
    lombas: { type: Array, default: () => [] },
});

const isEditing = computed(() => Boolean(props.kriteria?.id));

const form = useForm({
    lomba_id: props.kriteria?.lomba_id ?? '',
    golongan: props.kriteria?.golongan ?? '',
    nama_komponen: props.kriteria?.nama_komponen ?? '',
    urutan: props.kriteria?.urutan ?? 1,
    is_active: props.kriteria?.is_active ?? true,
});

const submit = () => {
    if (isEditing.value) {
        form.put(route('admin.kriterias.update', props.kriteria.id), { preserveScroll: true });
    } else {
        form.post(route('admin.kriterias.store'), { preserveScroll: true });
    }
};
</script>

<template>
    <AdminLayout :header="isEditing ? 'Edit Kriteria' : 'Tambah Kriteria'">
        <Head :title="isEditing ? 'Edit Kriteria' : 'Tambah Kriteria'" />

        <div class="max-w-3xl">
            <div class="mb-6">
                <Link :href="route('admin.kriterias.index')" class="text-sm text-forest hover:underline">
                    ← Kembali ke daftar kriteria
                </Link>
            </div>

            <form
                @submit.prevent="submit"
                class="bg-white rounded-xl border border-line shadow-sm p-6 space-y-6"
            >
                <!-- Lomba -->
                <div>
                    <label class="block font-semibold mb-1">Lomba</label>
                    <select
                        v-model="form.lomba_id"
                        class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold"
                    >
                        <option value="">Pilih Lomba</option>
                        <option v-for="l in props.lombas" :key="l.id" :value="l.id">{{ l.nama }}</option>
                    </select>
                    <div v-if="form.errors.lomba_id" class="text-red-600 text-sm mt-1">
                        {{ form.errors.lomba_id }}
                    </div>
                </div>

                <!-- Golongan (Pramuka) -->
                <div>
                    <label class="block font-semibold mb-1">Golongan</label>
                    <select
                        v-model="form.golongan"
                        class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold"
                    >
                        <option value="">Pilih Golongan</option>
                        <option value="siaga">Siaga</option>
                        <option value="penggalang">Penggalang</option>
                        <option value="penegak">Penegak</option>
                        <option value="pandega">Pandega</option>
                    </select>
                    <div v-if="form.errors.golongan" class="text-red-600 text-sm mt-1">
                        {{ form.errors.golongan }}
                    </div>
                </div>

                <!-- Nama Komponen -->
                <div>
                    <label class="block font-semibold mb-1">Nama Komponen</label>
                    <textarea
                        v-model="form.nama_komponen"
                        type="text"
                        class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold"
                        placeholder="Contoh: Kekompakan, Teknik, Ketepatan waktu"
                    />
                    <div v-if="form.errors.nama_komponen" class="text-red-600 text-sm mt-1">
                        {{ form.errors.nama_komponen }}
                    </div>
                </div>

                <!-- Urutan -->
                <div>
                    <label class="block font-semibold mb-1">Urutan</label>
                    <input
                        v-model.number="form.urutan"
                        type="number"
                        min="1"
                        class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold"
                    />
                    <div v-if="form.errors.urutan" class="text-red-600 text-sm mt-1">
                        {{ form.errors.urutan }}
                    </div>
                </div>

                <!-- Aktif -->
                <div>
                    <label class="flex items-center gap-2">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            class="rounded border-line text-forest focus:ring-gold"
                        />
                        <span class="font-semibold">Aktif</span>
                    </label>
                </div>

                <!-- Action -->
                <div class="flex items-center gap-3 pt-4 border-t border-line">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-5 py-2 bg-forest text-parchment rounded-lg font-semibold hover:bg-forest/90 disabled:opacity-50"
                    >
                        {{ isEditing ? 'Update Kriteria' : 'Simpan Kriteria' }}
                    </button>
                    <Link
                        :href="route('admin.kriterias.index')"
                        class="px-5 py-2 border border-line rounded-lg hover:bg-parchment"
                    >
                        Batal
                    </Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>