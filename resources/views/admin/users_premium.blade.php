@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <x-admin.page-header title="{{ __('admin/users.us_title') }}" />

    @include('admin.partials.users_nav', ['active' => 'premium'])

    <div class="row">
        <div class="col-lg-12">
            <x-admin.card
                title="{{ __('admin/users.us_premium_title', ['user' => $user->name]) }}"
                icon="fas fa-star"
            >
                <form method="POST" action="{{ route('admin.users.premium.update', $user->id) }}">
                        @csrf

                        {{-- Dark matter --}}
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right" for="premium_dark_matter">
                                {{ __('admin/users.us_user_dark_matter') }}
                            </label>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-gem"></i></span>
                                    </div>
                                    <input type="number" id="premium_dark_matter" name="premium_dark_matter"
                                        class="form-control" min="0"
                                        value="{{ old('premium_dark_matter', $dark_matter) }}">
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h6 class="font-weight-bold text-gray-700 mb-3">{{ __('admin/users.us_officers') }}</h6>

                        @foreach ($officers as $officer)
                            <div class="form-group row align-items-center">
                                <label class="col-md-4 col-form-label text-md-right" for="{{ $officer['field'] }}">
                                    {{ $officer['label'] }}
                                </label>
                                <div class="col-md-5">
                                    <select id="{{ $officer['field'] }}" name="{{ $officer['field'] }}" class="form-control">
                                        <option value="0">{{ __('admin/users.us_officer_keep') }}</option>
                                        <option value="1">{{ __('admin/users.us_officer_deactivate') }}</option>
                                        <option value="2">{{ __('admin/users.us_officer_activate_week') }}</option>
                                        <option value="3">{{ __('admin/users.us_officer_activate_quarter') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    @if ($officer['active'])
                                        <span class="badge badge-success"><i class="fas fa-check mr-1"></i>{{ $officer['status_text'] }}</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $officer['status_text'] }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach

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
