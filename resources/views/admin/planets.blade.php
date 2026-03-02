@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <form action="" method="POST">
        @csrf
        <x-admin.page-header
            title="{{ __('admin/planets.np_title') }}"
            subtitle="{{ __('admin/planets.np_sub_title') }}"
        >
            <x-slot name="action">
                <button type="submit" class="btn btn-primary btn-icon-split">
                    <span class="icon text-white-50">
                        <i class="fas fa-save"></i>
                    </span>
                    <span class="text">{{ __('admin/planets.np_save_parameters') }}</span>
                </button>
            </x-slot>
        </x-admin.page-header>

        <div class="row">
            <div class="col-lg-6">
                <x-admin.card-collapsible id="collapseGeneral" title="{{ __('admin/planets.np_general') }}">
                            <div class="table-responsive">
                                <table class="table table-borderless" width="100%" cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <span>
                                                    {{ __('admin/planets.np_initial_fields') }}
                                                </span>
                                            </td>
                                            <td>
                                                <input class="form-control" type="number" name="initial_fields"
                                                    maxlength="10" value="{{ $initial_fields }}">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                    </x-admin.card-collapsible>
            </div>
            <div class="col-lg-6">
                <x-admin.card-collapsible id="collapseOtherParameters" title="{{ __('admin/planets.np_production') }}">
                            <div class="table-responsive">
                                <table class="table table-borderless" width="100%" cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <span>
                                                    {{ __('admin/planets.np_metal_production') }}
                                                </span>
                                            </td>
                                            <td>
                                                <input class="form-control" type="number" name="metal_basic_income"
                                                    maxlength="10" value="{{ $metal_basic_income }}">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span>
                                                    {{ __('admin/planets.np_crystal_production') }}
                                                </span>
                                            </td>
                                            <td>
                                                <input class="form-control" type="number" name="crystal_basic_income"
                                                    maxlength="10" value="{{ $crystal_basic_income }}">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span>
                                                    {{ __('admin/planets.np_deuterium_production') }}
                                                </span>
                                            </td>
                                            <td>
                                                <input class="form-control" type="number" name="deuterium_basic_income"
                                                    maxlength="10" value="{{ $deuterium_basic_income }}">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span>
                                                    {{ __('admin/planets.np_energy_production') }}
                                                </span>
                                            </td>
                                            <td>
                                                <input class="form-control" type="number" name="energy_basic_income"
                                                    maxlength="10" value="{{ $energy_basic_income }}">
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