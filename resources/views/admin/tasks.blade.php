@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <x-admin.page-header
        title="{{ __('admin/tasks.ta_title') }}"
        subtitle="{{ __('admin/tasks.ta_sub_title') }}"
    />

    <div class="row">
        <div class="col-lg-6">
            <x-admin.card-collapsible id="collapseGeneral" title="{{ __('admin/tasks.ta_general') }}">
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <thead>
                            <tr>
                                <th>{{ __('admin/tasks.ta_task') }}</th>
                                <th>{{ __('admin/tasks.ta_next_run') }}</th>
                                <th>{{ __('admin/tasks.ta_last_run') }}</th>
                                <th>{{ __('admin/tasks.ta_actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tasks_list as $task)
                            <tr>
                                <td class="align-middle">{{ $task->name }}</td>
                                <td class="align-middle">{{ $task->nextRun }}</td>
                                <td class="align-middle">{{ $task->lastRun }}</td>
                                <td class="align-middle">
                                    @foreach ($task->actions as $action)
                                        <a href="{{ route($action->route) }}"
                                            title="{{ $action->title }}"
                                            data-toggle="popover"
                                            data-placement="top"
                                            data-trigger="hover"
                                            data-content="{{ $action->title }}"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="{{ $action->icon }}"></i>
                                        </a>
                                    @endforeach
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-admin.card-collapsible>
        </div>
        <div class="col-lg-6">
        </div>
    </div>
</div>
@endsection