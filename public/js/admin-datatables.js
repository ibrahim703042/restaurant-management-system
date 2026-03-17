/**
 * Shared DataTables defaults for admin listing pages.
 */
(function ($) {
    if (typeof $ === 'undefined' || !$.fn || !$.fn.dataTable) {
        return;
    }

    $.extend(true, $.fn.dataTable.defaults, {
        processing: true,
        pageLength: 25,
        lengthMenu: [
            [10, 25, 50, 100],
            [10, 25, 50, 100],
        ],
        dom:
            '<"row align-items-center mb-2 gx-0 gy-2"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row align-items-center mt-2 gx-0"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        language: {
            processing: 'Loading…',
            search: '',
            searchPlaceholder: 'Search table…',
            lengthMenu: 'Show _MENU_ rows',
            info: 'Showing _START_–_END_ of _TOTAL_',
            infoEmpty: 'No rows',
            infoFiltered: '(filtered from _MAX_)',
            paginate: {
                first: '«',
                previous: '‹',
                next: '›',
                last: '»',
            },
        },
    });
})(window.jQuery);
