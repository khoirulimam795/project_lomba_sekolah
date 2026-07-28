// ============================================================
// ENUM GOLONGAN PRAMUKA — satu sumber kebenaran
// Value di DB = kolom kiri; yang tampil di UI = label.
// Ubah/tambah golongan? Cukup edit file ini.
// ============================================================

export const GOL_OPTIONS = [
    { value: 'penggalang_ramu', label: 'Penggalang Ramu' },
    { value: 'penggalang',      label: 'Penggalang' },
    { value: 'penegak',         label: 'Penegak' },
];

// urutan tampil (dipakai grouping Nomor Urut / tab / filter)
export const GOL_ORDER = GOL_OPTIONS.map((o) => o.value);

// warna titik per golongan (konsisten tema)
export const GOL_DOT = {
    penggalang_ramu: 'bg-emerald-500',
    penggalang:      'bg-gold',
    penegak:         'bg-forest',
};

const LABEL_MAP = GOL_OPTIONS.reduce((m, o) => ((m[o.value] = o.label), m), {});

// value DB → label cantik ("penggalang_ramu" → "Penggalang Ramu")
export const golLabel = (v) => LABEL_MAP[v] || v || '-';
export const golDot = (v) => GOL_DOT[v] || 'bg-ink/30';