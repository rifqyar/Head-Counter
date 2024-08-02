$(document).ready(function () {
    const link = $(".spa_route"),
        render = $("#render");

    link.on("click", function (t) {
        $('.spa_route').removeClass('active')
        $('.spa_route').parent().removeClass('active')
        if ($(this).attr("id") != "dashboard") {
            t.preventDefault();
            let route = $(this).attr("href");
            const currentURL = route;
            const newURL = currentURL.replace("{{ url('/') }}", "/");
            history.pushState({}, null, newURL);
            NProgress.configure({
                template: `<div class="progress bar">
                                <div class="progress-bar bg-danger" role="bar" style="width:50%; height:6px;"></div>
                            </div>`
            });
            $.ajax({
                url: route,
                method: "GET",
                beforeSend: () => {
                    NProgress.start();
                    beforeAjaxSend();
                },
                success: (res) => {
                    NProgress.done();
                    $(this).addClass('active')
                    $(this).parent().addClass('active')
                    render.html(res);
                    $(".loading").hide();
                },
                error: (err) => {
                    NProgress.done();
                    onAjaxError(err);
                },
            });

            NProgress.remove();
        }
    });
});

//render halaman ketika navigasi browser di klik
function handleNavigationChange(event) {
    if (event.state != null) {
        renderView(window.location.pathname);
    }
}

// Mendaftarkan event listener untuk event popstate
window.addEventListener("popstate", handleNavigationChange);

function renderView(route) {
    const currentURL = route;
    const newURL = currentURL.replace("{{ url('/') }}", "/");
    history.pushState({}, null, newURL);
    const render = $("#render");
    NProgress.configure({
        template: `<div class="progress bar">
                        <div class="progress-bar bg-danger" role="bar" style="width:50%; height:6px;"></div>
                    </div>`
    });

    $.ajax({
        url: route,
        method: "GET",
        beforeSend: () => {
            NProgress.start();
            beforeAjaxSend();
        },
        success: (res) => {
            NProgress.done();
            render.html(res);
            $(".loading").hide();
        },
        error: (err) => {
            NProgress.done();
            onAjaxError(err);
        },
    });
    NProgress.remove();
}

function beforeAjaxSend() {
    $(".loading").show();
}

function onAjaxError(err, statusCode = null) {
    $(".loading").hide();

    Toastify({
        text:
            err.responseJSON?.message == undefined
                ? "Something went error!, Please try again"
                : err.responseJSON.message,
        duration: 3000,
        close: true,
        gravity: "top",
        position: "right",
        style: {
            background: "linear-gradient(to right, #ff5f6d, #ffc371)",
        },
    }).showToast();
}

function fillResData(form_id) {
    // .form-input = mandatory class to provide form value automatically
    // to use that feature form name must be same as database field
    var form = $(`#${form_id}`).find(".form-input");
    var formData = new FormData();
    for (let i = 0; i < form.length; i++) {
        // Fill formData
        if ($(form[i]).attr("multiple") != "multiple") {
            if (
                $(form[i]).attr("type") == "checkbox" ||
                $(form[i]).attr("type") == "radio"
            ) {
                if ($(form[i]).is(":checked")) {
                    formData.append(`${$(form)[i].name}`, $(form)[i].value);
                }
            } else {
                formData.append(`${$(form)[i].name}`, $(form)[i].value);
            }
        } else {
            const val = $(form[i]).val().toString();
            formData.append(`${$(form)[i].name}`, val);
        }
    }

    return formData;
}

function apiCall(
    url,
    method,
    form_id,
    header = null,
    beforeAjax = null,
    onError = null,
    showError = true,
    callback
) {
    let data = form_id != "" ? fillResData(form_id) : null;

    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $(`meta[name="csrf-token"]`).attr("content"),
        },
    });

    $.ajax({
        url: `${$('meta[name="baseurl"]').attr("content")}${url}`,
        method: method,
        headers: header,
        data: data,
        processData: false,
        contentType: false,
        beforeSend: () => {
            if (beforeAjax == null) {
                beforeAjaxSend();
            } else {
                beforeAjax();
            }
        },
        success: (res) => {
            if (res.status?.code) {
                if (res.status?.code == 200) {
                    callback(res);
                } else if (onError == null) {
                    onAjaxError(res.status.msg, res.status.code);
                } else {
                    onError(res);
                }
            } else if (res.status) {
                callback(res);
            } else if (onError == null) {
                onAjaxError("There is an Error while fetching data");
            } else {
                onError(res);
            }
        },
        error: (err, xhr) => {
            if (err.statusText == "Unauthorized") {
                Toastify({
                    text: "Login Session Expired",
                    duration: 1000,
                    close: true,
                    gravity: "top",
                    callback: function () {
                        window.location.reload();
                    },
                    position: "right",
                    style: {
                        background:
                            "linear-gradient(to right, #ff5f6d, #ffc371)",
                    },
                }).showToast();
            } else {
                if (showError == 1) {
                    if (onError == null) {
                        onAjaxError(err, err.status);
                    } else {
                        onError(err);
                    }
                }
            }
        },
    });
}

function prompt(type, data, callback) {
    let text, icon, confirmText;
    switch (type) {
        case "submit":
            text = `Apakah anda yakin ingin menyimpan data ${data}?`;
            icon = "info";
            confirmText = "Ya, Simpan Data";
            break;

        case "update":
            text = `Apakah anda yakin ingin merubah data ${data}?`;
            icon = "info";
            confirmText = "Ya, Rubah Data";
            break;

        case "delete":
            text = `Apakah anda yakin ingin menghapus data ${data}?`;
            icon = "warning";
            confirmText = "Ya, Nonaktifkan Data";
            break;

        case "active":
            text = `Apakah anda yakin ingin mengaktifkan data ${data}?`;
            icon = "info";
            confirmText = "Ya, Aktifkan Data";
            break;

        case "approve":
            text = `Apakah anda yakin ingin approve ${data}?`;
            icon = "info";
            confirmText = `Ya, Approve ${data}`;
            break;

        case "reject":
            text = `Apakah anda yakin ingin reject ${data}?`;
            icon = "info";
            confirmText = `Ya, Reject ${data}`;
            break;

        case "cancel":
            text = `Apakah anda yakin ingin cancel ${data}?`;
            icon = "info";
            confirmText = `Ya, Reject ${data}`;
            break;

        case "set-default":
            text = `Apakah anda yakin ingin menjadikan ${data} ini sebagai default?`;
            icon = "info";
            confirmText = "Ya, Gunakan Data Ini";
            break;

        default:
            break;
    }
    Swal.fire({
        title: text,
        type: icon,
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "Batal",
        confirmButtonText: confirmText,
    }).then((result) => {
        if (result.value) {
            callback(true);
        } else {
            callback(false);
        }
    });
}
