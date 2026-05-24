@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <form action="{{ route('admin.expeditions.update') }}" method="POST">
        @csrf
        <x-admin.page-header
            :title="__('admin/expeditions.ex_title')"
            :subtitle="__('admin/expeditions.ex_sub_title')"
        >
            <x-slot name="action">
                <button type="submit" class="btn btn-primary btn-icon-split">
                    <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                    <span class="text">{{ __('admin/expeditions.ex_save_changes') }}</span>
                </button>
            </x-slot>
        </x-admin.page-header>

        <div class="row align-items-start">
            @foreach ([$primaryGroups, $secondaryGroups] as $columnGroups)
                <div class="col-lg-6">
                    @foreach ($columnGroups as $groupKey => $group)
                    <x-admin.card-collapsible id="collapseExpedition{{ ucfirst($groupKey) }}" :title="$group['title']" :icon="$group['icon']">
                        <x-admin.settings-table>
                            @foreach ($group['fields'] as $field)
                                <tr>
                                    <td>{{ $field['label'] }}</td>
                                    <td>
                                        <div class="input-group">
                                            <input class="form-control" type="number" name="{{ $field['name'] }}"
                                                value="{{ old($field['name'], $field['value']) }}" min="0" max="100" step="0.01">
                                            <div class="input-group-append">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </x-admin.settings-table>
                    </x-admin.card-collapsible>
                    @endforeach
                </div>
            @endforeach
        </div>
    </form>
</div>
@endsection