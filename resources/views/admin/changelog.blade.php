@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('admin/changelog.ch_title') }}</h1>
        <a href="{{ route('admin.changelog.create') }}" class="btn btn-primary btn-icon-split">
            <span class="icon text-white-50">
                <i class="fas fa-save"></i>
            </span>
            <span class="text">{{ __('admin/changelog.ch_new_item') }}</span>
        </a>
    </div>
    <p class="mb-4 text-gray-600">{{ __('admin/changelog.ch_sub_title') }}</p>

        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow mb-4">
                    <!-- Card Header - Accordion -->
                    <a href="#collapseGeneral" class="d-block card-header py-3" data-toggle="collapse" role="button"
                        aria-expanded="true" aria-controls="collapseGeneral">
                        <h6 class="m-0 font-weight-bold text-primary">{{ __('admin/changelog.ch_general') }}</h6>
                    </a>
                    <!-- Card Content - Collapse -->
                    <div class="collapse show" id="collapseGeneral" style="">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-borderless" width="100%" cellspacing="0">
                                    <tr>
                                        <th>{{ __('admin/changelog.ch_date') }}</th>
                                        <th>{{ __('admin/changelog.ch_version') }}</th>
                                        <th>{{ __('admin/changelog.ch_language') }}</th>
                                        <th>{{ __('admin/changelog.ch_actions') }}</th>
                                    </tr>
                                    @foreach ($changelog as $item)
                                    <tr data-toggle="collapse" data-target="#toggle{{ $item['changelog_id'] }}" aria-expanded="false"
                                        aria-controls="toggle{{ $item['changelog_id'] }}">
                                        <td>{{ $item['changelog_date'] }}</td>
                                        <td>{{ $item['changelog_version'] }}</td>
                                        <td>{{ $item['changelog_language'] }}</td>
                                        <td>
                                            <a href="{{ route('admin.changelog.edit', $item['changelog_id']) }}"
                                                class="btn btn-primary btn-circle btn-sm">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>
                                            <button type="button" class="btn btn-primary btn-circle btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <form action="{{ route('admin.changelog.destroy', $item['changelog_id']) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-circle btn-sm">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4">
                                            <div class="collapse" id="toggle{{ $item['changelog_id'] }}">
                                                <div class="card shadow mb-4">
                                                    <div
                                                        class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                                        <h6 class="m-0 font-weight-bold text-primary">
                                                            {{ __('admin/changelog.ch_the_description') }}
                                                        </h6>
                                                        <div class="dropdown no-arrow">
                                                            <a class="dropdown-toggle" href="#" role="button"
                                                                id="dropdownMenuLink" data-toggle="dropdown"
                                                                aria-haspopup="true" aria-expanded="false">
                                                                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                                            </a>
                                                            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                                                aria-labelledby="dropdownMenuLink"
                                                                x-placement="bottom-end"
                                                                style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(17px, 19px, 0px);">
                                                                <div class="dropdown-header">{{ __('admin/changelog.ch_actions') }}</div>
                                                                <a class="dropdown-item" href="{{ route('admin.changelog.edit', $item['changelog_id']) }}">
                                                                    {{ __('admin/changelog.ch_edit_this') }}
                                                                </a>
                                                                <form action="{{ route('admin.changelog.destroy', $item['changelog_id']) }}" method="POST">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="dropdown-item">
                                                                        {{ __('admin/changelog.ch_delete_this') }}
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Card Body -->
                                                    <div class="card-body justify-content-center mx-auto">
                                                        {{ $item['changelog_description'] }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>
@endsection