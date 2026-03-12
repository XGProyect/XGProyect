@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>

    <x-admin.page-header
        :title="__('admin/fleets.ff_title')"
        :subtitle="__('admin/fleets.ff_sub_title')"
    />

    <div class="row">
        <div class="col-lg-12">
            <x-admin.card-collapsible id="collapseFleets" :title="__('admin/fleets.ff_general')" :flush="true">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ __('admin/fleets.ff_mission') }}</th>
                                <th>{{ __('admin/fleets.ff_ammount') }}</th>
                                <th>{{ __('admin/fleets.ff_resources') }}</th>
                                <th>{{ __('admin/fleets.ff_beginning') }}</th>
                                <th>{{ __('admin/fleets.ff_departure') }}</th>
                                <th>{{ __('admin/fleets.ff_objective') }}</th>
                                <th>{{ __('admin/fleets.ff_arrival') }}</th>
                                <th>{{ __('admin/fleets.ff_return') }}</th>
                                <th style="width: 140px;">{{ __('admin/fleets.ff_actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($fleetMovements as $item)
                                <tr>
                                    <td class="align-middle font-weight-bold">{{ $item['mission'] }}</td>
                                    <td class="align-middle">
                                        <span>
                                            {{ $item['amount'] }}
                                            <i class="fas fa-question-circle text-muted ml-1" data-toggle="popover"
                                                data-trigger="hover"
                                                data-content="{{ $item['amount_content'] }}"
                                                data-html="true"></i>
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <span>
                                            {{ __('admin/fleets.ff_resources') }}
                                            <i class="fas fa-question-circle text-muted ml-1" data-toggle="popover"
                                                data-trigger="hover"
                                                data-content="{{ $item['resources_content'] }}"
                                                data-html="true"></i>
                                        </span>
                                    </td>
                                    <td class="align-middle">{!! $item['beginning'] !!}</td>
                                    <td class="align-middle"><small class="text-muted">{{ $item['departure'] }}</small></td>
                                    <td class="align-middle">{!! $item['objective'] !!}</td>
                                    <td class="align-middle"><small class="text-muted">{{ $item['arrival'] }}</small></td>
                                    <td class="align-middle"><small class="text-muted">{{ $item['return'] }}</small></td>
                                    <td class="align-middle text-nowrap">
                                        <div class="d-flex" style="gap: 0.25rem;">
                                            <form method="POST" action="{{ route('admin.fleets.restart', $item['fleet_id']) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary"
                                                    title="{{ __('admin/fleets.ff_restart_action_title') }}"
                                                    data-toggle="popover" data-trigger="hover"
                                                    data-content="{{ __('admin/fleets.ff_restart_action_description') }}"
                                                    data-html="true">
                                                    <i class="fas fa-fast-backward fa-sm"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.fleets.end', $item['fleet_id']) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success"
                                                    title="{{ __('admin/fleets.ff_end_action_title') }}"
                                                    data-toggle="popover" data-trigger="hover"
                                                    data-content="{{ __('admin/fleets.ff_end_action_description') }}"
                                                    data-html="true">
                                                    <i class="fas fa-fast-forward fa-sm"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.fleets.return', $item['fleet_id']) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-warning"
                                                    title="{{ __('admin/fleets.ff_return_action_title') }}"
                                                    data-toggle="popover" data-trigger="hover"
                                                    data-content="{{ __('admin/fleets.ff_return_action_description') }}"
                                                    data-html="true">
                                                    <i class="fas fa-undo-alt fa-sm"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.fleets.destroy', $item['fleet_id']) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    title="{{ __('admin/fleets.ff_delete_action_title') }}"
                                                    data-toggle="popover" data-trigger="hover"
                                                    data-content="{{ __('admin/fleets.ff_delete_action_description') }}"
                                                    data-html="true">
                                                    <i class="fas fa-trash-alt fa-sm"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
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