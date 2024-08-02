var table;
$(function () {
    var move = "255px";

    $(".prev-btn").click(function () {
        $(".timeline-list").animate({ scrollLeft: "-=" + move });
    });

    $(".next-btn").click(function () {
        $(".timeline-list").animate({ scrollLeft: "+=" + move });
    });
    var form = $(".validation-wizard").show();
    var formInput = $("#add-meeting").find("input");

    $(".validation-wizard").steps({
        headerTag: "h6",
        bodyTag: "section",
        transitionEffect: "fade",
        titleTemplate: '<span class="step">#index#</span> #title#',
        labels: {
            finish: "Submit",
        },
        onStepChanging: function (event, currentIndex, newIndex) {
            for (let i = 0; i < formInput.length; i++) {
                const el = $(formInput[i]).attr("name");
                const val = $(`input[name="${el}"]`).val();
                if (el != "rooms[]") {
                    $("#preview").find(`input[name="preview-${el}"]`).val(val);
                } else {
                    const roomId = $(formInput[i]).attr("id");
                    const roomInput = $(`input[id="${roomId}"]`).is(":checked");
                    if (roomInput) {
                        const roomName = $(`input[id="${roomId}"]`).data(
                            "roomname"
                        );
                        $("#preview")
                            .find(`input[name="preview-${el}"]`)
                            .val(roomName);
                    }
                }
            }
            return (
                currentIndex > newIndex ||
                (!(3 === newIndex && Number($("#age-2").val()) < 18) &&
                    (currentIndex < newIndex &&
                        (form
                            .find(".body:eq(" + newIndex + ") label.error")
                            .remove(),
                        form
                            .find(".body:eq(" + newIndex + ") .error")
                            .removeClass("error")),
                    (form.validate().settings.ignore = ":disabled,:hidden"),
                    form.valid()))
            );
        },
        onFinishing: function (event, currentIndex) {
            return (
                (form.validate().settings.ignore = ":disabled"), form.valid()
            );
        },
        onFinished: function (event, currentIndex) {
            postSchedule();
        },
    }),
        $(".validation-wizard").validate({
            ignore: "",
            errorClass: "text-danger",
            successClass: "text-success",
            highlight: function (element, errorClass) {
                $(element).removeClass(errorClass);
            },
            unhighlight: function (element, errorClass) {
                $(element).removeClass(errorClass);
            },
            errorPlacement: function (error, element) {
                error.insertAfter(element);
            },
            rules: {
                email: {
                    email: !0,
                },
            },
        });

    $(".datetime").daterangepicker({
        timePicker: true,
        timePickerIncrement: 30,
        locale: {
            format: "MM/DD/YYYY h:mm A",
        },
    });

    $("#tgl_start").bootstrapMaterialDatePicker({ weekStart: 0, time: false });
    $("#tgl_end").bootstrapMaterialDatePicker({ weekStart: 0, time: false });

    $(".clockpicker")
        .clockpicker({
            donetext: "Done",
        })
        .find("input")
        .change(function () {
            console.log(this.value);
        });

    $(".select2-client").select2({
        theme: "bootstrap4",
        placeholder: "Choose Client",
        allowClear: true,
        debug: true,
    });

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
            )}master-data/meeting-schedule/data`,
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

function choosePackage(kd_pck, name) {
    $('input[name="package-name"]').val(name);
    $('input[name="package"]').val(kd_pck);
}

function setTglMeeting(el) {
    let tgl = $(el).data("tgl");
    $('input[name="tgl_meeting"]').val(tgl);

    let formEl = $("#add-meeting").find(".form-body");
    if ($(formEl).css("display") == "none") {
        $(formEl).slideDown();
        $("#client").select2({
            theme: "bootstrap-5",
            allowClear: true,
        });
        $('input[name="kuota"]').inputmask({
            regex: "^[1-9][0-9][0-9][0-9]?$|^10000$",
            placeholder: "",
        });
    }
}

function postSchedule() {
    prompt("submit", "Meeting Schedule", (confirm) => {
        if (confirm) {
            apiCall(
                "master-data/meeting-schedule/store",
                "POST",
                "add-meeting",
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
                        text: `Berhasil simpan data Meeting Schedule - QR Code dapat dilihat atau di generate di halaman list meeting schedule`,
                        duration: 2000,
                        close: true,
                        gravity: "top",
                        callback: function () {
                            renderView(
                                `${$('meta[name="baseurl"]').attr(
                                    "content"
                                )}master-data/meeting-schedule`
                            );
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
// $("#add-meeting").on("submit", function () {
//     const fAddComponent = $("#add-meeting");
//     var required = fAddComponent.find(".required");
//     var canInput = true;

//     required.removeClass("is-invalid");

//     // Form Validation
//     for (var i = 0; i < required.length; i++) {
//         if (required[i].value == "") {
//             canInput = false;
//             fAddComponent
//                 .find(`input[name="${required[i].name}"]`)
//                 .addClass("is-invalid");
//             fAddComponent
//                 .find(`select[name="${required[i].name}"]`)
//                 .addClass("is-invalid");
//             var form_name = required[i].id.replace("_", " ").toUpperCase();
//             Toastify({
//                 text: `Form ${form_name} is Required`,
//                 duration: 3000,
//                 close: true,
//                 gravity: "top",
//                 position: "right",
//                 style: {
//                     background: "linear-gradient(to right, #ff5f6d, #ffc371)",
//                 },
//             }).showToast();
//         }
//     }

//     if (canInput == true) {
//     }
// });

$("#form-filter").on("submit", function () {
    table.destroy();
    getData();
});

function getClientDetail(el) {
    var clientCode = $(el).val();
    $("#container-client").slideUp();
    if (clientCode != "") {
        apiCall(
            `master-data/client/get-detail/${clientCode}`,
            "GET",
            "",
            null,
            null,
            null,
            true,
            (res) => {
                $(".loading").hide();
                $("#container-client").slideDown();
                var contClient = $("#container-client").find("input");
                const dataClient = res.data;
                for (let i = 0; i < contClient.length; i++) {
                    let formId = $(contClient[i]).attr("id");
                    $(`#${formId}`).val(dataClient[formId]);
                }
            }
        );
    }
}

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

function deleteSchedule(id) {
    console.log(id);
}

function showQr(el, id) {
    var tagMeeting = $(el).data("tag");
    apiCall(
        `master-data/meeting-schedule/get-qr/${id}`,
        "GET",
        "",
        null,
        null,
        null,
        true,
        (res) => {
            $(".loading").hide();
            $("#container-qr").html("");
            var elImg = "";

            for (let i = 0; i < res.data.length; i++) {
                let pathQr = res.data[i].qr_path;
                pathQr = decodeURIComponent(pathQr);
                elImg += `<div class="col text-center">
                            <img src="${
                                res.asset_path
                            }/${pathQr}" alt="QR Path" class="img-fluid">
                            <p>QR Code - ${i + 1}</p>
                        </div>`;
                // var src = $("#modal-preview-qr").find("img").attr("src");
                // src = src + pathQr;
                // $("#modal-preview-qr").find("img").attr("src", src);
            }
            console.log(tagMeeting);
            $("#container-qr").html(elImg);
            $("#tag-meeting").html(tagMeeting);
            $("#modal-preview-qr").modal("toggle");
        }
    );
}

function generateQr(id) {
    apiCall(
        `master-data/meeting-schedule/generate-qr/${id}`,
        "GET",
        "",
        null,
        null,
        null,
        true,
        (res) => {
            console.log(res);
            $(".loading").hide();
            Toastify({
                text: `Berhasil Generate QR Code`,
                duration: 1000,
                close: true,
                gravity: "top",
                callback: function () {
                    table.ajax.reload();
                },
                position: "right",
                style: {
                    background: "linear-gradient(to right, #00b09b, #96c93d)",
                },
            }).showToast();
        }
    );
}
