@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <form action="" method="POST">
        @csrf
        <input type="hidden" name="save" value="1">
        <x-admin.page-header
            title="{{ __('admin/registration.ur_title') }}"
            subtitle="{{ __('admin/registration.ur_sub_title') }}"
        >
            <x-slot name="action">
                <button type="submit" class="btn btn-primary btn-icon-split">
                    <span class="icon text-white-50">
                        <i class="fas fa-save"></i>
                    </span>
                    <span class="text">{{ __('admin/registration.ur_save_parameters') }}</span>
                </button>
            </x-slot>
        </x-admin.page-header>

        <div class="row">
            <div class="col-lg-6">
                <x-admin.card-collapsible id="collapseGeneral" title="{{ __('admin/registration.ur_general') }}">
                            <div class="table-responsive">
                                <table class="table table-borderless" width="100%" cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <span>
                                                    {{ __('admin/registration.ur_open_close') }}
                                                </span>
                                            </td>
                                            <td>
                                                <input class="form-check" type="checkbox" name="reg_enable"
                                                    {{ $reg_enable }}>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span>
                                                    {{ __('admin/registration.ur_welcome_message') }}
                                                </span>
                                            </td>
                                            <td>
                                                <input class="form-check" type="checkbox" name="reg_welcome_message"
                                                    {{ $reg_welcome_message }}>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span>
                                                    {{ __('admin/registration.ur_welcome_email') }}
                                                </span>
                                            </td>
                                            <td>
                                                <input class="form-check" type="checkbox" name="reg_welcome_email"
                                                    {{ $reg_welcome_email }}>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                    </x-admin.card-collapsible>
            </div>
        </div>
    </form>
</div>
@endsection