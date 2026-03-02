@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <form method="POST" action="{{ route('admin.statistics.update') }}">
        @csrf
        <x-admin.page-header
            :title="__('admin/statistics.cs_title')"
            sub:title="__('admin/statistics.cs_sub_title')"
        >
            <x-slot name="action">
                <button type="submit" class="btn btn-primary btn-icon-split">
                    <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                    <span class="text">{{ __('admin/statistics.cs_save_changes') }}</span>
                </button>
            </x-slot>
        </x-admin.page-header>

        <div class="row">
            <div class="col-lg-6">
                <x-admin.card-collapsible id="collapseGeneral" :title="__('admin/statistics.cs_general')" icon="fas fa-chart-bar">
                    <x-admin.settings-table>
                        <tr>
                            <td>{{ __('admin/statistics.cs_point_per_resources_used') }}</td>
                            <td>
                                <input class="form-control" type="number" name="stat_points"
                                    value="{{ $stat_points }}" min="1">
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __('admin/statistics.cs_time_between_updates') }}</td>
                            <td>
                                <input class="form-control" type="number" name="stat_update_time"
                                    value="{{ $stat_update_time }}" min="1">
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __('admin/statistics.cs_access_lvl') }}</td>
                            <td>
                                <select class="form-control" name="stat_admin_level">
                                    @foreach ($user_levels as $level)
                                        <option value="{{ $level['id'] }}" {{ $level['selected'] ? 'selected' : '' }}>
                                            {{ $level['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    </x-admin.settings-table>
                </x-admin.card-collapsible>
            </div>
        </div>
    </form>
</div>
@endsection