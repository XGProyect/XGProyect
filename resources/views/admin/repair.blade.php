@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <form action="" method="POST">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">{{ __('admin/repair.db_opt_db') }}</h1>

            @if($results === null)
                <div class="d-flex flex-wrap align-items-center mt-3 mt-sm-0" style="gap: .75rem;">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" name="optimize" id="optimize" checked>
                        <label class="custom-control-label" for="optimize">{{ __('admin/repair.db_optimize') }}</label>
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" name="repair" id="repair" checked>
                        <label class="custom-control-label" for="repair">{{ __('admin/repair.db_repair') }}</label>
                    </div>
                    <button type="submit" class="btn btn-primary btn-icon-split">
                        <span class="icon text-white-50"><i class="fas fa-clipboard-check"></i></span>
                        <span class="text">{{ __('admin/repair.db_check') }}</span>
                    </button>
                </div>
            @else
                <a href="/admin/repair" class="btn btn-secondary btn-icon-split">
                    <span class="icon text-white-50"><i class="fas fa-redo"></i></span>
                    <span class="text">{{ __('admin/repair.db_opt_db') }}</span>
                </a>
            @endif
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <a href="#collapseGeneral" class="d-block card-header py-3" data-toggle="collapse" role="button"
                        aria-expanded="true" aria-controls="collapseGeneral">
                        <h6 class="m-0 font-weight-bold text-primary">{{ __('admin/repair.db_general') }}</h6>
                    </a>
                    <div class="collapse show" id="collapseGeneral">
                        <div class="card-body">
                            <div class="table-responsive">
                            @if($results === null)
                                <table class="table table-bordered table-hover" id="repairTable" cellspacing="0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>{{ __('admin/repair.db_table_name') }}</th>
                                            <th class="d-none d-md-table-cell">{{ __('admin/repair.db_data_length') }}</th>
                                            <th class="d-none d-md-table-cell">{{ __('admin/repair.db_index_length') }}</th>
                                            <th class="d-none d-md-table-cell">{{ __('admin/repair.db_overhead') }}</th>
                                            <th class="text-center" style="width: 56px;">
                                                <div class="custom-control custom-checkbox m-0">
                                                    <input type="checkbox" class="custom-control-input form-check-input" id="checkall">
                                                    <label class="custom-control-label" for="checkall"></label>
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tables as $table)
                                            <tr>
                                                <td class="align-middle font-weight-bold">{{ $table['name'] }}</td>
                                                <td class="align-middle d-none d-md-table-cell">{{ $table['data'] }}</td>
                                                <td class="align-middle d-none d-md-table-cell">{{ $table['index'] }}</td>
                                                <td class="align-middle d-none d-md-table-cell">{{ $table['overhead'] }}</td>
                                                <td class="text-center align-middle">
                                                    <div class="custom-control custom-checkbox m-0">
                                                        <input type="checkbox" class="custom-control-input form-check-input"
                                                            name="table[]" value="{{ $table['name'] }}"
                                                            id="tbl_{{ $table['name'] }}">
                                                        <label class="custom-control-label" for="tbl_{{ $table['name'] }}"></label>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <table class="table table-bordered table-hover" id="repairTable" cellspacing="0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>{{ __('admin/repair.db_table_name') }}</th>
                                            <th>{{ __('admin/repair.db_table_result') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($results as $result)
                                            <tr>
                                                <td class="align-middle font-weight-bold">{{ $result['table'] }}</td>
                                                <td class="align-middle">
                                                    <span class="badge badge-success p-2">{{ $result['result'] }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
