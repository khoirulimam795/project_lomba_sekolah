<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { GOL_OPTIONS } from '@/golongan';

const props = defineProps({
    lomba: { type: Object, default: null },
    events: { type: Array, default: () => [] },
    prefill: { type: Object, default: null }, // ✅ ada saat mode duplikat
});

const isEditing = computed(() => Boolean(props.lomba?.id));
const isDuplicate = computed(() => !isEditing.value && Boolean(props.prefill));

const KATEGORI_OPTIONS = [
    { value: 'PA', label: 'Putra' },
    { value: 'PI', label: 'Putri' },
];
const STATUS_OPTIONS = [
    { value: 'draft', label: 'Draft' },
    { value: 'aktif', label: 'Aktif' },
    { value: 'selesai', label: 'Selesai' },
];

// komponen: edit → dari lomba; duplikat → dari prefill; create → 1 baris kosong
const initialKomponens = (() => {
    if (props.lomba?.kriteria_komponens?.length) {
        return props.lomba.kriteria_komponens.map((k) => ({ nama_komponen: k.nama_komponen, is_active: k.is_active }));
    }
    if (props.prefill?.komponens?.length) {
        return props.prefill.komponens.map((k) => ({ nama_komponen: k.nama_komponen, is_active: k.is_active }));
    }
    return [{ nama_komponen: '', is_active: true }];
})();

const form = useForm({
    event_id: props.lomba?.event_id ?? props.prefill?.event_id ?? '',
    nama: props.lomba?.nama ?? props.prefill?.nama ?? '',
    deskripsi: props.lomba?.deskripsi ?? props.prefill?.deskripsi ?? '',
    golongan: props.lomba?.golongan ?? props.prefill?.golongan ?? '',
    kategori: props.lomba?.kategori ?? props.prefill?.kategori ?? '',
    status: props.lomba?.status ?? props.prefill?.status ?? 'draft',
    komponens: [...initialKomponens],
});

const addKomponen = () => {
    if (form.komponens.length < 5) form.komponens.push({ nama_komponen: '', is_active: true });
};
const removeKomponen = (i) => {
    if (form.komponens.length > 1) form.komponens.splice(i, 1);
};
const canAdd = computed(() => form.komponens.length < 5);
const canRemove = computed(() => form.komponens.length > 1);

const headerTitle = computed(() =>
    isEditing.value ? 'Edit Lomba' : isDuplicate.value ? 'Duplikat Lomba' : 'Tambah Lomba'
);

const submit = () => {
    if (isEditing.value) {
        form.put(route('admin.lombas.update', props.lomba.id), { preserveScroll: true });
    } else {
        form.post(route('admin.lombas.store'), { preserveScroll: true });
    }
};
</script>

<template>
    <AdminLayout :header="headerTitle">
        <Head :title="headerTitle" />
        <div class="max-w-3xl mx-auto px-2 sm:px-0">
            <div class="mb-6 flex items-center justify-between">
                <Link :href="route('admin.lombas.index')" class="text-sm text-forest hover:underline">← Kembali ke daftar lomba</Link>
                <span v-if="isDuplicate" class="text-xs font-semibold text-gold bg-gold/10 border border-gold/30 px-3 py-1 rounded-full">⧉ Mode Duplikat — akan disimpan sebagai lomba baru</span>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-xl border border-line shadow-sm p-4 sm:p-6 space-y-6">
                <!-- Event -->
                <div>
                    <label class="block font-semibold mb-1">Event</label>
                    <select v-model="form.event_id" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold">
                        <option value="">Pilih Event</option>
                        <option v-for="ev in events" :key="ev.id" :value="ev.id">{{ ev.nama }}</option>
                    </select>
                    <div v-if="form.errors.event_id" class="text-red-600 text-sm mt-1">{{ form.errors.event_id }}</div>
                </div>

                <!-- Nama -->
                <div>
                    <label class="block font-semibold mb-1">Nama Lomba</label>
                    <input v-model="form.nama" type="text" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold" placeholder="Contoh: Pionering" />
                    <div v-if="form.errors.nama" class="text-red-600 text-sm mt-1">{{ form.errors.nama }}</div>
                </div>

                <!-- Deskripsi -->
                <div>
                    <label class="block font-semibold mb-1">Deskripsi Lomba</label>
                    <textarea v-model="form.deskripsi" rows="3" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold" placeholder="Deskripsi singkat lomba" />
                    <div v-if="form.errors.deskripsi" class="text-red-600 text-sm mt-1">{{ form.errors.deskripsi }}</div>
                </div>

                <!-- Golongan / Kategori / Status -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-semibold mb-1">Golongan</label>
                        <select v-model="form.golongan" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold">
                            <option value="">Pilih</option>
                            <option v-for="g in GOL_OPTIONS" :key="g.value" :value="g.value">{{ g.label }}</option>
                        </select>
                        <div v-if="form.errors.golongan" class="text-red-600 text-sm mt-1">{{ form.errors.golongan }}</div>
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Kategori</label>
                        <select v-model="form.kategori" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold">
                            <option value="">Pilih</option>
                            <option v-for="k in KATEGORI_OPTIONS" :key="k.value" :value="k.value">{{ k.label }}</option>
                        </select>
                        <div v-if="form.errors.kategori" class="text-red-600 text-sm mt-1">{{ form.errors.kategori }}</div>
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Status</label>
                        <select v-model="form.status" class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold">
                            <option v-for="s in STATUS_OPTIONS" :key="s.value" :value="s.value">{{ s.label }}</option>
                        </select>
                        <div v-if="form.errors.status" class="text-red-600 text-sm mt-1">{{ form.errors.status }}</div>
                    </div>
                </div>

                <!-- ===== KOMPONEN PENILAIAN (dinamis ≤5) ===== -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="block font-semibold text-lg text-forest">Komponen Penilaian</label>
                        <span class="text-xs text-ink/50">{{ form.komponens.length }} / 5</span>
                    </div>
                    <p class="text-xs text-ink/50 mb-3">Tuliskan nama tiap komponen penilaian (misal "Kerapian", "Simpul"). Maksimal 5, bisa tambah/kurang.</p>

                    <div class="space-y-3">
                        <div v-for="(komp, i) in form.komponens" :key="i"
                            class="flex items-center gap-3 p-3 rounded-xl border border-line bg-parchment/30">
                            <span class="w-7 h-7 rounded-full bg-forest/10 text-forest flex items-center justify-center text-xs font-bold flex-shrink-0">{{ i + 1 }}</span>
                            <input v-model="komp.nama_komponen" type="text" class="flex-1 border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gold" :placeholder="`Komponen ${i + 1}`" />
                            <label class="flex items-center gap-1.5 text-xs text-ink/60 cursor-pointer select-none whitespace-nowrap">
                                <input v-model="komp.is_active" type="checkbox" class="rounded border-line text-forest focus:ring-gold/40" /> Aktif
                            </label>
                            <button type="button" @click="removeKomponen(i)" :disabled="!canRemove"
                                class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 disabled:opacity-30 transition-colors" title="Hapus">✕</button>
                        </div>
                    </div>

                    <button type="button" @click="addKomponen" :disabled="!canAdd"
                        class="mt-3 w-full py-2.5 border-2 border-dashed border-line rounded-xl text-sm font-semibold text-forest hover:border-gold hover:text-gold disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                        + Tambah Komponen ({{ form.komponens.length }}/5)
                    </button>
                    <div v-if="form.errors.komponens" class="text-red-600 text-sm mt-2">{{ form.errors.komponens }}</div>
                </div>

                <!-- Aksi -->
                <div class="flex flex-col sm:flex-row items-center gap-3 pt-4 border-t border-line">
                    <button type="submit" :disabled="form.processing"
                        class="w-full sm:w-auto px-5 py-2 bg-forest text-parchment rounded-lg font-semibold hover:bg-forest/90 disabled:opacity-50 text-sm sm:text-base">
                        {{ isEditing ? 'Update Lomba' : 'Simpan Lomba' }}
                    </button>
                    <Link :href="route('admin.lombas.index')" class="w-full sm:w-auto text-center px-5 py-2 border border-line rounded-lg hover:bg-parchment text-sm sm:text-base">Batal</Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>