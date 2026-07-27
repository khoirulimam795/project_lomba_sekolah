<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    eventName: { type: String, default: 'PERSIMANU' },
});

// ganti ekstensi di sini kalau file logo bukan .png
const LOGO = '/img/logo-persimanu.png';
const logoOk = ref(true);
</script>

<template>
    <div class="min-h-screen flex flex-col bg-parchment text-ink font-sans">
        <!-- navbar -->
        <header class="sticky top-0 z-40 bg-forest/95 backdrop-blur-md border-b border-white/10">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 h-16 sm:h-[68px] flex items-center justify-between gap-4">
                <Link :href="route('publik.landing')" class="flex items-center gap-2.5 group">
                    <img
                        v-if="logoOk"
                        :src="LOGO"
                        @error="logoOk = false"
                        alt="Logo PERSIMANU"
                        class="w-15 h-15 sm:w-20 sm:h-25 object-contain drop-shadow group-hover:rotate-6 transition-transform duration-300"
                    />
                    <span v-else class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-gold text-ink flex items-center justify-center font-display font-extrabold text-sm">⚜</span>
                    <span class="leading-tight">
                        <span class="block font-display font-extrabold tracking-wide text-parchment text-sm sm:text-base uppercase">PERSIMANU</span>
                        <span class="block text-[9px] sm:text-[10px] tracking-[0.2em] text-gold/80 uppercase font-semibold">Jepara</span>
                    </span>
                </Link>

                <nav class="hidden md:flex items-center gap-7 text-sm text-parchment/75 font-medium">
                    <a href="#tentang" class="hover:text-gold transition-colors">Tentang</a>
                    <a href="#nilai" class="hover:text-gold transition-colors">Nilai SiELANG</a>
                    <a href="#klasemen" class="hover:text-gold transition-colors">Klasemen</a>
                    <a href="#leaderboard" class="hover:text-gold transition-colors">Per Lomba</a>
                    <a href="#jadwal" class="hover:text-gold transition-colors">Jadwal</a>
                </nav>

                <Link :href="route('login')" class="px-3.5 sm:px-4 py-1.5 sm:py-2 bg-gold text-ink rounded-lg text-xs sm:text-sm font-bold hover:opacity-90 transition active:scale-95 whitespace-nowrap">
                    Login Panitia
                </Link>
            </div>
        </header>

        <main class="flex-1">
            <slot />
        </main>

        <!-- footer -->
        <footer class="bg-forest text-parchment/70">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 py-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs sm:text-sm">
                <div class="flex items-center gap-3">
                    <img v-if="logoOk" :src="LOGO" alt="" class="w-10 h-10 object-contain opacity-90" />
                    <div class="leading-tight">
                        <div class="font-display font-extrabold text-parchment tracking-wide">PERSIMANU JEPARA</div>
                        <div class="text-parchment/45">Sako Pandu Ma'arif NU • LP Ma'arif NU Kab. Jepara</div>
                    </div>
                </div>
                <div class="text-parchment/45 text-center sm:text-right">© {{ new Date().getFullYear() }} • Sistem Penilaian Lomba Kepramukaan</div>
            </div>
        </footer>
    </div>
</template>