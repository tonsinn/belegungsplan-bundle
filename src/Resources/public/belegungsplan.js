document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.belegungsplan-year-select').forEach(function (form) {
        var select = form.querySelector('select');
        var btn = form.querySelector('.belegungsplan-year-select__submit');
        if (!select) return;
        if (btn) btn.style.display = 'none';
        select.addEventListener('change', function () {
            form.submit();
        });
    });
});
