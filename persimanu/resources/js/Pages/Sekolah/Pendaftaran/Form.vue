<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import SekolahLayout from '@/Layouts/SekolahLayout.vue';

const props = defineProps({
    kontingen: { type: Object, default: null },
    event: { type: Object, default: null },
    pangkalan: { type: Object, default: null },
});

const isEditing = computed(() => Boolean(props.kontingen?.id));
const teamName = props.pangkalan?.name ?? props.kontingen?.team?.name ?? '';

const form = useForm({
    event_id: props.event?.id ?? props.kontingen?.event_id ?? '',
    nama_kontingen: props.kontingen?.nama_kontingen ?? '',
    nama_kepala_madrasah: props.kontingen?.nama_kepala_madrasah ?? '',
    asal_instansi: props.kontingen?.asal_instansi ?? teamName,
    contact_person: props.kontingen?.contact_person ?? '',
    contact_phone: props.kontingen?.contact_phone ?? '',
    pendamping_putra: props.kontingen?.pendamping_putra ?? 0,
    pendamping_putri: props.kontingen?.pendamping_putri ?? 0,
    peserta_putra: props.kontingen?.peserta_putra ?? 0,
    peserta_putri: props.kontingen?.peserta_putri ?? 0,
});

const submit = () => {
    const opts = { preserveScroll: true };
    if (isEditing.value) {
        form.put(route('sekolah.pendaftaran.update', { kontingen: props.kontingen.id }), opts);
    } else {
        form.post(route('sekolah.pendaftaran.store'), opts);
    }
};
</script>

<template>
    <SekolahLayout :header="isEditing ? 'Edit Formulir Kesediaan' : 'Formulir Kesediaan (C.01)'">
        <Head :title="isEditing ? 'Edit Formulir Kesediaan' : 'Formulir Kesediaan (C.01)'" />
        <div class="max-w-3xl mx-auto px-2 sm:px-0">
            <div class="mb-6"><Link :href="route('sekolah.pendaftaran.index')" class="text-sm text-forest hover:underline">← Kembali ke Pendaftaran</Link></div>
            <div v-if="event" class="mb-6 bg-parchment/40 rounded-xl border border-line/60 p-4">
                <div class="font-display font-bold text-forest text-lg">{{ event.nama }}</div>
                <div class="text-xs text-ink/50 mt-1">Isi formulir kesediaan untuk mengikuti event ini.</div>
            </div>
            <form @submit.prevent="submit" class="bg-white rounded-xl border border-line shadow-sm p-4 sm:p-6 space-y-6">
                <input type="hidden" v-model="form.event_id" />
                <div>
                    <h3 class="font-display font-bold text-forest text-base mb-4 pb-2 border-b border-line">Identitas Kontingen</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2"><label class="block font-semibold mb-1">Nama Kontingen / Madrasah</label><input v-model="form.nama_kontingen" type="text" required class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold" /><div v-if="form.errors.nama_kontingen" class="text-red-600 text-sm mt-1">{{ form.errors.nama_kontingen }}</div></div>
                        <div class="sm:col-span-2"><label class="block font-semibold mb-1">Nama Kepala Madrasah</label><input v-model="form.nama_kepala_madrasah" type="text" required class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold" /><div v-if="form.errors.nama_kepala_madrasah" class="text-red-600 text-sm mt-1">{{ form.errors.nama_kepala_madrasah }}</div></div>
                        <div class="sm:col-span-2"><label class="block font-semibold mb-1">Asal Instansi</label><input v-model="form.asal_instansi" type="text" required class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold" /><p class="text-[11px] text-ink/45 mt-1">Otomatis dari pangkalan Anda — ubah hanya bila berbeda.</p><div v-if="form.errors.asal_instansi" class="text-red-600 text-sm mt-1">{{ form.errors.asal_instansi }}</div></div>
                        <!-- ✅ LABEL BARU -->
                        <div><label class="block font-semibold mb-1">Nama Penanggung Jawab</label><input v-model="form.contact_person" type="text" required class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold" /><div v-if="form.errors.contact_person" class="text-red-600 text-sm mt-1">{{ form.errors.contact_person }}</div></div>
                        <div><label class="block font-semibold mb-1">No. HP / WA</label><input v-model="form.contact_phone" type="text" required class="w-full border border-line rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-gold" placeholder="08xxxxxxxxxx" /><div v-if="form.errors.contact_phone" class="text-red-600 text-sm mt-1">{{ form.errors.contact_phone }}</div></div>
                    </div>
                </div>
                <div>
                    <h3 class="font-display font-bold text-forest text-base mb-4 pb-2 border-b border-line">Jumlah Pendamping</h3>
                    <p class="text-xs text-ink/50 mb-3">Isi jumlah pendamping yang akan dikirim. Angka ini menentukan berapa form biodata pendamping yang harus diisi nanti.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div><label class="block font-semibold mb-1">Pendamping Putra</label><div class="flex items-center gap-2"><input v-model.number="form.pendamping_putra" type="number" min="0" max="20" required class="w-24 border border-line rounded-lg px-4 py-2 text-center font-mono font-bold text-lg focus:outline-none focus:ring-2 focus:ring-gold" /><span class="text-sm text-ink/60">orang</span></div><div v-if="form.errors.pendamping_putra" class="text-red-600 text-sm mt-1">{{ form.errors.pendamping_putra }}</div></div>
                        <div><label class="block font-semibold mb-1">Pendamping Putri</label><div class="flex items-center gap-2"><input v-model.number="form.pendamping_putri" type="number" min="0" max="20" required class="w-24 border border-line rounded-lg px-4 py-2 text-center font-mono font-bold text-lg focus:outline-none focus:ring-2 focus:ring-gold" /><span class="text-sm text-ink/60">orang</span></div><div v-if="form.errors.pendamping_putri" class="text-red-600 text-sm mt-1">{{ form.errors.pendamping_putri }}</div></div>
                    </div>
                </div>
                <div>
                    <h3 class="font-display font-bold text-forest text-base mb-4 pb-2 border-b border-line">Jumlah Peserta</h3>
                    <p class="text-xs text-ink/50 mb-3">Isi jumlah peserta yang akan dikirim. Angka ini menentukan berapa form biodata siswa yang harus diisi nanti.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div><label class="block font-semibold mb-1">Peserta Putra</label><div class="flex items-center gap-2"><input v-model.number="form.peserta_putra" type="number" min="0" max="50" required class="w-24 border border-line rounded-lg px-4 py-2 text-center font-mono font-bold text-lg focus:outline-none focus:ring-2 focus:ring-gold" /><span class="text-sm text-ink/60">orang</span></div><div v-if="form.errors.peserta_putra" class="text-red-600 text-sm mt-1">{{ form.errors.peserta_putra }}</div></div>
                        <div><label class="block font-semibold mb-1">Peserta Putri</label><div class="flex items-center gap-2"><input v-model.number="form.peserta_putri" type="number" min="0" max="50" required class="w-24 border border-line rounded-lg px-4 py-2 text-center font-mono font-bold text-lg focus:outline-none focus:ring-2 focus:ring-gold" /><span class="text-sm text-ink/60">orang</span></div><div v-if="form.errors.peserta_putri" class="text-red-600 text-sm mt-1">{{ form.errors.peserta_putri }}</div></div>
                    </div>
                </div>
                <div class="bg-parchment/40 rounded-xl border border-line/60 p-4">
                    <div class="font-display font-bold text-forest text-sm mb-2">Ringkasan Kuota</div>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div class="text-ink/60">Pendamping:</div><div class="font-mono font-bold text-forest">{{ (form.pendamping_putra || 0) + (form.pendamping_putri || 0) }} orang ({{ form.pendamping_putra || 0 }} PA + {{ form.pendamping_putri || 0 }} PI)</div>
                        <div class="text-ink/60">Peserta:</div><div class="font-mono font-bold text-forest">{{ (form.peserta_putra || 0) + (form.peserta_putri || 0) }} orang ({{ form.peserta_putra || 0 }} PA + {{ form.peserta_putri || 0 }} PI)</div>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row items-center gap-3 pt-4 border-t border-line">
                    <button type="submit" :disabled="form.processing" class="w-full sm:w-auto px-5 py-2 bg-forest text-parchment rounded-lg font-semibold hover:bg-forest/90 disabled:opacity-50 text-sm sm:text-base">{{ isEditing ? 'Update Formulir Kesediaan' : 'Simpan Formulir Kesediaan' }}</button>
                    <Link :href="route('sekolah.pendaftaran.index')" class="w-full sm:w-auto text-center px-5 py-2 border border-line rounded-lg hover:bg-parchment text-sm sm:text-base">Batal</Link>
                </div>
            </form>
        </div>
    </SekolahLayout>
</template>