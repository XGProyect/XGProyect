@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>

    <x-admin.page-header
        title="{{ __('admin/update.up_title') }}"
        subtitle="{!! $up_sub_title !!}"
    >
        <x-slot name="action">
            <a href="/admin/update" class="btn btn-primary btn-icon-split mt-3 mt-sm-0">
                <span class="icon text-white-50"><i class="fas fa-chevron-left"></i></span>
                <span class="text">{{ __('admin/update.up_back') }}</span>
            </a>
        </x-slot>
    </x-admin.page-header>

    <div class="row">
        <div class="col-12">
            <x-admin.card title="{{ __('admin/update.up_test_mode') }}">
                    <pre>{{ $result }}</pre>
                </x-admin.card>
        </div>
    </div>
</div>
@endsection
