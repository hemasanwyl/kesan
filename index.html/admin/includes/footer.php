</div>
    </div>
</div>

<!-- jQuery (dibutuhkan DataTables) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables JS CDN -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // Matikan popup alert warning bawaan DataTables agar tidak mengganggu pengguna
    if ($.fn.dataTable) {
        $.fn.dataTable.ext.errMode = 'none';
    }

    // Inisialisasi otomatis untuk semua tabel yang memiliki class .datatable
    if ($.fn.DataTable) {
        $('.datatable').each(function() {
            var $table = $(this);
            if ($.fn.dataTable.isDataTable($table)) return;

            $table.DataTable({
                language: {
                    search: "Cari cepat di tabel:",
                    searchPlaceholder: "Ketik kata kunci...",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 data",
                    infoFiltered: "(difilter dari total _MAX_ data)",
                    zeroRecords: "Tidak ada data yang sesuai",
                    emptyTable: "Belum ada data yang tersedia di tabel",
                    paginate: {
                        first: "Awal",
                        last: "Akhir",
                        next: "&rsaquo;",
                        previous: "&lsaquo;"
                    }
                },
                pageLength: 10,
                autoWidth: false,
                ordering: true,
                order: [] // mempertahankan urutan awal dari query PHP
            });
        });
    }
});
</script>
</body>
</html>