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
                                    @foreach ($tasks_list as $item)
                                    <tr>
                                        <td>
                                            <span>
                                                {{ $item['name'] }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $item['next_run'] }}
                                        </td>
                                        <td>
                                            {{ $item['last_run'] }}
                                        </td>
                                        <td>
                                            {!! $item['actions'] !!}
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