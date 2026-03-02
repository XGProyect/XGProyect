@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <x-admin.page-header title="{{ __('admin/users.us_title') }}" />

    @include('admin.partials.users_nav', ['active' => 'planets'])

    <div class="row">
        <div class="col-lg-12">
            {{-- Sub-navigation for this planet --}}
            <div class="mb-3 d-flex flex-wrap" style="gap: 0.4rem;">
                <a class="btn btn-sm btn-primary btn-icon-split"
                    href="{{ route('admin.users.planet.edit', [$user->id, $planet['planet_id']]) }}">
                    <span class="icon text-white-50"><i class="fas fa-globe"></i></span>
                    <span class="text">{{ __('admin/users.us_planet_general') }}</span>
                </a>
                <a class="btn btn-sm btn-secondary btn-icon-split"
                    href="{{ route('admin.users.planet.buildings', [$user->id, $planet['planet_id']]) }}">
                    <span class="icon text-white-50"><i class="fas fa-building"></i></span>
                    <span class="text">{{ __('admin/users.us_buildings') }}</span>
                </a>
                <a class="btn btn-sm btn-secondary btn-icon-split"
                    href="{{ route('admin.users.planet.ships', [$user->id, $planet['planet_id']]) }}">
                    <span class="icon text-white-50"><i class="fas fa-rocket"></i></span>
                    <span class="text">{{ __('admin/users.us_ships') }}</span>
                </a>
                <a class="btn btn-sm btn-secondary btn-icon-split"
                    href="{{ route('admin.users.planet.defenses', [$user->id, $planet['planet_id']]) }}">
                    <span class="icon text-white-50"><i class="fas fa-shield-alt"></i></span>
                    <span class="text">{{ __('admin/users.us_defenses') }}</span>
                </a>
                <a class="btn btn-sm btn-outline-secondary btn-icon-split ml-auto"
                    href="{{ route('admin.users.planets', $user->id) }}">
                    <span class="icon text-white-50"><i class="fas fa-arrow-left"></i></span>
                    <span class="text">{{ __('admin/users.us_back_to_planets') }}</span>
                </a>
            </div>

            <x-admin.card
                    title="{{ $planet['planet_name'] }} <span class='text-muted font-weight-normal small ml-2'>[{{ $planet['planet_galaxy'] }}:{{ $planet['planet_system'] }}:{{ $planet['planet_planet'] }}]</span>"
                    icon="fas fa-globe"
                >
                    <x-slot name="action">
                        @if ($planet['is_destroyed'])
                            <span class="badge badge-warning"><i class="fas fa-skull-crossbones mr-1"></i>{{ __('admin/users.us_planet_scheduled_destroy') }}</span>
                        @endif
                    </x-slot>
                    <form method="POST" action="{{ route('admin.users.planet.update', [$user->id, $planet['planet_id']]) }}">
                        @csrf

                        {{-- ── SECTION 1: General ─────────────────────────────────────────── --}}
                        <div class="card border-left-primary shadow-sm mb-4">
                            <div class="card-header py-2">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-globe mr-1"></i>
                                    {{ __('admin/users.us_planet_general') }}
                                </h6>
                            </div>
                            <div class="card-body py-3">
                                <div class="row">
                                    {{-- Name --}}
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="planet_name">{{ __('admin/users.us_planet_field_name') }}</label>
                                            <input type="text" id="planet_name" name="planet_name" class="form-control"
                                                value="{{ old('planet_name', $planet['planet_name']) }}">
                                        </div>
                                    </div>
                                    {{-- Owner --}}
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="planet_user_id">{{ __('admin/users.us_planet_field_owner') }}</label>
                                            <select id="planet_user_id" name="planet_user_id" class="form-control">
                                                @foreach ($all_users as $u)
                                                    <option value="{{ $u->id }}" @selected(old('planet_user_id', $planet['planet_user_id']) == $u->id)>{{ $u->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    {{-- Image --}}
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="planet_image">{{ __('admin/users.us_planet_field_image') }}</label>
                                            <div class="d-flex align-items-center" style="gap:0.75rem;">
                                                <img id="planet_image_preview"
                                                    src="/assets/upload/skins/xgproyect/planets/{{ old('planet_image', $planet['planet_image']) }}.jpg"
                                                    alt="" style="width:48px;height:48px;object-fit:cover;border-radius:4px;flex-shrink:0;"
                                                    onerror="this.style.visibility='hidden'" onload="this.style.visibility='visible'">
                                                <select id="planet_image" name="planet_image" class="form-control"
                                                    onchange="document.getElementById('planet_image_preview').src='/assets/upload/skins/xgproyect/planets/'+this.value+'.jpg'">
                                                    @foreach ($images as $imgName => $imgFile)
                                                        <option value="{{ $imgName }}" @selected(old('planet_image', $planet['planet_image']) == $imgName)>{{ $imgName }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Diameter --}}
                                    <div class="col-md-3">
                                        <div class="form-group mb-0">
                                            <label for="planet_diameter">{{ __('admin/users.us_planet_field_diameter') }}</label>
                                            <input type="number" id="planet_diameter" name="planet_diameter" class="form-control"
                                                min="0" value="{{ old('planet_diameter', $planet['planet_diameter']) }}">
                                        </div>
                                    </div>
                                    {{-- Max fields --}}
                                    <div class="col-md-3">
                                        <div class="form-group mb-0">
                                            <label for="planet_field_max">{{ __('admin/users.us_planet_field_max') }}</label>
                                            <input type="number" id="planet_field_max" name="planet_field_max" class="form-control"
                                                min="0" value="{{ old('planet_field_max', $planet['planet_field_max']) }}">
                                            <small class="form-text text-muted">
                                                <i class="fas fa-th-large mr-1"></i>
                                                {{ __('admin/users.us_planet_field_occupied') }}: <strong>{{ $planet['planet_field_current'] }}</strong> / {{ $planet['planet_field_max'] }}
                                            </small>
                                        </div>
                                    </div>
                                    {{-- Temp min --}}
                                    <div class="col-md-3">
                                        <div class="form-group mb-0">
                                            <label for="planet_temp_min">
                                                <i class="fas fa-thermometer-quarter text-info mr-1"></i>
                                                {{ __('admin/users.us_planet_field_temp_min') }}
                                            </label>
                                            <input type="number" id="planet_temp_min" name="planet_temp_min" class="form-control"
                                                value="{{ old('planet_temp_min', $planet['planet_temp_min']) }}">
                                        </div>
                                    </div>
                                    {{-- Temp max --}}
                                    <div class="col-md-3">
                                        <div class="form-group mb-0">
                                            <label for="planet_temp_max">
                                                <i class="fas fa-thermometer-three-quarters text-danger mr-1"></i>
                                                {{ __('admin/users.us_planet_field_temp_max') }}
                                            </label>
                                            <input type="number" id="planet_temp_max" name="planet_temp_max" class="form-control"
                                                value="{{ old('planet_temp_max', $planet['planet_temp_max']) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── SECTION 2: Resources ────────────────────────────────────────── --}}
                        <div class="card border-left-warning shadow-sm mb-4">
                            <div class="card-header py-2">
                                <h6 class="m-0 font-weight-bold text-warning">
                                    <i class="fas fa-coins mr-1"></i>
                                    {{ __('admin/users.us_planet_resources') }}
                                </h6>
                            </div>
                            <div class="card-body py-3">
                                <div class="row">
                                    <div class="col-sm-6 col-md-3">
                                        <div class="form-group mb-0">
                                            <label for="planet_metal">
                                                <i class="fas fa-cube text-warning mr-1"></i>
                                                {{ __('admin/users.us_planet_field_metal') }}
                                            </label>
                                            <input type="number" id="planet_metal" name="planet_metal" class="form-control"
                                                min="0" step="0.01" value="{{ old('planet_metal', $planet['planet_metal']) }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-3">
                                        <div class="form-group mb-0">
                                            <label for="planet_crystal">
                                                <i class="fas fa-gem text-info mr-1"></i>
                                                {{ __('admin/users.us_planet_field_crystal') }}
                                            </label>
                                            <input type="number" id="planet_crystal" name="planet_crystal" class="form-control"
                                                min="0" step="0.01" value="{{ old('planet_crystal', $planet['planet_crystal']) }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-3">
                                        <div class="form-group mb-0">
                                            <label for="planet_deuterium">
                                                <i class="fas fa-tint text-primary mr-1"></i>
                                                {{ __('admin/users.us_planet_field_deuterium') }}
                                            </label>
                                            <input type="number" id="planet_deuterium" name="planet_deuterium" class="form-control"
                                                min="0" step="0.01" value="{{ old('planet_deuterium', $planet['planet_deuterium']) }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-3">
                                        <div class="form-group mb-0">
                                            <label for="planet_energy_used">
                                                <i class="fas fa-bolt text-success mr-1"></i>
                                                {{ __('admin/users.us_planet_field_energy') }}
                                            </label>
                                            <input type="number" id="planet_energy_used" name="planet_energy_used" class="form-control"
                                                value="{{ old('planet_energy_used', $planet['planet_energy_used']) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── SECTION 3: Production ───────────────────────────────────────── --}}
                        <div class="card border-left-info shadow-sm mb-4">
                            <div class="card-header py-2">
                                <h6 class="m-0 font-weight-bold text-info">
                                    <i class="fas fa-industry mr-1"></i>
                                    {{ __('admin/users.us_planet_production') }}
                                </h6>
                            </div>
                            <div class="card-body py-3">
                                <div class="row align-items-end">
                                    @php
                                        $pct_fields = [
                                            'planet_building_metal_mine_percent'           => ['icon' => 'cube',        'color' => 'text-warning'],
                                            'planet_building_crystal_mine_percent'         => ['icon' => 'gem',         'color' => 'text-info'],
                                            'planet_building_deuterium_sintetizer_percent' => ['icon' => 'tint',        'color' => 'text-primary'],
                                            'planet_building_solar_plant_percent'          => ['icon' => 'solar-panel', 'color' => 'text-success'],
                                            'planet_building_fusion_reactor_percent'       => ['icon' => 'atom',        'color' => 'text-danger'],
                                            'planet_ship_solar_satellite_percent'          => ['icon' => 'satellite',   'color' => 'text-secondary'],
                                        ];
                                    @endphp
                                    @foreach ($pct_fields as $pct_field => $meta)
                                        <div class="col-sm-6 col-md-4 col-xl-2 mb-3">
                                            <label class="small font-weight-bold text-gray-700 d-block" for="{{ $pct_field }}">
                                                <i class="fas fa-{{ $meta['icon'] }} {{ $meta['color'] }} mr-1"></i>
                                                {{ __('admin/users.us_user_main_' . str_replace('planet_', '', $pct_field)) }}
                                            </label>
                                            <select id="{{ $pct_field }}" name="{{ $pct_field }}" class="form-control">
                                                @foreach ($percent_options as $val => $label)
                                                    <option value="{{ $val }}" @selected(old($pct_field, $planet[$pct_field] ?? 10) == $val)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- ── Timestamps ──────────────────────────────────────────────────── --}}
                        <div class="card bg-light border-0 shadow-sm mb-4">
                            <div class="card-body py-2">
                                <div class="row text-muted small">
                                    <div class="col-md-6">
                                        <i class="fas fa-clock mr-1"></i>
                                        <strong>{{ __('admin/users.us_planet_last_update') }}:</strong>
                                        {{ $planet['planet_last_update_display'] }}
                                    </div>
                                    <div class="col-md-6">
                                        <i class="fas fa-hammer mr-1"></i>
                                        <strong>{{ __('admin/users.us_planet_b_building') }}:</strong>
                                        {{ $planet['planet_b_building_display'] }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── Destruction toggle ──────────────────────────────────────────── --}}
                        <div class="custom-control custom-switch mb-4">
                            <input type="hidden" name="planet_destroyed" value="0">
                            <input type="checkbox" class="custom-control-input" id="planet_destroyed"
                                name="planet_destroyed" value="1"
                                @checked(old('planet_destroyed', $planet['is_destroyed'] ? 1 : 0) == 1)>
                            <label class="custom-control-label text-danger" for="planet_destroyed">
                                <i class="fas fa-skull-crossbones mr-1"></i>
                                {{ __('admin/users.us_planet_schedule_destroy') }}
                                @if ($planet['planet_destroyed_at'])
                                    <small class="text-muted ml-1">({{ $planet['planet_destroyed_at'] }})</small>
                                @endif
                            </label>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.users.planets', $user->id) }}" class="btn btn-secondary btn-icon-split">
                                <span class="icon text-white-50"><i class="fas fa-arrow-left"></i></span>
                                <span class="text">{{ __('admin/users.us_back_to_planets') }}</span>
                            </a>
                            <button type="submit" class="btn btn-primary btn-icon-split">
                                <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                                <span class="text">{{ __('admin/users.us_send_data') }}</span>
                            </button>
                        </div>
                    </form>
                </x-admin.card>
        </div>
    </div>
</div>
@endsection
