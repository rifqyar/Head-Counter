var table
$(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    if ($('.data-table').length) {
        table = $('.data-table').DataTable({
        responsive: true,
        autoWidth: false,
        processing: true,
        serverSide: true,
        ajax: {
            url: `${$('meta[name="baseurl"]').attr('content')}setting/permission/data`,
            method: "POST",
            data: function(data) {
                data._token = `${$('meta[name="csrf-token"]').attr('content')}`
            },
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                className: 'text-muted',
                width: '20px'
            },
            {
                data: 'name',
                name: 'name'
            },
            {
                data: 'roles_count',
                name: 'roles_count',
                className: 'text-muted'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                className: 'text-right',
                width: '200px'
            },
        ],
        fnDrawCallback: () => {
            $('[data-toggle="tooltip"]').tooltip()
        },
        });
    }
})

$(document).off('click.permissionSettings', '#btn-update-permission').on('click.permissionSettings', '#btn-update-permission', function () {
    apiCall($(this).data('url'), 'POST', 'edit-permission', {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
        },
        null,
        null,
        true,
        () => {
            $('.loading').hide()
            renderView(`${$('meta[name="baseurl"]').attr('content')}setting/permission`)
        })
})

$(document).off('submit.permissionSettings', '#add-permission').on('submit.permissionSettings', '#add-permission', function () {
    const fAddComponent = $('#add-permission')
    var required = fAddComponent.find('.required')
    var canInput = true

    required.removeClass('is-invalid')

    // Form Validation
    for (var i = 0; i < required.length; i++) {
        if (required[i].value == '') {
            canInput = false
            fAddComponent.find(`input[name="${required[i].name}"]`).addClass('is-invalid')
            fAddComponent.find(`select[name="${required[i].name}"]`).addClass('is-invalid')
            var form_name = required[i].id.replace('_', ' ').toUpperCase()
            Toastify({
                text: `Form ${form_name} is Required`,
                duration: 3000,
                close: true,
                gravity: "top",
                position: "right",
                style: {
                    background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                }
            }).showToast();
        }
    }

    if (canInput == true) {
        prompt('submit', 'Permission', (confirm) => {
            if(confirm){
                apiCall('setting/permission/store', 'POST', 'add-permission', {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                    },
                    null,
                    null,
                    true,
                    (res) => {
                        console.log(res)
                        $('.loading').hide()
                        Toastify({
                            text: `Berhasil simpan data permission`,
                            duration: 1000,
                            close: true,
                            gravity: "top",
                            callback: function() {
                                renderView(
                                    `${$('meta[name="baseurl"]').attr('content')}setting/permission`)
                            },
                            position: "right",
                            style: {
                                background: "linear-gradient(to right, #00b09b, #96c93d)",
                            }

                        }).showToast();
                    })
            }
        })
    }
})
