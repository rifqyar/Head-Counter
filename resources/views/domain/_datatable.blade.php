<script>
    $(function () {
        $('.canonical-datatable').DataTable({
            responsive: true,
            autoWidth: false,
            language: {
                emptyTable: 'No records found'
            }
        });

        if ($.fn.select2) {
            $('.select2').each(function () {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2('destroy');
                }
                $(this).select2({ width: '100%' });
            });
        }

        $('form').on('submit', function () {
            $(this).find('.js-disable-on-submit').prop('disabled', true).text('Saving...');
        });
    });
</script>
