<script>
    (function () {
        function initBookingWizard() {
            var wizard = $('.booking-wizard');

            if (!wizard.length || typeof $.fn.steps !== 'function' || wizard.data('booking-wizard-ready')) {
                return;
            }

            if ($.fn.select2) {
                wizard.find('.select2').each(function () {
                    if ($(this).hasClass('select2-hidden-accessible')) {
                        $(this).select2('destroy');
                    }
                });
            }

            wizard.data('booking-wizard-ready', true).show().steps({
                headerTag: 'h6',
                bodyTag: 'section',
                transitionEffect: 'fade',
                autoFocus: true,
                titleTemplate: '<span class="step">#index#</span> #title#',
                labels: {
                    finish: 'Save Booking',
                    next: 'Next',
                    previous: 'Back'
                },
                onStepChanging: function (event, currentIndex, newIndex) {
                    var form = wizard.closest('form');

                    if (currentIndex > newIndex || typeof form.validate !== 'function') {
                        return true;
                    }

                    form.validate().settings.ignore = ':disabled,:hidden';
                    return form.valid();
                },
                onFinishing: function () {
                    var form = wizard.closest('form');

                    if (typeof form.validate === 'function') {
                        form.validate().settings.ignore = ':disabled';
                        return form.valid();
                    }

                    return true;
                },
                onFinished: function () {
                    wizard.closest('form').trigger('submit');
                }
            });
            wizard.closest('form').find('.booking-form-actions').hide();

            if (typeof wizard.closest('form').validate === 'function') {
                wizard.closest('form').validate({
                    ignore: '',
                    errorClass: 'text-danger',
                    errorPlacement: function (error, element) {
                        error.insertAfter(element);
                    }
                });
            }

            if ($.fn.select2) {
                wizard.find('.select2').select2({ width: '100%' });
            }
        }

        $(initBookingWizard);
        $(document).ajaxComplete(initBookingWizard);
    })();
</script>
