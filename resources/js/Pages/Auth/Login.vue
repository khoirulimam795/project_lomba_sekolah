// File: Login.vue
<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { LOGO, MASKOT } from '@/brand';

defineProps({
    canResetPassword: { type: Boolean, default: true },
    status: { type: String, default: null },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPw = ref(false);
const logoOk = ref(true);
const maskotOk = ref(true);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Masuk — PERSIMANU" />

    <div class="min-h-screen grid lg:grid-cols-2 font-sans text-ink">
        <!-- ============ SISI BRAND (gelap) ============ -->
        <aside class="relative overflow-hidden bg-forest text-parchment flex flex-col justify-between p-5 sm:p-8 lg:p-12 min-h-[300px] lg:min-h-screen">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(70%_90%_at_80%_-10%,theme(colors.gold/14%),transparent_60%)]"></div>
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(50%_70%_at_0%_100%,theme(colors.khaki/12%),transparent_55%)]"></div>
            <div class="pointer-events-none absolute inset-0 opacity-[0.05] bg-[linear-gradient(theme(colors.parchment)_1px,transparent_1px),linear-gradient(90deg,theme(colors.parchment)_1px,transparent_1px)] bg-[size:42px_42px]"></div>
            <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-gold via-khaki to-forest"></div>

            <!-- atas: logo + bintang -->
            <div class="relative auth-reveal">
                <div class="flex items-end gap-1 h-5 sm:h-7 mb-3 sm:mb-5">
                    <svg v-for="(off, i) in [16, 8, 3, 0, 3, 8, 16]" :key="i" :style="{ marginTop: off + 'px', animationDelay: i * 90 + 'ms' }"
                        class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-gold twinkle" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2l2.9 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l7.1-1.01L12 2z" />
                    </svg>
                </div>
                <div class="flex items-center gap-3 sm:gap-4">
                    <img v-if="logoOk" :src="LOGO" @error="logoOk = false" alt="PERSIMANU"
                        class="w-14 h-14 sm:w-20 sm:h-20 lg:w-24 lg:h-24 object-contain drop-shadow-2xl emblem-float flex-shrink-0" />
                    <div v-else class="w-14 h-14 sm:w-20 sm:h-20 lg:w-24 lg:h-24 rounded-full bg-gold/15 border border-gold/30 flex items-center justify-center text-3xl sm:text-4xl flex-shrink-0">⚜</div>
                    <div>
                        <div class="text-[8px] sm:text-[10px] lg:text-xs font-display font-bold tracking-[0.2em] sm:tracking-[0.3em] uppercase text-gold/90">Sako Pandu Ma'arif NU</div>
                        <h1 class="font-display font-extrabold leading-[0.9] tracking-tight text-2xl sm:text-3xl lg:text-5xl mt-0.5 sm:mt-1">PERSIMANU<br /><span class="text-gold">JEPARA</span></h1>
                    </div>
                </div>
                <p class="mt-4 sm:mt-6 max-w-md text-parchment/70 text-xs sm:text-sm lg:text-base leading-relaxed hidden sm:block">
                    Sistem Penilaian Lomba Kepramukaan — dari pendaftaran kontingen sampai papan juara, dalam satu tempat.
                </p>
            </div>

            <!-- bawah: maskot + sapaan -->
            <div class="relative mt-6 sm:mt-10 flex items-end gap-3 sm:gap-4 auth-reveal" style="animation-delay: 160ms">
                <div class="relative bg-parchment text-ink rounded-2xl rounded-bl-sm px-3 sm:px-4 py-2 sm:py-2.5 shadow-xl rotate-[-1.5deg] max-w-[160px] sm:max-w-[220px]">
                    <p class="text-xs sm:text-sm font-semibold leading-snug">Selamat datang kembali! ️</p>
                </div>
                <img v-if="maskotOk" :src="MASKOT" @error="maskotOk = false" alt="SiELANG"
                    class="w-20 sm:w-28 lg:w-36 object-contain drop-shadow-2xl maskot-float select-none" draggable="false" />
                <div v-else class="w-20 sm:w-28 lg:w-36 h-24 sm:h-32 lg:h-36 rounded-2xl sm:rounded-3xl bg-white/5 border border-white/10 flex items-center justify-center text-4xl sm:text-5xl maskot-float">🦅</div>
            </div>
        </aside>

        <!-- ============ SISI FORM (terang) ============ -->
        <main class="relative bg-parchment flex items-center justify-center p-4 sm:p-6 lg:p-10 min-h-[500px] lg:min-h-screen">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(60%_60%_at_100%_0%,theme(colors.gold/8%),transparent_60%)]"></div>

            <a href="/" class="absolute top-3 sm:top-5 lg:top-7 left-3 sm:left-5 lg:left-7 text-xs sm:text-sm text-ink/50 hover:text-forest transition-colors inline-flex items-center gap-1 group">
                <span class="transition-transform group-hover:-translate-x-0.5">←</span> Beranda
            </a>

            <div class="relative w-full max-w-md auth-reveal px-1 sm:px-0" style="animation-delay: 80ms">
                <div class="mb-5 sm:mb-7">
                    <span class="text-[10px] sm:text-xs font-display font-bold tracking-[0.2em] sm:tracking-[0.3em] uppercase text-khaki">Masuk ke Sistem</span>
                    <h2 class="font-display text-2xl sm:text-3xl lg:text-4xl font-extrabold text-forest mt-1 leading-none">Selamat Datang</h2>
                    <p class="text-xs sm:text-sm text-ink/55 mt-1.5 sm:mt-2">Masuk dengan akun yang diberikan panitia.</p>
                </div>

                <div v-if="status" class="mb-4 sm:mb-5 flex items-center gap-2 text-xs sm:text-sm bg-green-50 border border-green-200 text-green-700 rounded-lg px-3 py-2 sm:py-2.5">
                    <span>✅</span><span>{{ status }}</span>
                </div>

                <form @submit.prevent="submit" class="space-y-4 sm:space-y-5">
                    <!-- email -->
                    <div>
                        <label for="email" class="block text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-ink/55 mb-1 sm:mb-1.5">Email</label>
                        <div class="group flex items-center gap-2 sm:gap-3 border-2 rounded-xl px-3 sm:px-4 py-2.5 sm:py-3 bg-white transition-all duration-200"
                            :class="form.errors.email ? 'border-red-400' : 'border-line focus-within:border-gold focus-within:ring-4 focus-within:ring-gold/15'">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0 transition-colors" :class="form.errors.email ? 'text-red-400' : 'text-ink/35 group-focus-within:text-gold'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            <input id="email" v-model="form.email" type="email" required autofocus autocomplete="username"
                                class="flex-1 bg-transparent outline-none text-forest placeholder:text-ink/35 text-sm sm:text-base" placeholder="nama@email.com" />
                        </div>
                        <div v-if="form.errors.email" class="text-red-600 text-[11px] sm:text-xs mt-1.5">{{ form.errors.email }}</div>
                    </div>

                    <!-- password -->
                    <div>
                        <label for="password" class="block text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-ink/55 mb-1 sm:mb-1.5">Kata Sandi</label>
                        <div class="group flex items-center gap-2 sm:gap-3 border-2 rounded-xl px-3 sm:px-4 py-2.5 sm:py-3 bg-white transition-all duration-200"
                            :class="form.errors.password ? 'border-red-400' : 'border-line focus-within:border-gold focus-within:ring-4 focus-within:ring-gold/15'">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0 transition-colors" :class="form.errors.password ? 'text-red-400' : 'text-ink/35 group-focus-within:text-gold'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 11c1.1 0 2-.9 2-2a2 2 0 10-4 0c0 1.1.9 2 2 2zm6 0h-1V9a5 5 0 10-10 0v2H6a2 2 0 00-2 2v7a2 2 0 002 2h12a2 2 0 002-2v-7a2 2 0 00-2-2z" /></svg>
                            <input id="password" v-model="form.password" :type="showPw ? 'text' : 'password'" required autocomplete="current-password"
                                class="flex-1 bg-transparent outline-none text-forest placeholder:text-ink/35 text-sm sm:text-base" placeholder="••••••••" />
                            <button type="button" @click="showPw = !showPw" class="text-ink/35 hover:text-forest transition-colors flex-shrink-0" :title="showPw ? 'Sembunyikan' : 'Lihat'">
                                <svg v-if="!showPw" class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M1.5 12S5 5 12 5s10.5 7 10.5 7-3.5 7-10.5 7S1.5 12 1.5 12z" /><circle cx="12" cy="12" r="3" stroke-width="1.8" /></svg>
                                <svg v-else class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3l18 18M10.6 10.6a3 3 0 004.2 4.2M9.4 5.2A9.6 9.6 0 0112 5c7 0 10.5 7 10.5 7a17 17 0 01-3.2 4M6.1 6.1A17 17 0 001.5 12S5 19 12 19a9.6 9.6 0 003-.5" /></svg>
                            </button>
                        </div>
                        <div v-if="form.errors.password" class="text-red-600 text-[11px] sm:text-xs mt-1.5">{{ form.errors.password }}</div>
                    </div>

                    <!-- remember + forgot -->
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <label class="flex items-center gap-2 cursor-pointer select-none group">
                            <input v-model="form.remember" type="checkbox" class="w-3.5 h-3.5 sm:w-4 sm:h-4 rounded border-line text-forest focus:ring-gold accent-forest" />
                            <span class="text-xs sm:text-sm text-ink/65 group-hover:text-forest transition-colors">Ingat saya</span>
                        </label>
                        <Link v-if="canResetPassword" :href="route('password.request')" class="text-xs sm:text-sm font-semibold text-forest hover:text-gold transition-colors">
                            Lupa sandi?
                        </Link>
                    </div>

                    <!-- submit -->
                    <button type="submit" :disabled="form.processing"
                        class="w-full relative px-4 sm:px-5 py-2.5 sm:py-3 bg-forest text-parchment rounded-xl font-bold text-sm sm:text-base hover:bg-forest/90 disabled:opacity-50 transition-all duration-200 active:scale-[0.98] shadow-lg shadow-forest/20 flex items-center justify-center gap-2">
                        <svg v-if="form.processing" class="w-4 h-4 sm:w-5 sm:h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" /></svg>
                        <span>{{ form.processing ? 'Memproses…' : 'Masuk' }}</span>
                    </button>
                </form>

                <!-- divider -->
                <div class="flex items-center gap-3 my-4 sm:my-6">
                    <span class="flex-1 h-px bg-line"></span>
                    <span class="text-[10px] sm:text-[11px] uppercase tracking-wider text-ink/40 font-semibold">atau</span>
                    <span class="flex-1 h-px bg-line"></span>
                </div>

                <!-- link daftar -->
                <div class="text-center bg-white border border-line rounded-xl px-4 sm:px-5 py-3 sm:py-4">
                    <p class="text-xs sm:text-sm text-ink/60">Belum punya akun?</p>
                    <Link :href="route('register')"
                        class="group inline-flex items-center gap-1.5 mt-1 font-display font-bold text-forest hover:text-gold transition-colors text-sm sm:text-base">
                        Daftar sebagai Operator Sekolah
                        <span class="transition-transform group-hover:translate-x-1">→</span>
                    </Link>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
.auth-reveal { opacity: 0; animation: authReveal 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
@keyframes authReveal { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: none; } }
.maskot-float { animation: maskotFloat 4s ease-in-out infinite; }
@keyframes maskotFloat { 0%, 100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(-10px) rotate(-1deg); } }
.emblem-float { animation: emblemFloat 6s ease-in-out infinite; }
@keyframes emblemFloat { 0%, 100% { transform: translateY(0) scale(1); } 50% { transform: translateY(-5px) scale(1.02); } }
.twinkle { animation: twinkle 2.4s ease-in-out infinite; }
@keyframes twinkle { 0%, 100% { opacity: 0.4; transform: scale(0.85); } 50% { opacity: 1; transform: scale(1.1); } }
</style>