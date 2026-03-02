@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <form action="{{ route('admin.registration.update') }}" method="POST">
        @csrf
        <x-admin.page-header
            :title="__('admin/registration.ur_title')"
            :subtitle="__('admin/registration.ur_sub_title')"
        >
            <x-slot name="action">
                <button type="submit" class="btn btn-primary btn-icon-split">
                    <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                    <span class="text">{{ __('admin/registration.ur_save_parameters') }}</span>
                </button>
            </x-slot>
        </x-admin.page-header>

        <div class="row">
            <div class="col-lg-6">
                <x-admin.card-collapsible id="collapseGeneral" :title="__('admin/registration.ur_general')" icon="fas fa-user-plus">
                    <x-admin.settings-table>
                        <tr>
                            <td>{{ __('admin/registration.ur_open_close') }}</td>
                            <td>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="reg_enable"
                                        name="reg_enable" {{ $reg_enable ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="reg_enable"></label>
                                </div>
                            </td>
                        </tr>
                    </x-admin.settings-table>
                </x-admin.card-collapsible>
            </div>

            <div class="col-lg-6">
                <x-admin.card-collapsible id="collapseWelcome" :title="__('admin/registration.ur_section_welcome')" icon="fas fa-envelope-open-text">
                    <x-admin.settings-table>
                        <tr>
                            <td>{{ __('admin/registration.ur_welcome_message') }}</td>
                            <td>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="reg_welcome_message"
                                        name="reg_welcome_message" {{ $reg_welcome_message ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="reg_welcome_message"></label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __('admin/registration.ur_welcome_email') }}</td>
                            <td>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="reg_welcome_email"
                                        name="reg_welcome_email" {{ $reg_welcome_email ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="reg_welcome_email"></label>
                                </div>
                            </td>
                        </tr>
                    </x-admin.settings-table>
                </x-admin.card-collapsible>
            </div>
        </div>
    </form>
</div>
@endsection