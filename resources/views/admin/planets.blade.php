@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <form action="{{ route('admin.planets.update') }}" method="POST">
        @csrf
        <x-admin.page-header
            :title="__('admin/planets.np_title')"
            :subtitle="__('admin/planets.np_sub_title')"
        >
            <x-slot name="action">
                <button type="submit" class="btn btn-primary btn-icon-split">
                    <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                    <span class="text">{{ __('admin/planets.np_save_parameters') }}</span>
                </button>
            </x-slot>
        </x-admin.page-header>

        <div class="row">
            <div class="col-lg-6">
                <x-admin.card-collapsible id="collapseStructure" :title="__('admin/planets.np_general')" icon="fas fa-th-large">
                    <x-admin.settings-table>
                        <tr>
                            <td>{{ __('admin/planets.np_initial_fields') }}</td>
                            <td>
                                <input class="form-control" type="number" name="initial_fields"
                                    value="{{ $initial_fields }}" min="0" max="9999">
                            </td>
                        </tr>
                    </x-admin.settings-table>
                </x-admin.card-collapsible>
            </div>

            <div class="col-lg-6">
                <x-admin.card-collapsible id="collapseProduction" :title="__('admin/planets.np_production')" icon="fas fa-industry">
                    <x-admin.settings-table>
                        <tr>
                            <td>
                                <span class="text-warning font-weight-bold"><i class="fas fa-circle fa-xs mr-1"></i></span>
                                {{ __('admin/planets.np_metal_production') }}
                            </td>
                            <td>
                                <input class="form-control" type="number" name="metal_basic_income"
                                    value="{{ $metal_basic_income }}" min="0">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="text-info font-weight-bold"><i class="fas fa-circle fa-xs mr-1"></i></span>
                                {{ __('admin/planets.np_crystal_production') }}
                            </td>
                            <td>
                                <input class="form-control" type="number" name="crystal_basic_income"
                                    value="{{ $crystal_basic_income }}" min="0">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="text-primary font-weight-bold"><i class="fas fa-circle fa-xs mr-1"></i></span>
                                {{ __('admin/planets.np_deuterium_production') }}
                            </td>
                            <td>
                                <input class="form-control" type="number" name="deuterium_basic_income"
                                    value="{{ $deuterium_basic_income }}" min="0">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="text-success font-weight-bold"><i class="fas fa-circle fa-xs mr-1"></i></span>
                                {{ __('admin/planets.np_energy_production') }}
                            </td>
                            <td>
                                <input class="form-control" type="number" name="energy_basic_income"
                                    value="{{ $energy_basic_income }}" min="0">
                            </td>
                        </tr>
                    </x-admin.settings-table>
                </x-admin.card-collapsible>
            </div>
        </div>
    </form>
</div>
@endsection
