<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { LOGO, MASKOT } from '@/brand';

defineProps({
    terms: { type: Boolean, default: false },
    policy: { type: Boolean, default: false },
});

// 🔥 SESUAIKAN DENGAN CONTROLLER
const form = useForm({
    // Field dasar (wajib di controller)
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
    
    // 🔥 Field tambahan yang ada di controller
    // Sesuaikan dengan nama field di database
    team_name: '',      // Nama pangkalan
    npsn: '',           // NPSN
    jenjang: '',        // Jenjang pendidikan
    alamat: '',         // Alamat
    no_telp: '',        // Nomor telepon
});

const showPw = ref(false);
const showPw2 = ref(false);
const logoOk = ref(true);
const maskotOk = ref(true);

// 🔥 SUBMIT DENGAN HANDLING ERROR
const submit = () => {
    console.log('📤 Mengirim data:', form.data()); // Debug
    
    form.post(route('register'), {
        onSuccess: () => {
            console.log('✅ Register berhasil!');
            // Reset password saja
            form.reset('password', 'password_confirmation');
        },
        onError: (errors) => {
            console.log('❌ Error validasi:', errors);
            // Error akan otomatis tampil di bawah field
        },
        onFinish: () => {
            console.log('🏁 Proses selesai');
        },
    });
};

// Meter kekuatan password
const pwStrength = computed(() => {
    const p = form.password;
    if (!p) return { score: 0, label: '', cls: '', w: '0%' };
    let s = 0;
    if (p.length >= 8) s++;
    if (/[A-Z]/.test(p)) s++;
    if (/[0-9]/.test(p)) s++;
    if (/[^A-Za-z0-9]/.test(p)) s++;
    const map = [
        { label: 'Terlalu lemah', cls: 'bg-red-400', w: '25%' },
        { label: 'Lemah', cls: 'bg-amber-400', w: '50%' },
        { label: 'Cukup', cls: 'bg-gold', w: '75%' },
        { label: 'Kuat', cls: 'bg-forest', w: '100%' },
    ];
    const i = Math.max(0, s - 1);
    return { score: s, label: map[i].label, cls: map[i].cls, w: map[i].w };
});

const pwMatch = computed(() =>
    form.password_confirmation.length > 0 && form.password === form.password_confirmation
);
</script>

<template>
    <Head title="Daftar — PERSIMANU" />

    <div class="min-h-screen grid lg:grid-cols-2 font-sans text-ink">
        <!-- SISI BRAND (gelap) -->
        <aside class="relative overflow-hidden bg-forest text-parchment flex flex-col justify-between p-7 sm:p-10 lg:p-12">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(70%_90%_at_80%_-10%,theme(colors.gold/14%),transparent_60%)]"></div>
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(50%_70%_at_0%_100%,theme(colors.khaki/12%),transparent_55%)]"></div>
            <div class="pointer-events-none absolute inset-0 opacity-[0.05] bg-[linear-gradient(theme(colors.parchment)_1px,transparent_1px),linear-gradient(90deg,theme(colors.parchment)_1px,transparent_1px)] bg-[size:42px_42px]"></div>
            <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-gold via-khaki to-forest"></div>

            <div class="relative auth-reveal">
                <div class="flex items-end gap-1.5 h-7 mb-5">
                    <svg v-for="(off, i) in [16, 8, 3, 0, 3, 8, 16]" :key="i" :style="{ marginTop: off + 'px', animationDelay: i * 90 + 'ms' }"
                        class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-gold twinkle" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2l2.9 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l7.1-1.01L12 2z" />
                    </svg>
                </div>
                <div class="flex items-center gap-4">
                    <img v-if="logoOk" :src="LOGO" @error="logoOk = false" alt="PERSIMANU"
                        class="w-20 h-20 sm:w-24 sm:h-24 object-contain drop-shadow-2xl emblem-float flex-shrink-0" />
                    <div v-else class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-gold/15 border border-gold/30 flex items-center justify-center text-4xl flex-shrink-0">⚜</div>
                    <div>
                        <div class="text-[10px] sm:text-xs font-display font-bold tracking-[0.3em] uppercase text-gold/90">Sako Pandu Ma'arif NU</div>
                        <h1 class="font-display font-extrabold leading-[0.9] tracking-tight text-3xl sm:text-5xl mt-1">PERSIMANU<br /><span class="text-gold">JEPARA</span></h1>
                    </div>
                </div>
                <p class="mt-6 max-w-md text-parchment/70 text-sm sm:text-base leading-relaxed">
                    Daftarkan pangkalan / sekolah Anda untuk mengikuti kegiatan dan mendaftarkan kontingen peserta.
                </p>
            </div>

            <div class="relative mt-10 flex items-end gap-4 auth-reveal" style="animation-delay: 160ms">
                <div class="relative bg-parchment text-ink rounded-2xl rounded-bl-sm px-4 py-2.5 shadow-xl rotate-[-1.5deg] max-w-[220px]">
                    <p class="text-sm font-semibold leading-snug">Ayo bergabung! 🎉</p>
                </div>
                <img v-if="maskotOk" :src="MASKOT" @error="maskotOk = false" alt="SiELANG"
                    class="w-28 sm:w-36 object-contain drop-shadow-2xl maskot-float select-none" draggable="false" />
                <div v-else class="w-28 sm:w-36 h-36 rounded-3xl bg-white/5 border border-white/10 flex items-center justify-center text-5xl maskot-float">🦅</div>
            </div>
        </aside>

        <!-- SISI FORM -->
        <main class="relative bg-parchment flex items-center justify-center p-6 sm:p-10 py-10">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(60%_60%_at_100%_0%,theme(colors.gold/8%),transparent_60%)]"></div>

            <a href="/" class="absolute top-5 left-5 sm:top-7 sm:left-7 text-xs sm:text-sm text-ink/50 hover:text-forest transition-colors inline-flex items-center gap-1 group">
                <span class="transition-transform group-hover:-translate-x-0.5">←</span> Beranda
            </a>

            <div class="relative w-full max-w-md auth-reveal" style="animation-delay: 80ms">
                <div class="mb-6">
                    <span class="text-[10px] sm:text-xs font-display font-bold tracking-[0.3em] uppercase text-khaki">Buat Akun Baru</span>
                    <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-forest mt-1 leading-none">Daftar Sekarang</h2>
                    <p class="text-sm text-ink/55 mt-2">Isi data untuk membuat akun operator sekolah.</p>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <!-- NAMA LENGKAP -->
                    <div>
                        <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-ink/55 mb-1.5">Nama Lengkap</label>
                        <div class="group flex items-center gap-3 border-2 rounded-xl px-4 py-3 bg-white transition-all duration-200"
                            :class="form.errors.name ? 'border-red-400' : 'border-line focus-within:border-gold focus-within:ring-4 focus-within:ring-gold/15'">
                            <svg class="w-5 h-5 flex-shrink-0 transition-colors" :class="form.errors.name ? 'text-red-400' : 'text-ink/35 group-focus-within:text-gold'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 21v-1a6 6 0 0112 0v1" /></svg>
                            <input id="name" v-model="form.name" type="text" required autofocus autocomplete="name"
                                class="flex-1 bg-transparent outline-none text-forest placeholder:text-ink/35 text-sm sm:text-base" placeholder="Nama Anda" />
                        </div>
                        <div v-if="form.errors.name" class="text-red-600 text-xs mt-1.5">{{ form.errors.name }}</div>
                    </div>

                    <!-- 🔥 NAMA PANGKALAN / SEKOLAH -->
                    <div>
                        <label for="team_name" class="block text-xs font-semibold uppercase tracking-wider text-ink/55 mb-1.5">Nama Pangkalan / Sekolah</label>
                        <div class="group flex items-center gap-3 border-2 rounded-xl px-4 py-3 bg-white transition-all duration-200"
                            :class="form.errors.team_name ? 'border-red-400' : 'border-line focus-within:border-gold focus-within:ring-4 focus-within:ring-gold/15'">
                            <svg class="w-5 h-5 flex-shrink-0 transition-colors" :class="form.errors.team_name ? 'text-red-400' : 'text-ink/35 group-focus-within:text-gold'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 21v-4H7v4" /></svg>
                            <input id="team_name" v-model="form.team_name" type="text"
                                class="flex-1 bg-transparent outline-none text-forest placeholder:text-ink/35 text-sm sm:text-base" placeholder="Contoh: MTs NU Jepara" />
                        </div>
                        <div v-if="form.errors.team_name" class="text-red-600 text-xs mt-1.5">{{ form.errors.team_name }}</div>
                    </div>

                    <!-- 🔥 NPSN -->
                    <div>
                        <label for="npsn" class="block text-xs font-semibold uppercase tracking-wider text-ink/55 mb-1.5">NPSN</label>
                        <div class="group flex items-center gap-3 border-2 rounded-xl px-4 py-3 bg-white transition-all duration-200"
                            :class="form.errors.npsn ? 'border-red-400' : 'border-line focus-within:border-gold focus-within:ring-4 focus-within:ring-gold/15'">
                            <svg class="w-5 h-5 flex-shrink-0 transition-colors" :class="form.errors.npsn ? 'text-red-400' : 'text-ink/35 group-focus-within:text-gold'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg>
                            <input id="npsn" v-model="form.npsn" type="text"
                                class="flex-1 bg-transparent outline-none text-forest placeholder:text-ink/35 text-sm sm:text-base" placeholder="Opsional" />
                        </div>
                        <div v-if="form.errors.npsn" class="text-red-600 text-xs mt-1.5">{{ form.errors.npsn }}</div>
                    </div>

                    <!-- 🔥 JENJANG -->
                    <div>
                        <label for="jenjang" class="block text-xs font-semibold uppercase tracking-wider text-ink/55 mb-1.5">Jenjang</label>
                        <div class="group flex items-center gap-3 border-2 rounded-xl px-4 py-3 bg-white transition-all duration-200"
                            :class="form.errors.jenjang ? 'border-red-400' : 'border-line focus-within:border-gold focus-within:ring-4 focus-within:ring-gold/15'">
                            <svg class="w-5 h-5 flex-shrink-0 transition-colors" :class="form.errors.jenjang ? 'text-red-400' : 'text-ink/35 group-focus-within:text-gold'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422A12.083 12.083 0 0112 20.5a12.083 12.083 0 01-6.16-9.922L12 14z" /></svg>
                            <select id="jenjang" v-model="form.jenjang"
                                class="flex-1 bg-transparent outline-none text-forest text-sm sm:text-base">
                                <option value="">Pilih Jenjang</option>
                                <option value="SD">SD</option>
                                <option value="MI">MI</option>
                                <option value="SMP">SMP</option>
                                <option value="MTs">MTs</option>
                                <option value="SMA">SMA</option>
                                <option value="MA">MA</option>
                                <option value="SMK">SMK</option>
                            </select>
                        </div>
                        <div v-if="form.errors.jenjang" class="text-red-600 text-xs mt-1.5">{{ form.errors.jenjang }}</div>
                    </div>

                    <!-- 🔥 ALAMAT -->
                    <div>
                        <label for="alamat" class="block text-xs font-semibold uppercase tracking-wider text-ink/55 mb-1.5">Alamat Pangkalan</label>
                        <div class="group flex items-center gap-3 border-2 rounded-xl px-4 py-3 bg-white transition-all duration-200"
                            :class="form.errors.alamat ? 'border-red-400' : 'border-line focus-within:border-gold focus-within:ring-4 focus-within:ring-gold/15'">
                            <svg class="w-5 h-5 flex-shrink-0 transition-colors" :class="form.errors.alamat ? 'text-red-400' : 'text-ink/35 group-focus-within:text-gold'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <textarea id="alamat" v-model="form.alamat" rows="2"
                                class="flex-1 bg-transparent outline-none text-forest placeholder:text-ink/35 text-sm sm:text-base resize-none" placeholder="Alamat sekolah / pangkalan"></textarea>
                        </div>
                        <div v-if="form.errors.alamat" class="text-red-600 text-xs mt-1.5">{{ form.errors.alamat }}</div>
                    </div>

                    <!-- 🔥 NO. TELEPON -->
                    <div>
                        <label for="no_telp" class="block text-xs font-semibold uppercase tracking-wider text-ink/55 mb-1.5">No. Telepon</label>
                        <div class="group flex items-center gap-3 border-2 rounded-xl px-4 py-3 bg-white transition-all duration-200"
                            :class="form.errors.no_telp ? 'border-red-400' : 'border-line focus-within:border-gold focus-within:ring-4 focus-within:ring-gold/15'">
                            <svg class="w-5 h-5 flex-shrink-0 transition-colors" :class="form.errors.no_telp ? 'text-red-400' : 'text-ink/35 group-focus-within:text-gold'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                            <input id="no_telp" v-model="form.no_telp" type="tel"
                                class="flex-1 bg-transparent outline-none text-forest placeholder:text-ink/35 text-sm sm:text-base" placeholder="Contoh: 081234567890" />
                        </div>
                        <div v-if="form.errors.no_telp" class="text-red-600 text-xs mt-1.5">{{ form.errors.no_telp }}</div>
                    </div>

                    <!-- EMAIL -->
                    <div>
                        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-ink/55 mb-1.5">Email</label>
                        <div class="group flex items-center gap-3 border-2 rounded-xl px-4 py-3 bg-white transition-all duration-200"
                            :class="form.errors.email ? 'border-red-400' : 'border-line focus-within:border-gold focus-within:ring-4 focus-within:ring-gold/15'">
                            <svg class="w-5 h-5 flex-shrink-0 transition-colors" :class="form.errors.email ? 'text-red-400' : 'text-ink/35 group-focus-within:text-gold'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            <input id="email" v-model="form.email" type="email" required autocomplete="email"
                                class="flex-1 bg-transparent outline-none text-forest placeholder:text-ink/35 text-sm sm:text-base" placeholder="nama@sekolah.sch.id" />
                        </div>
                        <div v-if="form.errors.email" class="text-red-600 text-xs mt-1.5">{{ form.errors.email }}</div>
                    </div>

                    <!-- PASSWORD -->
                    <div>
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-ink/55 mb-1.5">Kata Sandi</label>
                        <div class="group flex items-center gap-3 border-2 rounded-xl px-4 py-3 bg-white transition-all duration-200"
                            :class="form.errors.password ? 'border-red-400' : 'border-line focus-within:border-gold focus-within:ring-4 focus-within:ring-gold/15'">
                            <svg class="w-5 h-5 flex-shrink-0 transition-colors" :class="form.errors.password ? 'text-red-400' : 'text-ink/35 group-focus-within:text-gold'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 11c1.1 0 2-.9 2-2a2 2 0 10-4 0c0 1.1.9 2 2 2zm6 0h-1V9a5 5 0 10-10 0v2H6a2 2 0 00-2 2v7a2 2 0 002 2h12a2 2 0 002-2v-7a2 2 0 00-2-2z" /></svg>
                            <input id="password" v-model="form.password" :type="showPw ? 'text' : 'password'" required autocomplete="new-password"
                                class="flex-1 bg-transparent outline-none text-forest placeholder:text-ink/35 text-sm sm:text-base" placeholder="Min. 8 karakter" />
                            <button type="button" @click="showPw = !showPw" class="text-ink/35 hover:text-forest transition-colors flex-shrink-0">
                                <svg v-if="!showPw" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M1.5 12S5 5 12 5s10.5 7 10.5 7-3.5 7-10.5 7S1.5 12 1.5 12z" /><circle cx="12" cy="12" r="3" stroke-width="1.8" /></svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3l18 18M10.6 10.6a3 3 0 004.2 4.2M9.4 5.2A9.6 9.6 0 0112 5c7 0 10.5 7 10.5 7a17 17 0 01-3.2 4M6.1 6.1A17 17 0 001.5 12S5 19 12 19a9.6 9.6 0 003-.5" /></svg>
                            </button>
                        </div>
                        <div v-if="form.password" class="mt-2">
                            <div class="h-1.5 rounded-full bg-line overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-300" :class="pwStrength.cls" :style="{ width: pwStrength.w }"></div>
                            </div>
                            <p class="text-[11px] mt-1 text-ink/50">Kekuatan: <span class="font-semibold">{{ pwStrength.label }}</span></p>
                        </div>
                        <div v-if="form.errors.password" class="text-red-600 text-xs mt-1.5">{{ form.errors.password }}</div>
                    </div>

                    <!-- KONFIRMASI PASSWORD -->
                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-ink/55 mb-1.5">Konfirmasi Kata Sandi</label>
                        <div class="group flex items-center gap-3 border-2 rounded-xl px-4 py-3 bg-white transition-all duration-200"
                            :class="form.errors.password_confirmation ? 'border-red-400' : pwMatch ? 'border-green-400 focus-within:ring-4 focus-within:ring-green-400/15' : 'border-line focus-within:border-gold focus-within:ring-4 focus-within:ring-gold/15'">
                            <svg class="w-5 h-5 flex-shrink-0 transition-colors" :class="pwMatch ? 'text-green-500' : 'text-ink/35 group-focus-within:text-gold'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <input id="password_confirmation" v-model="form.password_confirmation" :type="showPw2 ? 'text' : 'password'" required autocomplete="new-password"
                                class="flex-1 bg-transparent outline-none text-forest placeholder:text-ink/35 text-sm sm:text-base" placeholder="Ulangi kata sandi" />
                            <button type="button" @click="showPw2 = !showPw2" class="text-ink/35 hover:text-forest transition-colors flex-shrink-0">
                                <svg v-if="!showPw2" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M1.5 12S5 5 12 5s10.5 7 10.5 7-3.5 7-10.5 7S1.5 12 1.5 12z" /><circle cx="12" cy="12" r="3" stroke-width="1.8" /></svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3l18 18M10.6 10.6a3 3 0 004.2 4.2M9.4 5.2A9.6 9.6 0 0112 5c7 0 10.5 7 10.5 7a17 17 0 01-3.2 4M6.1 6.1A17 17 0 001.5 12S5 19 12 19a9.6 9.6 0 003-.5" /></svg>
                            </button>
                        </div>
                        <div v-if="form.errors.password_confirmation" class="text-red-600 text-xs mt-1.5">{{ form.errors.password_confirmation }}</div>
                    </div>

                    <!-- TERMS -->
                    <div v-if="terms || policy" class="pt-1">
                        <label class="flex items-start gap-2.5 cursor-pointer select-none group">
                            <input v-model="form.terms" type="checkbox" class="mt-0.5 w-4 h-4 rounded border-line text-forest focus:ring-gold accent-forest" />
                            <span class="text-xs sm:text-sm text-ink/65 leading-relaxed">
                                Saya menyetujui
                                <Link v-if="terms" :href="route('terms.show')" target="_blank" class="text-forest font-semibold underline hover:text-gold">Ketentuan Layanan</Link>
                                <template v-if="terms && policy"> &amp; </template>
                                <Link v-if="policy" :href="route('policy.show')" target="_blank" class="text-forest font-semibold underline hover:text-gold">Kebijakan Privasi</Link>.
                            </span>
                        </label>
                        <div v-if="form.errors.terms" class="text-red-600 text-xs mt-1.5">{{ form.errors.terms }}</div>
                    </div>

                    <!-- SUBMIT -->
                    <button type="submit" :disabled="form.processing"
                        class="w-full relative px-5 py-3 bg-forest text-parchment rounded-xl font-bold text-sm sm:text-base hover:bg-forest/90 disabled:opacity-50 transition-all duration-200 active:scale-[0.98] shadow-lg shadow-forest/20 flex items-center justify-center gap-2">
                        <svg v-if="form.processing" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" /></svg>
                        <span>{{ form.processing ? 'Mendaftar…' : 'Daftar' }}</span>
                    </button>
                </form>

                <!-- LINK LOGIN -->
                <div class="text-center mt-6 bg-white border border-line rounded-xl px-5 py-4">
                    <p class="text-sm text-ink/60">Sudah punya akun?</p>
                    <Link :href="route('login')"
                        class="group inline-flex items-center gap-1.5 mt-1 font-display font-bold text-forest hover:text-gold transition-colors text-base">
                        Masuk di sini
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