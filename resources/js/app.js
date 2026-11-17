import Chart from 'chart.js/auto';

window.Chart = Chart;

// --- Finora Global Accessibility & Helper Functions ---

// 1. Text-to-Speech (TTS) dengan Web Speech API
window.finoraSpeak = function (text) {
    if (!('speechSynthesis' in window)) {
        alert('Fitur pembaca suara (Text-to-Speech) belum didukung oleh browser ini.');
        return;
    }

    window.speechSynthesis.cancel(); // Hentikan ucapan yang sedang berjalan

    const utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = 'id-ID';
    utterance.rate = 0.9; // Kecepatan sedikit lebih tenang untuk kemudahan pendengaran lansia
    utterance.pitch = 1.0;

    window.speechSynthesis.speak(utterance);
};

// 2. Toolbar Ukuran Font Lansia (A-, A, A+)
window.setSeniorFontSize = function (size) {
    const root = document.getElementById('senior-app-container') || document.body;
    root.classList.remove('font-size-large', 'font-size-xlarge');
    if (size === 'large') {
        root.classList.add('font-size-large');
    } else if (size === 'xlarge') {
        root.classList.add('font-size-xlarge');
    }
    localStorage.setItem('finora_font_size', size);
};

// 3. Mode Kontras Tinggi Lansia
window.toggleSeniorHighContrast = function () {
    const root = document.getElementById('senior-app-container') || document.body;
    const isHigh = root.classList.toggle('high-contrast-mode');
    localStorage.setItem('finora_high_contrast', isHigh ? '1' : '0');
};

// Inisialisasi preferensi tersimpan saat halaman dimuat
document.addEventListener('DOMContentLoaded', () => {
    const savedFontSize = localStorage.getItem('finora_font_size');
    if (savedFontSize) {
        window.setSeniorFontSize(savedFontSize);
    }
    const savedContrast = localStorage.getItem('finora_high_contrast');
    if (savedContrast === '1') {
        const root = document.getElementById('senior-app-container') || document.body;
        root.classList.add('high-contrast-mode');
    }

    // 4. Proteksi Duplikasi Transaksi (Disable submit button on submit)
    document.querySelectorAll('form[data-prevent-duplicate]').forEach(form => {
        form.addEventListener('submit', (e) => {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                if (submitBtn.disabled) {
                    e.preventDefault();
                    return;
                }
                submitBtn.disabled = true;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    Memproses...
                `;
            }
        });
    });
});
