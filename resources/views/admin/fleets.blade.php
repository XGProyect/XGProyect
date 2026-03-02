@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <x-admin.page-header
        title="{{ __('admin/fleets.ff_title') }}"
        subtitle="{{ __('admin/fleets.ff_sub_title') }}"
    />
    <div class="row">
        <div class="col-lg-12">
            <x-admin.card-collapsible id="collapseFleets" title="{{ __('admin/fleets.ff_general') }}" :flush="true">
                        <div class="table-responsive">
                            <table class="table table-borderless" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>{{ __('admin/fleets.ff_mission') }}</th>
                                        <th>{{ __('admin/fleets.ff_ammount') }}</th>
                                        <th>{{ __('admin/fleets.ff_metal') }}</th>
                                        <th>{{ __('admin/fleets.ff_crystal') }}</th>
                                        <th>{{ __('admin/fleets.ff_deuterium') }}</th>
                                        <th>{{ __('admin/fleets.ff_beginning') }}</th>
                                        <th>{{ __('admin/fleets.ff_departure') }}</th>
                                        <th>{{ __('admin/fleets.ff_objective') }}</th>
                                        <th>{{ __('admin/fleets.ff_arrival') }}</th>
                                        <th>{{ __('admin/fleets.ff_return') }}</th>
                                        <th>{{ __('admin/fleets.ff_actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($fleetMovements as $item)
                                    <tr>
                                        <td>{{ $item['mission'] }}</td>
                                        <td>
                                            <span>
                                                {{ $item['amount'] }}
                                                <i class="fas fa-question-circle" data-toggle="popover"
                                                    data-trigger="hover" data-content="{{ $item['amount_content'] }}"
                                                    data-html="true"></i>
                                            </span>
                                        </td>
                                        <td>{{ $item['metal'] }}</td>
                                        <td>{{ $item['crystal'] }}</td>
                                        <td>{{ $item['deuterium'] }}</td>
                                        <td>{!! $item['beginning'] !!}</td>
                                        <td>{{ $item['departure'] }}</td>
                                        <td>{!! $item['objective'] !!}</td>
                                        <td>{{ $item['arrival'] }}</td>
                                        <td>{{ $item['return'] }}</td>
                                        <th>
                                            <a href="/admin/fleets?action=restart&fleetId={{ $item['fleet_id'] }}"
                                                class="btn btn-primary btn-circle btn-sm">
                                                <i class="fas fa-fast-backward" title="{{ __('admin/fleets.ff_restart_action_title') }}"
                                                    data-toggle="popover" data-trigger="hover"
                                                    data-content="{{ __('admin/fleets.ff_restart_action_description') }}" data-html="true"></i>
                                            </a>
                                            <a href="/admin/fleets?action=end&fleetId={{ $item['fleet_id'] }}"
                                                class="btn btn-success btn-circle btn-sm">
                                                <i class="fas fa-fast-forward" title="{{ __('admin/fleets.ff_end_action_title') }}"
                                                    data-toggle="popover" data-trigger="hover"
                                                    data-content="{{ __('admin/fleets.ff_end_action_description') }}" data-html="true"></i>
                                            </a>
                                            <a href="/admin/fleets?action=return&fleetId={{ $item['fleet_id'] }}"
                                                class="btn btn-warning btn-circle btn-sm">
                                                <i class="fas fa-undo-alt" title="{{ __('admin/fleets.ff_return_action_title') }}"
                                                    data-toggle="popover" data-trigger="hover"
                                                    data-content="{{ __('admin/fleets.ff_return_action_description') }}" data-html="true"></i>
                                            </a>
                                            <a href="/admin/fleets?action=delete&fleetId={{ $item['fleet_id'] }}"
                                                class="btn btn-danger btn-circle btn-sm">
                                                <i class="fas fa-trash-alt" title="{{ __('admin/fleets.ff_delete_action_title') }}"
                                                    data-toggle="popover" data-trigger="hover"
                                                    data-content="{{ __('admin/fleets.ff_delete_action_description') }}" data-html="true"></i>
                                            </a>
                                        </th>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-admin.card-collapsible>
        </div>
    </div>
</div>
@endsection