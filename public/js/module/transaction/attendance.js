$( function(){

})

$('#add-attendance').on('submit', function(){
    const fAddComponent = $('#add-attendance')
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
        prompt('submit', 'Meeting Attendance', (confirm) => {
            if(confirm){
                apiCall('transaction/meeting-attendance/store', 'POST', 'add-attendance', {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                    },
                    null,
                    null,
                    true,
                    (res) => {
                        console.log(res)
                        $('.loading').hide()
                        Toastify({
                            text: `Berhasil simpan data Meeting Attendance, QR Code akan ditampilkan dan harap simpan atau screenshoot qr code anda`,
                            duration: 2000,
                            close: true,
                            gravity: "top",
                            callback: function() {
                                // renderView(
                                //     `${$('meta[name="baseurl"]').attr('content')}master-data/meeting-schedule`)
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
