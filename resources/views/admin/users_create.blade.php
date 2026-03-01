@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('admin/users.us_title') }}</h1>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-plus mr-1"></i>
                        {{ __('admin/users.us_create_title') }}
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.users.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">{{ __('admin/users.us_create_name') }}</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        minlength="4" maxlength="20" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">{{ __('admin/users.us_create_email') }}</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">{{ __('admin/users.us_create_pass') }}</label>
                                    <input type="password" class="form-control" id="password" name="password" minlength="8">
                                    <div class="custom-control custom-checkbox mt-2">
                                        <input type="checkbox" class="custom-control-input" id="password_check"
                                            name="password_check" checked>
                                        <label class="custom-control-label" for="password_check">
                                            {{ __('admin/users.us_create_password_random') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="authlevel">{{ __('admin/users.us_create_level') }}</label>
                                    <select class="form-control" id="authlevel" name="authlevel">
                                        @foreach ($user_levels as $item)
                                            <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>{{ __('admin/users.us_create_coords') }}</label>
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
                            <a href="{{ route('admin.users') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i>{{ __('admin/users.us_back') }}
                            </a>
                            <button type="submit" class="btn btn-primary btn-icon-split">
                                <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                                <span class="text">{{ __('admin/users.us_create_add_user') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
