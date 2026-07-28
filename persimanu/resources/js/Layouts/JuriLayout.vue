<script setup>
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
    header: {
        type: String,
        default: 'Juri',
    },
});

const page = usePage();
const user = page.props.auth?.user;
const flash = page.props.flash;

const navItems = [
    {
        label: 'Dashboard',
        href: route('juri.dashboard'),
        active: route().current('juri.dashboard'),
    },
    {
        label: 'Penilaian',
        href: route('juri.penilaian.index'),
        active: route().current('juri.penilaian.*'),
    },
    {
        label: 'Rekap Saya',
        href: route('juri.penilaian.rekap'),
        active: route().current('juri.penilaian.rekap'),
    },

];
</script>

<template>
    <div class="min-h-screen bg-parchment text-ink">
        <div class="flex">
            <!-- Sidebar -->
            <aside class="w-64 min-h-screen bg-forest text-parchment hidden md:block">
                <div class="p-6 border-b border-white/10">
                    <div class="font-display text-xl font-semibold tracking-wide">
                        PERSIMANU
                    </div>
                    <div class="text-xs text-parchment/70 mt-1">
                        Panel Juri
                    </div>
                </div>

                <nav class="p-4 space-y-1">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        :class="[
                            'block px-4 py-2 rounded-lg transition',
                            item.active
                                ? 'bg-gold text-ink font-semibold'
                                : 'text-parchment/80 hover:bg-white/10',
                        ]"
                    >
                        {{ item.label }}
                    </Link>
                </nav>
            </aside>

            <!-- Main -->
            <div class="flex-1 flex flex-col min-h-screen">
                <header class="bg-white border-b border-line">
                    <div class="px-6 py-4 flex items-center justify-between">
                        <h1 class="font-display text-2xl font-semibold text-forest">
                            {{ header }}
                        </h1>

                        <div class="flex items-center gap-4">
                            <div v-if="user" class="text-sm text-right">
                                <div class="font-semibold">{{ user.name }}</div>
                                <div class="text-ink/60">{{ user.email }}</div>
                            </div>

                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="px-4 py-2 bg-forest text-parchment rounded-lg hover:bg-forest/90"
                            >
                                Logout
                            </Link>
                        </div>
                    </div>
                </header>

                <div v-if="flash?.success" class="px-6 pt-4">
                    <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg">
                        {{ flash.success }}
                    </div>
                </div>

                <div v-if="flash?.error" class="px-6 pt-4">
                    <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg">
                        {{ flash.error }}
                    </div>
                </div>

                <main class="flex-1 p-6">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>