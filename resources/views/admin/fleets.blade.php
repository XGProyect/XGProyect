@extends('master.admin')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('admin/fleets.ff_title') }}</h1>
    </div>
    <p class="mb-4">{{ __('admin/fleets.ff_sub_title') }}</p>
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <!-- Card Header - Accordion -->
                <a href="#collapseErrors" class="d-block card-header py-3" data-toggle="collapse" role="button"
                    aria-expanded="true" aria-controls="collapseErrors">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('admin/fleets.ff_general') }}</h6>
                </a>
                <!-- Card Content - Collapse -->
                <div class="collapse show" id="collapseErrors" style="">
                    <div class="card-body">
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
                                    {fleet_movements}
                                    <tr>
                                        <td>{mission}</td>
                                        <td>
                                            <span>
                                                {amount}
                                                <i class="fas fa-question-circle" data-toggle="popover"
                                                    data-trigger="hover" data-content="{amount_content}"
                                                    data-html="true"></i>
                                            </span>
                                        </td>
                                        <td>{metal}</td>
                                        <td>{crystal}</td>
                                        <td>{deuterium}</td>
                                        <td>{beginning}</td>
                                        <td>{departure}</td>
                                        <td>{objective}</td>
                                        <td>{arrival}</td>
                                        <td>{return}</td>
                                        <th>
                                            <a href="admin.php?page=fleets&action=restart&fleetId={fleet_id}"
                                                class="btn btn-primary btn-circle btn-sm">
                                                <i class="fas fa-fast-backward" title="{{ __('admin/fleets.ff_restart_action_title') }}"
                                                    data-toggle="popover" data-trigger="hover"
                                                    data-content="{{ __('admin/fleets.ff_restart_action_description') }}" data-html="true"></i>
                                            </a>
                                            <a href="admin.php?page=fleets&action=end&fleetId={fleet_id}"
                                                class="btn btn-success btn-circle btn-sm">
                                                <i class="fas fa-fast-forward" title="{{ __('admin/fleets.ff_end_action_title') }}"
                                                    data-toggle="popover" data-trigger="hover"
                                                    data-content="{{ __('admin/fleets.ff_end_action_description') }}" data-html="true"></i>
                                            </a>
                                            <a href="admin.php?page=fleets&action=return&fleetId={fleet_id}"
                                                class="btn btn-warning btn-circle btn-sm">
                                                <i class="fas fa-undo-alt" title="{{ __('admin/fleets.ff_return_action_title') }}"
                                                    data-toggle="popover" data-trigger="hover"
                                                    data-content="{{ __('admin/fleets.ff_return_action_description') }}" data-html="true"></i>
                                            </a>
                                            <a href="admin.php?page=fleets&action=delete&fleetId={fleet_id}"
                                                class="btn btn-danger btn-circle btn-sm">
                                                <i class="fas fa-trash-alt" title="{{ __('admin/fleets.ff_delete_action_title') }}"
                                                    data-toggle="popover" data-trigger="hover"
                                                    data-content="{{ __('admin/fleets.ff_delete_action_description') }}" data-html="true"></i>
                                            </a>
                                        </th>
                                    </tr>
                                    {/fleet_movements}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection