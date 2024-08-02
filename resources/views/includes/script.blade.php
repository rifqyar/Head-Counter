    <script src="{{asset('assets/plugins/moment/moment.js')}}"></script>
    <script src="{{asset('assets/plugins/jquery/jquery.min.js')}}"></script>
    <!-- Bootstrap tether Core JavaScript -->
    <script src="{{asset('assets/plugins/popper/popper.min.js')}}"></script>
    <script src="{{asset('assets/plugins/bootstrap/js/bootstrap.min.js')}}"></script>
    <!-- slimscrollbar scrollbar JavaScript -->
    <script src="{{asset('assets/js/jquery.slimscroll.js')}}"></script>
    <!--Wave Effects -->
    <script src="{{asset('assets/js/waves.js')}}"></script>
    <!--Menu sidebar -->
    <script src="{{asset('assets/js/sidebarmenu.js')}}"></script>
    <!--stickey kit -->
    <script src="{{asset('assets/plugins/sticky-kit-master/dist/sticky-kit.min.js')}}"></script>
    <script src="{{asset('assets/plugins/sparkline/jquery.sparkline.min.js')}}"></script>
    <!--Custom JavaScript -->
    <script src="{{asset('assets/js/custom.min.js')}}"></script>
    <!-- ============================================================== -->
    <!-- This page plugins -->
    <!-- ============================================================== -->
    <!-- chartist chart -->
    <script src="{{asset('assets/plugins/chartist-js/dist/chartist.min.js')}}"></script>
    <script src="{{asset('assets/plugins/chartist-plugin-tooltip-master/dist/chartist-plugin-tooltip.min.js')}}"></script>
    <!-- Vector map JavaScript -->
    <script src="{{asset('assets/plugins/vectormap/jquery-jvectormap-2.0.2.min.js')}}"></script>
    <script src="{{asset('assets/plugins/vectormap/jquery-jvectormap-us-aea-en.js')}}"></script>
    <script src="{{asset('assets/js/dashboard3.js')}}"></script>
    <!-- ============================================================== -->
    <!-- Style switcher -->
    <!-- ============================================================== -->
    <script src="{{asset('assets/plugins/styleswitcher/jQuery.style.switcher.js')}}"></script>

    <script src="{{ asset('js/core/core.js') }}"></script>
    <script src=" {{ asset('assets/plugins/toastify-js/src/toastify.js') }} "></script>
    <script src="{{ asset('assets/plugins/sweetalert/sweetalert.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/sweetalert/jquery.sweet-alert.custom.js') }}"></script>
    <script src="{{ asset('assets/plugins/toast-master/js/jquery.toast.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables/datatables.bootstrap4.min.js') }}"></script>
    <script src="{{asset('assets/js/datatable-config.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.7/jquery.inputmask.min.js" integrity="sha512-jTgBq4+dMYh73dquskmUFEgMY5mptcbqSw2rmhOZZSJjZbD2wMt0H5nhqWtleVkyBEjmzid5nyERPSNBafG4GQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://unpkg.com/nprogress@0.2.0/nprogress.js"></script>
    <script src="{{asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js')}}"></script>
    <script src="{{asset('assets/plugins/clockpicker/dist/jquery-clockpicker.min.js')}}"></script>
    <script src="{{asset('assets/plugins/select2/dist/js/select2.min.js')}}"></script>
    <script src="{{ asset('assets/plugins/wizard/jquery.steps.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/wizard/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/timepicker/bootstrap-timepicker.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/daterangepicker/daterangepicker.js') }}"></script>

    <!-- js menu -->
    <script>
        $(function() {
            var parentMenu = $('#sidebarnav').find('a')
            for (let i = 0; i < parentMenu.length; i++) {
                if ($(parentMenu[i]).next().length > 0) {
                    $(parentMenu[i]).addClass('has-arrow')
                }
            }
        })
    </script>
