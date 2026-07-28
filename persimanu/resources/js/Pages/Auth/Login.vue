<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { LOGO } from '@/brand';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const logoOk = ref(true);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform((data) => ({ ...data, remember: data.remember ? 'on' : '' }))
        .post(route('login'), {
            onFinish: () => form.reset('password'),
        });
};
</script>

<template>
    <Head title="Login" />

    <div class="min-h-screen flex items-center justify-center bg-forest px-3 sm:px-4 py-8 sm:py-10 relative overflow-hidden">
        <!-- ambient on-theme -->
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(70%_90%_at_50%_-10%,#ffc4001f,transparent_60%)]"></div>
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(50%_70%_at_100%_100%,theme(colors.khaki/14%),transparent_55%)]"></div>
        <div class="pointer-events-none absolute inset-0 opacity-[0.04] bg-[linear-gradient(theme(colors.parchment)_1px,transparent_1px),linear-gradient(90deg,theme(colors.parchment)_1px,transparent_1px)] bg-[size:44px_44px]"></div>
        <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-[#ffc400] via-khaki to-forest"></div>

        <!-- oval / kartu di tengah -->
        <div class="relative w-full max-w-md bg-parchment rounded-[1.75rem] sm:rounded-[2.25rem] md:rounded-[2.75rem] shadow-2xl border border-white/10 px-4 sm:px-6 md:px-10 py-7 sm:py-9 md:py-11 mx-2 sm:mx-0">
            <!-- brand -->
            <div class="flex flex-col items-center text-center">
                <img v-if="logoOk" :src="LOGO" @error="logoOk = false" alt="SAKOMA" class="w-16 h-16 sm:w-18 sm:h-18 md:w-20 md:h-20 object-contain drop-shadow emblem-float" />
                <div v-else class="w-16 h-16 sm:w-18 sm:h-18 md:w-20 md:h-20 rounded-full bg-gold text-ink flex items-center justify-center font-display font-extrabold text-2xl sm:text-3xl">⚜</div>
                <h1 class="mt-2 sm:mt-3 font-display font-extrabold tracking-wide text-xl sm:text-2xl md:text-3xl text-forest uppercase">SAKOMA</h1>
                <p class="mt-1.5 sm:mt-2 text-[10px] sm:text-[11px] md:text-xs font-semibold tracking-wide text-ink/55 uppercase leading-relaxed max-w-[200px] sm:max-w-none">
                    Sistem Pendataan Peserta – Perlombaan – Rekapitulasi Hasil
                </p>
            </div>

            <!-- status (reset password dll) -->
            <div v-if="status" class="mt-5 sm:mt-6 text-xs sm:text-sm font-semibold text-green-700 bg-green-50 border border-green-200 rounded-xl px-3 py-2 text-center">{{ status }}</div>

            <!-- form -->
            <form @submit.prevent="submit" class="mt-6 sm:mt-7 space-y-3.5 sm:space-y-4">
                <div>
                    <label class="block text-[10px] sm:text-xs font-bold uppercase tracking-wider text-ink/55 mb-1 sm:mb-1.5">Email</label>
                    <input v-model="form.email" type="email" required autofocus autocomplete="username"
                        class="w-full rounded-xl border border-line bg-white px-3 sm:px-4 py-2 sm:py-2.5 text-xs sm:text-sm text-ink focus:outline-none focus:border-gold focus:ring-2 focus:ring-gold/30 transition" />
                    <div v-if="form.errors.email" class="text-red-600 text-[11px] sm:text-xs mt-1">{{ form.errors.email }}</div>
                </div>
                <div>
                    <label class="block text-[10px] sm:text-xs font-bold uppercase tracking-wider text-ink/55 mb-1 sm:mb-1.5">Kata Sandi</label>
                    <input v-model="form.password" type="password" required autocomplete="current-password"
                        class="w-full rounded-xl border border-line bg-white px-3 sm:px-4 py-2 sm:py-2.5 text-xs sm:text-sm text-ink focus:outline-none focus:border-gold focus:ring-2 focus:ring-gold/30 transition" />
                    <div v-if="form.errors.password" class="text-red-600 text-[11px] sm:text-xs mt-1">{{ form.errors.password }}</div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-0">
                    <label class="flex items-center gap-2 text-xs sm:text-sm text-ink/70 cursor-pointer select-none">
                        <input v-model="form.remember" type="checkbox" class="rounded border-line text-forest focus:ring-gold/40 w-3.5 h-3.5 sm:w-4 sm:h-4" />
                        Ingat saya
                    </label>
                    <Link v-if="canResetPassword" :href="route('password.request')" class="text-[11px] sm:text-xs md:text-sm font-semibold text-forest hover:text-gold transition-colors">Lupa kata sandi?</Link>
                </div>

                <button type="submit" :disabled="form.processing"
                    class="w-full py-2.5 sm:py-3 rounded-xl bg-forest text-parchment font-bold tracking-wide hover:bg-forest/90 disabled:opacity-50 transition active:scale-[0.98] text-xs sm:text-sm md:text-base">
                    {{ form.processing ? 'Memproses…' : 'LOGIN' }}
                </button>
            </form>

            <div class="mt-5 sm:mt-6 text-center text-xs sm:text-sm text-ink/60">
                Belum punya akun?
                <Link :href="route('register')" class="font-bold text-forest hover:text-gold transition-colors">DAFTAR SEBAGAI PESERTA</Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
.emblem-float { animation: emblemFloat 6s ease-in-out infinite; }
@keyframes emblemFloat { 0%, 100% { transform: translateY(0) scale(1); } 50% { transform: translateY(-5px) scale(1.02); } }

/* Extra small screen adjustments */
@media (max-width: 400px) {
    .rounded-\[1\.75rem\] {
        border-radius: 1.25rem;
    }
    .px-4 {
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }
    .py-7 {
        padding-top: 1.25rem;
        padding-bottom: 1.25rem;
    }
}
</style>