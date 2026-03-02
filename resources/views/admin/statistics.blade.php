@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <form method="post" action="">
        @csrf
        <x-admin.page-header
            title="{{ __('admin/statistics.cs_title') }}"
            subtitle="{{ __('admin/statistics.cs_sub_title') }}"
        >
            <x-slot name="action">
                <button type="submit" class="btn btn-primary btn-icon-split">
                    <span class="icon text-white-50">
                        <i class="fas fa-save"></i>
                    </span>
                    <span class="text">{{ __('admin/statistics.cs_save_changes') }}</span>
                </button>
            </x-slot>
        </x-admin.page-header>

        <div class="row">
            <div class="col-lg-6">
                <x-admin.card-collapsible id="collapseGeneral" title="{{ __('admin/statistics.cs_general') }}">
                            <div class="table-responsive">
                                <table class="table table-borderless">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <span>
                                                    {{ __('admin/statistics.cs_point_per_resources_used') }}
                                                </span>
                                            </td>
                                            <td>
                                                <input class="form-control" type="number" name="stat_points"
                                                    id="stat_points" value="{{ $stat_points }}" min="1">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span>
                                                    {{ __('admin/statistics.cs_time_between_updates') }}
                                                </span>
                                            </td>
                                            <td>
                                                <input class="form-control" type="number" name="stat_update_time"
                                                    id="stat_update_time" value="{{ $stat_update_time }}" min="1">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span>
                                                    {{ __('admin/statistics.cs_access_lvl') }}
                                                </span>
                                            </td>
                                            <td>
                                                <select class="form-control" name="stat_admin_level"
                                                    id="stat_admin_level">
                                                    @foreach ($user_levels as $level)
                                                    <option value="{{ $level['id'] }}" {{ $level['sel'] }}>{{ $level['name'] }}</option>
                                                    @endforeach
                                                </select>
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