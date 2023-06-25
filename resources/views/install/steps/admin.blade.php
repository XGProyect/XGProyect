@extends('master.install')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <!-- Page Heading -->
    <h1 class="h3 mb-0 text-gray-800">{{ __('install/install.admin') }}</h1>
    <hr class="sidebar-divider d-none d-md-block">
    <p class="mb-4">{!! __('install/install.admin_details') !!}</p>

    <div class="card mb-4 py-3 border-bottom-primary">
        <div class="card-body">
            @if ($hideForm)
            <p>
                <span class="icon text-success">
                    <i class="fas fa-fw fa-check-circle"></i>
                </span>
                <strong>{{ __('install/install.admin_create_success') }}</strong>
            </p>
            @else
            <form method="POST" action="{{ route('install.step.admin.check') }}">
                @csrf
                <div class="form-group row">
                    <label for="email" class="col-4 col-form-label">{{ __('install/install.admin_email') }}</label>
                    <div class="col-8">
                        <input id="email" name="email" placeholder="captain_quantum@xgproyect.org" type="text" class="form-control" aria-describedby="emailHelpBlock" value="{{ old('email') }}">
                        <span id="emailHelpBlock" class="form-text text-muted">{{ __('install/install.admin_email_help') }}</span>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="username" class="col-4 col-form-label">{{ __('install/install.admin_username') }}</label>
                    <div class="col-8">
                        <input id="username" name="username" placeholder="Captain Quantum" type="text" class="form-control" aria-describedby="usernameHelpBlock" value="{{ old('username') }}">
                        <span id="usernameHelpBlock" class="form-text text-muted">{{ __('install/install.admin_username_help') }}</span>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="password" class="col-4 col-form-label">{{ __('install/install.admin_password') }}</label>
                    <div class="col-8">
                        <input id="password" name="password" placeholder="12345" type="password" class="form-control">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="password-confirm" class="col-4 col-form-label">{{ __('install/install.admin_password_confirm') }}</label>
                    <div class="col-8">
                        <input id="password-confirm" name="password-confirm" placeholder="12345" type="password" class="form-control">
                    </div>
                </div>
                <div class="form-group row">
                    <div class="offset-4 col-8">
                        <button type="submit" class="btn btn-primary btn-icon-split">
                            <span class="icon text-white-50">
                                <i class="fas fa-user-plus"></i>
                            </span>
                            <span class="text">{{ __('install/install.create_admin') }}</span>
                        </button>
                    </div>
                </div>
            </form>
            @endif
        </div>
    </div>

    @unless (!$hideForm)
    <div class="card mb-4 py-3 border-bottom-primary">
        <div class="card-body text-center">
            <p>{{ __('install/install.admin_notice') }}</p>
            <a href="{{ route('install.step.final') }}" class="btn btn-primary btn-icon-split">
                <span class="icon text-white-50">
                    <i class="fas fa-check-circle"></i>
                </span>
                <span class="text">{{ __('install/install.final') }}</span>
            </a>
        </div>
    </div>
    @endunless
</div>
@endsection