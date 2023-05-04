@extends('master.admin')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('admin/errors.er_title') }}</h1>
        <div class="align-items-right">
            <a href="admin.php?page=errors&exportall=yes" class="btn btn-success btn-icon-split">
                <span class="icon text-white-50">
                    <i class="fas fa-file-export"></i>
                </span>
                <span class="text">{{ __('admin/errors.er_export') }}</span>
            </a>
            <a href="admin.php?page=errors&deleteall=yes" class="btn btn-danger btn-icon-split">
                <span class="icon text-white-50">
                    <i class="fas fa-trash-alt"></i>
                </span>
                <span class="text">{{ __('admin/errors.er_delete_all') }}</span>
            </a>
        </div>
    </div>
    <p class="mb-4">{!! __('admin/errors.er_sub_title') !!}</p>
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <!-- Card Header - Accordion -->
                <a href="#collapseErrors" class="d-block card-header py-3" data-toggle="collapse" role="button"
                    aria-expanded="true" aria-controls="collapseErrors">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('admin/errors.er_error_list') }}</h6>
                </a>
                <!-- Card Content - Collapse -->
                <div class="collapse show" id="collapseErrors" style="">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-borderless" width="100%" cellspacing="0">
                                <tbody>
                                @foreach ($errorsList as $item)
                                    <tr>
                                        <th>
                                            <div class="alert alert-danger" role="alert">
                                                {{ $item['error_message'] }}
                                            </div>
                                        </th>
                                    </tr>
                                    <tr>
                                        <td>
                                        @foreach ($item['errors'] as $error)
                                            {{ $error }} <br>
                                        @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="5">
                                            {{ trans_choice('admin/errors.er_errors', $totalErrors, ['count' => $totalErrors]) }}
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"></h1>
        <a href="admin.php?page=errors&deleteall=yes" class="btn btn-danger btn-icon-split">
            <span class="icon text-white-50">
                <i class="fas fa-trash-alt"></i>
            </span>
            <span class="text">{{ __('admin/errors.er_delete_all') }}</span>
        </a>
    </div>
</div>
@endsection