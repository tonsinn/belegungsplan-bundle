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

    if (typeof bootstrap === 'undefined') {
        var backdrop = document.createElement('div');
        backdrop.style.cssText = 'display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1040;';

        function openModal(modal) {
            document.body.appendChild(backdrop);
            backdrop.style.display = 'block';
            modal.style.display = 'block';
            modal.removeAttribute('aria-hidden');
            document.body.classList.add('modal-open');
        }

        function closeModal(modal) {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            backdrop.style.display = 'none';
            document.body.classList.remove('modal-open');
        }

        document.querySelectorAll('[data-bs-toggle="modal"]').forEach(function (trigger) {
            trigger.style.cursor = 'pointer';
            trigger.addEventListener('click', function () {
                var target = document.querySelector(trigger.getAttribute('data-bs-target'));
                if (target) openModal(target);
            });
        });

        document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                closeModal(btn.closest('.modal'));
            });
        });

        backdrop.addEventListener('click', function () {
            var open = document.querySelector('.modal[style*="display: block"], .modal[style*="display:block"]');
            if (open) closeModal(open);
        });
    }
});
