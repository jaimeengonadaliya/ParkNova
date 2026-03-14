// Admin Scripts
$(document).ready(function () {
    // Initialize common datatables if class exists
    if ($('.admin-datatable').length > 0) {
        $('.admin-datatable').DataTable({
            "pageLength": 10,
            "order": [[0, 'desc']],
            "language": {
                "search": "",
                "searchPlaceholder": "Search records..."
            },
            "dom": "<'row mb-3'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 d-flex justify-content-end'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
        });
        $('.dataTables_filter input').addClass('form-control rounded-pill ms-2').css('width', '250px');
        $('.dataTables_length select').addClass('form-select border-0 bg-light d-inline-block w-auto');
    }
});


