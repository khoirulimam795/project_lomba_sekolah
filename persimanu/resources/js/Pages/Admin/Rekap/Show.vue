<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, computed } from 'vue';
import ExportMenu from '@/Components/ExportMenu.vue';
import { GOL_ORDER, golLabel } from '@/golongan';

const props = defineProps({
    lomba: { type: Object, default: null },
    rekap: { type: Object, default: () => ({}) },
    juri_columns: { type: Array, default: () => [] },
    finalized_at: { type: [String, null], default: null },
    last_change_at: { type: [String, null], default: null },
});

// golongan diurutkan sesuai urutan baku (bukan urutan muncul)
const golonganList = computed(() =>
    Object.keys(props.rekap).sort((a, b) => GOL_ORDER.indexOf(a) - GOL_ORDER.indexOf(b))
);
const selected = ref(golonganList.value[0] ?? null);

const rows = computed(() => props.rekap[selected.value] || []);
const podium = computed(() => rows.value.slice(0, 3));
const maxNilai = computed(() =>
    rows.value.length ? Math.max(...rows.value.map((r) => Number(r.nilai_akhir))) : 100
);

const saveState = computed(() => {
    if (!props.finalized_at) return { t: 'Belum disimpan', cls: 'bg-amber-100 text-amber-700 border-amber-300', dot: 'bg-amber-500' };
    if (props.last_change_at && new Date(props.last_change_at) > new Date(props.finalized_at)) {
        return { t: 'Ada perubahan setelah juara dihitung', cls: 'bg-red-100 text-red-700 border-red-300', dot: 'bg-red-500' };
    }
    return { t: 'Tersimpan ' + fmt(props.finalized_at), cls: 'bg-green-100 text-green-700 border-green-300', dot: 'bg-green-500' };
});

const fmt = (v) => (v ? String(v).slice(0, 16).replace('T', ' ') : '-');
const fmtScore = (v) => (v === null || v === undefined || v === '') ? '—' : v;

const medal = {
    1: { emoji: '🥇', text: 'text-gold', bar: 'from-gold to-forest', podiumBg: 'bg-gradient-to-b from-gold/25 to-gold/5', height: 'h-40 sm:h-48' },
    2: { emoji: '🥈', text: 'text-slate-500', bar: 'from-slate-300 to-slate-400', podiumBg: 'bg-gradient-to-b from-slate-200/60 to-slate-100/30', height: 'h-32 sm:h-40' },
    3: { emoji: '🥉', text: 'text-khaki', bar: 'from-khaki to-amber-700', podiumBg: 'bg-gradient-to-b from-khaki/25 to-khaki/5', height: 'h-24 sm:h-32' },
};

const finalize = () => {
    if (!rows.value.length) return;
    if (!confirm('Hitung & simpan juara 1-2-3 untuk semua golongan lomba ini? Hasil sebelumnya ditimpa.')) return;
    // router di-import via Link? pakai window? — pakai inertia router
    // (router sudah tersedia lewat @inertiajs/vue3; import di bawah tidak dipakai di sini, pakai fetch via Link tidak bisa POST)
    // kita pakai helper global route + form submit sederhana:
    postRoute(route('admin.rekap.finalize', props.lomba.id));
};

// POST minimal tanpa menambah import router (biar konsisten dgn pola lama)
function postRoute(url) {
    const f = document.createElement('form');
    f.method = 'POST'; f.action = url; f.style.display = 'none';
    const t = document.createElement('input');
    t.type = 'hidden'; t.name = '_token';
    t.value = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    f.appendChild(t); document.body.appendChild(f); f.submit();
}

// ===== CETAK / PDF: tabel rekap rapi di window baru (bukan dump excel, bukan layar penuh) =====
const esc = (s) => String(s ?? '').replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));

const cetakPdf = () => {
    const cols = props.juri_columns || [];
    let body = '';
    for (const g of golonganList.value) {
        const list = props.rekap[g] || [];
        body += `<h2>${esc(golLabel(g))} <span>(${list.length} regu dinilai)</span></h2>`;
        body += '<table><thead><tr><th style="width:34px">No</th><th>Pangkalan / Kontingen</th>';
        cols.forEach((c) => (body += `<th>${esc(c.name)}</th>`));
        body += '<th class="avg">Nilai Akhir</th></tr></thead><tbody>';
        if (!list.length) body += '<tr><td colspan="99" style="text-align:center;color:#888">Belum ada penilaian.</td></tr>';
        list.forEach((r) => {
            const rk = r.rank <= 3 ? ['','🥇','🥈','🥉'][r.rank] : r.rank + '.';
            body += `<tr><td class="c">${rk}</td><td class="l">${esc(r.team_name)}</td>`;
            cols.forEach((c, i) => (body += `<td class="c">${fmtScore(r.juri_scores ? r.juri_scores[i] : null)}</td>`));
            body += `<td class="c avg">${fmtScore(r.nilai_akhir)}</td></tr>`;
        });
        body += '</tbody></table>';
    }

    const html = `<!doctype html><html lang="id"><head><meta charset="utf-8">
<title>Rekap Nilai — ${esc(props.lomba?.nama ?? '')}</title>
<style>
@page{size:A4 landscape;margin:12mm}
*{box-sizing:border-box}
body{font-family:Arial,Helvetica,sans-serif;color:#1a1a1a;margin:0}
.hd{border-bottom:3px solid #163a2c;padding-bottom:8px;margin-bottom:6px}
.hd h1{margin:0;font-size:20px;color:#163a2c}
.hd div{color:#666;font-size:12px;margin-top:2px}
h2{font-size:14px;margin:18px 0 6px;border-bottom:2px solid #cf9d18;padding-bottom:3px;color:#163a2c}
h2 span{font-weight:400;color:#777;font-size:11px}
table{border-collapse:collapse;width:100%;font-size:11px;margin-bottom:4px}
th,td{border:1px solid #9a9a9a;padding:5px 7px}
th{background:#163a2c;color:#fff;text-align:center}
td.l{text-align:left} td.c{text-align:center}
td.avg,th.avg{font-weight:700}
td.avg{background:#f3e3b0}
tbody tr:nth-child(even) td{background:#f7f2e6}
tbody tr:nth-child(even) td.avg{background:#ecd98f}
</style></head><body>
<div class="hd"><h1>${esc(props.lomba?.nama ?? '')}</h1>
<div>${esc(props.lomba?.event?.nama ?? '')} · Rekap Nilai per Juri &amp; Rata-rata</div></div>
${body || '<p>Belum ada data penilaian.</p>'}
<` + `script>window.onload=function(){window.focus();window.print();}<` + `/script>
</body></html>`;

    const w = window.open('', '_blank');
    if (!w) { alert('Popup terblokir peramban — izinkan popup untuk halaman ini, lalu coba lagi.'); return; }
    w.document.open(); w.document.write(html); w.document.close();
};
</script>

<template>
    <AdminLayout header="Rekap & Juara">
        <Head :title="`Rekap — ${lomba?.nama ?? ''}`" />
        <div class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-72 bg-[radial-gradient(60%_100%_at_50%_0%,theme(colors.gold/10%),transparent)]"></div>
            <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-gold via-forest to-khaki"></div>

            <div class="relative px-2 sm:px-4 md:px-0 pt-4 pb-10 space-y-6">
                <header class="space-y-3">
                    <Link :href="route('admin.rekap.index', { event_id: lomba?.event_id })" class="text-xs sm:text-sm text-forest hover:underline inline-flex items-center">← Daftar lomba</Link>
                    <span class="block text-[10px] sm:text-xs font-display font-bold tracking-[0.25em] uppercase text-gold">Papan Hasil</span>
                    <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-forest leading-none">{{ lomba?.nama }}</h2>
                    <p class="text-sm text-ink/60">🏆 {{ lomba?.event?.nama ?? '-' }}</p>
                </header>

                <!-- toolbar -->
                <div class="bg-white rounded-xl border border-line shadow-sm p-3 sm:p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <span :class="['inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border text-xs sm:text-sm font-semibold whitespace-nowrap', saveState.cls]">
                        <span :class="['w-2 h-2 rounded-full', saveState.dot]"></span>{{ saveState.t }}
                    </span>
                    <div class="flex items-center gap-2 flex-wrap justify-end">
                        <!-- ✅ CETAK / PDF (tabel rapi) -->
                        <button @click="cetakPdf" :disabled="!rows.length"
                            class="px-4 py-2.5 border border-forest text-forest rounded-lg font-bold hover:bg-forest hover:text-parchment disabled:opacity-40 transition active:scale-95 text-sm sm:text-base whitespace-nowrap">
                            🖨️ Cetak / PDF
                        </button>
                        <ExportMenu
                            :excel-url="route('admin.rekap.export-excel', lomba.id)"
                            :csv-url="route('admin.rekap.export-csv', lomba.id)"
                            label="Unduh Rekap" />
                        <button @click="finalize" :disabled="!rows.length"
                            class="px-5 py-2.5 bg-gold text-ink rounded-lg font-bold hover:opacity-90 disabled:opacity-40 transition active:scale-95 text-sm sm:text-base whitespace-nowrap">
                            🏆 Hitung &amp; Simpan Juara
                        </button>
                    </div>
                </div>

                <div v-if="golonganList.length === 0" class="bg-white rounded-xl border border-line shadow-sm p-10 text-center text-ink/50">
                    <div class="text-5xl mb-3">📭</div>
                    <p class="font-semibold text-ink/60">Belum ada penilaian masuk untuk lomba ini.</p>
                </div>

                <template v-else>
                    <!-- tab golongan -->
                    <div class="flex flex-wrap gap-2">
                        <button v-for="g in golonganList" :key="g" @click="selected = g"
                            :class="['px-4 sm:px-5 py-2 rounded-full border font-display font-bold text-sm sm:text-base tracking-wide transition-all duration-200 active:scale-95',
                                selected === g ? 'bg-forest text-parchment border-forest shadow-md' : 'bg-white text-forest border-line hover:border-gold hover:bg-parchment/40']">
                            {{ golLabel(g) }}
                        </button>
                    </div>

                    <!-- PODIUM -->
                    <div v-if="podium.length" class="bg-white rounded-2xl border border-line shadow-sm p-4 sm:p-8">
                        <h3 class="font-display text-lg sm:text-xl font-extrabold text-forest mb-6 text-center">🏆 Podium Juara</h3>
                        <div class="flex items-end justify-center gap-2 sm:gap-4 max-w-2xl mx-auto">
                            <div v-for="rank in [2, 1, 3]" :key="rank" class="flex-1 flex flex-col items-center"
                                :style="{ animationDelay: (rank === 1 ? 0 : rank === 2 ? 120 : 240) + 'ms' }">
                                <template v-if="podium[rank - 1]">
                                    <div class="text-4xl sm:text-5xl mb-2 drop-shadow-sm podium-pop">{{ medal[rank].emoji }}</div>
                                    <div class="text-center mb-3 px-1">
                                        <div class="font-display font-bold text-forest text-sm sm:text-base leading-tight line-clamp-2">{{ podium[rank - 1].team_name }}</div>
                                        <div class="font-mono font-extrabold text-lg sm:text-xl mt-1 tabular-nums" :class="medal[rank].text">{{ podium[rank - 1].nilai_akhir }}</div>
                                    </div>
                                    <div :class="['w-full rounded-t-xl border border-line flex flex-col items-center justify-start pt-3 transition-all duration-300 hover:-translate-y-1', medal[rank].podiumBg, medal[rank].height]">
                                        <span class="font-display font-extrabold text-2xl sm:text-3xl text-ink/70">{{ rank }}</span>
                                        <span class="text-[10px] sm:text-xs font-semibold text-ink/50 uppercase tracking-wider mt-1">Juara {{ rank }}</span>
                                    </div>
                                </template>
                                <div v-else class="w-full" :class="medal[rank].height"></div>
                            </div>
                        </div>
                    </div>

                    <!-- ✅ TABEL REKAP: kolom per-juri + nilai akhir -->
                    <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
                        <div class="px-4 sm:px-6 py-4 border-b border-line bg-parchment/30 flex items-center justify-between">
                            <h3 class="font-display text-lg sm:text-xl font-extrabold text-forest">Ranking Lengkap</h3>
                            <span class="text-xs sm:text-sm text-ink/50">{{ rows.length }} regu dinilai</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-forest text-parchment">
                                        <th class="px-3 py-2.5 text-left w-12">#</th>
                                        <th class="px-3 py-2.5 text-left min-w-[180px]">Pangkalan / Kontingen</th>
                                        <th v-for="jc in juri_columns" :key="jc.id" class="px-3 py-2.5 text-center whitespace-nowrap">{{ jc.name }}</th>
                                        <th class="px-3 py-2.5 text-center whitespace-nowrap bg-gold text-ink">Nilai Akhir</th>
                                        <th class="px-3 py-2.5 text-center w-20">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line/60">
                                    <tr v-for="(r, i) in rows" :key="r.kontingen_id"
                                        :style="{ animationDelay: i * 35 + 'ms' }"
                                        class="reveal-row hover:bg-parchment/40 transition-colors">
                                        <td class="px-3 py-3 text-center">
                                            <span v-if="r.rank <= 3" class="text-xl sm:text-2xl">{{ medal[r.rank].emoji }}</span>
                                            <span v-else class="font-mono font-bold text-ink/40 tabular-nums">{{ r.rank }}</span>
                                        </td>
                                        <td class="px-3 py-3">
                                            <div class="font-semibold text-forest truncate">{{ r.team_name }}</div>
                                            <div class="mt-1.5 h-1.5 rounded-full bg-line overflow-hidden max-w-[220px]">
                                                <div class="h-full rounded-full bg-gradient-to-r transition-all duration-700 ease-out"
                                                    :class="r.rank <= 3 ? medal[r.rank].bar : 'from-forest/60 to-forest'"
                                                    :style="{ width: Math.max(4, (Number(r.nilai_akhir) / maxNilai) * 100) + '%' }"></div>
                                            </div>
                                        </td>
                                        <!-- ✅ skor tiap juri -->
                                        <td v-for="(jc, idx) in juri_columns" :key="jc.id"
                                            class="px-3 py-3 text-center font-mono tabular-nums"
                                            :class="fmtScore(r.juri_scores[idx]) === '—' ? 'text-ink/25' : 'text-ink/70'">
                                            {{ fmtScore(r.juri_scores ? r.juri_scores[idx] : null) }}
                                        </td>
                                        <!-- ✅ nilai akhir (rata-rata) -->
                                        <td class="px-3 py-3 text-center">
                                            <span class="inline-block font-mono font-extrabold tabular-nums px-2 py-1 rounded-lg bg-gold/15 text-gold border border-gold/40">
                                                {{ r.nilai_akhir }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <Link :href="route('admin.rekap.edit-nilai', { lomba: lomba.id, kontingen: r.kontingen_id, golongan: selected })"
                                                class="inline-flex px-2.5 py-1.5 border border-line text-forest rounded-lg text-[11px] font-semibold hover:bg-forest hover:text-parchment hover:border-forest transition active:scale-95 whitespace-nowrap"
                                                title="Revisi nilai juri (tercatat di audit log)">✏️</Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-if="juri_columns.length === 0" class="px-4 sm:px-6 py-3 text-xs text-amber-700 bg-amber-50 border-t border-amber-200">
                            ⚠️ Belum ada juri yang ditugaskan ke lomba ini — kolom per-juri belum bisa ditampilkan.
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.reveal-row { opacity: 0; animation: revealRow 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
@keyframes revealRow { from { opacity: 0; transform: translateX(-10px); } to { opacity: 1; transform: translateX(0); } }
.podium-pop { animation: podiumPop 0.5s cubic-bezier(0.16, 1, 0.3, 1) both; }
@keyframes podiumPop { 0% { opacity: 0; transform: scale(0.4) translateY(10px); } 60% { transform: scale(1.15); } 100% { opacity: 1; transform: scale(1) translateY(0); } }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>