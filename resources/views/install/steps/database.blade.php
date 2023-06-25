@extends('master.install')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <!-- Page Heading -->
    <h1 class="h3 mb-0 text-gray-800">{{ __('install/install.database') }}</h1>
    <hr class="sidebar-divider d-none d-md-block">
    <p class="mb-4">{!! __('install/install.database_details') !!}</p>

    <div class="card mb-4 py-3 border-bottom-primary">
        <div class="card-body">
            @if ($hideForm)
            <p>
                <span class="icon text-success">
                    <i class="fas fa-fw fa-check-circle"></i>
                </span>
                <strong>{{ __('install/install.db_connect_success') }}</strong>
            </p>
            @else
            <form method="POST" action="{{ route('install.step.database.check') }}">
                @csrf
                <div class="form-group row">
                    <label for="driver" class="col-4 col-form-label">{{ __('install/install.driver') }}</label>
                    <div class="col-8">
                        <select id="driver" name="driver" required="required" class="custom-select">
                            <option value="mysql">MySQL</option>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="host" class="col-4 col-form-label">{{ __('install/install.host') }}</label>
                    <div class="col-8">
                        <input id="host" name="host" placeholder="localhost" type="text" required="required" class="form-control" value="{{ old('host') }}">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="port" class="col-4 col-form-label">{{ __('install/install.port') }}</label>
                    <div class="col-8">
                    <input id="port" name="port" type="text" aria-describedby="portHelpBlock" class="form-control" value="{{ old('port') }}">
                        <span id="portHelpBlock" class="form-text text-muted">{{ __('install/install.port_help') }}</span>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="database" class="col-4 col-form-label">{{ __('install/install.db_name') }}</label>
                    <div class="col-8">
                        <input id="database" name="database" placeholder="xgproyect" type="text" class="form-control" required="required" value="{{ old('database') }}">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="username" class="col-4 col-form-label">{{ __('install/install.db_username') }}</label>
                    <div class="col-8">
                        <input id="username" name="username" placeholder="root" type="text" class="form-control" required="required" value="{{ old('username') }}">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="password" class="col-4 col-form-label">{{ __('install/install.db_password') }}</label>
                    <div class="col-8">
                        <input id="password" name="password" placeholder="root" type="password" class="form-control">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="prefix" class="col-4 col-form-label">{{ __('install/install.prefix') }}</label>
                    <div class="col-8">
                        <input id="prefix" name="prefix" placeholder="xgp_" type="text" class="form-control" aria-describedby="prefixHelpBlock" value="{{ old('prefix') }}">
                        <span id="prefixHelpBlock" class="form-text text-muted">{{ __('install/install.prefix_help') }}</span>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="offset-4 col-8">
                        <button type="submit" class="btn btn-primary btn-icon-split">
                            <span class="icon text-white-50">
                                <i class="fas fa-link"></i>
                            </span>
                            <span class="text">{{ __('install/install.db_check') }}</span>
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
            <p>{{ __('install/install.tables_notice') }}</p>
            <a href="{{ route('install.step.tables') }}" class="btn btn-primary btn-icon-split">
                <span class="icon text-white-50">
                    <i class="fas fa-fw fa-layer-group"></i>
                </span>
                <span class="text">{{ __('install/install.tables') }}</span>
            </a>
        </div>
    </div>
    @endunless
</div>
@endsection