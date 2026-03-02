@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>

    <form name="update_form" method="post" action="">
        @csrf
        @if ($continue)
        <x-admin.page-header
            title="{{ __('admin/update.up_title') }}"
            subtitle="{!! $up_sub_title !!}"
        >
            <x-slot name="action">
                <button type="submit" class="btn btn-primary btn-icon-split mt-3 mt-sm-0">
                    <span class="icon text-white-50"><i class="fas fa-sync-alt"></i></span>
                    <span class="text">{{ __('admin/update.up_go') }}</span>
                </button>
            </x-slot>
        </x-admin.page-header>
        @endif

        @if ($continue)
        <div class="row">
            <div class="col-12">
                <x-admin.card title="{{ __('admin/update.up_test_mode') }}">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" name="demo_mode" id="demo_mode" checked>
                        <label class="custom-control-label" for="demo_mode">{{ __('admin/update.up_test_mode') }}</label>
                    </div>
                    <p class="mt-2 mb-0 text-gray-600"><em>{!! __('admin/update.up_test_mode_notice') !!}</em></p>
                </x-admin.card>
            </div>
        </div>
        @endif
    </form>
</div>
@endsection
