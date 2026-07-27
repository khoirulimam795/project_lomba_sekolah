import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

/**
 * Langganan channel publik event & auto-refresh data landing saat ada broadcast.
 * No-op-safe: kalau window.Echo belum ada / Reverb mati, diam-diam skip (tanpa crash).
 */
export function useLiveUpdates(eventId) {
    const isLive = ref(false);
    const toast = ref(null);
    let channel = null;
    let toastTimer = null;

    const showToast = (msg) => {
        toast.value = msg;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => { toast.value = null; }, 4500);
    };

    const refresh = (msg) => {
        // partial reload — cuma ambil props yang bisa berubah, tanpa reload penuh
        router.reload({
            only: ['standings', 'leaderboards', 'stats', 'phases', 'liveStatus'],
            preserveScroll: true,
        });
        if (msg) showToast(msg);
    };

    onMounted(() => {
        if (!eventId) return;
        try {
            if (typeof window.Echo === 'undefined') return; // Echo belum ke-setup → skip

            channel = window.Echo.channel(`event.${eventId}`);

            channel.listen('.JuaraUpdated', (e) => {
                isLive.value = true;
                refresh(e?.message || '🏆 Klasemen juara diperbarui');
            });

            channel.listen('.NilaiSubmitted', (e) => {
                isLive.value = true;
                refresh(e?.message || '📊 Nilai baru masuk');
            });
        } catch (err) {
            // jangan sampai landing crash gara-gara live update
            console.warn('[live] realtime tidak aktif:', err);
        }
    });

    onUnmounted(() => {
        clearTimeout(toastTimer);
        try {
            if (channel && typeof window.Echo !== 'undefined') {
                window.Echo.leave(`event.${eventId}`);
            }
        } catch (e) { /* ignore */ }
    });

    return { isLive, toast };
}