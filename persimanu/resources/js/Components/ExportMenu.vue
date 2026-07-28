<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

defineProps({
    excelUrl: { type: String, default: null },  // null = tombol Excel disembunyikan
    csvUrl:   { type: String, default: null },
    label:    { type: String, default: 'Unduh' },
});

const open = ref(false);
const toggle = () => { open.value = !open.value; };
const close = () => { open.value = false; };

const printPdf = () => {
    open.value = false;
    window.print();
};

// tutup dropdown kalau klik di luar
const onDoc = (e) => {
    if (open.value && !e.target.closest('[data-export-menu]')) open.value = false;
};
onMounted(() => document.addEventListener('click', onDoc));
onUnmounted(() => document.removeEventListener('click', onDoc));
</script>

<template>
    <div class="relative" data-export-menu>
        <!-- tombol utama -->
        <button
            @click.stop="toggle"
            class="no-print inline-flex items-center gap-2 px-3.5 sm:px-4 py-2 sm:py-2.5 bg-forest text-parchment rounded-lg text-xs sm:text-sm font-bold hover:bg-forest/90 transition active:scale-95 whitespace-nowrap"
            :class="open ? 'ring-2 ring-gold/50' : ''"
        >
            <span>📥</span>
            <span>{{ label }}</span>
            <svg :class="['w-3.5 h-3.5 transition-transform duration-200', open ? 'rotate-180' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- dropdown -->
        <Transition name="menu">
            <div
                v-if="open"
                class="no-print absolute right-0 z-30 mt-2 w-52 origin-top-right rounded-xl border border-line bg-white shadow-xl overflow-hidden"
            >
                <a
                    v-if="excelUrl"
                    :href="excelUrl"
                    @click="close"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-forest hover:bg-parchment/60 transition-colors group"
                >
                    <span class="w-7 h-7 rounded-lg bg-green-100 text-green-700 flex items-center justify-center text-base group-hover:scale-110 transition-transform">📊</span>
                    <span class="font-semibold">Excel (.xlsx)</span>
                </a>
                <a
                    v-if="csvUrl"
                    :href="csvUrl"
                    @click="close"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-forest hover:bg-parchment/60 transition-colors group border-t border-line/60"
                >
                    <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-base group-hover:scale-110 transition-transform">📄</span>
                    <span class="font-semibold">CSV (.csv)</span>
                </a>
                <button
                    @click="printPdf"
                    class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-forest hover:bg-parchment/60 transition-colors group border-t border-line/60"
                >
                    <span class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-base group-hover:scale-110 transition-transform">🖨️</span>
                    <span class="font-semibold">Cetak / PDF</span>
                </button>
                <div v-if="!excelUrl" class="px-4 py-2 text-[10px] text-ink/40 border-t border-line/60">
                    Format .xlsx butuh <code class="text-ink/60">maatwebsite/excel</code>.
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.menu-enter-active, .menu-leave-active { transition: opacity 0.18s ease, transform 0.18s cubic-bezier(0.16, 1, 0.3, 1); }
.menu-enter-from, .menu-leave-to { opacity: 0; transform: scale(0.95) translateY(-6px); }
</style>

<!-- stylesheet CETAK: sembunyikan chrome admin, rapikan konten -->
<style>
@media print {
    /* sembunyikan sidebar, header admin, navbar publik, footer, dan semua .no-print */
    aside, body > header, header.sticky, footer, .no-print { display: none !important; }
    /* biar konten rekap pakai lebar penuh saat cetak */
    main, main > div, main section { padding: 0 !important; margin: 0 !important; box-shadow: none !important; }
    body { background: #fff !important; }
    /* paksa warna latar/teks tercetak (penting buat badge & bar) */
    * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    /* hindari baris tabel/section terpotong antar halaman */
    tr, .reveal-row, section { break-inside: avoid; }
}
</style>