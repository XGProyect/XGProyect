@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('admin/users.us_title') }}</h1>
    </div>

    @include('admin.partials.users_nav', ['active' => 'settings'])

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cog mr-1"></i>
                        {{ __('admin/users.us_settings_title', ['user' => $user->name]) }}
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.settings.update', $user->id) }}">
                        @csrf

                        {{-- Spy probes --}}
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right" for="preference_spy_probes">
                                {{ __('admin/users.us_user_preference_spy_probes') }}
                            </label>
                            <div class="col-md-4">
                                <input type="number" id="preference_spy_probes" name="preference_spy_probes"
                                    class="form-control" min="0" max="100"
                                    value="{{ old('preference_spy_probes', $prefs->preference_spy_probes ?? 1) }}">
                            </div>
                        </div>

                        {{-- Planet sort --}}
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right" for="preference_planet_sort">
                                {{ __('admin/users.us_user_preference_planet_sort') }}
                            </label>
                            <div class="col-md-4">
                                <select id="preference_planet_sort" name="preference_planet_sort" class="form-control">
                                    @foreach ($planet_sort_options as $val => $label)
                                        <option value="{{ $val }}"
                                            @selected(old('preference_planet_sort', $prefs->preference_planet_sort ?? 0) == $val)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Planet sort sequence --}}
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right" for="preference_planet_sort_sequence">
                                {{ __('admin/users.us_user_preference_planet_sort_sequence') }}
                            </label>
                            <div class="col-md-4">
                                <select id="preference_planet_sort_sequence" name="preference_planet_sort_sequence" class="form-control">
                                    @foreach ($planet_sort_sequence_options as $val => $label)
                                        <option value="{{ $val }}"
                                            @selected(old('preference_planet_sort_sequence', $prefs->preference_planet_sort_sequence ?? 0) == $val)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Vacation mode --}}
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right">
                                {{ __('admin/users.us_user_preference_vacation_mode') }}
                            </label>
                            <div class="col-md-8 d-flex align-items-center">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="preference_vacations_status"
                                        name="preference_vacations_status" value="on"
                                        @checked(old('preference_vacations_status') === 'on' || ($prefs && $prefs->preference_vacation_mode > 0))>
                                    <label class="custom-control-label" for="preference_vacations_status">
                                        @if ($vacation_until)
                                            <span class="text-warning">{{ __('admin/users.us_user_on_vacation_until') }} {{ $vacation_until }}</span>
                                        @else
                                            {{ __('admin/users.us_user_preference_vacation_enable') }}
                                        @endif
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Delete mode --}}
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right">
                                {{ __('admin/users.us_user_preference_delete_mode') }}
                            </label>
                            <div class="col-md-8 d-flex align-items-center">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="preference_delete_mode"
                                        name="preference_delete_mode" value="on"
                                        @checked(old('preference_delete_mode') === 'on' || ($prefs && $prefs->preference_delete_mode > 0))>
                                    <label class="custom-control-label" for="preference_delete_mode">
                                        {{ __('admin/users.us_user_preference_delete_enable') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.users') }}" class="btn btn-secondary btn-icon-split">
                                <span class="icon text-white-50"><i class="fas fa-arrow-left"></i></span>
                                <span class="text">{{ __('admin/users.us_back') }}</span>
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
