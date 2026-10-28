{{-- Builds the chapter list on the left of the guide from its headings, and highlights where the reader is. Plain JavaScript, no Blade code, shared by the admin page and the published copy. --}}
<script>
(function () {
    var side = document.querySelector('[data-guide-side]');
    var root = document.querySelector('.guide');
    if (!side || !root) return;
    var slug = function (t) { return t.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, ''); };
    var list = document.createElement('ol');
    var entries = [];
    root.querySelectorAll('section[id]').forEach(function (sec, n) {
        var h2 = sec.querySelector('h2');
        if (!h2) return;
        var li = document.createElement('li');
        var a = document.createElement('a');
        a.href = '#' + sec.id;
        a.innerHTML = '<span class="gn-num">' + (n + 1) + '</span><span>' + h2.textContent + '</span>';
        li.appendChild(a);
        var sub = document.createElement('ul');
        sec.querySelectorAll('h3').forEach(function (h3) {
            if (!h3.id) h3.id = sec.id + '-' + slug(h3.textContent);
            var si = document.createElement('li');
            var sa = document.createElement('a');
            sa.href = '#' + h3.id;
            sa.textContent = h3.textContent;
            si.appendChild(sa);
            sub.appendChild(si);
            entries.push({ el: h3, link: sa, li: li });
        });
        if (sub.children.length) li.appendChild(sub);
        list.appendChild(li);
        entries.push({ el: h2, link: a, li: li, chapter: true });
    });
    side.appendChild(list);

    var current = null;
    var update = function () {
        var best = entries[0];
        entries.forEach(function (e) {
            if (e.el.getBoundingClientRect().top <= 140) best = e;
        });
        if (best === current) return;
        current = best;
        entries.forEach(function (e) { e.link.removeAttribute('aria-current'); e.li.removeAttribute('data-open'); });
        best.link.setAttribute('aria-current', 'true');
        best.li.setAttribute('data-open', 'true');
        if (!best.chapter) best.li.firstChild.setAttribute('data-trail', 'true'); else best.li.firstChild.removeAttribute('data-trail');
    };
    var ticking = false;
    window.addEventListener('scroll', function () {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(function () { ticking = false; update(); });
    }, { passive: true });
    update();
})();
</script>
