// File: Dashboard.vue
<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
</script>

<template>
    <AdminLayout header="Dashboard Admin">
        <Head title="Admin Dashboard" />

        <div class="px-4 sm:px-0">
            <div class="bg-white rounded-xl border border-line p-6 shadow-sm">
                <h2 class="font-display text-xl font-semibold text-forest mb-2">
                    Sistem Persimanu v3
                </h2>

                <p class="text-ink/70">
                    Modul aktif: Manajemen Event.
                </p>
            </div>
        </div>
    </AdminLayout>
</template>