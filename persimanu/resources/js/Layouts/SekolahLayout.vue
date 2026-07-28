<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    header: { type: String, default: 'Peserta' },
});

const page = usePage();
const user = page.props.auth?.user;
const flash = page.props.flash;

const isMobileMenuOpen = ref(false);

const navItems = [
    {
        label: 'Pendaftaran',
        href: route('sekolah.pendaftaran.index'),
        active: route().current('sekolah.pendaftaran.*'),
    },
    // B2/B3 menyusul: Biodata, Alokasi Lomba, Status Verifikasi
];

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const closeMobileMenu = () => {
    isMobileMenuOpen.value = false;
};
</script>

<template>
    <div class="min-h-screen bg-parchment text-ink">
        <div class="flex flex-col md:flex-row">
            <!-- Mobile Top Bar -->
            <header class="md:hidden bg-forest text-parchment px-4 py-3 flex items-center justify-between sticky top-0 z-50">
                <div>
                    <div class="font-display text-lg font-semibold tracking-wide">SAKOMA</div>
                    <div class="text-xs text-parchment/70">Peserta</div>
                </div>

                <button
                    @click="toggleMobileMenu"
                    class="p-2 hover:bg-white/10 rounded-lg transition"
                    aria-label="Toggle menu"
                >
                    <svg
                        v-if="!isMobileMenuOpen"
                        class="w-6 h-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg
                        v-else
                        class="w-6 h-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </header>

            <!-- Mobile Sidebar Overlay -->
            <div
                v-if="isMobileMenuOpen"
                class="fixed inset-0 bg-black/50 z-40 md:hidden"
                @click="closeMobileMenu"
            ></div>

            <!-- Sidebar -->
            <aside
                :class="[
                    'fixed md:sticky top-0 z-50 h-full w-64 bg-forest text-parchment transition-transform duration-300 ease-in-out',
                    'md:block md:translate-x-0 min-h-screen',
                    isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full',
                ]"
            >
                <div class="p-6 border-b border-white/10">
                    <div class="font-display text-xl font-semibold tracking-wide">SAKOMA</div>
                    <div class="text-xs text-parchment/70 mt-1">Peserta</div>
                </div>

                <nav class="p-4 space-y-1">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        @click="closeMobileMenu"
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
            <div class="flex-1 flex flex-col min-h-screen w-full">
                <!-- Desktop Header -->
                <header class="bg-white border-b border-line hidden md:block">
                    <div class="px-4 sm:px-6 py-4 flex items-center justify-between">
                        <h1 class="font-display text-xl sm:text-2xl font-semibold text-forest">{{ header }}</h1>

                        <div class="flex items-center gap-3 sm:gap-4">
                            <div v-if="user" class="text-sm text-right hidden sm:block">
                                <div class="font-semibold">{{ user.name }}</div>
                                <div class="text-ink/60 text-xs">{{ user.email }}</div>
                            </div>
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="px-3 sm:px-4 py-1.5 sm:py-2 bg-forest text-parchment rounded-lg hover:bg-forest/90 text-xs sm:text-sm transition-colors"
                            >
                                Logout
                            </Link>
                        </div>
                    </div>
                </header>

                <!-- Mobile Header -->
                <div class="md:hidden bg-white border-b border-line px-4 py-3">
                    <h1 class="font-display text-lg font-semibold text-forest">{{ header }}</h1>
                </div>

                <!-- Flash Messages -->
                <div class="px-3 sm:px-4 md:px-6 pt-3 sm:pt-4 space-y-2 sm:space-y-3">
                    <div v-if="flash?.success" class="bg-green-100 border border-green-300 text-green-800 px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg text-xs sm:text-sm">
                        {{ flash.success }}
                    </div>
                    <div v-if="flash?.error" class="bg-red-100 border border-red-300 text-red-800 px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg text-xs sm:text-sm">
                        {{ flash.error }}
                    </div>
                </div>

                <!-- Content -->
                <main class="flex-1 p-3 sm:p-4 md:p-6">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Mobile Menu Animation */
.mobile-menu-enter-active,
.mobile-menu-leave-active {
    transition: all 0.3s ease;
}
.mobile-menu-enter-from,
.mobile-menu-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
.mobile-menu-enter-to,
.mobile-menu-leave-from {
    opacity: 1;
    transform: translateY(0);
}
</style>