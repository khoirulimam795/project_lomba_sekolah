<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    eventName: { type: String, default: 'PERSIMANU' },
});

// ganti ekstensi di sini kalau file logo bukan .png
const LOGO = '/img/logo-persimanu.png';
const logoOk = ref(true);

// Mobile menu state
const isMobileMenuOpen = ref(false);

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const closeMobileMenu = () => {
    isMobileMenuOpen.value = false;
};
</script>

<template>
    <div class="min-h-screen flex flex-col bg-parchment text-ink font-sans">
        <!-- navbar -->
        <header class="sticky top-0 z-40 bg-forest/95 backdrop-blur-md border-b border-white/10">
            <div class="mx-auto max-w-6xl px-3 sm:px-4 md:px-6 h-14 sm:h-16 md:h-[68px] flex items-center justify-between gap-2 sm:gap-4">
                <Link :href="route('publik.landing')" class="flex items-center gap-2 sm:gap-2.5 group flex-shrink-0">
                    <img
                        v-if="logoOk"
                        :src="LOGO"
                        @error="logoOk = false"
                        alt="Logo PERSIMANU"
                        class="w-10 h-10 sm:w-12 sm:h-12 md:w-14 md:h-14 lg:w-16 lg:h-16 object-contain drop-shadow group-hover:rotate-6 transition-transform duration-300"
                    />
                    <span v-else class="w-7 h-7 sm:w-8 sm:h-8 md:w-9 md:h-9 lg:w-10 lg:h-10 rounded-lg bg-gold text-ink flex items-center justify-center font-display font-extrabold text-xs sm:text-sm">⚜</span>
                    <span class="leading-tight hidden xs:inline">
                        <span class="block font-display font-extrabold tracking-wide text-parchment text-xs sm:text-sm md:text-base uppercase">PERSIMANU</span>
                        <span class="block text-[7px] sm:text-[8px] md:text-[9px] lg:text-[10px] tracking-[0.15em] sm:tracking-[0.2em] text-gold/80 uppercase font-semibold">Jepara</span>
                    </span>
                </Link>

                <!-- Desktop Navigation -->
                <nav class="hidden md:flex items-center gap-4 lg:gap-7 text-xs lg:text-sm text-parchment/75 font-medium">
                    <a href="#tentang" class="hover:text-gold transition-colors whitespace-nowrap">Tentang</a>
                    <a href="#nilai" class="hover:text-gold transition-colors whitespace-nowrap">Nilai SiELANG</a>
                    <a href="#klasemen" class="hover:text-gold transition-colors whitespace-nowrap">Klasemen</a>
                    <a href="#leaderboard" class="hover:text-gold transition-colors whitespace-nowrap">Per Lomba</a>
                    <a href="#jadwal" class="hover:text-gold transition-colors whitespace-nowrap">Jadwal</a>
                </nav>

                <div class="flex items-center gap-2 sm:gap-3">
                    <!-- Mobile Menu Toggle -->
                    <button
                        @click="toggleMobileMenu"
                        class="md:hidden p-1.5 sm:p-2 text-parchment hover:text-gold transition-colors rounded-lg hover:bg-white/5"
                        aria-label="Toggle menu"
                    >
                        <svg
                            v-if="!isMobileMenuOpen"
                            class="w-5 h-5 sm:w-6 sm:h-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg
                            v-else
                            class="w-5 h-5 sm:w-6 sm:h-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <Link 
                        :href="route('login')" 
                        class="px-3 sm:px-3.5 md:px-4 py-1.5 sm:py-2 bg-gold text-ink rounded-lg text-[10px] sm:text-xs md:text-sm font-bold hover:opacity-90 transition active:scale-95 whitespace-nowrap"
                    >
                        Login Panitia
                    </Link>
                </div>
            </div>

            <!-- Mobile Navigation Dropdown -->
            <Transition name="mobile-menu">
                <div
                    v-if="isMobileMenuOpen"
                    class="md:hidden border-t border-white/10 bg-forest/98 backdrop-blur-md"
                >
                    <nav class="mx-auto max-w-6xl px-3 sm:px-4 py-3 sm:py-4 flex flex-col gap-1 text-sm text-parchment/75 font-medium">
                        <a
                            href="#tentang"
                            @click="closeMobileMenu"
                            class="px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg hover:bg-white/5 hover:text-gold transition-colors"
                        >
                            Tentang
                        </a>
                        <a
                            href="#nilai"
                            @click="closeMobileMenu"
                            class="px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg hover:bg-white/5 hover:text-gold transition-colors"
                        >
                            Nilai SiELANG
                        </a>
                        <a
                            href="#klasemen"
                            @click="closeMobileMenu"
                            class="px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg hover:bg-white/5 hover:text-gold transition-colors"
                        >
                            Klasemen
                        </a>
                        <a
                            href="#leaderboard"
                            @click="closeMobileMenu"
                            class="px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg hover:bg-white/5 hover:text-gold transition-colors"
                        >
                            Per Lomba
                        </a>
                        <a
                            href="#jadwal"
                            @click="closeMobileMenu"
                            class="px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg hover:bg-white/5 hover:text-gold transition-colors"
                        >
                            Jadwal
                        </a>
                    </nav>
                </div>
            </Transition>
        </header>

        <main class="flex-1">
            <slot />
        </main>

        <!-- footer -->
        <footer class="bg-forest text-parchment/70">
            <div class="mx-auto max-w-6xl px-3 sm:px-4 md:px-6 py-6 sm:py-7 md:py-8 flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-4 text-[10px] sm:text-xs md:text-sm">
                <div class="flex items-center gap-2 sm:gap-3">
                    <img v-if="logoOk" :src="LOGO" alt="" class="w-8 h-8 sm:w-9 sm:h-9 md:w-10 md:h-10 object-contain opacity-90" />
                    <div class="leading-tight">
                        <div class="font-display font-extrabold text-parchment tracking-wide text-xs sm:text-sm">PERSIMANU JEPARA</div>
                        <div class="text-parchment/45 text-[8px] sm:text-[10px] md:text-xs">Sako Pandu Ma'arif NU • LP Ma'arif NU Kab. Jepara</div>
                    </div>
                </div>
                <div class="text-parchment/45 text-center sm:text-right text-[8px] sm:text-[10px] md:text-xs">
                    © {{ new Date().getFullYear() }} • Sistem Penilaian Lomba Kepramukaan
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* Extra small devices */
@media (min-width: 480px) {
    .xs\:inline {
        display: inline;
    }
}

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