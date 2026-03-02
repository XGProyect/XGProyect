<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="XG Proyect | Admin CP">
    <meta name="author" content="XG Proyect">

    <title>XG Proyect | Admin CP</title>

    <!-- Custom fonts for this template-->
    <link href="{{ asset('assets/admin/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ asset('assets/admin/css/admin.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/admin/css/sb-admin-2.min.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <!-- Sidebar -->
        <x-admin.sidebar />
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Topbar -->
                <x-admin.navigation />
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                @yield('content')
                <!-- /.container-fluid -->
            </div>

            <!-- Footer -->
            <x-admin.footer />
            <!-- End of Footer -->
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModal"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModal">{{ __('admin/popups.ready_to_leave') }}</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">{{ __('admin/popups.ready_to_leave_instructions') }}</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">{{ __('admin/popups.ready_to_leave_cancel') }}</button>
                    <a class="btn btn-primary" href="{{ route('admin.logout') }}">{{ __('admin/popups.ready_to_leave_logout') }}</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="{{ asset('assets/admin/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/admin/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Core plugin JavaScript-->
    <script src="{{ asset('assets/admin/vendor/jquery-easing/jquery.easing.min.js') }}"></script>

    <!-- Custom scripts for all pages-->
    <script src="{{ asset('assets/admin/js/sb-admin-2.min.js') }}"></script>

    <!-- Page level plugins -->
    <script src="{{ asset('assets/admin/vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/admin/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <!--<script src="{{ asset('assets/admin/vendor/chart.js/Chart.min.js') }}"></script>-->

    <!-- Page level custom scripts -->
    <!--<script src="{{ asset('assets/admin/js/demo/chart-area-demo.js') }}"></script>-->
    <!--<script src="{{ asset('assets/admin/js/demo/chart-pie-demo.js') }}"></script>-->

    <script type="text/javascript">
        $(document).ready(function () {
            // color pickers
            $('[name=color-picker]').change(function(){
                $('[name=text]').css('color', $(this).val());
            });

            // confirm dialogs via data-confirm attribute
            $(document).on('submit', 'form[data-confirm]', function (e) {
                if (!confirm($(this).data('confirm'))) {
                    e.preventDefault();
                }
            });
            $(document).on('click', '[data-confirm]:not(form)', function (e) {
                if (!confirm($(this).data('confirm'))) {
                    e.preventDefault();
                }
            });

            // popovers
            $('[data-toggle="popover"]').popover({
                trigger: 'hover'
            })

            // datatables
            $('#dataTable').DataTable();

            // check all
            $('#checkall').click(function () {
                $(this).parents('table:eq(0)').find('.form-check-input').attr('checked', this.checked);
            });

            // check version
            $('.badge-counter').html('');
            $('.dropdown-list').hide();

            $.ajax({
                url: '{{ route("admin.update.check") }}',
                dataType: 'json',
                success: function (data) {
                    $.each(data, function (index, element) {
                        if (compareversion('{{ config("version.files") }}', element)) {
                            $('.badge-counter').html('1');
                            $('.dropdown-list').css('display', '');
                        }
                    });
                }
            });
        });

        function compareversion(version1, version2) {
                var result = false;

                if (typeof version1 !== 'object') {
                    version1 = version1.toString().split('.');
                }
                if (typeof version2 !== 'object') {
                    version2 = version2.toString().split('.');
                }

                for (var i = 0; i < (Math.max(version1.length, version2.length)); i++) {
                    if (version1[i] == undefined) {
                        version1[i] = 0;
                    }

                    if (version2[i] == undefined) {
                        version2[i] = 0;
                    }

                    if (Number(version1[i]) < Number(version2[i])) {
                        result = true;
                        break;
                    }
                    if (version1[i] != version2[i]) {
                        break;
                    }
                }
                return (result);
            }
    </script>

    @stack('scripts')
</body>
</html>