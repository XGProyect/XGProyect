@extends('master.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <div class="alert alert-danger">
                <strong>{{ __('admin/global.gn_danger_title') }}</strong> {{ __('admin/global.no_permissions') }}
            </div>
        </div>
    </div>
</div>
@endsection