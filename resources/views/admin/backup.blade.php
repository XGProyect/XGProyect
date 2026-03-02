@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <x-admin.page-header
        title="{{ __('admin/backup.bku_title') }}"
        subtitle="{{ __('admin/backup.bku_sub_title') }}"
    >
        <x-slot name="action">
            <form name="frm_backup_now" method="POST" action="{{ route('admin.backup.create') }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-icon-split">
                    <span class="icon text-white-50"><i class="fas fa-download"></i></span>
                    <span class="text">{{ __('admin/backup.bku_now') }}</span>
                </button>
            </form>
        </x-slot>
    </x-admin.page-header>

    <div class="row">
        <div class="col-lg-6">
            <x-admin.card-collapsible id="collapseGeneral" title="{{ __('admin/backup.bku_general') }}">
                    <div class="table-responsive">
                        <form name="frm_backup" method="POST" action="{{ route('admin.backup.save') }}">
                            @csrf
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <td>
                                            <span>
                                                {{ __('admin/backup.bku_auto') }}
                                                <i class="fas fa-question-circle" data-toggle="popover"
                                                    data-trigger="hover" data-content="{{ __('admin/backup.bku_auto_legend') }}"
                                                    data-html="true"></i>
                                            </span>
                                        </td>
                                        <td>
                                            <input class="form-check-input" type="checkbox" name="auto_backup"
                                                @checked($auto_backup)>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-icon-split">
                                    <span class="icon text-white-50">
                                        <i class="fas fa-save"></i>
                                    </span>
                                    <span class="text">{{ __('admin/backup.bku_save') }}</span>
                                </button>
                            </div>
                        </form>
                    </div>
            </x-admin.card-collapsible>
        </div>
        <div class="col-lg-6">
            <x-admin.card-collapsible id="collapseList" title="{{ __('admin/backup.bku_list') }}">
                        @if ($backup_list->isEmpty())
                            <x-admin.empty-state icon="fas fa-database" message="{{ __('admin/backup.bku_no_backups') }}" size="sm" />
                        @else
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                @foreach ($backup_list as $item)                                <tr>
                                    <td>
                                        {{ $item['file_name'] }}
                                    </td>
                                    <td>
                                        {{ $item['file_size'] }}
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.backup.download', $item['full_file_name']) }}"
                                            class="btn btn-primary btn-circle btn-sm">
                                            <i class="fas fa-file-download"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.backup.destroy', $item['full_file_name']) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-circle btn-sm">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                        </div>
                        @endif
            </x-admin.card-collapsible>
        </div>
    </div>
</div>
@endsection