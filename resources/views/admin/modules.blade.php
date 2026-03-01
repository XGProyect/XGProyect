@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <form name="frm_modules" method="POST" action="/admin/modules">
        @csrf
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">{{ __('admin/modules.mdl_title') }}</h1>
            <button type="submit" name="save" class="btn btn-primary btn-icon-split">
                <span class="icon text-white-50">
                    <i class="fas fa-save"></i>
                </span>
                <span class="text">{{ __('admin/modules.mdl_save') }}</span>
            </button>
        </div>
        <p class="mb-4 text-gray-600">{{ __('admin/modules.mdl_sub_title') }}</p>

        <div class="row">
            <div class="col-lg-12">
                <div class="row">
                    @foreach ($modules as $module)
                    <div class="col-sm-6 col-md-4 col-lg-3 col-xl-3">
                        <div class="card border-left-{{ $module['color'] }} shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-{{ $module['color'] }} text-uppercase mb-1">
                                            {{ $module['module_name'] }}</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <input type="checkbox" name="status{{ $module['module'] }}" id="status" {{ $module['module_value'] }}>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-cogs fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </form>
</div>
@endsection