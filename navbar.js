// ═══════════════════════════════════════
//  navbar.js — Responsive Hamburger Menu
//  Slide-in drawer from the RIGHT
// ═══════════════════════════════════════

(function () {
    'use strict';

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function () {

        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const mobileMenu   = document.getElementById('mobileMenu');
        const menuBackdrop = document.getElementById('menuBackdrop');
        const drawerClose  = document.getElementById('drawerClose');
        const drawer       = document.querySelector('.mobile-menu-drawer');

        // Guard: if navbar elements don't exist on this page, do nothing
        if (!hamburgerBtn || !mobileMenu) return;

        // ── OPEN ──────────────────────────────
        function openMenu() {
            // 1. Make the overlay visible (display:block)
            mobileMenu.classList.add('open');

            // 2. Trigger reflow so the CSS transition fires on the drawer
            //    (without this, the initial transform:translateX(100%) → 0 won't animate)
            drawer.getBoundingClientRect();

            // 3. Animate backdrop in
            menuBackdrop.style.opacity = '1';

            // 4. Slide drawer in from right
            drawer.style.transform = 'translateX(0)';

            // 5. Hamburger → X
            hamburgerBtn.classList.add('open');
            hamburgerBtn.setAttribute('aria-expanded', 'true');

            // 6. Lock body scroll
            document.body.style.overflow = 'hidden';
        }

        // ── CLOSE ─────────────────────────────
        function closeMenu() {
            // 1. Slide drawer back out to the right
            drawer.style.transform = 'translateX(100%)';

            // 2. Fade backdrop out
            menuBackdrop.style.opacity = '0';

            // 3. After transition finishes, hide the overlay entirely
            drawer.addEventListener('transitionend', function handler() {
                mobileMenu.classList.remove('open');
                drawer.removeEventListener('transitionend', handler);
            });

            // 4. X → Hamburger
            hamburgerBtn.classList.remove('open');
            hamburgerBtn.setAttribute('aria-expanded', 'false');

            // 5. Restore body scroll
            document.body.style.overflow = '';
        }

        // ── EVENT LISTENERS ───────────────────

        // Hamburger button toggles the menu
        hamburgerBtn.addEventListener('click', function () {
            mobileMenu.classList.contains('open') ? closeMenu() : openMenu();
        });

        // Backdrop click closes
        if (menuBackdrop) {
            menuBackdrop.addEventListener('click', closeMenu);
        }

        // Drawer close button
        if (drawerClose) {
            drawerClose.addEventListener('click', closeMenu);
        }

        // Escape key closes
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && mobileMenu.classList.contains('open')) {
                closeMenu();
            }
        });

        // Close on any drawer nav link click
        document.querySelectorAll('.drawer-nav a').forEach(function (link) {
            link.addEventListener('click', closeMenu);
        });

        // ── RESIZE HANDLER ────────────────────
        // If user resizes to desktop width while drawer is open, close it cleanly
        window.addEventListener('resize', function () {
            if (window.innerWidth > 900 && mobileMenu.classList.contains('open')) {
                // Close without animation on resize
                mobileMenu.classList.remove('open');
                drawer.style.transform = 'translateX(100%)';
                menuBackdrop.style.opacity = '0';
                hamburgerBtn.classList.remove('open');
                hamburgerBtn.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }
        });

    });

})();