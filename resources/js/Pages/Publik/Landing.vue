<script setup>
import { Head, Link } from "@inertiajs/vue3";
import PublicLayout from "@/Layouts/PublicLayout.vue";
import { ref, reactive, computed, onMounted, nextTick } from "vue";
import { LOGO, MASKOT } from "@/brand";
import { useLiveUpdates } from "@/composables/useLiveUpdates";
const props = defineProps({
    event: { type: Object, default: null },
    liveStatus: { type: String, default: null },
    stats: { type: Object, default: null },
    phases: { type: Array, default: () => [] },
    standings: { type: Array, default: () => [] },
    leaderboards: { type: Array, default: () => [] },
});

const logoOk = ref(true);
const maskotOk = ref(true);
const { isLive, toast } = useLiveUpdates(props.event?.id ?? null);
const fmt = (v) =>
    v ? String(v).slice(0, 10).split("-").reverse().join("/") : "-";
const initials = (n) =>
    (n || "?")
        .split(" ")
        .slice(0, 2)
        .map((x) => x[0])
        .join("")
        .toUpperCase();

// 6 pilar nilai SiELANG (konten brand, statis)
const nilaiSielang = [
    {
        h: "B",
        t: "Berprestasi",
        d: "Bersemangat meraih prestasi akademik & nonakademik.",
        e: "🏅",
        c: "from-gold to-amber-600",
    },
    {
        h: "E",
        t: "Empatik",
        d: "Peduli terhadap sesama dan lingkungan sekitar.",
        e: "🤝",
        c: "from-forest to-emerald-700",
    },
    {
        h: "L",
        t: "Luhur",
        d: "Berakhlak & berkarakter Ahlussunnah wal Jamaah.",
        e: "✨",
        c: "from-khaki to-amber-800",
    },
    {
        h: "A",
        t: "Adaptif",
        d: "Mandiri, kreatif, dan tangguh menghadapi perubahan.",
        e: "🧭",
        c: "from-forest to-teal-700",
    },
    {
        h: "N",
        t: "Nasionalis",
        d: "Berjiwa kebangsaan & cinta tanah air.",
        e: "🇮",
        c: "from-red-600 to-red-800",
    },
    {
        h: "G",
        t: "Gigih",
        d: "Disiplin & pantang menyerah dalam berkompetisi.",
        e: "💪",
        c: "from-gold to-khaki",
    },
];

// busur bintang (meng-echo lengkungan 9 bintang di logo)
const starArc = [20, 11, 5, 1, 0, 1, 5, 11, 20];

const liveMeta = computed(
    () =>
        ({
            live: {
                t: "Sedang Berlangsung",
                cls: "bg-green-500/15 text-green-300 border-green-400/40",
                dot: "bg-green-400",
                pulse: true,
            },
            upcoming: {
                t: "Segera Dimulai",
                cls: "bg-gold/15 text-gold border-gold/40",
                dot: "bg-gold",
                pulse: false,
            },
            ended: {
                t: "Selesai",
                cls: "bg-white/10 text-parchment/70 border-white/20",
                dot: "bg-parchment/50",
                pulse: false,
            },
        })[props.liveStatus] || {
            t: "-",
            cls: "bg-white/10 text-parchment/70 border-white/20",
            dot: "bg-parchment/50",
            pulse: false,
        },
);

// stat count-up
const animated = reactive({ pangkalan: 0, regu: 0, lomba: 0, juri: 0 });
const countUp = (key, target) => {
    const dur = 1100,
        t0 = performance.now();
    const step = (t) => {
        const p = Math.min(1, (t - t0) / dur);
        animated[key] = Math.round((target || 0) * (1 - Math.pow(1 - p, 3)));
        if (p < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
};

const barsReady = ref(false);

// tab leaderboard
const activeLombaId = ref(props.leaderboards[0]?.lomba?.id ?? null);
const currentBoard = computed(
    () =>
        props.leaderboards.find((b) => b.lomba.id === activeLombaId.value) ||
        props.leaderboards[0] || { rows: [] },
);
const maxBoardNilai = computed(() =>
    currentBoard.value.rows.length
        ? Math.max(...currentBoard.value.rows.map((r) => Number(r.nilai_akhir)))
        : 100,
);

const openRow = ref(null);
const toggleRow = (key) => {
    openRow.value = openRow.value === key ? null : key;
};

const medal = {
    emas: {
        emoji: "🥇",
        chip: "bg-gold/15 text-gold border-gold/40",
        seg: "bg-gold",
        text: "text-gold",
    },
    perak: {
        emoji: "🥈",
        chip: "bg-slate-200/15 text-slate-200 border-slate-300/40",
        seg: "bg-slate-300",
        text: "text-slate-200",
    },
    perunggu: {
        emoji: "🥉",
        chip: "bg-khaki/20 text-khaki border-khaki/40",
        seg: "bg-khaki",
        text: "text-khaki",
    },
};
const rankMedal = { 1: "emas", 2: "perak", 3: "perunggu" };
const maxPoin = computed(() =>
    props.standings.length
        ? Math.max(...props.standings.map((r) => Number(r.poin)))
        : 1,
);
const segW = (row, k) => {
    const t = row.emas + row.perak + row.perunggu;
    return t ? (row[k] / t) * 100 : 0;
};

onMounted(() => {
    if (props.stats)
        ["pangkalan", "regu", "lomba", "juri"].forEach((k) =>
            countUp(k, props.stats[k]),
        );
    nextTick(() =>
        setTimeout(() => {
            barsReady.value = true;
        }, 80),
    );

    const io = new IntersectionObserver(
        (entries) => {
            entries.forEach((e) => {
                if (e.isIntersecting) {
                    e.target.classList.add("is-visible");
                    io.unobserve(e.target);
                }
            });
        },
        { threshold: 0.12 },
    );
    document.querySelectorAll("[data-reveal]").forEach((el) => io.observe(el));
});
</script>

<template>
    <PublicLayout :event-name="event?.nama ?? 'PERSIMANU'">
        <Head
            :title="
                event
                    ? `${event.nama} • Papan Arena`
                    : 'PERSIMANU Jepara • Papan Arena'
            "
        />

        <!-- ===== TOAST LIVE UPDATE ===== -->
        <Transition name="toast">
            <div
                v-if="toast"
                class="no-print fixed top-20 left-1/2 -translate-x-1/2 z-50 flex items-center gap-3 bg-forest text-parchment pl-4 pr-5 py-3 rounded-2xl shadow-2xl border border-gold/30"
            >
                <span class="relative flex w-2.5 h-2.5">
                    <span
                        class="absolute inline-flex h-full w-full rounded-full bg-gold opacity-75 animate-ping"
                    ></span>
                    <span
                        class="relative inline-flex rounded-full w-2.5 h-2.5 bg-gold"
                    ></span>
                </span>
                <span class="text-sm font-semibold">{{ toast }}</span>
            </div>
        </Transition>

        <!-- ============ KOSONG ============ -->
        <section
            v-if="!event"
            class="relative overflow-hidden min-h-[78vh] flex items-center bg-forest text-parchment px-4"
        >
            <div
                class="pointer-events-none absolute inset-0 bg-[radial-gradient(60%_90%_at_50%_0%,theme(colors.gold/12%),transparent_60%)]"
            ></div>
            <div class="relative mx-auto max-w-3xl text-center space-y-5">
                <img
                    v-if="logoOk"
                    :src="LOGO"
                    @error="logoOk = false"
                    alt="PERSIMANU"
                    class="w-40 sm:w-52 mx-auto drop-shadow-2xl emblem-float"
                />
                <div v-else class="text-7xl emblem-float">⚜</div>
                <h1
                    class="font-display text-3xl sm:text-5xl font-extrabold tracking-tight"
                >
                    PERSIMANU Jepara
                </h1>
                <p
                    class="text-parchment/65 text-sm sm:text-base max-w-xl mx-auto"
                >
                    Papan arena akan tampil begitu ada event dipublikasikan oleh
                    panitia.
                </p>
                <Link
                    :href="route('login')"
                    class="inline-block px-6 py-3 bg-gold text-ink rounded-lg font-bold hover:opacity-90 transition active:scale-95"
                    >Masuk ke Dasbor →</Link
                >
            </div>
        </section>

        <template v-else>
            <!-- ============ HERO IDENTITAS (gelap, asimetris) ============ -->
            <section class="relative overflow-hidden bg-forest text-parchment">
                <div
                    class="pointer-events-none absolute inset-0 bg-[radial-gradient(75%_120%_at_78%_-15%,#ffc40022,transparent_58%)]"
                ></div>
                <div
                    class="pointer-events-none absolute inset-0 bg-[radial-gradient(50%_80%_at_0%_100%,theme(colors.khaki/12%),transparent_55%)]"
                ></div>
                <div
                    class="pointer-events-none absolute inset-0 opacity-[0.04] bg-[linear-gradient(theme(colors.parchment)_1px,transparent_1px),linear-gradient(90deg,theme(colors.parchment)_1px,transparent_1px)] bg-[size:46px_46px]"
                ></div>
                <div
                    class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-[#ffc400] via-khaki to-forest"
                ></div>

                <div
                    class="relative mx-auto max-w-6xl px-4 sm:px-6 pt-10 sm:pt-14 pb-12 sm:pb-16"
                >
                    <div
                        class="grid lg:grid-cols-[1.15fr_0.85fr] gap-8 lg:gap-6 items-center"
                    >
                        <!-- KOLOM KIRI: emblem + identitas -->
                        <div>
                            <div
                                class="flex justify-center lg:justify-start items-end gap-1.5 sm:gap-2 h-8 mb-3 pl-1"
                            >
                                <svg
                                    v-for="(off, i) in starArc"
                                    :key="i"
                                    :style="{
                                        marginTop: off + 'px',
                                        animationDelay: i * 90 + 'ms',
                                    }"
                                    class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-[#ffc400] twinkle drop-shadow"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                >
                                    <path
                                        d="M12 2l2.9 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l7.1-1.01L12 2z"
                                    />
                                </svg>
                            </div>

                            <div class="flex items-center gap-4 sm:gap-5">
                                <img
                                    v-if="logoOk"
                                    :src="LOGO"
                                    @error="logoOk = false"
                                    alt="Logo PERSIMANU Jepara"
                                    class="w-48 h-48 sm:w-60 sm:h-60 lg:w-68 lg:h-68 object-contain drop-shadow-2xl emblem-float flex-shrink-0"
                                />
                                <div
                                    v-else
                                    class="w-24 h-24 sm:w-32 sm:h-32 rounded-full bg-gold/15 border border-gold/30 flex items-center justify-center text-5xl flex-shrink-0"
                                ></div>
                                <div>
                                    <div
                                        class="text-[10px] sm:text-xs font-display font-bold tracking-[0.3em] uppercase text-gold/90"
                                    >
                                        Sako Pandu Ma'arif NU
                                    </div>
                                    <h1
                                        class="font-display font-extrabold leading-[0.92] tracking-tight text-4xl sm:text-6xl mt-1"
                                    >
                                        PERSIMANU<br /><span class="text-gold"
                                            >JEPARA</span
                                        >
                                    </h1>
                                </div>
                            </div>

                            <p
                                class="mt-5 text-[11px] sm:text-sm font-display font-bold tracking-[0.18em] uppercase text-parchment/55"
                            >
                                Perkemahan Prestasi Ma'arif Nahdlatul Ulama
                            </p>

                            <div class="mt-5 flex flex-wrap items-center gap-3">
                                <span
                                    :class="[
                                        'inline-flex items-center gap-2 px-3 py-1.5 rounded-full border text-xs sm:text-sm font-semibold',
                                        liveMeta.cls,
                                    ]"
                                >
                                    <span class="relative flex w-2 h-2">
                                        <span
                                            v-if="liveMeta.pulse"
                                            class="absolute inline-flex h-full w-full rounded-full opacity-75 animate-ping"
                                            :class="liveMeta.dot"
                                        ></span>
                                        <span
                                            class="relative inline-flex rounded-full w-2 h-2"
                                            :class="liveMeta.dot"
                                        ></span>
                                    </span>
                                    {{ liveMeta.t }}
                                </span>
                                <span
                                    v-if="isLive"
                                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-red-400/40 bg-red-500/15 text-red-300 text-xs sm:text-sm font-bold"
                                >
                                    <span class="relative flex w-2 h-2">
                                        <span
                                            class="absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75 animate-ping"
                                        ></span>
                                        <span
                                            class="relative inline-flex rounded-full w-2 h-2 bg-red-500"
                                        ></span>
                                    </span>
                                    SIARAN LANGSUNG
                                </span>
                                <span
                                    class="text-parchment/70 text-xs sm:text-sm"
                                    >🗓️
                                    {{ fmt(event.tanggal_pelaksanaan_mulai) }} –
                                    {{
                                        fmt(event.tanggal_pelaksanaan_selesai)
                                    }}</span
                                >
                            </div>

                            <div class="mt-6 flex flex-wrap gap-3">
                                <a
                                    href="#klasemen"
                                    class="px-5 py-2.5 bg-gold text-ink rounded-lg font-bold hover:opacity-90 transition active:scale-95 text-sm sm:text-base"
                                    >🏆 Lihat Klasemen</a
                                >
                                <a
                                    href="#tentang"
                                    class="px-5 py-2.5 border border-parchment/25 text-parchment rounded-lg font-semibold hover:bg-white/5 transition text-sm sm:text-base"
                                    >Tentang Kegiatan</a
                                >
                            </div>
                        </div>

                        <!-- KOLOM KANAN: maskot SiELANG + sapaan -->
                        <div
                            class="relative flex flex-col items-center lg:items-end"
                        >
                            <div
                                class="relative mb-3 mr-2 sm:mr-6 max-w-[260px] bg-parchment text-ink rounded-2xl rounded-br-sm px-4 py-3 shadow-xl rotate-[-1.5deg]"
                            >
                                <p class="text-sm font-semibold leading-snug">
                                    Selamat datang di PERSIMANU! 🎉<span
                                        class="block text-ink/60 font-normal text-xs mt-0.5"
                                        >Mari berkompetisi secara sportif &
                                        berprestasi.</span
                                    >
                                </p>
                            </div>
                            <img
                                v-if="maskotOk"
                                :src="MASKOT"
                                @error="maskotOk = false"
                                alt="Maskot SiELANG"
                                class="w-52 sm:w-64 lg:w-72 object-contain drop-shadow-2xl maskot-float select-none"
                                draggable="false"
                            />
                            <div
                                v-else
                                class="w-52 sm:w-64 h-64 rounded-3xl bg-white/5 border border-white/10 flex items-center justify-center text-7xl maskot-float"
                            >
                                🦅
                            </div>
                            <div class="mt-1 text-center lg:text-right">
                                <div
                                    class="font-display font-extrabold text-2xl sm:text-3xl tracking-wide text-parchment"
                                >
                                    Si<span class="text-gold">ELANG</span>
                                </div>
                                <div
                                    class="text-[10px] sm:text-xs text-parchment/55 max-w-[240px] mx-auto lg:mx-0 lg:ml-auto"
                                >
                                    Siswa Ma'arif Berprestasi, Empatik, Luhur,
                                    Adaptif, Nasionalis & Gigih
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- stat strip -->
                    <div
                        class="mt-10 sm:mt-12 grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4"
                    >
                        <div
                            v-for="(s, i) in [
                                { k: 'pangkalan', l: 'Pangkalan', i: '🏫' },
                                { k: 'regu', l: 'Regu', i: '🚩' },
                                { k: 'lomba', l: 'Cabang Lomba', i: '🏅' },
                                { k: 'juri', l: 'Juri', i: '👨‍⚖️' },
                            ]"
                            :key="s.k"
                            data-reveal
                            :style="{ transitionDelay: i * 70 + 'ms' }"
                            class="rounded-xl border border-white/10 bg-white/[0.04] p-3 sm:p-4 backdrop-blur-sm hover:bg-white/[0.07] hover:border-gold/30 transition-colors"
                        >
                            <div
                                class="flex items-center gap-1.5 text-[10px] uppercase tracking-wider text-parchment/45 font-semibold"
                            >
                                {{ s.i }} {{ s.l }}
                            </div>
                            <div
                                class="font-mono font-extrabold text-3xl sm:text-4xl tabular-nums text-gold mt-1"
                            >
                                {{ animated[s.k] }}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============ TENTANG (terang) ============ -->
            <section id="tentang" class="relative bg-parchment overflow-hidden">
                <div
                    class="pointer-events-none absolute -right-10 top-10 text-[180px] sm:text-[260px] leading-none opacity-[0.04] font-display font-extrabold text-forest select-none"
                >
                    ⚜
                </div>
                <div
                    class="relative mx-auto max-w-6xl px-4 sm:px-6 py-14 sm:py-20"
                >
                    <div
                        class="grid lg:grid-cols-[0.9fr_1.1fr] gap-10 lg:gap-14 items-center"
                    >
                        <div class="relative flex justify-center" data-reveal>
                            <div
                                class="absolute inset-0 m-auto w-56 h-56 sm:w-72 sm:h-72 rounded-full bg-[radial-gradient(circle,#ffc40033,transparent_70%)] blur-xl"
                            ></div>
                            <img
                                v-if="logoOk"
                                :src="LOGO"
                                @error="logoOk = false"
                                alt="PERSIMANU"
                                class="relative w-56 sm:w-72 object-contain drop-shadow-xl emblem-float"
                            />
                            <div
                                v-else
                                class="relative w-56 h-56 rounded-full bg-gold/15 border border-gold/30 flex items-center justify-center text-7xl"
                            >
                                ⚜
                            </div>
                        </div>
                        <div data-reveal>
                            <div
                                class="text-[10px] sm:text-xs font-display font-bold tracking-[0.3em] uppercase text-khaki"
                            >
                                Tentang Kegiatan
                            </div>
                            <h2
                                class="font-display text-3xl sm:text-4xl font-extrabold text-forest mt-1 leading-tight"
                            >
                                Membentuk Kader Muda Ma'arif NU
                            </h2>
                            <p
                                class="mt-4 text-ink/75 text-sm sm:text-base leading-relaxed"
                            >
                                <strong class="text-forest"
                                    >Perkemahan Prestasi Ma'arif Nahdlatul Ulama
                                    (PERSIMANU)</strong
                                >
                                merupakan kegiatan pembinaan peserta didik di
                                lingkungan LP Ma'arif NU yang mengintegrasikan
                                pendidikan kepramukaan, penguatan karakter
                                <em>Ahlussunnah wal Jama'ah An-Nahdliyah</em>,
                                kepemimpinan, kemandirian, serta pengembangan
                                prestasi akademik dan nonakademik.
                            </p>
                            <p
                                class="mt-3 text-ink/75 text-sm sm:text-base leading-relaxed"
                            >
                                Bertepatan dengan peringatan
                                <strong class="text-forest"
                                    >Hari Lahir LP Ma'arif NU</strong
                                >, pelaksanaan PERSIMANU diharapkan mampu
                                melahirkan kader-kader muda yang berkarakter
                                religius, berjiwa nasionalis, disiplin, mandiri,
                                kreatif, peduli terhadap sesama dan lingkungan,
                                serta memiliki semangat berkompetisi secara
                                sportif — wujud nyata komitmen LP Ma'arif NU
                                Kabupaten Jepara dalam meningkatkan mutu
                                pembinaan peserta didik.
                            </p>
                            <a
                                href="#nilai"
                                class="inline-flex items-center gap-2 mt-5 text-forest font-semibold text-sm hover:text-gold transition-colors"
                            >
                                Kenali nilai karakter SiELANG <span>→</span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============ NILAI SiELANG (gelap) ============ -->
            <section
                id="nilai"
                class="relative bg-forest text-parchment border-y border-white/5 overflow-hidden"
            >
                <div
                    class="pointer-events-none absolute inset-0 bg-[radial-gradient(60%_100%_at_50%_0%,theme(colors.gold/7%),transparent_60%)]"
                ></div>
                <div
                    class="relative mx-auto max-w-6xl px-4 sm:px-6 py-14 sm:py-20"
                >
                    <div
                        class="text-center max-w-2xl mx-auto mb-10 sm:mb-12"
                        data-reveal
                    >
                        <div
                            class="text-[10px] sm:text-xs font-display font-bold tracking-[0.3em] uppercase text-gold/90"
                        >
                            Karakter Unggulan
                        </div>
                        <h2
                            class="font-display text-3xl sm:text-5xl font-extrabold mt-1"
                        >
                            Nilai <span class="text-gold">SiELANG</span>
                        </h2>
                        <p class="text-parchment/60 text-sm mt-2">
                            Enam pilar karakter yang dihidupi setiap peserta
                            didik Ma'arif NU.
                        </p>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4">
                        <div
                            v-for="(n, i) in nilaiSielang"
                            :key="n.h"
                            data-reveal
                            :style="{ transitionDelay: i * 70 + 'ms' }"
                            class="group relative rounded-2xl border border-white/10 bg-white/[0.04] p-4 sm:p-5 overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:border-gold/40 hover:bg-white/[0.07]"
                        >
                            <span
                                class="pointer-events-none absolute -right-3 -top-4 font-display font-extrabold text-7xl sm:text-8xl text-white/[0.04] group-hover:text-gold/10 transition-colors select-none"
                                >{{ n.h }}</span
                            >
                            <div
                                :class="[
                                    'relative w-12 h-12 rounded-xl bg-gradient-to-br flex items-center justify-center text-2xl shadow-lg group-hover:scale-110 transition-transform duration-300',
                                    n.c,
                                ]"
                            >
                                {{ n.e }}
                            </div>
                            <h3
                                class="relative mt-3 font-display font-bold text-lg text-parchment group-hover:text-gold transition-colors"
                            >
                                {{ n.t }}
                            </h3>
                            <p
                                class="relative mt-1 text-xs sm:text-sm text-parchment/60 leading-relaxed"
                            >
                                {{ n.d }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============ PAPAN MEDALI (gelap lanjut) ============ -->
            <section id="klasemen" class="relative bg-forest text-parchment">
                <div
                    class="pointer-events-none absolute inset-0 bg-[radial-gradient(60%_100%_at_50%_0%,theme(colors.gold/6%),transparent_60%)]"
                ></div>
                <div
                    class="relative mx-auto max-w-6xl px-4 sm:px-6 py-12 sm:py-16"
                >
                    <div
                        class="flex items-end justify-between gap-4 mb-6 sm:mb-8"
                        data-reveal
                    >
                        <div>
                            <div
                                class="text-[10px] sm:text-xs font-display font-bold tracking-[0.3em] uppercase text-gold/90"
                            >
                                Klasemen Akhir
                            </div>
                            <h2
                                class="font-display text-3xl sm:text-4xl font-extrabold mt-1"
                            >
                                Papan Medali
                            </h2>
                        </div>
                        <span class="hidden sm:block text-xs text-parchment/45"
                            >poin = 🥇3 · 🥈2 · 🥉1</span
                        >
                    </div>

                    <div
                        v-if="standings.length === 0"
                        class="rounded-2xl border border-white/10 bg-white/[0.03] p-8 sm:p-12 text-center"
                        data-reveal
                    >
                        <div class="text-5xl mb-3">🏆</div>
                        <p
                            class="font-display text-lg sm:text-xl font-bold text-parchment/80"
                        >
                            Klasemen belum dibekukan
                        </p>
                        <p
                            class="text-parchment/50 text-xs sm:text-sm mt-1 max-w-md mx-auto"
                        >
                            Papan medali muncul setelah panitia menghitung &
                            menyimpan juara di tiap lomba. Sementara itu, lihat
                            peringkat nilai per lomba di bawah.
                        </p>
                    </div>

                    <div v-else class="space-y-2.5">
                        <div
                            v-for="(r, i) in standings"
                            :key="r.kontingen_id"
                            data-reveal
                            :style="{ transitionDelay: i * 55 + 'ms' }"
                            class="group flex items-center gap-3 sm:gap-4 rounded-xl border border-white/10 bg-white/[0.04] p-3 sm:p-4 transition-all duration-300 hover:bg-white/[0.08] hover:border-gold/40 hover:-translate-y-0.5"
                        >
                            <div class="w-10 sm:w-12 flex-shrink-0 text-center">
                                <span
                                    v-if="r.rank <= 3"
                                    class="text-3xl sm:text-4xl drop-shadow"
                                    >{{ medal[rankMedal[r.rank]].emoji }}</span
                                >
                                <span
                                    v-else
                                    class="font-mono font-extrabold text-xl sm:text-2xl text-parchment/40 tabular-nums"
                                    >{{ r.rank }}</span
                                >
                            </div>
                            <div
                                class="flex items-center gap-3 min-w-0 w-40 sm:w-56 flex-shrink-0"
                            >
                                <div
                                    class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-gold/15 text-gold flex items-center justify-center font-display font-bold text-sm flex-shrink-0 group-hover:bg-gold group-hover:text-ink transition-colors"
                                >
                                    {{ initials(r.team_name) }}
                                </div>
                                <div class="min-w-0">
                                    <div
                                        class="font-display font-bold text-parchment text-sm sm:text-base truncate group-hover:text-gold transition-colors"
                                    >
                                        {{ r.team_name }}
                                    </div>
                                    <div
                                        class="text-[10px] sm:text-xs text-parchment/45 truncate"
                                    >
                                        {{ r.jenjang || "-" }}
                                    </div>
                                </div>
                            </div>
                            <div
                                class="hidden md:flex items-center gap-1.5 flex-shrink-0"
                            >
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1 px-2 py-0.5 rounded-full border text-xs font-mono font-bold tabular-nums',
                                        medal.emas.chip,
                                    ]"
                                    >🥇 {{ r.emas }}</span
                                >
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1 px-2 py-0.5 rounded-full border text-xs font-mono font-bold tabular-nums',
                                        medal.perak.chip,
                                    ]"
                                    >🥈 {{ r.perak }}</span
                                >
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1 px-2 py-0.5 rounded-full border text-xs font-mono font-bold tabular-nums',
                                        medal.perunggu.chip,
                                    ]"
                                    >🥉 {{ r.perunggu }}</span
                                >
                            </div>
                            <div class="flex-1 min-w-0 hidden sm:block">
                                <div
                                    class="h-3 rounded-full bg-white/10 overflow-hidden flex ring-1 ring-inset ring-white/10"
                                    :style="{
                                        width:
                                            (barsReady
                                                ? Math.max(
                                                      8,
                                                      (Number(r.poin) /
                                                          maxPoin) *
                                                          100,
                                                  )
                                                : 0) + '%',
                                    }"
                                >
                                    <div
                                        class="h-full bg-gold transition-all duration-700 ease-out"
                                        :style="{
                                            width: segW(r, 'emas') + '%',
                                        }"
                                    ></div>
                                    <div
                                        class="h-full bg-slate-300 transition-all duration-700 ease-out"
                                        :style="{
                                            width: segW(r, 'perak') + '%',
                                        }"
                                    ></div>
                                    <div
                                        class="h-full bg-khaki transition-all duration-700 ease-out"
                                        :style="{
                                            width: segW(r, 'perunggu') + '%',
                                        }"
                                    ></div>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0 w-14 sm:w-16">
                                <div
                                    class="font-mono font-extrabold text-2xl sm:text-3xl tabular-nums"
                                    :class="
                                        r.rank <= 3
                                            ? medal[rankMedal[r.rank]].text
                                            : 'text-parchment'
                                    "
                                >
                                    {{ r.poin }}
                                </div>
                                <div
                                    class="text-[9px] uppercase tracking-wider text-parchment/40 font-semibold"
                                >
                                    poin
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============ LEADERBOARD PER LOMBA (terang) ============ -->
            <section id="leaderboard" class="bg-parchment">
                <div class="mx-auto max-w-6xl px-4 sm:px-6 py-12 sm:py-16">
                    <div class="mb-6 sm:mb-8" data-reveal>
                        <div
                            class="text-[10px] sm:text-xs font-display font-bold tracking-[0.3em] uppercase text-khaki"
                        >
                            Peringkat Nilai
                        </div>
                        <h2
                            class="font-display text-3xl sm:text-4xl font-extrabold text-forest mt-1"
                        >
                            Leaderboard per Lomba
                        </h2>
                        <p class="text-sm text-ink/55 mt-1">
                            Nilai = rata-rata seluruh juri. Ketuk baris untuk
                            lihat rincian per juri.
                        </p>
                    </div>

                    <div
                        v-if="leaderboards.length === 0"
                        class="bg-white rounded-2xl border border-line shadow-sm p-10 text-center text-ink/50"
                        data-reveal
                    >
                        <div class="text-5xl mb-3">📭</div>
                        <p>Belum ada penilaian masuk.</p>
                    </div>

                    <template v-else>
                        <div class="flex flex-wrap gap-2 mb-5" data-reveal>
                            <button
                                v-for="b in leaderboards"
                                :key="b.lomba.id"
                                @click="activeLombaId = b.lomba.id"
                                :class="[
                                    'px-4 py-2 rounded-full border font-display font-bold text-xs sm:text-sm uppercase tracking-wide transition-all duration-200 active:scale-95',
                                    activeLombaId === b.lomba.id
                                        ? 'bg-forest text-parchment border-forest shadow-md'
                                        : 'bg-white text-forest border-line hover:border-gold hover:bg-white/60',
                                ]"
                            >
                                {{ b.lomba.nama }}
                            </button>
                        </div>

                        <div
                            class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden"
                            data-reveal
                        >
                            <div
                                v-if="currentBoard.rows.length === 0"
                                class="p-10 text-center text-ink/50"
                            >
                                Belum ada regu dinilai di lomba ini.
                            </div>
                            <div v-else class="divide-y divide-line/60">
                                <div
                                    v-for="(row, i) in currentBoard.rows"
                                    :key="row.kontingen_id"
                                >
                                    <div
                                        @click="
                                            toggleRow(
                                                currentBoard.lomba.id +
                                                    '-' +
                                                    row.kontingen_id,
                                            )
                                        "
                                        class="flex items-center gap-3 sm:gap-4 px-4 sm:px-6 py-3 sm:py-4 cursor-pointer hover:bg-parchment/40 transition-colors"
                                    >
                                        <div
                                            class="w-8 sm:w-9 flex-shrink-0 text-center"
                                        >
                                            <span
                                                v-if="row.rank <= 3"
                                                class="text-2xl sm:text-3xl"
                                                >{{
                                                    medal[rankMedal[row.rank]]
                                                        .emoji
                                                }}</span
                                            >
                                            <span
                                                v-else
                                                class="font-mono font-bold text-ink/40 text-lg tabular-nums"
                                                >{{ row.rank }}</span
                                            >
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div
                                                class="flex items-center justify-between gap-2"
                                            >
                                                <span
                                                    class="font-semibold text-forest text-sm sm:text-base truncate"
                                                    >{{ row.team_name }}</span
                                                >
                                                <span
                                                    class="font-mono font-extrabold text-base sm:text-lg tabular-nums flex-shrink-0"
                                                    :class="
                                                        row.rank <= 3
                                                            ? medal[
                                                                  rankMedal[
                                                                      row.rank
                                                                  ]
                                                              ].text
                                                            : 'text-forest'
                                                    "
                                                    >{{ row.nilai_akhir }}</span
                                                >
                                            </div>
                                            <div
                                                class="mt-1.5 flex items-center gap-2"
                                            >
                                                <div
                                                    class="flex-1 h-2 rounded-full bg-line overflow-hidden"
                                                >
                                                    <div
                                                        class="h-full rounded-full bg-gradient-to-r from-forest/70 to-forest transition-all duration-700 ease-out"
                                                        :style="{
                                                            width:
                                                                (barsReady
                                                                    ? Math.max(
                                                                          4,
                                                                          (Number(
                                                                              row.nilai_akhir,
                                                                          ) /
                                                                              maxBoardNilai) *
                                                                              100,
                                                                      )
                                                                    : 0) + '%',
                                                        }"
                                                    ></div>
                                                </div>
                                                <span
                                                    v-if="row.golongan"
                                                    class="text-[10px] px-2 py-0.5 rounded-full bg-forest/10 text-forest font-semibold uppercase whitespace-nowrap"
                                                    >{{ row.golongan }}</span
                                                >
                                                <span
                                                    class="text-[10px] sm:text-xs text-ink/45 whitespace-nowrap"
                                                    >👨‍️
                                                    {{ row.jumlah_juri }}</span
                                                >
                                            </div>
                                        </div>
                                        <svg
                                            :class="[
                                                'w-4 h-4 text-ink/40 transition-transform duration-300 flex-shrink-0',
                                                openRow ===
                                                currentBoard.lomba.id +
                                                    '-' +
                                                    row.kontingen_id
                                                    ? 'rotate-90'
                                                    : '',
                                            ]"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 5l7 7-7 7"
                                            />
                                        </svg>
                                    </div>
                                    <Transition name="expand">
                                        <div
                                            v-if="
                                                openRow ===
                                                currentBoard.lomba.id +
                                                    '-' +
                                                    row.kontingen_id
                                            "
                                            class="px-4 sm:px-6 pb-4 bg-parchment/30 border-t border-line/40"
                                        >
                                            <div
                                                class="pt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2"
                                            >
                                                <div
                                                    v-for="(
                                                        j, ji
                                                    ) in row.juri_scores"
                                                    :key="ji"
                                                    class="flex items-center justify-between gap-2 bg-white rounded-lg border border-line/60 px-3 py-2"
                                                >
                                                    <span
                                                        class="text-sm text-forest truncate"
                                                        >{{ j.nama }}</span
                                                    >
                                                    <span
                                                        class="font-mono font-bold text-forest tabular-nums"
                                                        >{{ j.nilai }}</span
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                    </Transition>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </section>

            <!-- ============ TIMELINE FASE ============ -->
            <section id="jadwal" class="bg-white border-t border-line">
                <div class="mx-auto max-w-6xl px-4 sm:px-6 py-12 sm:py-16">
                    <div class="mb-8 sm:mb-10" data-reveal>
                        <div
                            class="text-[10px] sm:text-xs font-display font-bold tracking-[0.3em] uppercase text-khaki"
                        >
                            Alur Kegiatan
                        </div>
                        <h2
                            class="font-display text-3xl sm:text-4xl font-extrabold text-forest mt-1"
                        >
                            Jadwal &amp; Fase
                        </h2>
                    </div>
                    <div
                        class="relative grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6"
                    >
                        <span
                            class="hidden sm:block absolute left-0 right-0 top-7 h-px bg-line"
                        ></span>
                        <div
                            v-for="(p, i) in phases"
                            :key="p.key"
                            data-reveal
                            :style="{ transitionDelay: i * 90 + 'ms' }"
                            :class="[
                                'relative rounded-2xl border p-4 sm:p-5 transition-all duration-300',
                                p.active
                                    ? 'border-gold bg-gold/5 shadow-lg shadow-gold/10'
                                    : 'border-line bg-parchment/40',
                            ]"
                        >
                            <div class="flex items-center gap-3">
                                <span
                                    class="relative z-10 w-12 h-12 rounded-full flex items-center justify-center text-xl flex-shrink-0"
                                    :class="
                                        p.active
                                            ? 'bg-gold text-ink'
                                            : 'bg-white border border-line text-forest'
                                    "
                                >
                                    <span
                                        v-if="p.active"
                                        class="absolute inline-flex h-full w-full rounded-full bg-gold/40 animate-ping"
                                    ></span>
                                    <span class="relative">{{ p.icon }}</span>
                                </span>
                                <div>
                                    <div
                                        class="text-[10px] uppercase tracking-wider font-semibold"
                                        :class="
                                            p.active
                                                ? 'text-gold'
                                                : 'text-ink/40'
                                        "
                                    >
                                        Fase {{ i + 1 }}
                                    </div>
                                    <div
                                        class="font-display font-bold text-forest text-base sm:text-lg"
                                    >
                                        {{ p.label }}
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 text-xs sm:text-sm text-ink/60">
                                {{ fmt(p.mulai)
                                }}<span v-if="p.mulai !== p.selesai">
                                    – {{ fmt(p.selesai) }}</span
                                >
                            </div>
                            <span
                                v-if="p.active"
                                class="inline-block mt-2 text-[10px] font-bold uppercase tracking-wider text-gold"
                                >● Berlangsung</span
                            >
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============ CTA MASKOT (gelap) ============ -->
            <section class="relative bg-forest text-parchment overflow-hidden">
                <div
                    class="pointer-events-none absolute inset-0 bg-[radial-gradient(50%_120%_at_100%_50%,theme(colors.gold/10%),transparent_60%)]"
                ></div>
                <div
                    class="relative mx-auto max-w-6xl px-4 sm:px-6 py-10 sm:py-12 grid sm:grid-cols-[1fr_auto] gap-6 items-center"
                >
                    <div>
                        <div
                            class="font-display text-2xl sm:text-3xl font-extrabold"
                        >
                            Panitia, Juri, atau Operator?
                        </div>
                        <p class="text-parchment/60 text-sm mt-1 max-w-lg">
                            Masuk ke dasbor untuk mengelola pendaftaran,
                            verifikasi, penilaian, dan rekap juara PERSIMANU.
                        </p>
                    </div>
                    <div
                        class="flex items-center gap-4 justify-start sm:justify-end"
                    >
                        <img
                            v-if="maskotOk"
                            :src="MASKOT"
                            @error="maskotOk = false"
                            alt="SiELANG"
                            class="hidden sm:block w-28 lg:w-32 object-contain drop-shadow-xl maskot-float"
                        />
                        <Link
                            :href="route('login')"
                            class="px-6 py-3 bg-gold text-ink rounded-lg font-bold hover:opacity-90 transition active:scale-95 whitespace-nowrap"
                            >Masuk ke Dasbor →</Link
                        >
                    </div>
                </div>
            </section>
        </template>
    </PublicLayout>
</template>

<style scoped>
[data-reveal] {
    opacity: 0;
    transform: translateY(18px);
    transition:
        opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1),
        transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}
[data-reveal].is-visible {
    opacity: 1;
    transform: none;
}

.maskot-float {
    animation: maskotFloat 4s ease-in-out infinite;
}
@keyframes maskotFloat {
    0%,
    100% {
        transform: translateY(0) rotate(0deg);
    }
    50% {
        transform: translateY(-12px) rotate(-1deg);
    }
}
.maskot-float:hover {
    animation-play-state: paused;
    transform: scale(1.04) rotate(2deg);
    transition: transform 0.3s ease;
}

.emblem-float {
    animation: emblemFloat 6s ease-in-out infinite;
}
@keyframes emblemFloat {
    0%,
    100% {
        transform: translateY(0) scale(1);
    }
    50% {
        transform: translateY(-6px) scale(1.015);
    }
}

.twinkle {
    animation: twinkle 2.4s ease-in-out infinite;
}
@keyframes twinkle {
    0%,
    100% {
        opacity: 0.45;
        transform: scale(0.85);
    }
    50% {
        opacity: 1;
        transform: scale(1.1);
    }
}

.expand-enter-active,
.expand-leave-active {
    transition: all 0.3s ease;
    overflow: hidden;
}
.expand-enter-from,
.expand-leave-to {
    opacity: 0;
    max-height: 0;
}
.expand-enter-to,
.expand-leave-from {
    opacity: 1;
    max-height: 500px;
}
</style>
