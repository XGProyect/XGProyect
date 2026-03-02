@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <x-admin.page-header title="{{ __('admin/users.us_title') }}" />

    @include('admin.partials.users_nav', ['active' => 'planets'])

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <x-admin.card title="{{ __('admin/users.us_create_planet_title') }}" icon="fas fa-globe">
                <form action="{{ route('admin.users.planet.store', $user->id) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">{{ __('admin/users.us_create_planet_name') }}</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        maxlength="25" value="{{ __('admin/users.us_create_planet_default_name') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="planet_field_max">{{ __('admin/users.us_create_planet_available_fields') }}</label>
                                    <input type="number" class="form-control" id="planet_field_max"
                                        name="planet_field_max" value="163" maxlength="3">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>{{ __('admin/users.us_create_planet_coords') }}</label>
                            <div class="input-group" style="max-width: 320px;">
                                <input type="number" class="form-control" name="galaxy" placeholder="1" min="1" required>
                                <div class="input-group-prepend input-group-append">
                                    <span class="input-group-text">:</span>
                                </div>
                                <input type="number" class="form-control" name="system" placeholder="1" min="1" required>
                                <div class="input-group-prepend input-group-append">
                                    <span class="input-group-text">:</span>
                                </div>
                                <input type="number" class="form-control" name="planet" placeholder="1" min="1" required>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.users.planets', $user->id) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i>{{ __('admin/users.us_back_to_planets') }}
                            </a>
                            <button type="submit" class="btn btn-primary btn-icon-split">
                                <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                                <span class="text">{{ __('admin/users.us_create_planet_add') }}</span>
                            </button>
                        </div>
                    </form>
            </x-admin.card>
        </div>
    </div>
</div>
@endsection
