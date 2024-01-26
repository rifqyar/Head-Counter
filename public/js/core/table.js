$.extend(true, $.fn.dataTable.defaults, {
    language: {
        lengthMenu: "Menampilkan _MENU_ entri",
        info: "Menampilkan _START_ hingga _END_ dari _TOTAL_ entri",
        infoEmpty: "Tidak ada entri yang tersedia",
        infoFiltered: "(disaring dari _MAX_ total entri)",
        search: "Cari:",
        paginate: {
            first: "Pertama",
            last: "Terakhir",
            next: "Selanjutnya",
            previous: "Sebelumnya"
        }
    }
});
$.extend(DataTable.ext.classes, {
    sProcessing: "spinner",
});

function ImportExport(table){
    $('#printButton').click(function() {
        // Call print function for DataTable
        table.button('.buttons-print').trigger();
    });

    $('#excelButton').click(function() {
        // Call print function for DataTable
        table.button('.buttons-excel').trigger();
    });

    $('#copyButton').click(function() {
        // Call print function for DataTable
        table.button('.buttons-copy').trigger();
    });

    $('#pdfButton').click(function() {
        // Call print function for DataTable
        table.button('.buttons-pdf').trigger();
    });

    $('#reloadButton').click(function() {
        // Reload DataTable
        dataTable.ajax.reload();
    });
}