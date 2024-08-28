var table;
$(function () {
    getData();
});

function getData() {
    table = $(".data-table").DataTable({
        responsive: true,
        scrollX: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: `${$('meta[name="baseurl"]').attr(
                "content"
            )}transaction/meeting-attendance/data`,
            method: "POST",
            data: function (data) {
                data._token = `${$('meta[name="csrf-token"]').attr("content")}`;
                data.client = $('input[name="client"]').val();
                data.tgl = $('input[name="tgl"]').val();
            },
        },
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                className: "text-center",
                width: "20px",
                orderable: false,
                searchable: false,
            },
            {
                data: "code_client",
                name: "code_client",
                className: "text-center",
            },
            {
                data: "tgl_meeting",
                name: "tgl_meeting",
                className: "text-center",
            },
            {
                data: "jam_mulai",
                name: "jam_mulai",
                className: "text-center",
            },
            {
                data: "jam_selesai",
                name: "jam_selesai",
                className: "text-center",
            },
            {
                data: "kuota",
                name: "kuota",
                className: "text-center",
            },
            {
                data: "ruangan.name",
                name: "ruangan.name",
                className: "text-center",
            },
            {
                data: "paket.name",
                name: "paket.name",
                className: "text-center",
            },
            {
                data: "action",
                name: "action",
                orderable: false,
                searchable: false,
                className: "text-center",
                width: "200px",
            },
        ],
        fnDrawCallback: () => {
            const tooltipTriggerList = document.querySelectorAll(
                '[data-bs-toggle="tooltip"]'
            );
            const tooltipList = [...tooltipTriggerList].map(
                (tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl)
            );
        },
    });
}

$("#add-attendance").on("submit", function () {
    const fAddComponent = $("#add-attendance");
    var required = fAddComponent.find(".required");
    var canInput = true;

    required.removeClass("is-invalid");

    // Form Validation
    for (var i = 0; i < required.length; i++) {
        if (required[i].value == "") {
            canInput = false;
            fAddComponent
                .find(`input[name="${required[i].name}"]`)
                .addClass("is-invalid");
            fAddComponent
                .find(`select[name="${required[i].name}"]`)
                .addClass("is-invalid");
            var form_name = required[i].id.replace("_", " ").toUpperCase();
            Toastify({
                text: `Form ${form_name} is Required`,
                duration: 3000,
                close: true,
                gravity: "top",
                position: "right",
                style: {
                    background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                },
            }).showToast();
        }
    }

    if (canInput == true) {
        prompt("submit", "Meeting Attendance", (confirm) => {
            if (confirm) {
                apiCall(
                    "transaction/meeting-attendance/store",
                    "POST",
                    "add-attendance",
                    {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content"
                        ),
                    },
                    null,
                    null,
                    true,
                    (res) => {
                        console.log(res);
                        $(".loading").hide();
                        Toastify({
                            text: `Berhasil simpan data Meeting Attendance, QR Code akan ditampilkan dan harap simpan atau screenshoot qr code anda`,
                            duration: 2000,
                            close: true,
                            gravity: "top",
                            callback: function () {
                                // renderView(
                                //     `${$('meta[name="baseurl"]').attr('content')}master-data/meeting-schedule`)
                            },
                            position: "right",
                            style: {
                                background:
                                    "linear-gradient(to right, #00b09b, #96c93d)",
                            },
                        }).showToast();
                    }
                );
            }
        });
    }
});

$("#form-filter").on("submit", function () {
    table.destroy();
    getData();
});

function resetFilter() {
    $('input[name="client"]').val("");

    var now = new Date();
    var day = ("0" + now.getDate()).slice(-2);
    var month = ("0" + (now.getMonth() + 1)).slice(-2);

    var today = now.getFullYear() + "-" + month + "-" + day;
    $('input[name="tgl"]').val(today);

    table.destroy();
    getData();
}
