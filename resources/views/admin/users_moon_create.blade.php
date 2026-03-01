@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('admin/users.us_title') }}</h1>
    </div>

    @include('admin.partials.users_nav', ['active' => 'moons'])

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-moon mr-1"></i>
                        {{ __('admin/users.us_create_moon_title') }}
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.users.moon.store', $user->id) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="planet">{{ __('admin/users.us_create_moon_planet') }}</label>
                            <select class="form-control" id="planet" name="planet" required>
                                <option value="0">-</option>
                                @foreach ($planets as $p)
                                    <option value="{{ $p->planet_id }}">
                                        {{ $p->planet_name }} [{{ $p->planet_galaxy }}:{{ $p->planet_system }}:{{ $p->planet_planet }}]
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">{{ __('admin/users.us_create_moon_name') }}</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="{{ __('admin/users.us_create_moon_default_name') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="planet_field_max">{{ __('admin/users.us_create_moon_available_fields') }}</label>
                                    <input type="number" class="form-control" id="planet_field_max"
                                        name="planet_field_max" value="1" maxlength="5">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="planet_diameter">{{ __('admin/users.us_create_moon_diameter') }}</label>
                                    <input type="number" class="form-control" id="planet_diameter"
                                        name="planet_diameter" maxlength="5">
                                    <div class="custom-control custom-checkbox mt-2">
                                        <input type="checkbox" class="custom-control-input" id="diameter_check"
                                            name="diameter_check" checked>
                                        <label class="custom-control-label" for="diameter_check">
                                            {{ __('admin/users.us_create_moon_random') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin/users.us_create_moon_temperature') }}</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="planet_temp_min" placeholder="Min">
                                        <div class="input-group-prepend input-group-append">
                                            <span class="input-group-text">/</span>
                                        </div>
                                        <input type="number" class="form-control" name="planet_temp_max" placeholder="Max">
                                    </div>
                                    <div class="custom-control custom-checkbox mt-2">
                                        <input type="checkbox" class="custom-control-input" id="temp_check"
                                            name="temp_check" checked>
                                        <label class="custom-control-label" for="temp_check">
                                            {{ __('admin/users.us_create_moon_random') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.users.moons', $user->id) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i>{{ __('admin/users.us_back_to_moons') }}
                            </a>
                            <button type="submit" class="btn btn-primary btn-icon-split">
                                <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                                <span class="text">{{ __('admin/users.us_create_moon_add') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
