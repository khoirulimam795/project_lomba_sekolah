<script setup>
import { Head } from '@inertiajs/vue3';
import JuriLayout from '@/Layouts/JuriLayout.vue';
</script>

<template>
    <JuriLayout header="Dashboard Juri">
        <Head title="Dashboard Juri" />

        <div class="bg-white rounded-xl border border-line p-6 shadow-sm">
            <h2 class="font-display text-xl font-semibold text-forest mb-2">
                Selamat datang, Juri
            </h2>
            <p class="text-ink/70">
                Modul penilaian akan tersedia setelah Admin menugaskan Anda ke
                suatu lomba. Untuk sekarang, halaman ini adalah landing akun juri Anda.
            </p>
        </div>
    </JuriLayout>
</template>