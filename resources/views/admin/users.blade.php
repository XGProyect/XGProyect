@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('admin/users.us_title') }}</h1>
    </div>
    <p class="mb-4 text-gray-600">{{ __('admin/users.us_sub_title') }}</p>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-search mr-1"></i>
                        {{ __('admin/users.us_search') }}
                    </h6>
                    @if ($user !== null)
                        <div class="dropdown no-arrow">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-cog fa-sm mr-1"></i>{{ __('admin/users.us_actions') }}
                            </button>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                                <a class="dropdown-item" href="{{ route('admin.users.info', $user->id) }}">
                                    <i class="fas fa-pencil-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    {{ __('admin/users.us_edit') }}
                                </a>
                                <div class="dropdown-divider"></div>
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                    onsubmit="return confirm('{{ __('admin/users.us_delete_confirm') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-trash-alt fa-sm fa-fw mr-2"></i>
                                        {{ __('admin/users.us_delete') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    {{-- Search form --}}
                    <form action="{{ route('admin.users') }}" method="GET">
                        <div class="input-group mb-3">
                            <input type="text" name="user" class="form-control bg-light border-0"
                                placeholder="{{ __('admin/users.us_username_placeholder') }}"
                                value="{{ $search }}" autocomplete="off" required>
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search fa-sm mr-1"></i>{{ __('admin/users.us_search') }}
                                </button>
                            </div>
                        </div>
                    </form>

                    @if ($user !== null)
                        <div class="card border-left-primary shadow-sm mt-2">
                            <div class="card-body py-3">
                                <div class="row align-items-center">
                                    <div class="col-12 col-md-5 mb-2 mb-md-0">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-3 text-primary" style="font-size: 2rem; line-height:1;">
                                                <i class="fas fa-user-circle"></i>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold text-gray-800" style="font-size: 1.05rem;">
                                                    {{ $user->name }}
                                                </div>
                                                <div class="small text-muted mt-1">
                                                    <i class="fas fa-id-badge fa-xs mr-1"></i>
                                                    ID: {{ $user->id }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4 mb-2 mb-md-0">
                                        @php
                                            $online = $user->onlinetime + 600 >= time();
                                            $away   = !$online && $user->onlinetime + 900 >= time();
                                        @endphp
                                        <span class="badge badge-{{ $online ? 'success' : ($away ? 'warning' : 'secondary') }}">
                                            {{ $online ? __('admin/users.us_online') : ($away ? __('admin/users.us_away') : __('admin/users.us_offline')) }}
                                        </span>
                                        <span class="badge badge-info ml-1">{{ __('admin/global.user_level')[$user->authlevel] ?? $user->authlevel }}</span>
                                    </div>
                                    <div class="col-12 col-md-3 text-md-right">
                                        <a href="{{ route('admin.users.info', $user->id) }}" class="btn btn-primary btn-sm btn-icon-split">
                                            <span class="icon text-white-50"><i class="fas fa-pencil-alt"></i></span>
                                            <span class="text">{{ __('admin/users.us_edit') }}</span>
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
