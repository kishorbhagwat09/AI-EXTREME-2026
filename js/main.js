/**
 * AI EXTREME 2026 - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {
    initNavbar();
    initMobileMenu();
    initParticles();
    initScrollAnimations();
    initSiteMedia();
});

function initSiteMedia() {
    const poster = document.getElementById('event-poster-image');
    const posterLink = document.getElementById('event-poster-link');
    if (!poster || !posterLink) return;

    const mediaEndpoint = new URL('php/site-media.php', window.location.href);
    mediaEndpoint.searchParams.set('t', Date.now().toString());

    fetch(mediaEndpoint, { headers: { Accept: 'application/json' }, cache: 'no-store' })
        .then(response => response.ok ? response.json() : Promise.reject(new Error('Unable to load site media')))
        .then(media => {
            if (!media.poster_image) return;
            const posterUrl = new URL(media.poster_image, window.location.href).href;
            poster.src = posterUrl;
            posterLink.href = posterUrl;
        })
        .catch(() => {});
}

function initNavbar() {
    const navbar = document.querySelector('.navbar');
    if (!navbar) return;

    const handleScroll = () => {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    };

    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();

    // Active nav link
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    document.querySelectorAll('.navbar-nav a, .mobile-menu a').forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage || (currentPage === '' && href === 'index.html')) {
            link.classList.add('active');
        }
    });
}

function initMobileMenu() {
    const hamburger = document.querySelector('.hamburger');
    const mobileMenu = document.querySelector('.mobile-menu');
    if (!hamburger || !mobileMenu) return;

    hamburger.addEventListener('click', () => {
        hamburger.classList.toggle('active');
        mobileMenu.classList.toggle('active');
        document.body.style.overflow = mobileMenu.classList.contains('active') ? 'hidden' : '';
    });

    mobileMenu.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            hamburger.classList.remove('active');
            mobileMenu.classList.remove('active');
            document.body.style.overflow = '';
        });
    });
}

function initParticles() {
    const container = document.querySelector('.hero-particles');
    if (!container) return;

    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReduced) return;

    for (let i = 0; i < 30; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.style.left = Math.random() * 100 + '%';
        particle.style.animationDelay = Math.random() * 8 + 's';
        particle.style.animationDuration = (6 + Math.random() * 6) + 's';
        container.appendChild(particle);
    }
}

function initScrollAnimations() {
    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReduced) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

    document.querySelectorAll('.glass-card, .highlight-card, .info-card, .timeline-item').forEach(el => {
        el.style.opacity = '0';
        observer.observe(el);
    });
}
