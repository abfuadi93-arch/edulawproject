const ADSENSE_CLIENT = 'ca-pub-7017591389930348';

function initializeMobileNavigation() {
    const header = document.querySelector('[data-site-header]');

    if (!header) {
        return;
    }

    const button = header.querySelector('[data-mobile-menu-button]');
    const menu = header.querySelector('[data-mobile-navigation]');
    const openIcon = header.querySelector('[data-mobile-menu-open-icon]');
    const closeIcon = header.querySelector('[data-mobile-menu-close-icon]');
    const firstLink = header.querySelector('[data-mobile-first-link]');

    if (!button || !menu || !openIcon || !closeIcon) {
        return;
    }

    let isOpen = false;

    const setOpen = (open, restoreFocus = false) => {
        isOpen = open;
        menu.hidden = !open;
        openIcon.hidden = open;
        closeIcon.hidden = !open;
        button.setAttribute('aria-expanded', String(open));
        button.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');

        if (open) {
            window.requestAnimationFrame(() => firstLink?.focus());
        } else if (restoreFocus) {
            window.requestAnimationFrame(() => button.focus());
        }
    };

    button.addEventListener('click', () => setOpen(!isOpen));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && isOpen) {
            setOpen(false, true);
        }
    });

    document.addEventListener('click', (event) => {
        if (isOpen && !header.contains(event.target)) {
            setOpen(false);
        }
    });

    window.addEventListener('resize', () => {
        if (isOpen && window.innerWidth >= 1280) {
            setOpen(false);
        }
    });
}

function initializeOpportunityFilters() {
    document.querySelectorAll('[data-opportunity-filters]').forEach((root) => {
        const toggle = root.querySelector('[data-opportunity-filters-toggle]');
        const panel = root.querySelector('[data-opportunity-filters-panel]');

        if (!toggle || !panel) {
            return;
        }

        toggle.addEventListener('click', () => {
            const open = panel.hidden;
            panel.hidden = !open;
            toggle.setAttribute('aria-expanded', String(open));
        });
    });
}

function initializePosterSliders() {
    document.querySelectorAll('[data-opportunity-poster-slider]').forEach((slider) => {
        const slides = [...slider.querySelectorAll('[data-poster-slide]')];
        const dots = [...slider.querySelectorAll('[data-poster-dot]')];
        const counter = slider.querySelector('[data-poster-counter]');
        let active = 0;

        if (slides.length < 2) {
            return;
        }

        const show = (index) => {
            active = (index + slides.length) % slides.length;

            slides.forEach((slide, slideIndex) => {
                slide.hidden = slideIndex !== active;
            });

            dots.forEach((dot, dotIndex) => {
                const selected = dotIndex === active;
                dot.classList.toggle('w-8', selected);
                dot.classList.toggle('bg-brand-navy', selected);
                dot.classList.toggle('w-2.5', !selected);
                dot.classList.toggle('bg-slate-300', !selected);
                dot.classList.toggle('hover:bg-slate-400', !selected);

                if (selected) {
                    dot.setAttribute('aria-current', 'true');
                } else {
                    dot.removeAttribute('aria-current');
                }
            });

            if (counter) {
                counter.textContent = String(active + 1).padStart(2, '0');
            }
        };

        slider.querySelector('[data-poster-previous]')?.addEventListener('click', () => show(active - 1));
        slider.querySelector('[data-poster-next]')?.addEventListener('click', () => show(active + 1));
        dots.forEach((dot) => dot.addEventListener('click', () => show(Number(dot.dataset.posterDot))));

        slider.addEventListener('keydown', (event) => {
            if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            show(active + (event.key === 'ArrowRight' ? 1 : -1));
        });
    });
}

function initializeDeferredAds() {
    let loaded = false;
    const engagementEvents = ['pointerdown', 'keydown', 'scroll'];

    const load = () => {
        if (loaded) {
            return;
        }

        loaded = true;
        engagementEvents.forEach((eventName) => window.removeEventListener(eventName, load));

        const script = document.createElement('script');
        script.async = true;
        script.crossOrigin = 'anonymous';
        script.src = `https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=${ADSENSE_CLIENT}`;
        document.head.append(script);
    };

    engagementEvents.forEach((eventName) => {
        window.addEventListener(eventName, load, { once: true, passive: true });
    });

    const adSlots = document.querySelectorAll('ins.adsbygoogle');

    if (adSlots.length === 0) {
        return;
    }

    if (!('IntersectionObserver' in window)) {
        load();

        return;
    }

    const observer = new IntersectionObserver((entries) => {
        if (entries.some((entry) => entry.isIntersecting)) {
            observer.disconnect();
            load();
        }
    }, { rootMargin: '600px' });

    adSlots.forEach((slot) => observer.observe(slot));
}

function initialize() {
    initializeMobileNavigation();
    initializeOpportunityFilters();
    initializePosterSliders();
    initializeDeferredAds();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize, { once: true });
} else {
    initialize();
}
