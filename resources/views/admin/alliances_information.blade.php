@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('admin/alliances.al_title') }}</h1>
    </div>

    @include('admin.partials.alliances_nav', ['active' => 'info'])

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle mr-1"></i>
                        {{ __('admin/alliances.al_alliance_information', ['alliance' => $alliance->alliance_name]) }}
                    </h6>
                </div>
                <div class="card-body">
                    <form name="save_info" method="POST" action="{{ route('admin.alliances.info.update', $alliance->alliance_id) }}">
                        @csrf

                        {{-- Register time (read-only) --}}
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right">
                                {{ __('admin/alliances.al_alliance_information_register_time') }}
                            </label>
                            <div class="col-md-8">
                                <p class="form-control-plaintext">{{ $register_time }}</p>
                            </div>
                        </div>

                        {{-- Alliance name --}}
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right" for="alliance_name">
                                {{ __('admin/alliances.al_alliance_information_name') }}
                            </label>
                            <div class="col-md-8">
                                <input type="text" id="alliance_name" name="alliance_name"
                                    class="form-control @error('alliance_name') is-invalid @enderror"
                                    value="{{ old('alliance_name', $alliance->alliance_name) }}">
                                @error('alliance_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Alliance tag --}}
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right" for="alliance_tag">
                                {{ __('admin/alliances.al_alliance_information_tag') }}
                            </label>
                            <div class="col-md-8">
                                <input type="text" id="alliance_tag" name="alliance_tag"
                                    class="form-control @error('alliance_tag') is-invalid @enderror"
                                    value="{{ old('alliance_tag', $alliance->alliance_tag) }}">
                                @error('alliance_tag')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Owner --}}
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right" for="alliance_owner">
                                {{ __('admin/alliances.al_alliance_information_owner') }}
                            </label>
                            <div class="col-md-8">
                                <select id="alliance_owner" name="alliance_owner"
                                    class="form-control @error('alliance_owner') is-invalid @enderror">
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}"
                                            @selected(old('alliance_owner', $alliance->alliance_owner) == $user->id)>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('alliance_owner')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Website --}}
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right" for="alliance_web">
                                {{ __('admin/alliances.al_alliance_information_web') }}
                            </label>
                            <div class="col-md-8">
                                <input type="text" id="alliance_web" name="alliance_web" class="form-control"
                                    value="{{ old('alliance_web', $alliance->alliance_web) }}">
                            </div>
                        </div>

                        {{-- Image --}}
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right" for="alliance_image">
                                {{ __('admin/alliances.al_alliance_information_image') }}
                            </label>
                            <div class="col-md-8">
                                <input type="text" id="alliance_image" name="alliance_image" class="form-control"
                                    value="{{ old('alliance_image', $alliance->alliance_image) }}">
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right" for="alliance_description">
                                {{ __('admin/alliances.al_alliance_information_description') }}
                            </label>
                            <div class="col-md-8">
                                <textarea id="alliance_description" name="alliance_description"
                                    class="form-control" rows="6">{{ old('alliance_description', $alliance->alliance_description) }}</textarea>
                            </div>
                        </div>

                        {{-- Alliance text --}}
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right" for="alliance_text">
                                {{ __('admin/alliances.al_alliance_information_text') }}
                            </label>
                            <div class="col-md-8">
                                <textarea id="alliance_text" name="alliance_text"
                                    class="form-control" rows="6">{{ old('alliance_text', $alliance->alliance_text) }}</textarea>
                            </div>
                        </div>

                        {{-- Request text --}}
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right" for="alliance_request">
                                {{ __('admin/alliances.al_alliance_information_request') }}
                            </label>
                            <div class="col-md-8">
                                <textarea id="alliance_request" name="alliance_request"
                                    class="form-control" rows="6">{{ old('alliance_request', $alliance->alliance_request) }}</textarea>
                            </div>
                        </div>

                        {{-- Accept requests toggle --}}
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right">
                                {{ __('admin/alliances.al_alliance_information_request_notallow') }}
                            </label>
                            <div class="col-md-8 d-flex align-items-center">
                                <div class="custom-control custom-switch">
                                    <input type="hidden" name="alliance_request_notallow" value="0">
                                    <input type="checkbox" class="custom-control-input" id="alliance_request_notallow"
                                        name="alliance_request_notallow" value="1"
                                        @checked(old('alliance_request_notallow', $alliance->alliance_request_notallow) == 1)>
                                    <label class="custom-control-label" for="alliance_request_notallow">
                                        {{ __('admin/alliances.al_allow_yes') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.alliances') }}" class="btn btn-secondary btn-icon-split">
                                <span class="icon text-white-50"><i class="fas fa-arrow-left"></i></span>
                                <span class="text">{{ __('admin/alliances.al_back') }}</span>
                            </a>
                            <button type="submit" class="btn btn-primary btn-icon-split">
                                <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                                <span class="text">{{ __('admin/alliances.al_send_data') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
