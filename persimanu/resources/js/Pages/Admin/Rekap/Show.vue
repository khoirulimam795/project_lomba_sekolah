<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { ref, computed } from "vue";
import ExportMenu from "@/Components/ExportMenu.vue";
const props = defineProps({
    lomba: { type: Object, default: null },
    rekap: { type: Object, default: () => ({}) },
    finalized_at: { type: [String, null], default: null },
    last_change_at: { type: [String, null], default: null },
});

const GOL_ORDER = ["siaga", "penggalang", "penegak", "pandega"];
const golonganList = computed(() =>
    Object.keys(props.rekap).sort(
        (a, b) => GOL_ORDER.indexOf(a) - GOL_ORDER.indexOf(b),
    ),
);
const selected = ref(golonganList.value[0] ?? null);

const rows = computed(() => props.rekap[selected.value] || []);
const podium = computed(() => rows.value.slice(0, 3));
const maxNilai = computed(() =>
    rows.value.length
        ? Math.max(...rows.value.map((r) => Number(r.nilai_akhir)))
        : 100,
);

const saveState = computed(() => {
    if (!props.finalized_at)
        return {
            t: "Belum disimpan",
            cls: "bg-amber-100 text-amber-700 border-amber-300",
            dot: "bg-amber-500",
        };
    if (
        props.last_change_at &&
        new Date(props.last_change_at) > new Date(props.finalized_at)
    ) {
        return {
            t: "Ada perubahan setelah juara dihitung",
            cls: "bg-red-100 text-red-700 border-red-300",
            dot: "bg-red-500",
        };
    }
    return {
        t: "Tersimpan " + fmt(props.finalized_at),
        cls: "bg-green-100 text-green-700 border-green-300",
        dot: "bg-green-500",
    };
});

const fmt = (v) => (v ? String(v).slice(0, 16).replace("T", " ") : "-");

const medal = {
    1: {
        emoji: "🥇",
        text: "text-gold",
        bar: "from-gold to-forest",
        podiumBg: "bg-gradient-to-b from-gold/25 to-gold/5",
        height: "h-40 sm:h-48",
    },
    2: {
        emoji: "🥈",
        text: "text-slate-500",
        bar: "from-slate-300 to-slate-400",
        podiumBg: "bg-gradient-to-b from-slate-200/60 to-slate-100/30",
        height: "h-32 sm:h-40",
    },
    3: {
        emoji: "🥉",
        text: "text-khaki",
        bar: "from-khaki to-amber-700",
        podiumBg: "bg-gradient-to-b from-khaki/25 to-khaki/5",
        height: "h-24 sm:h-32",
    },
};

const finalize = () => {
    if (!rows.value.length) return;
    if (
        !confirm(
            "Hitung & simpan juara 1-2-3 untuk semua golongan lomba ini? Hasil sebelumnya ditimpa.",
        )
    )
        return;
    router.post(
        route("admin.rekap.finalize", props.lomba.id),
        {},
        { preserveScroll: true },
    );
};
</script>

<template>
    <AdminLayout header="Rekap & Juara">
        <Head :title="`Rekap — ${lomba?.nama ?? ''}`" />

        <div class="relative overflow-hidden">
            <div
                class="pointer-events-none absolute inset-x-0 top-0 h-72 bg-[radial-gradient(60%_100%_at_50%_0%,theme(colors.gold/10%),transparent)]"
            ></div>
            <div
                class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-gold via-forest to-khaki"
            ></div>

            <div class="relative px-2 sm:px-4 md:px-0 pt-4 pb-10 space-y-6">
                <header class="space-y-3">
                    <Link
                        :href="
                            route('admin.rekap.index', {
                                event_id: lomba?.event_id,
                            })
                        "
                        class="text-xs sm:text-sm text-forest hover:underline inline-flex items-center"
                        >← Daftar lomba</Link
                    >
                    <span
                        class="block text-[10px] sm:text-xs font-display font-bold tracking-[0.25em] uppercase text-gold"
                        >Papan Hasil</span
                    >
                    <h2
                        class="font-display text-3xl sm:text-4xl font-extrabold text-forest leading-none"
                    >
                        {{ lomba?.nama }}
                    </h2>
                    <p class="text-sm text-ink/60">
                        🏆 {{ lomba?.event?.nama ?? "-" }}
                    </p>
                </header>

                <div
                    class="bg-white rounded-xl border border-line shadow-sm p-3 sm:p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                >
                    <span
                        :class="[
                            'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border text-xs sm:text-sm font-semibold whitespace-nowrap',
                            saveState.cls,
                        ]"
                    >
                        <span
                            :class="['w-2 h-2 rounded-full', saveState.dot]"
                        ></span
                        >{{ saveState.t }}
                    </span>
                    <div class="flex items-center gap-2 flex-wrap justify-end">
                        <ExportMenu
                            :excel-url="
                                route('admin.rekap.export-excel', lomba.id)
                            "
                            :csv-url="route('admin.rekap.export-csv', lomba.id)"
                            label="Unduh Rekap"
                        />
                        <button
                            @click="finalize"
                            :disabled="!rows.length"
                            class="px-5 py-2.5 bg-gold text-ink rounded-lg font-bold hover:opacity-90 disabled:opacity-40 transition active:scale-95 text-sm sm:text-base whitespace-nowrap"
                        >
                            🏆 Hitung &amp; Simpan Juara
                        </button>
                    </div>
                </div>

                <div
                    v-if="golonganList.length === 0"
                    class="bg-white rounded-xl border border-line shadow-sm p-10 text-center text-ink/50"
                >
                    <div class="text-5xl mb-3">📭</div>
                    <p class="font-semibold text-ink/60">
                        Belum ada penilaian masuk untuk lomba ini.
                    </p>
                </div>

                <template v-else>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="g in golonganList"
                            :key="g"
                            @click="selected = g"
                            :class="[
                                'px-4 sm:px-5 py-2 rounded-full border font-display font-bold text-sm sm:text-base uppercase tracking-wide transition-all duration-200 active:scale-95',
                                selected === g
                                    ? 'bg-forest text-parchment border-forest shadow-md'
                                    : 'bg-white text-forest border-line hover:border-gold hover:bg-parchment/40',
                            ]"
                        >
                            {{ g }}
                        </button>
                    </div>

                    <!-- PODIUM -->
                    <div
                        v-if="podium.length"
                        class="bg-white rounded-2xl border border-line shadow-sm p-4 sm:p-8"
                    >
                        <h3
                            class="font-display text-lg sm:text-xl font-extrabold text-forest mb-6 text-center"
                        >
                            🏆 Podium Juara
                        </h3>
                        <div
                            class="flex items-end justify-center gap-2 sm:gap-4 max-w-2xl mx-auto"
                        >
                            <div
                                v-for="rank in [2, 1, 3]"
                                :key="rank"
                                class="flex-1 flex flex-col items-center"
                                :style="{
                                    animationDelay:
                                        (rank === 1
                                            ? 0
                                            : rank === 2
                                              ? 120
                                              : 240) + 'ms',
                                }"
                            >
                                <template v-if="podium[rank - 1]">
                                    <div
                                        class="text-4xl sm:text-5xl mb-2 drop-shadow-sm podium-pop"
                                    >
                                        {{ medal[rank].emoji }}
                                    </div>
                                    <div class="text-center mb-3 px-1">
                                        <div
                                            class="font-display font-bold text-forest text-sm sm:text-base leading-tight line-clamp-2"
                                        >
                                            {{ podium[rank - 1].team_name }}
                                        </div>
                                        <div
                                            class="font-mono font-extrabold text-lg sm:text-xl mt-1 tabular-nums"
                                            :class="medal[rank].text"
                                        >
                                            {{ podium[rank - 1].nilai_akhir }}
                                        </div>
                                    </div>
                                    <div
                                        :class="[
                                            'w-full rounded-t-xl border border-line flex flex-col items-center justify-start pt-3 transition-all duration-300 hover:-translate-y-1',
                                            medal[rank].podiumBg,
                                            medal[rank].height,
                                        ]"
                                    >
                                        <span
                                            class="font-display font-extrabold text-2xl sm:text-3xl text-ink/70"
                                            >{{ rank }}</span
                                        >
                                        <span
                                            class="text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider mt-1"
                                            >Juara {{ rank }}</span
                                        >
                                    </div>
                                </template>
                                <div
                                    v-else
                                    class="w-full"
                                    :class="medal[rank].height"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <!-- RANKING + TOMBOL REVISI -->
                    <div
                        class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden"
                    >
                        <div
                            class="px-4 sm:px-6 py-4 border-b border-line bg-parchment/30 flex items-center justify-between"
                        >
                            <h3
                                class="font-display text-lg sm:text-xl font-extrabold text-forest"
                            >
                                Ranking Lengkap
                            </h3>
                            <span class="text-xs sm:text-sm text-ink/50"
                                >{{ rows.length }} regu dinilai</span
                            >
                        </div>
                        <div class="divide-y divide-line/60">
                            <div
                                v-for="(r, i) in rows"
                                :key="r.kontingen_id"
                                :style="{ animationDelay: i * 40 + 'ms' }"
                                class="reveal-row px-4 sm:px-6 py-3 sm:py-4 flex items-center gap-3 sm:gap-4 hover:bg-parchment/30 transition-colors"
                            >
                                <div
                                    class="w-9 sm:w-10 flex-shrink-0 text-center"
                                >
                                    <span
                                        v-if="r.rank <= 3"
                                        class="text-2xl sm:text-3xl"
                                        >{{ medal[r.rank].emoji }}</span
                                    >
                                    <span
                                        v-else
                                        class="font-mono font-bold text-ink/40 text-lg sm:text-xl tabular-nums"
                                        >{{ r.rank }}</span
                                    >
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div
                                        class="flex items-center justify-between gap-2"
                                    >
                                        <span
                                            class="font-semibold text-forest text-sm sm:text-base truncate"
                                            >{{ r.team_name }}</span
                                        >
                                        <span
                                            class="font-mono font-extrabold text-base sm:text-lg tabular-nums flex-shrink-0"
                                            :class="
                                                r.rank <= 3
                                                    ? medal[r.rank].text
                                                    : 'text-forest'
                                            "
                                            >{{ r.nilai_akhir }}</span
                                        >
                                    </div>
                                    <div class="mt-1.5 flex items-center gap-2">
                                        <div
                                            class="flex-1 h-2 rounded-full bg-line overflow-hidden"
                                        >
                                            <div
                                                class="h-full rounded-full bg-gradient-to-r transition-all duration-700 ease-out"
                                                :class="
                                                    r.rank <= 3
                                                        ? medal[r.rank].bar
                                                        : 'from-forest/60 to-forest'
                                                "
                                                :style="{
                                                    width:
                                                        Math.max(
                                                            4,
                                                            (Number(
                                                                r.nilai_akhir,
                                                            ) /
                                                                maxNilai) *
                                                                100,
                                                        ) + '%',
                                                }"
                                            ></div>
                                        </div>
                                        <span
                                            class="text-[10px] sm:text-xs text-ink/45 whitespace-nowrap"
                                            >👨‍⚖️ {{ r.jumlah_juri }} juri</span
                                        >
                                    </div>
                                </div>
                                <!-- TOMBOL REVISI (7B-2) -->
                                <Link
                                    :href="
                                        route('admin.rekap.edit-nilai', {
                                            lomba: lomba.id,
                                            kontingen: r.kontingen_id,
                                            golongan: selected,
                                        })
                                    "
                                    class="flex-shrink-0 px-3 py-1.5 border border-line text-forest rounded-lg text-[11px] sm:text-xs font-semibold hover:bg-forest hover:text-parchment hover:border-forest transition active:scale-95 whitespace-nowrap"
                                    title="Revisi nilai juri (tercatat di audit log)"
                                >
                                    ✏️ Revisi
                                </Link>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.reveal-row {
    opacity: 0;
    animation: revealRow 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes revealRow {
    from {
        opacity: 0;
        transform: translateX(-10px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}
.podium-pop {
    animation: podiumPop 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
}
@keyframes podiumPop {
    0% {
        opacity: 0;
        transform: scale(0.4) translateY(10px);
    }
    60% {
        transform: scale(1.15);
    }
    100% {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
