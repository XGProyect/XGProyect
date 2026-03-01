@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('admin/update.up_title') }}</h1>
        <a href="/admin/update" class="btn btn-primary btn-icon-split mt-3 mt-sm-0">
            <span class="icon text-white-50"><i class="fas fa-chevron-left"></i></span>
            <span class="text">{{ __('admin/update.up_back') }}</span>
        </a>
    </div>
    <p class="mb-4 text-gray-600">{!! $up_sub_title !!}</p>

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('admin/update.up_test_mode') }}</h6>
                </div>
                <div class="card-body">
                    <pre>{{ $result }}</pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
