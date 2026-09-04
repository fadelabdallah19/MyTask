(() => {
    const results = document.getElementById('task-results');

    if (!results) {
        return;
    }

    const form = document.getElementById('task-filter-form');
    const search = document.getElementById('search');
    const status = document.getElementById('status');
    const priority = document.getElementById('priority');
    const sort = document.getElementById('sort');
    const baseUrl = form?.getAttribute('action') || '/tasks';

    let debounceTimer;

    const refresh = () => {
        const params = new URLSearchParams();

        if (search?.value) params.set('search', search.value);
        if (status?.value) params.set('status', status.value);
        if (priority?.value) params.set('priority', priority.value);
        if (sort?.value) params.set('sort', sort.value);

        params.set('partial', '1');

        fetch(`${baseUrl}?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then((res) => res.text())
            .then((html) => {
                results.innerHTML = html;
            })
            .catch((err) => console.error('Filter error:', err));
    };

    const onDebouncedChange = (e) => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(refresh, 400);
    };

    search?.addEventListener('input', onDebouncedChange);
    status?.addEventListener('change', refresh);
    priority?.addEventListener('change', refresh);
    sort?.addEventListener('change', refresh);

    // Prevent the form's normal submit so fetch takes over.
    form?.addEventListener('submit', (e) => {
        e.preventDefault();
        refresh();
    });

    // Pagination links are fetched via AJAX; keep current filters.
    results.addEventListener('click', (e) => {
        const link = e.target.closest('#task-pagination a');
        if (!link) {
            return;
        }
        e.preventDefault();
        fetch(link.getAttribute('href'), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then((res) => res.text())
            .then((html) => {
                results.innerHTML = html;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            })
            .catch((err) => console.error('Pagination error:', err));
    });
})();