@php
    use Xgp\App\Core\Enumerators\PlanetTypesEnumerator;
    $is_moon = ($planet_type === PlanetTypesEnumerator::MOON);
    $back_route = $is_moon ? route('admin.users.moons', $user->id) : route('admin.users.planets', $user->id);
    $save_route = $is_moon
        ? route('admin.users.moon.ships.update', [$user->id, $planet_id])
        : route('admin.users.planet.ships.update', [$user->id, $planet_id]);
    $edit_route = $is_moon
        ? route('admin.users.moon.edit', [$user->id, $planet_id])
        : route('admin.users.planet.edit', [$user->id, $planet_id]);
    $buildings_route = $is_moon
        ? route('admin.users.moon.buildings', [$user->id, $planet_id])
        : route('admin.users.planet.buildings', [$user->id, $planet_id]);
    $defenses_route = $is_moon
        ? route('admin.users.moon.defenses', [$user->id, $planet_id])
        : route('admin.users.planet.defenses', [$user->id, $planet_id]);
    $nav_active = $is_moon ? 'moons' : 'planets';
    $back_label = $is_moon ? __('admin/users.us_back_to_moons') : __('admin/users.us_back_to_planets');
@endphp
@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('admin/users.us_title') }}</h1>
    </div>

    @include('admin.partials.users_nav', ['active' => $nav_active])

    <div class="row">
        <div class="col-lg-12">
            <div class="mb-3 d-flex flex-wrap" style="gap: 0.4rem;">
                <a class="btn btn-sm btn-secondary btn-icon-split" href="{{ $edit_route }}">
                    <span class="icon text-white-50"><i class="fas fa-{{ $is_moon ? 'moon' : 'globe' }}"></i></span>
                    <span class="text">{{ __('admin/users.us_planet_general') }}</span>
                </a>
                <a class="btn btn-sm btn-secondary btn-icon-split" href="{{ $buildings_route }}">
                    <span class="icon text-white-50"><i class="fas fa-building"></i></span>
                    <span class="text">{{ __('admin/users.us_buildings') }}</span>
                </a>
                <a class="btn btn-sm btn-primary btn-icon-split" href="#">
                    <span class="icon text-white-50"><i class="fas fa-rocket"></i></span>
                    <span class="text">{{ __('admin/users.us_ships') }}</span>
                </a>
                <a class="btn btn-sm btn-secondary btn-icon-split" href="{{ $defenses_route }}">
                    <span class="icon text-white-50"><i class="fas fa-shield-alt"></i></span>
                    <span class="text">{{ __('admin/users.us_defenses') }}</span>
                </a>
                <a class="btn btn-sm btn-outline-secondary btn-icon-split ml-auto" href="{{ $back_route }}">
                    <span class="icon text-white-50"><i class="fas fa-arrow-left"></i></span>
                    <span class="text">{{ $back_label }}</span>
                </a>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-rocket mr-1"></i>
                        {{ __('admin/users.us_ships_title') }}
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ $save_route }}">
                        @csrf

                        <div class="row">
                            @foreach ($ships as $s)
                                <div class="col-md-4 col-lg-3 mb-3">
                                    <label class="small font-weight-bold text-gray-700" for="{{ $s['field'] }}">
                                        {{ $s['label'] }}
                                    </label>
                                    <input type="number" id="{{ $s['field'] }}" name="{{ $s['field'] }}"
                                        class="form-control form-control-sm"
                                        min="0" value="{{ old($s['field'], $s['amount']) }}">
                                </div>
                            @endforeach
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <a href="{{ $back_route }}" class="btn btn-secondary btn-icon-split">
                                <span class="icon text-white-50"><i class="fas fa-arrow-left"></i></span>
                                <span class="text">{{ $back_label }}</span>
                            </a>
                            <button type="submit" class="btn btn-primary btn-icon-split">
                                <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                                <span class="text">{{ __('admin/users.us_send_data') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
