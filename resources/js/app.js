document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('time[datetime]').forEach(function (el) {
        const date = new Date(el.getAttribute('datetime'));
        if (isNaN(date.getTime())) {
            return;
        }
        const options = {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        };
        el.textContent = new Intl.DateTimeFormat(undefined, options).format(date);
        el.removeAttribute('datetime');
    });

    document.querySelectorAll('input[type="datetime-local"][data-utc]').forEach(function (input) {
        const utc = input.getAttribute('data-utc');
        if (!input.value && utc && !isNaN(new Date(utc).getTime())) {
            const local = new Date(utc);
            const pad = (n) => String(n).padStart(2, '0');
            input.value = [
                local.getFullYear(),
                '-',
                pad(local.getMonth() + 1),
                '-',
                pad(local.getDate()),
                'T',
                pad(local.getHours()),
                ':',
                pad(local.getMinutes()),
            ].join('');
        }
    });
});
