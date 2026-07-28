<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { GOL_OPTIONS } from '@/golongan';

const props = defineProps({
    lomba: { type: Object, required: true },
    golonganOptions: { type: Array, default: () => [] },
    existingKomponens: { type: Array, default: () => [] },
});

const isEditing = computed(() => props.existingKomponens.length > 0);

// ✅ Inisialisasi dari data existing atau 1 baris kosong
const initialKomponens = props.existingKomponens.length
    ? props.existingKomponens.map((k) => ({ ...k }))
    : [{ id: null, nama_komponen: '', golongan: '', urutan: 1, is_active: true }];

const form = useForm({
    lomba_id: props.lomba.id,
    komponens: [...initialKomponens],
});

const addKomponen = () => {
    if (form.komponens.length < 5) {
        form.komponens.push({
            id: null,
            nama_komponen: '',
            golongan: '',
            urutan: form.komponens.length + 1,
            is_active: true,
        });
    }
};

const removeKomponen = (index) => {
    if (form.komponens.length > 1) {
        form.komponens.splice(index, 1);
        // Re-number urutan setelah hapus
        form.komponens.forEach((k, i) => { k.urutan = i + 1; });
    }
};

const canAdd = computed(() => form.komponens.length < 5);
const canRemove = computed(() => form.komponens.length > 1);

const submit = () => {
    if (isEditing.value) {
        form.put(route('admin.kriterias.update', props.lomba.id), { preserveScroll: true });
    } else {
        form.post(route('admin.kriterias.store'), { preserveScroll: true });
    }
};
</script>

<template>
    <AdminLayout :header="isEditing ? `Edit Kriteria: ${lomba.nama}` : `Tambah Kriteria: ${lomba.nama}`">
        <Head :title="isEditing ? 'Edit Kriteria' : 'Tambah Kriteria'" />
        <div class="max-w-3xl mx-auto px-2 sm:px-0">
            <div class="mb-6 flex items-center justify-between">
                <Link :href="route('admin.kriterias.index')" class="text-sm text-forest hover:underline">
                    ← Kembali ke daftar kriteria
                </Link>
                <span class="text-xs font-semibold text-ink/50 bg-parchment px-3 py-1 rounded-full border border-line">
                    🏅 {{ lomba.nama }}
                </span>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-xl border border-line shadow-sm p-4 sm:p-6 space-y-6">

                <!-- ✅ KOMPONEN DINAMIS (repeatable field, max 5) -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="block font-semibold text-lg text-forest">Komponen Penilaian</label>
                        <span class="text-xs text-ink/50">{{ form.komponens.length }} / 5 komponen</span>
                    </div>
                    <p class="text-xs text-ink/50 mb-3">Tambahkan 1–5 komponen penilaian untuk lomba <strong>{{ lomba.nama }}</strong>. Urutan menentukan urutan tampil saat juri menilai.</p>

                    <div v-if="form.errors.komponens" class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-2 text-sm mb-3">
                        {{ form.errors.komponens }}
                    </div>

                    <div class="space-y-3">
                        <div v-for="(komp, index) in form.komponens" :key="index"
                            class="p-4 rounded-xl border border-line bg-parchment/30 space-y-3">

                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-forest uppercase tracking-wider">Komponen #{{ index + 1 }}</span>
                                <button type="button" @click="removeKomponen(index)" :disabled="!canRemove"
                                    class="text-red-500 hover:bg-red-50 rounded-lg p-1.5 disabled:opacity-30 transition-colors text-xs font-semibold"
                                    title="Hapus komponen">
                                    ✕ Hapus
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold text-ink/60 mb-1">Nama Komponen</label>
                                    <input v-model="komp.nama_komponen" type="text" required
                                        class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gold"
                                        :placeholder="`Contoh: Kekompakan Baris-berbaris`" />
                                    <div v-if="form.errors[`komponens.${index}.nama_komponen`]" class="text-red-600 text-xs mt-1">
                                        {{ form.errors[`komponens.${index}.nama_komponen`] }}
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-ink/60 mb-1">Golongan</label>
                                    <!-- ✅ ENUM BARU: 3 golongan via GOL_OPTIONS -->
                                    <select v-model="komp.golongan" required
                                        class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gold">
                                        <option value="">Pilih Golongan</option>
                                        <option v-for="g in GOL_OPTIONS" :key="g.value" :value="g.value">{{ g.label }}</option>
                                    </select>
                                    <div v-if="form.errors[`komponens.${index}.golongan`]" class="text-red-600 text-xs mt-1">
                                        {{ form.errors[`komponens.${index}.golongan`] }}
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-ink/60 mb-1">Urutan Tampil</label>
                                    <input v-model.number="komp.urutan" type="number" min="1" required
                                        class="w-full border border-line rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-gold" />
                                    <div v-if="form.errors[`komponens.${index}.urutan`]" class="text-red-600 text-xs mt-1">
                                        {{ form.errors[`komponens.${index}.urutan`] }}
                                    </div>
                                </div>
                            </div>

                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input v-model="komp.is_active" type="checkbox"
                                    class="rounded border-line text-forest focus:ring-gold/40" />
                                <span class="text-xs text-ink/60">Aktif (jika dicentang, komponen ini muncul saat juri menilai)</span>
                            </label>
                        </div>
                    </div>

                    <button type="button" @click="addKomponen" :disabled="!canAdd"
                        class="mt-3 w-full py-2.5 border-2 border-dashed border-line rounded-xl text-sm font-semibold text-forest hover:border-gold hover:text-gold disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                        + Tambah Komponen ({{ form.komponens.length }}/5)
                    </button>
                </div>

                <!-- Error global -->
                <div v-for="(err, key) in form.errors" :key="key"
                    class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
                    ⚠️ {{ err }}
                </div>

                <!-- Aksi -->
                <div class="flex flex-col sm:flex-row items-center gap-3 pt-4 border-t border-line">
                    <button type="submit" :disabled="form.processing"
                        class="w-full sm:w-auto px-5 py-2 bg-forest text-parchment rounded-lg font-semibold hover:bg-forest/90 disabled:opacity-50 text-sm sm:text-base">
                        {{ isEditing ? 'Update Komponen Kriteria' : 'Simpan Komponen Kriteria' }}
                    </button>
                    <Link :href="route('admin.kriterias.index')"
                        class="w-full sm:w-auto text-center px-5 py-2 border border-line rounded-lg hover:bg-parchment text-sm sm:text-base">
                        Batal
                    </Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>