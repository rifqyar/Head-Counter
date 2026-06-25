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
            url: `${$('meta[name="baseurl"]').attr('content')}setting/role/data`,
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
                data: 'permissions_count',
                name: 'permissions_count',
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

$(document).off('click.roleSettings', '#check-all-permissions').on('click.roleSettings', '#check-all-permissions', function () {
    $('.permission-checkbox').prop('checked', true)
})

$(document).off('click.roleSettings', '#clear-all-permissions').on('click.roleSettings', '#clear-all-permissions', function () {
    $('.permission-checkbox').prop('checked', false)
})

$(document).off('submit.roleSettings', '#add-role').on('submit.roleSettings', '#add-role', function () {
    const form = $('#add-role')
    if (!form.find('[name="name"]').val()) {
        form.find('[name="name"]').addClass('is-invalid')
        return
    }

    prompt('submit', 'Role', (confirm) => {
        if (confirm) {
            apiCall('setting/role/store', 'POST', 'add-role', {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }, null, null, true, () => {
                $('.loading').hide()
                renderView(`${$('meta[name="baseurl"]').attr('content')}setting/role`)
            })
        }
    })
})

$(document).off('submit.roleSettings', '#manage-permission').on('submit.roleSettings', '#manage-permission', function () {
    const fAddComponent = $('#manage-permission')
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
        prompt('submit', 'Manage Permission', (confirm) => {
            if(confirm){
                apiCall('setting/role/manage-permission', 'POST', 'manage-permission', {
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
                                    `${$('meta[name="baseurl"]').attr('content')}setting/role`)
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
