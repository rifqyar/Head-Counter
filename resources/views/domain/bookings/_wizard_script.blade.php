<script>
    (function () {
        function initBookingWizard() {
            var wizard = $('.booking-wizard');

            if (!wizard.length || typeof $.fn.steps !== 'function' || wizard.data('booking-wizard-ready')) {
                return;
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
            var cancelUrl = wizard.closest('form').data('cancel-url');
            if (cancelUrl && !wizard.find('.actions .booking-cancel-link').length) {
                wizard.find('.actions ul').prepend('<li><a href="' + cancelUrl + '" class="booking-cancel-link spa_route">Cancel</a></li>');
            }

            if (typeof wizard.closest('form').validate === 'function') {
                wizard.closest('form').validate({
                    ignore: '',
                    errorClass: 'text-danger',
                    errorPlacement: function (error, element) {
                        error.insertAfter(element);
                    }
                });
            }
        }

        $(initBookingWizard);
        $(document).ajaxComplete(initBookingWizard);
    })();
</script>
