@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('admin/users.us_title') }}</h1>
    </div>

    @include('admin.partials.users_nav', ['active' => 'research'])

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-flask mr-1"></i>
                        {{ __('admin/users.us_research_title', ['user' => $user->name]) }}
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.research.update', $user->id) }}">
                        @csrf

                        <div class="row">
                            @foreach ($technologies as $tech)
                                <div class="col-md-4 col-lg-3 mb-3">
                                    <label class="small font-weight-bold text-gray-700" for="{{ $tech['field'] }}">
                                        {{ $tech['label'] }}
                                    </label>
                                    <input type="number" id="{{ $tech['field'] }}" name="{{ $tech['field'] }}"
                                        class="form-control"
                                        min="0" value="{{ old($tech['field'], $tech['level']) }}">
                                </div>
                            @endforeach
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
