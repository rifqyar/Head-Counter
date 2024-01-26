  <script src="{{ asset('assets/extensions/jquery/jquery.js') }}"></script>
  <script src="https://unpkg.com/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
  <script src="{{ asset('assets/js/bootstrap.js') }}"></script>
  <script src="{{ asset('assets/js/app.js') }}"></script>
  <script src="{{ asset('assets/js/scriptacces.js') }}"></script>
  <script src="{{ asset('assets/extensions/choices.js/public/assets/scripts/choices.js') }}"></script>
  <script src="{{ asset('assets/js/pages/form-element-select.js') }}"></script>
  <script src="{{ asset('assets/extensions/sweetalert2/sweetalert2.min.js') }}"></script>
  <script src="{{ asset('js/core/core.js') }}"></script>
  <script src=" {{ asset('assets/extensions/toastify-js/src/toastify.js') }} "></script>
  <script src="https://cdn.datatables.net/v/bs5/dt-1.12.1/datatables.min.js"></script>
  <script src="{{ asset('assets/js/pages/datatables.js') }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/1.2.2/js/dataTables.buttons.min.js"></script>
  <script src="//cdn.datatables.net/buttons/1.2.2/js/buttons.flash.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
  <script src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
  <script src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
  <script src="//cdn.datatables.net/buttons/1.2.2/js/buttons.html5.min.js"></script>
  <script src="//cdn.datatables.net/buttons/1.2.2/js/buttons.print.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.7/jquery.inputmask.min.js" integrity="sha512-jTgBq4+dMYh73dquskmUFEgMY5mptcbqSw2rmhOZZSJjZbD2wMt0H5nhqWtleVkyBEjmzid5nyERPSNBafG4GQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="{{asset('assets/extensions/apexcharts/apexcharts.min.js')}}"></script>
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js" integrity="sha256-lSjKY0/srUM9BE3dPm+c4fBo1dky2v27Gdjm2uoZaL0=" crossorigin="anonymous"></script>

  <script>
      function startLoading() {
          $('<div id="loading-overlay"></div>').appendTo('body');

          // Menambahkan teks loading
          $('<span>Waiting Process... DONT STOP LOADING PROCESS!!!!</span>').appendTo('#loading-overlay');

          // Menambahkan tombol close ke dalam loading overlay
          var closeButton = $('<button id="close-button">Close</button>').hide();
          $('#loading-overlay').append(closeButton);

          // Menampilkan tombol close setelah 10 detik
          setTimeout(function() {
              closeButton.show();
          }, 120000);
      }



      function stopLoading() {
          $('#loading-overlay').remove();
      }


      document.addEventListener('click', function(event) {
          var closeButton = event.target.closest('#close-button');
          if (closeButton) {
              stopLoading();
          }
      });
  </script>
