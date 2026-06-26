$(document).ready(function () {
    configureProgressBar();
    ensureHistoryState(window.location.href, true);
    initEnhancedSelects($(document));

    $(document).on("click", "a", function (event) {
        if (!shouldHandleAsSpaLink(this, event)) {
            return;
        }

        event.preventDefault();
        renderView($(this).attr("href"), { source: this });
    });

    $(document).on("click", ".js-spa-back", function (event) {
        event.preventDefault();
        goBackInShell($(this).data("fallback-url"));
    });

    $(document).on("submit", "#render form", function (event) {
        const method = ($(this).attr("method") || "GET").toUpperCase();

        if (method !== "GET" || $(this).data("spa") === false || $(this).attr("target")) {
            return;
        }

        event.preventDefault();
        const action = $(this).attr("action") || window.location.href;
        const query = $(this).serialize();
        renderView(query ? `${action}?${query}` : action);
    });
});

function handleNavigationChange() {
    renderView(window.location.href, { history: false });
}

window.addEventListener("popstate", handleNavigationChange);

function renderView(route, options = {}) {
    const render = $("#render");
    const normalizedUrl = normalizeAppUrl(route);

    if (!render.length) {
        window.location.href = normalizedUrl.href;
        return;
    }

    ensureHistoryState(normalizedUrl.href, options.replace === true);
    syncActiveNavigation(normalizedUrl.href);

    $.ajax({
        url: normalizedUrl.href,
        method: "GET",
        beforeSend: () => {
            startNavigationFeedback();
        },
        success: (res) => {
            render.html(res);
            initPageEnhancements(render);
            syncActiveNavigation(normalizedUrl.href);
            finishNavigationFeedback();
        },
        error: (err) => {
            finishNavigationFeedback();
            onAjaxError(err);
        },
    });
}

function configureProgressBar() {
    if (window.NProgress) {
        NProgress.configure({
            showSpinner: false,
            minimum: 0.12,
            trickleSpeed: 120,
        });
    }
}

function startNavigationFeedback() {
    if (window.NProgress) {
        NProgress.start();
    }
    beforeAjaxSend();
    $("#render").attr("aria-busy", "true");
}

function finishNavigationFeedback() {
    if (window.NProgress) {
        NProgress.done();
    }
    $(".loading").hide();
    $("#render").removeAttr("aria-busy");
}

function normalizeAppUrl(route) {
    return new URL(route, window.location.origin);
}

function ensureHistoryState(route, replace = false) {
    const normalizedUrl = normalizeAppUrl(route);
    const current = `${window.location.pathname}${window.location.search}${window.location.hash}`;
    const target = `${normalizedUrl.pathname}${normalizedUrl.search}${normalizedUrl.hash}`;
    const currentDepth = Number(history.state?.spaDepth || 0);

    if (replace || current === "/redirect") {
        history.replaceState({ url: normalizedUrl.href, spaDepth: currentDepth }, "", target);
        return;
    }

    if (current !== target) {
        history.pushState({ url: normalizedUrl.href, spaDepth: currentDepth + 1 }, "", target);
    }
}

function shouldHandleAsSpaLink(link, event) {
    const $link = $(link);
    const href = $link.attr("href");

    if (
        event.defaultPrevented ||
        event.which > 1 ||
        event.metaKey ||
        event.ctrlKey ||
        event.shiftKey ||
        event.altKey ||
        !href ||
        href === "#" ||
        href.indexOf("javascript:") === 0 ||
        href.indexOf("mailto:") === 0 ||
        href.indexOf("tel:") === 0 ||
        $link.attr("target") ||
        $link.attr("download") ||
        $link.data("toggle") ||
        $link.data("spa") === false
    ) {
        return false;
    }

    const url = normalizeAppUrl(href);

    if (url.origin !== window.location.origin) {
        return false;
    }

    if (/\/(download|print)(\/|$)/.test(url.pathname)) {
        return false;
    }

    return $link.hasClass("spa_route") || $link.closest("#render, #sidebarnav").length > 0;
}

function initPageEnhancements(scope) {
    initEnhancedSelects(scope);

    if ($.fn.tooltip) {
        scope.find('[data-toggle="tooltip"], [data-bs-toggle="tooltip"]').tooltip();
    }

    collapseMobileSidebar();
    window.scrollTo({ top: 0, behavior: "auto" });
}

function collapseMobileSidebar() {
    if (window.matchMedia("(max-width: 767px)").matches) {
        $("body").removeClass("show-sidebar");
        $(".left-sidebar").removeClass("show-sidebar");
    }
}

function syncActiveNavigation(route) {
    const targetPath = normalizeAppUrl(route).pathname.replace(/\/+$/, "") || "/";

    $(".spa_route").each(function () {
        const linkPath = normalizeAppUrl($(this).attr("href")).pathname.replace(/\/+$/, "") || "/";
        const isActive = linkPath === targetPath;

        $(this).toggleClass("active", isActive);
        $(this).closest("li").toggleClass("active", isActive);
    });
}

function goBackInShell(fallbackUrl) {
    if (fallbackUrl) {
        renderView(fallbackUrl, { replace: true });
        return;
    }

    if (Number(history.state?.spaDepth || 0) > 0) {
        history.back();
    }
}

function initEnhancedSelects(scope = $(document)) {
    if ($.fn.select2) {
        scope.find('.select2').select2({
            width: '100%'
        });
    }
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
            const values = $(form[i]).val() || [];
            values.forEach((value) => {
                formData.append(`${$(form)[i].name}`, value);
            });
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
