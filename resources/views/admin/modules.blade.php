@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <form action="{{ route('admin.modules.update') }}" method="POST">
        @csrf
        <x-admin.page-header
            :title="__('admin/modules.mdl_title')"
            :subtitle="__('admin/modules.mdl_sub_title')"
        >
            <x-slot name="action">
                <button type="submit" class="btn btn-primary btn-icon-split">
                    <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                    <span class="text">{{ __('admin/modules.mdl_save') }}</span>
                </button>
            </x-slot>
        </x-admin.page-header>

        <div class="row">
            @foreach ($modules as $index => $status)
            @isset($module_names[$index])
            @php $enabled = (bool) $status; @endphp
            <div class="col-sm-6 col-md-4 col-lg-3">
                <div class="card shadow mb-4 border-left-{{ $enabled ? 'success' : 'danger' }}">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs font-weight-bold text-uppercase mb-1 {{ $enabled ? 'text-success' : 'text-danger' }}">
                                    {{ $module_names[$index] }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $enabled ? __('admin/global.gn_enabled') : __('admin/global.gn_disabled') }}
                                </div>
                            </div>
                            <div class="custom-control custom-switch ml-3">
                                <input type="checkbox" class="custom-control-input"
                                    id="status{{ $index }}"
                                    name="status{{ $index }}"
                                    {{ $enabled ? 'checked' : '' }}>
                                <label class="custom-control-label" for="status{{ $index }}"></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endisset
            @endforeach
        </div>
    </form>
</div>
@endsection