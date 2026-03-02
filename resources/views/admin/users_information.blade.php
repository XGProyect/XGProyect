@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <x-admin.page-header title="{{ __('admin/users.us_title') }}" />

    @include('admin.partials.users_nav', ['active' => 'info'])

    <div class="row">
        <div class="col-lg-12">
            <x-admin.card
                title="{{ __('admin/users.us_user_information', ['user' => $user->name]) }}"
                icon="fas fa-user"
            >
                <x-slot name="action">
                    @if ($online_status === 'online')
                        <span class="badge badge-success"><i class="fas fa-circle fa-xs mr-1"></i>{{ __('admin/users.us_online') }}</span>
                    @elseif ($online_status === 'away')
                        <span class="badge badge-warning"><i class="fas fa-circle fa-xs mr-1"></i>{{ __('admin/users.us_away') }}</span>
                    @else
                        <span class="badge badge-secondary"><i class="fas fa-circle fa-xs mr-1"></i>{{ __('admin/users.us_offline') }}</span>
                    @endif
                </x-slot>
                <form method="POST" action="{{ route('admin.users.info.update', $user->id) }}">
                        @csrf

                        {{-- Register time --}}
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right">{{ __('admin/users.us_user_register_time') }}</label>
                            <div class="col-md-8"><p class="form-control-plaintext">{{ $register_time }}</p></div>
                        </div>

                        {{-- Username --}}
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right" for="username">{{ __('admin/users.us_user_username') }}</label>
                            <div class="col-md-8">
                                <input type="text" id="username" name="username"
                                    class="form-control @error('username') is-invalid @enderror"
                                    value="{{ old('username', $user->name) }}">
                                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Email --}}
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right" for="email">{{ __('admin/users.us_user_email') }}</label>
                            <div class="col-md-8">
                                <input type="email" id="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $user->email) }}">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Password --}}
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right" for="password">{{ __('admin/users.us_user_password') }}</label>
                            <div class="col-md-8">
                                <input type="password" id="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    autocomplete="new-password"
                                    placeholder="{{ __('admin/users.us_password_placeholder') }}">
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <small class="form-text text-muted">{{ __('admin/users.us_password_hint') }}</small>
                            </div>
                        </div>

                        {{-- Role / Auth level --}}
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right" for="authlevel">{{ __('admin/users.us_user_authlevel') }}</label>
                            <div class="col-md-8">
                                <select id="authlevel" name="authlevel"
                                    class="form-control @error('authlevel') is-invalid @enderror">
                                    @foreach ($user_roles as $role)
                                        <option value="{{ $role['role_id'] }}"
                                            @selected($role['selected'])
                                            @disabled($role['disabled'])>
                                            {{ $role['role_name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('authlevel')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Alliance --}}
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right" for="ally_id">{{ __('admin/users.us_user_ally') }}</label>
                            <div class="col-md-8">
                                <select id="ally_id" name="ally_id" class="form-control">
                                    <option value="0">— {{ __('admin/users.us_no_alliance') }} —</option>
                                    @foreach ($alliances as $alliance)
                                        <option value="{{ $alliance->alliance_id }}"
                                            @selected(old('ally_id', $data->ally_id ?? 0) == $alliance->alliance_id)>
                                            [{{ $alliance->alliance_tag }}] {{ $alliance->alliance_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Home planet --}}
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right" for="home_planet_id">{{ __('admin/users.us_user_home_planet') }}</label>
                            <div class="col-md-8">
                                <select id="home_planet_id" name="home_planet_id" class="form-control">
                                    @foreach ($planets as $planet)
                                        <option value="{{ $planet->planet_id }}"
                                            @selected(old('home_planet_id', $user->home_planet_id) == $planet->planet_id)>
                                            {{ $planet->planet_name }} [{{ $planet->planet_galaxy }}:{{ $planet->planet_system }}:{{ $planet->planet_planet }}]
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Current planet --}}
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right" for="current_planet">{{ __('admin/users.us_user_current_planet') }}</label>
                            <div class="col-md-8">
                                <select id="current_planet" name="current_planet" class="form-control">
                                    @foreach ($planets as $planet)
                                        <option value="{{ $planet->planet_id }}"
                                            @selected(old('current_planet', $user->current_planet) == $planet->planet_id)>
                                            {{ $planet->planet_name }} [{{ $planet->planet_galaxy }}:{{ $planet->planet_system }}:{{ $planet->planet_planet }}]
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Ban status --}}
                        @if ($ban)
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label text-md-right">{{ __('admin/users.us_user_ban') }}</label>
                                <div class="col-md-8">
                                    <div class="alert alert-warning mb-0 py-2">
                                        <i class="fas fa-ban mr-1"></i>
                                        {{ __('admin/users.us_user_banned_reason') }}: <strong>{{ $ban->ban_reason }}</strong>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Fleet shortcuts --}}
                        @if (!empty($shortcuts))
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label text-md-right">{{ __('admin/users.us_user_shortcuts') }}</label>
                                <div class="col-md-8">
                                    <select class="form-control" size="4" disabled>
                                        @foreach ($shortcuts as $label)
                                            <option>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif

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
            </x-admin.card>
        </div>
    </div>
</div>
@endsection
