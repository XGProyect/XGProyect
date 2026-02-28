@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <form action="" method="POST">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">{{ __('admin/repair.db_opt_db') }}</h1>
            <div class="d-flex align-items-center">
                @if($results === null)
                <div style="margin-right: 1.25rem;">
                    <div class="custom-control custom-switch d-inline-block mr-3">
                        <input type="checkbox" class="custom-control-input" name="optimize" id="optimize" checked>
                        <label class="custom-control-label" for="optimize">{{ __('admin/repair.db_optimize') }}</label>
                    </div>
                    <div class="custom-control custom-switch d-inline-block">
                        <input type="checkbox" class="custom-control-input" name="repair" id="repair" checked>
                        <label class="custom-control-label" for="repair">{{ __('admin/repair.db_repair') }}</label>
                    </div>
                </div>
                @endif
                <button type="submit" class="btn btn-primary btn-icon-split">
                    <span class="icon text-white-50">
                        <i class="fas fa-clipboard-check"></i>
                    </span>
                    <span class="text">{{ __('admin/repair.db_check') }}</span>
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow mb-4">
                    <a href="#collapseGeneral" class="d-block card-header py-3" data-toggle="collapse" role="button"
                        aria-expanded="true" aria-controls="collapseGeneral">
                        <h6 class="m-0 font-weight-bold text-primary">{{ __('admin/repair.db_general') }}</h6>
                    </a>
                    <div class="collapse show" id="collapseGeneral">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-borderless" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            @if($results === null)
                                                <th>{{ __('admin/repair.db_table_name') }}</th>
                                                <th>{{ __('admin/repair.db_data_length') }}</th>
                                                <th>{{ __('admin/repair.db_index_length') }}</th>
                                                <th>{{ __('admin/repair.db_overhead') }}</th>
                                                <th class="text-right">
                                                    <input type="checkbox" class="form-check-input" id="checkall">
                                                </th>
                                            @else
                                                <th>{{ __('admin/repair.db_table_name') }}</th>
                                                <th>{{ __('admin/repair.db_table_result') }}</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($results === null)
                                            @foreach($tables as $table)
                                                <tr>
                                                    <td>{{ $table['name'] }}</td>
                                                    <td>{{ $table['data'] }}</td>
                                                    <td>{{ $table['index'] }}</td>
                                                    <td>{{ $table['overhead'] }}</td>
                                                    <td class="text-right">
                                                        <input type="checkbox" class="form-check-input" name="table[]" value="{{ $table['name'] }}">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            @foreach($results as $result)
                                                <tr>
                                                    <td>{{ $result['table'] }}</td>
                                                    <td colspan="4">{{ $result['result'] }}</td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
