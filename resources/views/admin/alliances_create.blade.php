@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <x-admin.page-header
        title="{{ __('admin/alliances.al_title') }}"
        subtitle="{{ __('admin/alliances.al_sub_title') }}"
    />

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <x-admin.card title="{{ __('admin/alliances.al_create_title') }}" icon="fas fa-shield-alt">
                <form action="{{ route('admin.alliances.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">{{ __('admin/alliances.al_create_name') }}</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tag">{{ __('admin/alliances.al_create_tag') }}</label>
                                    <input type="text" class="form-control" id="tag" name="tag" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="founder">{{ __('admin/alliances.al_create_founder') }}</label>
                            <select class="form-control" id="founder" name="founder" required>
                                <option value="0">-</option>
                                @foreach ($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.alliances') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i>{{ __('admin/alliances.al_back') }}
                            </a>
                            <button type="submit" class="btn btn-primary btn-icon-split">
                                <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                                <span class="text">{{ __('admin/alliances.al_create_add') }}</span>
                            </button>
                        </div>
                    </form>
            </x-admin.card>
        </div>
    </div>
</div>
@endsection
