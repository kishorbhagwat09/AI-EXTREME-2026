/**
 * AI EXTREME 2026 - Admin JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    initRejectionForms();
    initScreenshotModal();
});

function initSidebar() {
    const toggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.admin-sidebar');
    if (!toggle || !sidebar) return;

    toggle.addEventListener('click', () => {
        sidebar.classList.toggle('open');
    });

    document.addEventListener('click', (e) => {
        if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
            sidebar.classList.remove('open');
        }
    });
}

function initRejectionForms() {
    document.querySelectorAll('[data-toggle-rejection]').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-toggle-rejection');
            const form = document.getElementById(targetId);
            if (form) form.classList.toggle('active');
        });
    });
}

function initScreenshotModal() {
    document.querySelectorAll('.screenshot-preview').forEach(img => {
        img.addEventListener('click', () => {
            const overlay = document.createElement('div');
            overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.9);z-index:9999;display:flex;align-items:center;justify-content:center;cursor:pointer;padding:20px;';
            const fullImg = document.createElement('img');
            fullImg.src = img.src;
            fullImg.style.cssText = 'max-width:100%;max-height:100%;border-radius:8px;';
            overlay.appendChild(fullImg);
            overlay.addEventListener('click', () => overlay.remove());
            document.body.appendChild(overlay);
        });
    });
}

function confirmAction(message) {
    return confirm(message);
}
