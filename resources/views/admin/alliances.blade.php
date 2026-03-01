@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('admin/alliances.al_title') }}</h1>
        <a href="{{ route('admin.alliances.create') }}" class="btn btn-primary btn-icon-split">
            <span class="icon text-white-50">
                <i class="fas fa-shield-alt"></i>
            </span>
            <span class="text">{{ __('admin/alliances.al_create_title') }}</span>
        </a>
    </div>
    <p class="mb-4 text-gray-600">{{ __('admin/alliances.al_sub_title') }}</p>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-search mr-1"></i>
                        {{ __('admin/alliances.al_search') }}
                    </h6>
                    @if ($alliance !== null)
                        <div class="dropdown no-arrow">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-cog fa-sm mr-1"></i>{{ __('admin/alliances.al_actions') }}
                            </button>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                                <a class="dropdown-item" href="{{ route('admin.alliances.info', $alliance->alliance_id) }}">
                                    <i class="fas fa-pencil-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    {{ __('admin/alliances.al_edit') }}
                                </a>
                                <div class="dropdown-divider"></div>
                                <form action="{{ route('admin.alliances.destroy', $alliance->alliance_id) }}" method="POST"
                                    onsubmit="return confirm('{{ __('admin/alliances.al_delete_confirm') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-trash-alt fa-sm fa-fw mr-2"></i>
                                        {{ __('admin/alliances.al_delete') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    {{-- Search form --}}
                    <form action="{{ route('admin.alliances') }}" method="GET">
                        <div class="input-group mb-3">
                            <input type="text" name="alliance" class="form-control bg-light border-0"
                                placeholder="{{ __('admin/alliances.al_alliance_placeholder') }}"
                                value="{{ $search }}" autocomplete="off"
                                minlength="3" required>
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search fa-sm mr-1"></i>{{ __('admin/alliances.al_search') }}
                                </button>
                            </div>
                        </div>
                    </form>

                    @if ($alliance !== null)
                        {{-- Result: inline summary card --}}
                        <div class="card border-left-primary shadow-sm mt-2">
                            <div class="card-body py-3">
                                <div class="row align-items-center">

                                    {{-- Identity --}}
                                    <div class="col-12 col-md-5 mb-2 mb-md-0">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-3 text-primary" style="font-size: 2rem; line-height:1;">
                                                <i class="fas fa-shield-alt"></i>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold text-gray-800" style="font-size: 1.05rem;">
                                                    {{ $alliance->alliance_name }}
                                                    <span class="badge badge-secondary ml-1">[{{ $alliance->alliance_tag }}]</span>
                                                </div>
                                                <div class="small text-muted mt-1">
                                                    <i class="fas fa-user fa-xs mr-1"></i>
                                                    {{ __('admin/alliances.al_owner') }}:
                                                    <span class="text-gray-700">{{ $alliance->owner?->name ?? '—' }}</span>
                                                    <span class="mx-2 text-gray-400">|</span>
                                                    <i class="fas fa-users fa-xs mr-1"></i>
                                                    {{ __('admin/alliances.al_members') }}:
                                                    <span class="text-gray-700">{{ $alliance->members_count }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Quick actions --}}
                                    <div class="col-12 col-md-7 d-flex flex-wrap justify-content-md-end" style="gap: 0.5rem;">
                                        <a class="btn btn-sm btn-secondary btn-icon-split" href="{{ route('admin.alliances.info', $alliance->alliance_id) }}">
                                            <span class="icon text-white-50"><i class="fas fa-info-circle"></i></span>
                                            <span class="text">{{ __('admin/alliances.al_general_info') }}</span>
                                        </a>
                                        <a class="btn btn-sm btn-secondary btn-icon-split" href="{{ route('admin.alliances.ranks', $alliance->alliance_id) }}">
                                            <span class="icon text-white-50"><i class="fas fa-sitemap"></i></span>
                                            <span class="text">{{ __('admin/alliances.al_ranks') }}</span>
                                        </a>
                                        <a class="btn btn-sm btn-secondary btn-icon-split" href="{{ route('admin.alliances.members', $alliance->alliance_id) }}">
                                            <span class="icon text-white-50"><i class="fas fa-users"></i></span>
                                            <span class="text">{{ __('admin/alliances.al_members') }}</span>
                                        </a>
                                    </div>

                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
