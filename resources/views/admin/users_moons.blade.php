@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('admin/users.us_title') }}</h1>
    </div>

    @include('admin.partials.users_nav', ['active' => 'moons'])

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-moon mr-1"></i>
                        {{ __('admin/users.us_moons_title', ['user' => $user->name]) }}
                    </h6>
                    <a href="{{ route('admin.users.moon.create', $user->id) }}" class="btn btn-sm btn-primary btn-icon-split">
                        <span class="icon text-white-50"><i class="fas fa-plus"></i></span>
                        <span class="text">{{ __('admin/users.us_create_moon_add') }}</span>
                    </a>
                </div>
                <div class="card-body p-0">
                    @if (count($moons) === 0)
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-moon fa-3x mb-3 d-block"></i>
                            {{ __('admin/users.us_no_moons') }}
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{ __('admin/users.us_planet_name') }}</th>
                                        <th>{{ __('admin/users.us_planet_coords') }}</th>
                                        <th>{{ __('admin/users.us_planet_image') }}</th>
                                        <th>{{ __('admin/users.us_planet_status') }}</th>
                                        <th class="text-center">{{ __('admin/users.us_actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($moons as $moon)
                                        <tr class="{{ $moon->planet_destroyed > 0 ? 'table-warning' : '' }}">
                                            <td class="align-middle font-weight-bold">{{ $moon->planet_name }}</td>
                                            <td class="align-middle">
                                                [{{ $moon->planet_galaxy }}:{{ $moon->planet_system }}:{{ $moon->planet_planet }}]
                                            </td>
                                            <td class="align-middle">
                                                <img src="/assets/upload/skins/xgproyect/planets/{{ $moon->planet_image }}.jpg"
                                                    alt="{{ $moon->planet_image }}"
                                                    style="width:40px;height:40px;object-fit:cover;border-radius:4px;"
                                                    onerror="this.style.display='none'">
                                            </td>
                                            <td class="align-middle">
                                                @if ($moon->planet_destroyed > 0)
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-skull-crossbones mr-1"></i>{{ __('admin/users.us_planet_scheduled_destroy') }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-success"><i class="fas fa-check mr-1"></i>{{ __('admin/users.us_planet_active') }}</span>
                                                @endif
                                            </td>
                                            <td class="align-middle text-center" style="white-space: nowrap;">
                                                <div class="d-inline-flex" role="group">
                                                    <a href="{{ route('admin.users.moon.edit', [$user->id, $moon->planet_id]) }}"
                                                        class="btn btn-sm btn-outline-primary" title="{{ __('admin/users.us_edit') }}"
                                                        style="border-radius:0.2rem 0 0 0.2rem;">
                                                        <i class="fas fa-fw fa-pencil-alt"></i>
                                                    </a>
                                                    <a href="{{ route('admin.users.moon.buildings', [$user->id, $moon->planet_id]) }}"
                                                        class="btn btn-sm btn-outline-secondary" title="{{ __('admin/users.us_buildings') }}"
                                                        style="border-radius:0; margin-left:-1px;">
                                                        <i class="fas fa-fw fa-building"></i>
                                                    </a>
                                                    <a href="{{ route('admin.users.moon.ships', [$user->id, $moon->planet_id]) }}"
                                                        class="btn btn-sm btn-outline-secondary" title="{{ __('admin/users.us_ships') }}"
                                                        style="border-radius:0; margin-left:-1px;">
                                                        <i class="fas fa-fw fa-rocket"></i>
                                                    </a>
                                                    <a href="{{ route('admin.users.moon.defenses', [$user->id, $moon->planet_id]) }}"
                                                        class="btn btn-sm btn-outline-secondary" title="{{ __('admin/users.us_defenses') }}"
                                                        style="border-radius:0; margin-left:-1px;">
                                                        <i class="fas fa-fw fa-shield-alt"></i>
                                                    </a>
                                                    <form method="POST" action="{{ route('admin.users.moon.soft-delete', [$user->id, $moon->planet_id]) }}"
                                                        onsubmit="return confirm('{{ __('admin/users.us_moon_soft_delete_confirm') }}')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="{{ __('admin/users.us_soft_delete') }}"
                                                            style="border-radius:0; margin-left:-1px;">
                                                            <i class="fas fa-fw fa-hourglass-end"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.users.moon.destroy', [$user->id, $moon->planet_id]) }}"
                                                        onsubmit="return confirm('{{ __('admin/users.us_delete_moon_confirm') }}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('admin/users.us_hard_delete') }}"
                                                            style="border-radius:0 0.2rem 0.2rem 0; margin-left:-1px;">
                                                            <i class="fas fa-fw fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
