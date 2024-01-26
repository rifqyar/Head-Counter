var table
$( function(){
    $('input[name="company_phone"]').inputmask({ mask: '9999999999999', placeholder: '' })

    table = $('.data-table').DataTable({
        responsive: true,
        scrollX: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: `${$('meta[name="baseurl"]').attr('content')}master-data/client/data`,
            method: "POST",
            data: function(data) {
                data._token = `${$('meta[name="csrf-token"]').attr('content')}`
            },
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                className: 'text-center',
                width: '20px'
            },
            {
                data: 'code',
                name: 'code',
                className: 'text-center'
            },
            {
                data: 'name',
                name: 'name',
                className: 'text-center'
            },
            {
                data: 'contact_person',
                name: 'contact_person',
                className: 'text-center'
            },
            {
                data: 'company_phone',
                name: 'company_phone',
                className: 'text-center'
            },
            {
                data: 'email',
                name: 'email',
                className: 'text-center'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                className: 'text-center',
                width: '200px'
            },
        ],
        fnDrawCallback: () => {
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
        },
    });
})

$('#add-client').on('submit', function(){
    const fAddComponent = $('#add-client')
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
        prompt('submit', 'Client', (confirm) => {
            if(confirm){
                apiCall('master-data/client/store', 'POST', 'add-client', {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                    },
                    null,
                    null,
                    true,
                    (res) => {
                        console.log(res)
                        $('.loading').hide()
                        Toastify({
                            text: `Berhasil simpan data client`,
                            duration: 1000,
                            close: true,
                            gravity: "top",
                            callback: function() {
                                renderView(
                                    `${$('meta[name="baseurl"]').attr('content')}master-data/client`)
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

function deleteClient(id){
    console.log(id)
}
