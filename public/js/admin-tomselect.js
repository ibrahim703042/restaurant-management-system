/**
 * Tom Select for .admin-ts-select (searchable selects).
 * In-modal selects init on first open (layout/width); others on DOM ready.
 */
(function () {
    if (typeof TomSelect === 'undefined') {
        return;
    }

    var defaults = {
        create: false,
        sortField: { field: 'text', direction: 'asc' },
        plugins: [],
        dropdownParent: document.body,
    };

    function placeholderFrom(el) {
        var p = el.getAttribute('data-placeholder');
        return p || 'Search…';
    }

    function initOne(el) {
        if (!el || el.classList.contains('tomselected-skip')) {
            return;
        }
        if (el.tomselect) {
            return;
        }
        var isMulti = el.multiple;
        var opts = Object.assign({}, defaults, {
            placeholder: placeholderFrom(el),
            maxOptions: 500,
        });
        if (isMulti) {
            opts.plugins = ['remove_button'];
        }
        try {
            new TomSelect(el, opts);
        } catch (e) {
            console.warn('TomSelect init failed', e);
        }
    }

    function initIn(root) {
        root = root || document;
        root.querySelectorAll('select.admin-ts-select').forEach(initOne);
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('select.admin-ts-select').forEach(function (sel) {
            if (!sel.closest('.modal')) {
                initOne(sel);
            }
        });
    });

    document.addEventListener('shown.bs.modal', function (ev) {
        ev.target.querySelectorAll('select.admin-ts-select').forEach(initOne);
    });

    window.adminTomSelectInit = initIn;
    window.adminTomSelectInitOne = initOne;
})();
