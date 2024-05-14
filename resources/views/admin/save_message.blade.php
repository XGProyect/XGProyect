@extends('master.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close d-none" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>{{ __('admin/global.gn_danger_title') }}</strong> {{ __('admin/global.no_permissions') }}
            </div>
        </div>
    </div>
</div>
@endsection