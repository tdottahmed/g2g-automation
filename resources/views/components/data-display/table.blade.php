{{-- Table Wrapper --}}
<div class="table-responsive">
  <table id="scroll-horizontal" class="nowrap table align-middle" style="width:100%">
    {{ $slot }}
  </table>
</div>

@push('styles')
  <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
@endpush

@push('scripts')
  <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap.min.js"></script>

  <script>
    function initializeTables() {
      // Initialize main table with responsive and scrolling options
      new DataTable("#scroll-horizontal", {
        responsive: true,
        scrollX: true,
        scrollY: false,
        scrollCollapse: false,
        paging: true,
        fixedColumns: false,
        autoWidth: false,
        language: {
          paginate: {
            previous: "<i class='ri-arrow-left-s-line'>",
            next: "<i class='ri-arrow-right-s-line'>"
          }
        }
      });

      // Initialize other tables if needed
      if (document.getElementById("example")) {
        new DataTable("#example");
      }
    }

    document.addEventListener("DOMContentLoaded", function() {
      initializeTables();
    });
  </script>
@endpush
