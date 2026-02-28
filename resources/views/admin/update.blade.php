@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>

    <form name="update_form" method="post" action="">
        @if ($continue)
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">{{ __('admin/update.up_title') }}</h1>

            <button type="submit" class="btn btn-primary btn-icon-split mt-3 mt-sm-0">
                <span class="icon text-white-50"><i class="fas fa-sync-alt"></i></span>
                <span class="text">{{ __('admin/update.up_go') }}</span>
            </button>
        </div>
        <p class="mb-4">{!! $up_sub_title !!}</p>
        @endif

        @if ($continue)
        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">{{ __('admin/update.up_test_mode') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="demo_mode" id="demo_mode" checked>
                            <label class="custom-control-label" for="demo_mode">{{ __('admin/update.up_test_mode') }}</label>
                        </div>
                        <p class="mt-2 mb-0 text-gray-600"><em>{!! __('admin/update.up_test_mode_notice') !!}</em></p>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </form>
</div>
@endsection
