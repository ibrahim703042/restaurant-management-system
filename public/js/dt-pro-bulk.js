(function () {
    'use strict';

    /**
     * Initialise bulk-delete behaviour on a page.
     *
     * @param {object} opts
     * @param {jQuery.DataTable} opts.dt          - DataTables instance
     * @param {string}           opts.bulkUrl     - POST endpoint for bulk delete
     * @param {string}           opts.csrfToken   - CSRF token
     * @param {string}           [opts.confirmMsg]  - SweetAlert title
     * @param {string}           [opts.btnSelector] - selector for the "Delete selected" button
     * @param {string}           [opts.checkAll]    - selector for the header checkbox
     * @param {string}           [opts.checkRow]    - selector for row checkboxes
     */
    window.dtProBulk = function (opts) {
        var dt = opts.dt;
        var bulkUrl = opts.bulkUrl;
        var csrf = opts.csrfToken;
        var confirmTpl = opts.confirmMsg || 'Delete :count selected records?';
        var btnSel = opts.btnSelector || '#btnBulkDel';
        var checkAllSel = opts.checkAll || '#dtCheckAll';
        var checkRowSel = opts.checkRow || '.dt-pro-row-check';

        var $btn = $(btnSel);
        var $checkAll = $(checkAllSel);
        var $table = $(dt.table().node());

        function getChecked() {
            var ids = [];
            $table.find(checkRowSel + ':checked').each(function () { ids.push(+this.value); });
            return ids;
        }

        function syncBtn() {
            var ids = getChecked();
            if (ids.length) {
                $btn.removeClass('d-none').find('.dt-pro-bulk-count').text(ids.length);
            } else {
                $btn.addClass('d-none');
            }
        }

        $checkAll.on('change', function () {
            var checked = this.checked;
            $table.find(checkRowSel).prop('checked', checked);
            syncBtn();
        });

        $table.on('change', checkRowSel, function () {
            var total = $table.find(checkRowSel).length;
            var checked = $table.find(checkRowSel + ':checked').length;
            $checkAll.prop('checked', total > 0 && checked === total);
            syncBtn();
        });

        dt.on('draw', function () {
            $checkAll.prop('checked', false);
            syncBtn();
        });

        $btn.on('click', function () {
            var ids = getChecked();
            if (!ids.length) return;
            var msg = confirmTpl.replace(':count', ids.length);

            Swal.fire({
                title: msg,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: opts.deleteLabel || 'Delete'
            }).then(function (res) {
                if (!res.isConfirmed) return;
                fetch(bulkUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ ids: ids })
                }).then(function (r) { return r.json(); }).then(function (j) {
                    if (j.message) {
                        Swal.fire({ icon: 'success', title: j.message, timer: 1500, showConfirmButton: false });
                    }
                    dt.ajax.reload(null, false);
                    $checkAll.prop('checked', false);
                    syncBtn();
                }).catch(function () {
                    Swal.fire('Error', 'Bulk delete failed.', 'error');
                });
            });
        });
    };
})();
