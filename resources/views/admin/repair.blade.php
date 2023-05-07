@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <form action="" method="POST">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">{{ __('admin/repair.db_opt_db') }}</h1>
        </div>
        <p class="mb-4"></p>

        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow mb-4">
                    <!-- Card Header - Accordion -->
                    <a href="#collapseGeneral" class="d-block card-header py-3" data-toggle="collapse" role="button"
                        aria-expanded="true" aria-controls="collapseGeneral">
                        <h6 class="m-0 font-weight-bold text-primary">{{ __('admin/repair.db_general') }}</h6>
                    </a>
                    <!-- Card Content - Collapse -->
                    <div class="collapse show" id="collapseGeneral" style="">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-borderless" width="100%" cellspacing="0">
                                    <tr>
                                        {!! $head !!}
                                    </tr>
                                    {!! $tables !!}
                                    {!! $results !!}
                                    <tr>
                                        <td colspan="5">
                                            <div class="text-center" style="display: {{ $display }}">
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input" name="optimize" id="optimize" checked>
                                                    <label class="custom-control-label" for="optimize">{{ __('admin/repair.db_optimize') }}</label>
                                                </div>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input" name="repair" id="repair" checked>
                                                    <label class="custom-control-label" for="repair">{{ __('admin/repair.db_repair') }}</label>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-icon-split">
                                        <span class="icon text-white-50">
                                            <i class="fas fa-clipboard-check"></i>
                                        </span>
                                        <span class="text">{{ __('admin/repair.db_check') }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection