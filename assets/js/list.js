document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    const form = document.getElementById('documentFilterForm');
    if (form) {
        // Xử lý submit form
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            } else {
                form.classList.add('was-validated');
            }
        });

        // Xử lý thay đổi select để tự động submit
        const selects = form.querySelectorAll('select');
        selects.forEach(select => {
            select.addEventListener('change', function() {
                form.submit();
            });
        });
    }
});