/**
 * AI EXTREME 2026 - Countdown Timer
 */

document.addEventListener('DOMContentLoaded', () => {
    initCountdown('event-countdown', '2026-10-14T09:00:00+05:30', 'AI EXTREME 2026 IS LIVE!');
    initCountdown('problem-countdown', '2026-10-02T00:00:00+05:30', 'PROBLEM STATEMENTS RELEASED!');
});

function initCountdown(elementId, targetDate, liveMessage) {
    const container = document.getElementById(elementId);
    if (!container) return;

    const target = new Date(targetDate).getTime();

    function update() {
        const now = Date.now();
        const diff = target - now;

        if (diff <= 0) {
            container.innerHTML = `<div class="countdown-live">${liveMessage}</div>`;
            return;
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        container.innerHTML = `
            <div class="countdown-item">
                <span class="countdown-value">${String(days).padStart(2, '0')}</span>
                <span class="countdown-unit">Days</span>
            </div>
            <div class="countdown-item">
                <span class="countdown-value">${String(hours).padStart(2, '0')}</span>
                <span class="countdown-unit">Hours</span>
            </div>
            <div class="countdown-item">
                <span class="countdown-value">${String(minutes).padStart(2, '0')}</span>
                <span class="countdown-unit">Minutes</span>
            </div>
            <div class="countdown-item">
                <span class="countdown-value">${String(seconds).padStart(2, '0')}</span>
                <span class="countdown-unit">Seconds</span>
            </div>
        `;
    }

    update();
    setInterval(update, 1000);
}
