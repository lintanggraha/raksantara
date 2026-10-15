{{--
    Shared hover/focus tooltip for the dashboard. Any element inside
    .grc-dashboard with data-tip shows it; the value is rendered as HTML, so
    callers must escape user data before putting it there.
--}}
<script>
(function () {
    'use strict';
    const root = document.querySelector('.grc-dashboard');
    if (!root) return;

    const tip = document.createElement('div');
    tip.className = 'grc-tip';
    tip.setAttribute('role', 'tooltip');
    tip.hidden = true;
    document.body.appendChild(tip);

    function show(el, x, y) {
        tip.innerHTML = el.getAttribute('data-tip');
        tip.hidden = false;
        const r = tip.getBoundingClientRect();
        let left = x + 12, top = y + 14;
        if (left + r.width > window.innerWidth - 8) left = x - r.width - 12;
        if (top + r.height > window.innerHeight - 8) top = y - r.height - 10;
        tip.style.left = Math.max(8, left) + 'px';
        tip.style.top = Math.max(8, top) + 'px';
    }

    root.addEventListener('mousemove', (event) => {
        const el = event.target.closest('[data-tip]');
        if (el) show(el, event.clientX, event.clientY); else tip.hidden = true;
    });
    root.addEventListener('mouseleave', () => { tip.hidden = true; });
    root.addEventListener('focusin', (event) => {
        const el = event.target.closest('[data-tip]');
        if (!el) return;
        const r = el.getBoundingClientRect();
        show(el, r.left, r.bottom);
    });
    root.addEventListener('focusout', () => { tip.hidden = true; });
    window.addEventListener('scroll', () => { tip.hidden = true; }, true);
})();
</script>
