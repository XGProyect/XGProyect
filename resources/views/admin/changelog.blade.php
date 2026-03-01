@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('admin/changelog.ch_title') }}</h1>
        <a href="{{ route('admin.changelog.create') }}" class="btn btn-primary btn-icon-split">
            <span class="icon text-white-50">
                <i class="fas fa-plus"></i>
            </span>
            <span class="text">{{ __('admin/changelog.ch_new_item') }}</span>
        </a>
    </div>
    <p class="mb-4 text-gray-600">{{ __('admin/changelog.ch_sub_title') }}</p>

    @if(empty($changelog))
        <div class="card shadow">
            <div class="card-body text-center py-5 text-muted">
                <i class="fas fa-scroll fa-3x mb-3 d-block text-gray-300"></i>
                {{ __('admin/changelog.ch_no_entries') }}
            </div>
        </div>
    @else
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <a href="#collapseChangelog" class="d-block card-header py-3" data-toggle="collapse" role="button"
                    aria-expanded="true" aria-controls="collapseChangelog">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chevron-down fa-xs mr-2"></i>
                        {{ __('admin/changelog.ch_general') }}
                        <span class="badge badge-primary ml-2">{{ count($changelog) }}</span>
                    </h6>
                </a>
                <div class="collapse show" id="collapseChangelog">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="pl-4" style="width:20%;">{{ __('admin/changelog.ch_date') }}</th>
                                        <th style="width:20%;">{{ __('admin/changelog.ch_version') }}</th>
                                        <th style="width:15%;">{{ __('admin/changelog.ch_language') }}</th>
                                        <th class="text-center" style="width:12%;">{{ __('admin/changelog.ch_actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($changelog as $item)
                                    <tr>
                                        <td class="pl-4 align-middle">
                                            <span class="text-gray-700">
                                                <i class="fas fa-calendar-alt fa-xs mr-1 text-gray-400"></i>
                                                {{ $item['changelog_date'] }}
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge badge-primary px-2 py-1" style="font-size:.8rem; letter-spacing:.03rem;">
                                                v{{ $item['changelog_version'] }}
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge badge-secondary px-2 py-1">
                                                <i class="fas fa-language fa-xs mr-1"></i>{{ $item['changelog_language'] }}
                                            </span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <a href="{{ route('admin.changelog.edit', $item['changelog_id']) }}"
                                                class="btn btn-sm btn-outline-primary mr-1" title="{{ __('admin/changelog.ch_edit_this') }}">
                                                <i class="fas fa-pencil-alt fa-xs"></i>
                                            </a>
                                            <form action="{{ route('admin.changelog.destroy', $item['changelog_id']) }}" method="POST" class="d-inline"
                                                onsubmit="return confirm('{{ __('admin/changelog.ch_delete_confirm') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('admin/changelog.ch_delete_this') }}">
                                                    <i class="fas fa-trash-alt fa-xs"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection