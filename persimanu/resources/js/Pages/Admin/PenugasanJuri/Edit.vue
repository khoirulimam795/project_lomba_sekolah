<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    lomba: { type: Object, default: null },
    juris: { type: Array, default: () => [] },
    assigned: { type: Array, default: () => [] },
});

const form = useForm({
    juri_ids: [...props.assigned],
});

const toggleJuri = (id) => {
    const idx = form.juri_ids.indexOf(id);
    if (idx === -1) {
        form.juri_ids.push(id);
    } else {
        form.juri_ids.splice(idx, 1);
    }
};

const submit = () => {
    form.put(route('admin.penugasan-juri.update', props.lomba.id), {
        preserveScroll: true,
    });
};

const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').slice(0, 2).map((n) => n[0]).join('').toUpperCase();
};
</script>

<template>
    <AdminLayout :header="`Atur Juri — ${lomba?.nama ?? ''}`">
        <Head :title="`Atur Juri — ${lomba?.nama ?? ''}`" />

        <div class="max-w-4xl mx-auto px-2 sm:px-4 md:px-0">
            <div class="mb-6">
                <Link :href="route('admin.penugasan-juri.index')" class="text-sm text-forest hover:underline inline-flex items-center">
                    ← Kembali ke daftar lomba
                </Link>
            </div>

            <!-- Info lomba -->
            <div class="bg-white rounded-xl border border-line shadow-sm p-4 sm:p-6 mb-4 sm:mb-6">
                <h2 class="font-display text-lg sm:text-xl font-semibold text-forest">{{ lomba?.nama }}</h2>
                <p class="text-xs sm:text-sm text-ink/60 mt-1">Event: {{ lomba?.event?.nama ?? '-' }}</p>
                <p class="text-xs sm:text-sm text-ink/60 mt-2">
                    Centang juri yang ditugaskan menilai lomba ini. Bisa lebih dari satu.
                    Saat ini: <span class="font-semibold text-forest">{{ form.juri_ids.length }}</span> juri dipilih.
                </p>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-xl border border-line shadow-sm p-4 sm:p-6">
                <div v-if="props.juris.length === 0" class="text-center text-ink/50 py-8 sm:py-12">
                    <div class="text-4xl sm:text-5xl mb-3">👨‍⚖️</div>
                    <p class="text-sm sm:text-base">Belum ada akun juri.</p>
                    <p class="text-xs sm:text-sm">Buat dulu di menu Juri.</p>
                </div>

                <!-- Grid checkbox juri -->
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label
                        v-for="j in props.juris"
                        :key="j.id"
                        :class="[
                            'flex items-center gap-3 p-3 sm:p-4 rounded-xl border cursor-pointer transition-all duration-200',
                            form.juri_ids.includes(j.id)
                                ? 'border-forest bg-forest/5 ring-1 ring-forest/30'
                                : 'border-line hover:bg-parchment/40',
                        ]"
                    >
                        <input
                            type="checkbox"
                            :checked="form.juri_ids.includes(j.id)"
                            @change="toggleJuri(j.id)"
                            class="rounded border-line text-forest focus:ring-gold w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0"
                        />
                        <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-forest text-parchment flex items-center justify-center text-xs sm:text-sm font-semibold flex-shrink-0">
                            {{ getInitials(j.name) }}
                        </div>
                        <div class="min-w-0">
                            <div class="font-semibold text-forest text-sm sm:text-base truncate">{{ j.name }}</div>
                            <div class="text-[10px] sm:text-xs text-ink/50 truncate">{{ j.email }}</div>
                        </div>
                    </label>
                </div>

                <div v-if="form.errors.juri_ids" class="text-red-600 text-sm mt-3">
                    {{ form.errors.juri_ids }}
                </div>

                <!-- Action -->
                <div class="flex flex-col sm:flex-row items-center gap-3 pt-4 mt-4 border-t border-line">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full sm:w-auto px-5 py-2 sm:py-2.5 bg-forest text-parchment rounded-lg font-semibold hover:bg-forest/90 disabled:opacity-50 text-sm sm:text-base"
                    >
                        💾 Simpan Penugasan
                    </button>
                    <Link
                        :href="route('admin.penugasan-juri.index')"
                        class="w-full sm:w-auto text-center px-5 py-2 sm:py-2.5 border border-line rounded-lg hover:bg-parchment text-sm sm:text-base"
                    >
                        Batal
                    </Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>