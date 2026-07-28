<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { LOGO } from '@/brand';
import { useLiveUpdates } from '@/composables/useLiveUpdates';
import { GOL_OPTIONS } from '@/golongan';
import { ref, reactive, computed, onMounted, nextTick, watch } from 'vue';

const props = defineProps({
    event: { type: Object, default: null },
    liveStatus: { type: String, default: null },
    stats: { type: Object, default: null },
    phases: { type: Array, default: () => [] },
    standings: { type: Array, default: () => [] },
    leaderboards: { type: Array, default: () => [] },
    selectedGolongan: { type: String, default: '' },
});

const { isLive, toast } = useLiveUpdates(props.event?.id ?? null);
const logoOk = ref(true);

const fmt = (v) => (v ? String(v).slice(0, 10).split('-').reverse().join('/') : '-');

// ===== FILTER REKAP MEDALI (server-side via query string) =====
const filterByGolongan = (e) => {
    const gol = e.target.value;
    router.get(route('publik.landing'),
        { golongan: gol || undefined },
        { preserveState: true, preserveScroll: true }
    );
};

// ===== FILTER HASIL LOMBA (client-side, 3 dropdown dependent) =====
const filterKategori = ref('');
const filterGolonganHasil = ref('');
const filterLombaId = ref(props.leaderboards[0]?.lomba?.id ?? null);

// Daftar lomba yang tersedia berdasarkan golongan yang dipilih
const filteredLombaOptions = computed(() => {
    if (!filterGolonganHasil.value) return props.leaderboards;
    return props.leaderboards.filter((b) =>
        b.rows.some((r) => r.golongan === filterGolonganHasil.value)
    );
});

// Reset filter lomba ketika golongan berubah
watch(filterGolonganHasil, () => {
    const opts = filteredLombaOptions.value;
    filterLombaId.value = opts.length ? opts[0].lomba.id : null;
});

// ✅ SATU-SATUNYA definisi currentBoard (tanpa duplikat)
const currentBoard = computed(() => {
    const board = props.leaderboards.find((b) => b.lomba.id === filterLombaId.value)
        || filteredLombaOptions.value[0]
        || { lomba: { id: null, nama: '-' }, rows: [] };

    let rows = board.rows || [];

    if (filterKategori.value) {
        rows = rows.filter((r) => r.kategori === filterKategori.value);
    }
    if (filterGolonganHasil.value) {
        rows = rows.filter((r) => r.golongan === filterGolonganHasil.value);
    }

    // Re-rank setelah filter
    rows = rows.map((r, i) => ({ ...r, rank: i + 1 }));

    return { lomba: board.lomba, rows };
});

// ✅ SATU-SATUNYA definisi maxBoardNilai (tanpa duplikat)
const maxBoardNilai = computed(() =>
    currentBoard.value.rows.length
        ? Math.max(...currentBoard.value.rows.map((r) => Number(r.nilai_akhir)))
        : 100
);

const openRow = ref(null);
const toggleRow = (key) => { openRow.value = openRow.value === key ? null : key; };

const rankMedal = { 1: 'emas', 2: 'perak', 3: 'perunggu' };
const medalEmoji = { emas: '🥇', perak: '🥈', perunggu: '🥉' };

// 6 pilar nilai SiELANG
const nilaiSielang = [
    { h: 'B', t: 'Berprestasi', d: 'Bersemangat meraih prestasi akademik & nonakademik.', e: '🏅', c: 'from-gold to-amber-600' },
    { h: 'E', t: 'Empatik',     d: 'Peduli terhadap sesama dan lingkungan sekitar.',     e: '🤝', c: 'from-forest to-emerald-700' },
    { h: 'L', t: 'Luhur',       d: 'Berakhlak & berkarakter Ahlussunnah wal Jamaah.',     e: '✨', c: 'from-khaki to-amber-800' },
    { h: 'A', t: 'Adaptif',     d: 'Mandiri, kreatif, dan tangguh menghadapi perubahan.', e: '🧭', c: 'from-forest to-teal-700' },
    { h: 'N', t: 'Nasionalis',  d: 'Berjiwa kebangsaan & cinta tanah air.',               e: '🇮🇩', c: 'from-red-600 to-red-800' },
    { h: 'G', t: 'Gigih',       d: 'Disiplin & pantang menyerah dalam berkompetisi.',     e: '💪', c: 'from-gold to-khaki' },
];

const starArc = [20, 11, 5, 1, 0, 1, 5, 11, 20];

const liveMeta = computed(() => ({
    live:     { t: 'Sedang Berlangsung', cls: 'bg-green-500/15 text-green-300 border-green-400/40', dot: 'bg-green-400', pulse: true },
    upcoming: { t: 'Segera Dimulai',     cls: 'bg-gold/15 text-gold border-gold/40',                dot: 'bg-gold',      pulse: false },
    ended:    { t: 'Selesai',            cls: 'bg-white/10 text-parchment/70 border-white/20',       dot: 'bg-parchment/50', pulse: false },
}[props.liveStatus] || { t: '-', cls: 'bg-white/10 text-parchment/70 border-white/20', dot: 'bg-parchment/50', pulse: false }));

// ===== REKAP MEDALI =====
const medalRows = computed(() => {
    const rows = (props.standings || []).map((r) => ({
        ...r,
        total: (Number(r.emas) || 0) + (Number(r.perak) || 0) + (Number(r.perunggu) || 0),
    }));
    rows.sort((a, b) =>
        (b.emas - a.emas) || (b.perak - a.perak) || (b.perunggu - a.perunggu) || (a.kontingen_id - b.kontingen_id)
    );
    return rows.map((r, i) => ({ ...r, rank: i + 1 }));
});
const totalMedalAll = computed(() => medalRows.value.reduce((acc, r) => {
    acc.emas += r.emas; acc.perak += r.perak; acc.perunggu += r.perunggu; acc.total += r.total;
    return acc;
}, { emas: 0, perak: 0, perunggu: 0, total: 0 }));

// ===== animasi =====
const animated = reactive({ pangkalan: 0, regu: 0, lomba: 0, juri: 0 });
const barsReady = ref(false);
const countUp = (key, target) => {
    const dur = 1100, t0 = performance.now();
    const step = (t) => {
        const p = Math.min(1, (t - t0) / dur);
        animated[key] = Math.round((target || 0) * (1 - Math.pow(1 - p, 3)));
        if (p < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
};

onMounted(() => {
    if (props.stats) ['pangkalan', 'regu', 'lomba', 'juri'].forEach((k) => countUp(k, props.stats[k]));
    nextTick(() => setTimeout(() => { barsReady.value = true; }, 80));
    const io = new IntersectionObserver((entries) => {
        entries.forEach((e) => { if (e.isIntersecting) { e.target.classList.add('is-visible'); io.unobserve(e.target); } });
    }, { threshold: 0.12 });
    document.querySelectorAll('[data-reveal]').forEach((el) => io.observe(el));
});
</script>

<template>
    <PublicLayout :event-name="event?.nama ?? 'SAKOMA'">
        <Head :title="event ? `${event.nama} • SAKOMA` : 'SAKOMA • Papan Arena'" />

        <!-- toast realtime -->
        <Transition name="toast">
            <div v-if="toast" class="no-print fixed top-16 sm:top-20 left-1/2 -translate-x-1/2 z-50 flex items-center gap-2 sm:gap-3 bg-forest text-parchment pl-3 sm:pl-4 pr-4 sm:pr-5 py-2.5 sm:py-3 rounded-2xl shadow-2xl border border-gold/30 max-w-[90vw] sm:max-w-none">
                <span class="relative flex w-2 h-2 sm:w-2.5 sm:h-2.5">
                    <span class="absolute inline-flex h-full w-full rounded-full bg-gold opacity-75 animate-ping"></span>
                    <span class="relative inline-flex rounded-full w-2 h-2 sm:w-2.5 sm:h-2.5 bg-gold"></span>
                </span>
                <span class="text-xs sm:text-sm font-semibold">{{ toast }}</span>
            </div>
        </Transition>

        <!-- kosong -->
        <section v-if="!event" class="relative overflow-hidden min-h-[78vh] flex items-center bg-forest text-parchment px-4">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(60%_90%_at_50%_0%,theme(colors.gold/12%),transparent_60%)]"></div>
            <div class="relative mx-auto max-w-3xl text-center space-y-4 sm:space-y-5">
                <img v-if="logoOk" :src="LOGO" @error="logoOk = false" alt="SAKOMA" class="w-32 sm:w-40 md:w-52 mx-auto drop-shadow-2xl emblem-float" />
                <h1 class="font-display text-2xl sm:text-3xl md:text-5xl font-extrabold tracking-tight">SAKOMA</h1>
                <p class="text-parchment/65 text-xs sm:text-sm md:text-base max-w-xl mx-auto px-2">Papan arena akan tampil begitu ada event dipublikasikan oleh panitia.</p>
                <Link :href="route('login')" class="inline-block px-5 sm:px-6 py-2.5 sm:py-3 bg-gold text-ink rounded-lg font-bold hover:opacity-90 transition active:scale-95 text-sm sm:text-base">LOGIN →</Link>
            </div>
        </section>

        <template v-else>
            <!-- ===== HERO ===== -->
            <section class="relative overflow-hidden bg-forest text-parchment">
                <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(75%_120%_at_78%_-15%,#ffc40022,transparent_58%)]"></div>
                <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(50%_80%_at_0%_100%,theme(colors.khaki/12%),transparent_55%)]"></div>
                <div class="pointer-events-none absolute inset-0 opacity-[0.04] bg-[linear-gradient(theme(colors.parchment)_1px,transparent_1px),linear-gradient(90deg,theme(colors.parchment)_1px,transparent_1px)] bg-[size:46px_46px]"></div>
                <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-[#ffc400] via-khaki to-forest"></div>
                <div class="relative mx-auto max-w-6xl px-3 sm:px-4 md:px-6 pt-8 sm:pt-10 md:pt-14 pb-10 sm:pb-12 md:pb-16">
                    <div class="grid lg:grid-cols-[1.15fr_0.85fr] gap-6 md:gap-8 lg:gap-6 items-center">
                        <div class="text-center lg:text-left">
                            <div class="flex justify-center lg:justify-start items-end gap-1 sm:gap-2 h-6 sm:h-8 mb-2 sm:mb-3 pl-0 lg:pl-1">
                                <svg v-for="(off, i) in starArc" :key="i" :style="{ marginTop: off + 'px', animationDelay: i * 90 + 'ms' }"
                                    class="w-2.5 h-2.5 sm:w-3 sm:h-3 md:w-3.5 md:h-3.5 text-[#ffc400] twinkle drop-shadow" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2l2.9 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l7.1-1.01L12 2z" />
                                </svg>
                            </div>
                            <div class="flex flex-col sm:flex-row items-center lg:items-start gap-3 sm:gap-4 md:gap-5">
                                <img v-if="logoOk" :src="LOGO" @error="logoOk = false" alt="Logo SAKOMA"
                                    class="w-20 h-20 sm:w-24 sm:h-24 md:w-28 md:h-28 object-contain drop-shadow-2xl emblem-float flex-shrink-0" />
                                <div>
                                    <div class="text-[8px] sm:text-[10px] md:text-xs font-display font-bold tracking-[0.2em] sm:tracking-[0.3em] uppercase text-gold/90">Sako Pandu Ma'arif NU</div>
                                    <h1 class="font-display font-extrabold leading-[0.92] tracking-tight text-3xl sm:text-4xl md:text-5xl lg:text-6xl mt-0.5 sm:mt-1 text-parchment">SAKOMA</h1>
                                </div>
                            </div>
                            <p class="mt-3 sm:mt-5 font-display text-base sm:text-lg md:text-2xl font-bold text-parchment leading-snug">{{ event.nama }}</p>
                            <div class="mt-3 sm:mt-4 flex flex-wrap items-center justify-center lg:justify-start gap-2 sm:gap-3">
                                <span :class="['inline-flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-full border text-[10px] sm:text-xs md:text-sm font-semibold', liveMeta.cls]">
                                    <span class="relative flex w-1.5 h-1.5 sm:w-2 sm:h-2">
                                        <span v-if="liveMeta.pulse" class="absolute inline-flex h-full w-full rounded-full opacity-75 animate-ping" :class="liveMeta.dot"></span>
                                        <span class="relative inline-flex rounded-full w-1.5 h-1.5 sm:w-2 sm:h-2" :class="liveMeta.dot"></span>
                                    </span>
                                    {{ liveMeta.t }}
                                </span>
                                <span v-if="isLive" class="inline-flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-full border border-red-400/40 bg-red-500/15 text-red-300 text-[10px] sm:text-xs md:text-sm font-bold">
                                    <span class="relative flex w-1.5 h-1.5 sm:w-2 sm:h-2"><span class="absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75 animate-ping"></span><span class="relative inline-flex rounded-full w-1.5 h-1.5 sm:w-2 sm:h-2 bg-red-500"></span></span>
                                    SIARAN LANGSUNG
                                </span>
                                <span class="text-parchment/70 text-[10px] sm:text-xs md:text-sm">🗓️ {{ fmt(event.tanggal_pelaksanaan_mulai) }} – {{ fmt(event.tanggal_pelaksanaan_selesai) }}</span>
                            </div>
                            <div class="mt-4 sm:mt-6 flex flex-wrap justify-center lg:justify-start gap-2 sm:gap-3">
                                <a href="#klasemen" class="px-4 sm:px-5 py-2 sm:py-2.5 bg-gold text-ink rounded-lg font-bold hover:opacity-90 transition active:scale-95 text-xs sm:text-sm md:text-base">🏆 REKAP MEDALI</a>
                                <a href="#leaderboard" class="px-4 sm:px-5 py-2 sm:py-2.5 border border-parchment/25 text-parchment rounded-lg font-semibold hover:bg-white/5 transition text-xs sm:text-sm md:text-base">HASIL LOMBA</a>
                            </div>
                        </div>
                        <div class="relative mt-6 lg:mt-0" data-reveal>
                            <div class="rounded-2xl sm:rounded-3xl border border-white/12 bg-white/[0.05] backdrop-blur-sm p-4 sm:p-5 md:p-6 shadow-2xl">
                                <div class="text-[8px] sm:text-[10px] uppercase tracking-[0.2em] sm:tracking-[0.25em] text-gold/90 font-bold text-center sm:text-left">Sorotan Event</div>
                                <div class="mt-3 sm:mt-4 grid grid-cols-2 gap-2 sm:gap-3">
                                    <div class="rounded-xl bg-white/[0.05] p-2.5 sm:p-3">
                                        <div class="text-[8px] sm:text-[10px] uppercase tracking-wider text-parchment/45 font-semibold">🏫 Pangkalan</div>
                                        <div class="font-mono font-extrabold text-xl sm:text-2xl md:text-3xl text-gold tabular-nums">{{ animated.pangkalan }}</div>
                                    </div>
                                    <div class="rounded-xl bg-white/[0.05] p-2.5 sm:p-3">
                                        <div class="text-[8px] sm:text-[10px] uppercase tracking-wider text-parchment/45 font-semibold">🚩 Regu</div>
                                        <div class="font-mono font-extrabold text-xl sm:text-2xl md:text-3xl text-gold tabular-nums">{{ animated.regu }}</div>
                                    </div>
                                    <div class="rounded-xl bg-white/[0.05] p-2.5 sm:p-3">
                                        <div class="text-[8px] sm:text-[10px] uppercase tracking-wider text-parchment/45 font-semibold">🏅 Lomba</div>
                                        <div class="font-mono font-extrabold text-xl sm:text-2xl md:text-3xl text-gold tabular-nums">{{ animated.lomba }}</div>
                                    </div>
                                    <div class="rounded-xl bg-white/[0.05] p-2.5 sm:p-3">
                                        <div class="text-[8px] sm:text-[10px] uppercase tracking-wider text-parchment/45 font-semibold">⚖️ Juri</div>
                                        <div class="font-mono font-extrabold text-xl sm:text-2xl md:text-3xl text-gold tabular-nums">{{ animated.juri }}</div>
                                    </div>
                                </div>
                                <div class="mt-3 sm:mt-4 rounded-xl bg-gold/10 border border-gold/25 px-3 sm:px-4 py-2.5 sm:py-3">
                                    <div class="text-[8px] sm:text-[10px] uppercase tracking-wider text-gold/90 font-semibold">Pelaksanaan</div>
                                    <div class="text-parchment text-xs sm:text-sm font-semibold mt-0.5">{{ fmt(event.tanggal_pelaksanaan_mulai) }}<span v-if="event.tanggal_pelaksanaan_mulai !== event.tanggal_pelaksanaan_selesai"> – {{ fmt(event.tanggal_pelaksanaan_selesai) }}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ===== TENTANG ===== -->
            <section id="tentang" class="relative bg-parchment overflow-hidden">
                <div class="pointer-events-none absolute -right-10 top-10 text-[120px] sm:text-[180px] md:text-[260px] leading-none opacity-[0.04] font-display font-extrabold text-forest select-none">⚜</div>
                <div class="relative mx-auto max-w-6xl px-3 sm:px-4 md:px-6 py-10 sm:py-14 md:py-20">
                    <div class="grid md:grid-cols-[0.9fr_1.1fr] gap-8 md:gap-10 lg:gap-14 items-center">
                        <div class="relative flex justify-center order-2 md:order-1" data-reveal>
                            <div class="absolute inset-0 m-auto w-40 h-40 sm:w-56 sm:h-56 md:w-64 md:h-64 lg:w-72 lg:h-72 rounded-full bg-[radial-gradient(circle,#ffc40033,transparent_70%)] blur-xl"></div>
                            <img v-if="logoOk" :src="LOGO" @error="logoOk = false" alt="SAKOMA" class="relative w-40 sm:w-48 md:w-56 lg:w-72 object-contain drop-shadow-xl emblem-float" />
                        </div>
                        <div class="order-1 md:order-2" data-reveal>
                            <div class="text-[10px] sm:text-xs font-display font-bold tracking-[0.2em] sm:tracking-[0.3em] uppercase text-khaki">Tentang Kegiatan</div>
                            <h2 class="font-display text-2xl sm:text-3xl md:text-4xl font-extrabold text-forest mt-1 leading-tight">Membentuk Kader Muda Ma'arif NU</h2>
                            <p class="mt-3 sm:mt-4 text-ink/75 text-xs sm:text-sm md:text-base leading-relaxed">
                                <strong class="text-forest">Perkemahan Prestasi Ma'arif Nahdlatul Ulama (PERSIMANU)</strong> merupakan kegiatan pembinaan peserta didik di lingkungan LP Ma'arif NU yang mengintegrasikan pendidikan kepramukaan, penguatan karakter <em>Ahlussunnah wal Jama'ah An-Nahdliyah</em>, kepemimpinan, kemandirian, serta pengembangan prestasi akademik dan nonakademik.
                            </p>
                            <p class="mt-2 sm:mt-3 text-ink/75 text-xs sm:text-sm md:text-base leading-relaxed">
                                Bertepatan dengan peringatan <strong class="text-forest">Hari Lahir LP Ma'arif NU</strong>, pelaksanaan PERSIMANU diharapkan mampu melahirkan kader-kader muda yang berkarakter religius, berjiwa nasionalis, disiplin, mandiri, kreatif, peduli terhadap sesama dan lingkungan, serta memiliki semangat berkompetisi secara sportif.
                            </p>
                            <a href="#nilai" class="inline-flex items-center gap-2 mt-4 sm:mt-5 text-forest font-semibold text-xs sm:text-sm hover:text-gold transition-colors">Kenali nilai karakter SiELANG <span>→</span></a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ===== SiELANG ===== -->
            <section id="nilai" class="relative bg-forest text-parchment border-y border-white/5 overflow-hidden">
                <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(60%_100%_at_50%_0%,theme(colors.gold/7%),transparent_60%)]"></div>
                <div class="relative mx-auto max-w-6xl px-3 sm:px-4 md:px-6 py-10 sm:py-14 md:py-20">
                    <div class="text-center max-w-2xl mx-auto mb-8 sm:mb-10 md:mb-12" data-reveal>
                        <div class="text-[10px] sm:text-xs font-display font-bold tracking-[0.2em] sm:tracking-[0.3em] uppercase text-gold/90">Karakter Unggulan</div>
                        <h2 class="font-display text-2xl sm:text-3xl md:text-5xl font-extrabold mt-1 text-white">SiELANG</h2>
                        <p class="text-parchment/60 text-xs sm:text-sm mt-1.5 sm:mt-2">Siswa Ma'arif Berprestasi, Empatik, Luhur, Adaptif, Nasionalis &amp; Gigih.</p>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2 sm:gap-3 md:gap-4">
                        <div v-for="(n, i) in nilaiSielang" :key="n.h" data-reveal :style="{ transitionDelay: i * 70 + 'ms' }"
                            class="group relative rounded-2xl border border-white/10 bg-white/[0.04] p-3 sm:p-4 md:p-5 overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:border-gold/40 hover:bg-white/[0.07]">
                            <span class="pointer-events-none absolute -right-2 -top-3 sm:-right-3 sm:-top-4 font-display font-extrabold text-5xl sm:text-6xl md:text-7xl lg:text-8xl text-white/[0.04] group-hover:text-gold/10 transition-colors select-none">{{ n.h }}</span>
                            <div :class="['relative w-10 h-10 sm:w-11 sm:h-11 md:w-12 md:h-12 rounded-xl bg-gradient-to-br flex items-center justify-center text-xl sm:text-2xl shadow-lg group-hover:scale-110 transition-transform duration-300', n.c]">{{ n.e }}</div>
                            <h3 class="relative mt-2 sm:mt-3 font-display font-bold text-base sm:text-lg md:text-xl text-white group-hover:text-gold transition-colors">{{ n.t }}</h3>
                            <p class="relative mt-1 text-[10px] sm:text-xs md:text-sm text-parchment/60 leading-relaxed">{{ n.d }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ===== REKAP MEDALI (dengan FILTER GOLONGAN server-side) ===== -->
            <section id="klasemen" class="relative bg-forest text-parchment">
                <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(60%_100%_at_50%_0%,theme(colors.gold/6%),transparent_60%)]"></div>
                <div class="relative mx-auto max-w-5xl px-3 sm:px-4 md:px-6 py-10 sm:py-12 md:py-16">
                    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 sm:gap-4 mb-5 sm:mb-6 md:mb-8" data-reveal>
                        <div>
                            <div class="text-[10px] sm:text-xs font-display font-bold tracking-[0.2em] sm:tracking-[0.3em] uppercase text-gold/90">Klasemen Akhir</div>
                            <h2 class="font-display text-2xl sm:text-3xl md:text-4xl font-extrabold mt-1">REKAP MEDALI</h2>
                        </div>
                        <span class="text-[10px] sm:text-xs text-parchment/45">Perolehan Medali</span>
                    </div>

                    <!-- ✅ FILTER GOLONGAN (server-side) -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-3 mb-4 sm:mb-6" data-reveal>
                        <label class="text-xs sm:text-sm font-semibold text-parchment/70 whitespace-nowrap">Filter:</label>
                        <select
                            :value="selectedGolongan"
                            @change="filterByGolongan"
                            class="w-full sm:w-auto bg-white/10 border border-white/20 text-parchment rounded-lg px-3 py-2 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-gold/50 backdrop-blur-sm"
                        >
                            <option value="" class="text-ink">Semua Golongan</option>
                            <option v-for="g in GOL_OPTIONS" :key="g.value" :value="g.value" class="text-ink">{{ g.label }}</option>
                        </select>
                    </div>

                    <div v-if="medalRows.length === 0" class="rounded-2xl border border-white/10 bg-white/[0.03] p-6 sm:p-8 md:p-12 text-center" data-reveal>
                        <div class="text-4xl sm:text-5xl mb-2 sm:mb-3">🏆</div>
                        <p class="font-display text-base sm:text-lg md:text-xl font-bold text-parchment/80">Klasemen belum dibekukan</p>
                        <p class="text-parchment/50 text-[10px] sm:text-xs md:text-sm mt-1 max-w-md mx-auto">Papan medali muncul setelah panitia menghitung &amp; menyimpan juara di tiap lomba.</p>
                    </div>

                    <!-- tabel medal-table responsive -->
                    <div v-else class="rounded-2xl border border-white/10 bg-white/[0.04] overflow-hidden shadow-2xl" data-reveal>
                        <div class="grid grid-cols-[36px_1fr_40px_40px_40px_48px] sm:grid-cols-[44px_1fr_56px_56px_56px_64px] md:grid-cols-[56px_1fr_72px_72px_72px_80px] items-center gap-0.5 sm:gap-1 px-2 sm:px-3 md:px-5 py-2 sm:py-3 border-b border-white/10 bg-white/[0.03] text-[8px] sm:text-[10px] md:text-xs font-bold uppercase tracking-wider text-parchment/55">
                            <div class="text-center">Rank</div>
                            <div>Pangkalan / Madrasah</div>
                            <div class="text-center text-base sm:text-lg md:text-xl leading-none">🥇</div>
                            <div class="text-center text-base sm:text-lg md:text-xl leading-none">🥈</div>
                            <div class="text-center text-base sm:text-lg md:text-xl leading-none">🥉</div>
                            <div class="text-center">Total</div>
                        </div>
                        <div
                            v-for="(r, i) in medalRows"
                            :key="r.kontingen_id"
                            :style="{ transitionDelay: i * 40 + 'ms' }"
                            class="medal-row grid grid-cols-[36px_1fr_40px_40px_40px_48px] sm:grid-cols-[44px_1fr_56px_56px_56px_64px] md:grid-cols-[56px_1fr_72px_72px_72px_80px] items-center gap-0.5 sm:gap-1 px-2 sm:px-3 md:px-5 py-2 sm:py-3 border-b border-white/[0.06] last:border-0 transition-colors"
                            :class="r.rank === 1 ? 'bg-gold/10' : ''"
                        >
                            <div class="text-center">
                                <span v-if="r.rank <= 3" class="text-lg sm:text-xl md:text-2xl">{{ medalEmoji[rankMedal[r.rank]] }}</span>
                                <span v-else class="font-mono font-bold text-parchment/45 tabular-nums text-xs sm:text-sm">{{ r.rank }}.</span>
                            </div>
                            <div class="min-w-0 pr-1 sm:pr-2">
                                <div class="font-display font-bold text-xs sm:text-sm md:text-base truncate" :class="r.rank === 1 ? 'text-gold' : 'text-parchment'">{{ r.team_name }}</div>
                                <div class="text-[8px] sm:text-[10px] md:text-xs text-parchment/40 truncate">{{ r.jenjang || '' }}</div>
                            </div>
                            <div class="text-center font-mono font-bold tabular-nums text-gold text-xs sm:text-sm md:text-base">{{ r.emas }}</div>
                            <div class="text-center font-mono font-bold tabular-nums text-slate-300 text-xs sm:text-sm md:text-base">{{ r.perak }}</div>
                            <div class="text-center font-mono font-bold tabular-nums text-khaki text-xs sm:text-sm md:text-base">{{ r.perunggu }}</div>
                            <div class="text-center font-mono font-extrabold tabular-nums text-parchment text-sm sm:text-base md:text-lg">{{ r.total }}</div>
                        </div>
                        <div class="grid grid-cols-[36px_1fr_40px_40px_40px_48px] sm:grid-cols-[44px_1fr_56px_56px_56px_64px] md:grid-cols-[56px_1fr_72px_72px_72px_80px] items-center gap-0.5 sm:gap-1 px-2 sm:px-3 md:px-5 py-2 sm:py-3 bg-white/[0.05] text-[10px] sm:text-xs font-bold uppercase tracking-wider text-parchment/55">
                            <div></div>
                            <div class="text-parchment/70 text-[10px] sm:text-xs">Total Medali</div>
                            <div class="text-center font-mono text-gold text-xs sm:text-sm">{{ totalMedalAll.emas }}</div>
                            <div class="text-center font-mono text-slate-300 text-xs sm:text-sm">{{ totalMedalAll.perak }}</div>
                            <div class="text-center font-mono text-khaki text-xs sm:text-sm">{{ totalMedalAll.perunggu }}</div>
                            <div class="text-center font-mono text-parchment text-xs sm:text-sm">{{ totalMedalAll.total }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ===== HASIL LOMBA (dengan 3 FILTER DEPENDENT client-side) ===== -->
            <section id="leaderboard" class="bg-parchment">
                <div class="mx-auto max-w-6xl px-3 sm:px-4 md:px-6 py-10 sm:py-12 md:py-16">
                    <div class="mb-5 sm:mb-6 md:mb-8" data-reveal>
                        <div class="text-[10px] sm:text-xs font-display font-bold tracking-[0.2em] sm:tracking-[0.3em] uppercase text-khaki">Peringkat Nilai</div>
                        <h2 class="font-display text-2xl sm:text-3xl md:text-4xl font-extrabold text-forest mt-1">HASIL LOMBA</h2>
                    </div>

                    <!-- ✅ 3 DROPDOWN FILTER DEPENDENT -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5 sm:mb-6" data-reveal>
                        <div>
                            <label class="block text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider mb-1">Kategori</label>
                            <select v-model="filterKategori"
                                class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gold bg-white">
                                <option value="">Semua Kategori</option>
                                <option value="PA">Putra (PA)</option>
                                <option value="PI">Putri (PI)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider mb-1">Golongan</label>
                            <select v-model="filterGolonganHasil"
                                class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gold bg-white">
                                <option value="">Semua Golongan</option>
                                <option v-for="g in GOL_OPTIONS" :key="g.value" :value="g.value">{{ g.label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider mb-1">Jenis Lomba</label>
                            <select v-model="filterLombaId"
                                class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gold bg-white">
                                <option v-for="b in filteredLombaOptions" :key="b.lomba.id" :value="b.lomba.id">{{ b.lomba.nama }}</option>
                            </select>
                        </div>
                    </div>

                    <div v-if="leaderboards.length === 0" class="bg-white rounded-2xl border border-line shadow-sm p-8 sm:p-10 text-center text-ink/50" data-reveal>
                        <div class="text-4xl sm:text-5xl mb-2 sm:mb-3">📭</div>
                        <p class="text-sm sm:text-base">Belum ada penilaian masuk.</p>
                    </div>

                    <div v-else class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden" data-reveal>
                        <div v-if="currentBoard.rows.length === 0" class="p-8 sm:p-10 text-center text-ink/50 text-sm sm:text-base">
                            Belum ada data penilaian untuk filter yang dipilih.
                        </div>
                        <div v-else class="divide-y divide-line/60">
                            <div v-for="(row, i) in currentBoard.rows" :key="row.kontingen_id">
                                <div @click="toggleRow(currentBoard.lomba.id + '-' + row.kontingen_id)"
                                    class="flex items-center gap-2 sm:gap-3 md:gap-4 px-3 sm:px-4 md:px-6 py-2.5 sm:py-3 md:py-4 cursor-pointer hover:bg-parchment/40 transition-colors">
                                    <div class="w-6 sm:w-8 md:w-9 flex-shrink-0 text-center">
                                        <span v-if="row.rank <= 3" class="text-xl sm:text-2xl md:text-3xl">{{ medalEmoji[rankMedal[row.rank]] }}</span>
                                        <span v-else class="font-mono font-bold text-ink/40 text-sm sm:text-base md:text-lg tabular-nums">{{ row.rank }}</span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="font-semibold text-forest text-xs sm:text-sm md:text-base truncate">{{ row.team_name }}</span>
                                            <span class="font-mono font-extrabold text-sm sm:text-base md:text-lg tabular-nums flex-shrink-0"
    :class="row.nilai_akhir !== null
        ? (row.rank <= 3 ? (rankMedal[row.rank]==='emas'?'text-gold':rankMedal[row.rank]==='perak'?'text-slate-500':'text-khaki') : 'text-forest')
        : 'text-ink/30 italic'">
    {{ row.nilai_akhir !== null ? row.nilai_akhir : 'Belum dinilai' }}
</span>
                                        </div>
                                        <div class="mt-1 flex flex-wrap items-center gap-1.5 sm:gap-2">
                                            <div class="flex-1 min-w-[40px] h-1.5 sm:h-2 rounded-full bg-line overflow-hidden">
                                                <div class="h-full rounded-full bg-gradient-to-r from-forest/70 to-forest transition-all duration-700 ease-out"
                                                    :style="{ width: (barsReady && row.nilai_akhir !== null ? Math.max(4, (Number(row.nilai_akhir) / maxBoardNilai) * 100) : 0) + '%' }"></div>
                                            </div>
                                            <span v-if="row.kategori" class="text-[8px] sm:text-[10px] px-1.5 sm:px-2 py-0.5 rounded-full bg-gold/10 text-gold font-semibold uppercase whitespace-nowrap">{{ row.kategori }}</span>
                                            <span v-if="row.golongan" class="text-[8px] sm:text-[10px] px-1.5 sm:px-2 py-0.5 rounded-full bg-forest/10 text-forest font-semibold uppercase whitespace-nowrap">{{ row.golongan }}</span>
                                            <span class="text-[8px] sm:text-[10px] md:text-xs text-ink/45 whitespace-nowrap">⚖️ {{ row.jumlah_juri }}</span>
                                        </div>
                                    </div>
                                    <svg :class="['w-3 h-3 sm:w-4 sm:h-4 text-ink/40 transition-transform duration-300 flex-shrink-0', openRow === currentBoard.lomba.id + '-' + row.kontingen_id ? 'rotate-90' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </div>
                                <Transition name="expand">
                                    <div v-if="openRow === currentBoard.lomba.id + '-' + row.kontingen_id" class="px-3 sm:px-4 md:px-6 pb-3 sm:pb-4 bg-parchment/30 border-t border-line/40">
                                        <div class="pt-2 sm:pt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-1.5 sm:gap-2">
                                            <div v-for="(j, ji) in row.juri_scores" :key="ji" class="flex items-center justify-between gap-2 bg-white rounded-lg border border-line/60 px-2 sm:px-3 py-1.5 sm:py-2">
                                                <span class="text-xs sm:text-sm text-forest truncate">{{ j.nama }}</span>
                                                <span class="font-mono font-bold text-forest tabular-nums text-xs sm:text-sm">{{ j.nilai }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </Transition>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ===== INFORMASI PANITIA (Poin 20 - Menu Baru) ===== -->
<section id="info-panitia" class="bg-parchment border-t border-line">
    <div class="mx-auto max-w-6xl px-3 sm:px-4 md:px-6 py-10 sm:py-12 md:py-16">
        <div class="mb-6 sm:mb-8" data-reveal>
            <div class="text-[10px] sm:text-xs font-display font-bold tracking-[0.2em] sm:tracking-[0.3em] uppercase text-khaki">Panitia Pelaksana</div>
            <h2 class="font-display text-2xl sm:text-3xl md:text-4xl font-extrabold text-forest mt-1">Informasi &amp; Pengumuman</h2>
        </div>
        <div class="grid md:grid-cols-2 gap-4 sm:gap-6" data-reveal>
            <div class="bg-white rounded-2xl border border-line shadow-sm p-5 sm:p-6">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-10 h-10 rounded-full bg-forest/10 text-forest flex items-center justify-center text-xl">📢</span>
                    <h3 class="font-display font-bold text-forest text-lg">Pengumuman Terbaru</h3>
                </div>
                <p class="text-sm text-ink/60 leading-relaxed">
                    Informasi penting terkait pelaksanaan kegiatan akan disampaikan melalui saluran resmi panitia. Pastikan operator memantau halaman ini secara berkala.
                </p>
            </div>
            <div class="bg-white rounded-2xl border border-line shadow-sm p-5 sm:p-6">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-10 h-10 rounded-full bg-gold/10 text-gold flex items-center justify-center text-xl">📋</span>
                    <h3 class="font-display font-bold text-forest text-lg">Panduan Teknis</h3>
                </div>
                <p class="text-sm text-ink/60 leading-relaxed">
                    Petunjuk teknis pendaftaran, verifikasi dokumen, dan pelaksanaan lomba tersedia untuk diunduh. Hubungi panitia jika memerlukan klarifikasi lebih lanjut.
                </p>
            </div>
        </div>
    </div>
</section>
            <!-- ===== JADWAL ===== -->
            <section id="jadwal" class="bg-white border-t border-line">
                <div class="mx-auto max-w-6xl px-3 sm:px-4 md:px-6 py-10 sm:py-12 md:py-16">
                    <div class="mb-6 sm:mb-8 md:mb-10" data-reveal>
                        <div class="text-[10px] sm:text-xs font-display font-bold tracking-[0.2em] sm:tracking-[0.3em] uppercase text-khaki">Alur Kegiatan</div>
                        <h2 class="font-display text-2xl sm:text-3xl md:text-4xl font-extrabold text-forest mt-1">Jadwal &amp; Fase</h2>
                    </div>
                    <div class="relative grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 md:gap-6">
                        <span class="hidden sm:block absolute left-0 right-0 top-7 h-px bg-line"></span>
                        <div v-for="(p, i) in phases" :key="p.key" data-reveal :style="{ transitionDelay: i * 90 + 'ms' }"
                            :class="['relative rounded-2xl border p-3 sm:p-4 md:p-5 transition-all duration-300', p.active ? 'border-gold bg-gold/5 shadow-lg shadow-gold/10' : 'border-line bg-parchment/40']">
                            <div class="flex items-center gap-2 sm:gap-3">
                                <span class="relative z-10 w-10 h-10 sm:w-11 sm:h-11 md:w-12 md:h-12 rounded-full flex items-center justify-center text-lg sm:text-xl flex-shrink-0" :class="p.active ? 'bg-gold text-ink' : 'bg-white border border-line text-forest'">
                                    <span v-if="p.active" class="absolute inline-flex h-full w-full rounded-full bg-gold/40 animate-ping"></span>
                                    <span class="relative">{{ p.icon }}</span>
                                </span>
                                <div>
                                    <div class="text-[8px] sm:text-[10px] uppercase tracking-wider font-semibold" :class="p.active ? 'text-gold' : 'text-ink/40'">Fase {{ i + 1 }}</div>
                                    <div class="font-display font-bold text-forest text-sm sm:text-base md:text-lg">{{ p.label }}</div>
                                </div>
                            </div>
                            <div class="mt-2 sm:mt-3 text-[10px] sm:text-xs md:text-sm text-ink/60">{{ fmt(p.mulai) }}<span v-if="p.mulai !== p.selesai"> – {{ fmt(p.selesai) }}</span></div>
                            <span v-if="p.active" class="inline-block mt-1.5 sm:mt-2 text-[8px] sm:text-[10px] font-bold uppercase tracking-wider text-gold">● Berlangsung</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ===== CTA ===== -->
            <section class="relative bg-forest text-parchment overflow-hidden">
                <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(50%_120%_at_100%_50%,theme(colors.gold/10%),transparent_60%)]"></div>
                <div class="relative mx-auto max-w-6xl px-3 sm:px-4 md:px-6 py-8 sm:py-10 md:py-12 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-center sm:text-left">
                        <div class="font-display text-xl sm:text-2xl md:text-3xl font-extrabold">Panitia, Juri, atau Peserta?</div>
                        <p class="text-parchment/60 text-xs sm:text-sm mt-1 max-w-lg mx-auto sm:mx-0">Masuk ke dasbor untuk mengelola pendaftaran, verifikasi, penilaian, dan rekap juara.</p>
                    </div>
                    <Link :href="route('login')" class="px-5 sm:px-6 py-2.5 sm:py-3 bg-gold text-ink rounded-lg font-bold hover:opacity-90 transition active:scale-95 whitespace-nowrap text-sm sm:text-base">LOGIN →</Link>
                </div>
            </section>
        </template>
    </PublicLayout>
</template>

<style scoped>
[data-reveal] { opacity: 0; transform: translateY(18px); transition: opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1), transform 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
[data-reveal].is-visible { opacity: 1; transform: none; }
.emblem-float { animation: emblemFloat 6s ease-in-out infinite; }
@keyframes emblemFloat { 0%, 100% { transform: translateY(0) scale(1); } 50% { transform: translateY(-6px) scale(1.015); } }
.twinkle { animation: twinkle 2.4s ease-in-out infinite; }
@keyframes twinkle { 0%, 100% { opacity: 0.45; transform: scale(0.85); } 50% { opacity: 1; transform: scale(1.1); } }
.medal-row:hover { background: rgba(246,241,228,0.06); }
.expand-enter-active, .expand-leave-active { transition: all 0.3s ease; overflow: hidden; }
.expand-enter-from, .expand-leave-to { opacity: 0; max-height: 0; }
.expand-enter-to, .expand-leave-from { opacity: 1; max-height: 500px; }
.toast-enter-active, .toast-leave-active { transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1); }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translate(-50%, -16px); }
</style>