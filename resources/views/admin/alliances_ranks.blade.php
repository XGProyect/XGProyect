@extends('master.admin')

@section('content')

@php
$permissions = [
    'r1' => ['key' => 'delete',         'label_key' => 'al_rank_delete_alliance'],
    'r2' => ['key' => 'kick',           'label_key' => 'al_rank_kick_members'],
    'r3' => ['key' => 'applications',   'label_key' => 'al_rank_see_requests'],
    'r4' => ['key' => 'memberlist',     'label_key' => 'al_rank_see_memberslist'],
    'r5' => ['key' => 'app_management', 'label_key' => 'al_rank_check_requests'],
    'r6' => ['key' => 'administration', 'label_key' => 'al_rank_manage_alliance'],
    'r7' => ['key' => 'online_status',  'label_key' => 'al_rank_see_online_members'],
    'r8' => ['key' => 'send_circular',  'label_key' => 'al_rank_create_circular'],
    'r9' => ['key' => 'right_hand',     'label_key' => 'al_rank_right_hand'],
];
@endphp

<div class="container-fluid">
    <x-alert/>

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('admin/alliances.al_title') }}</h1>
    </div>

    @include('admin.partials.alliances_nav', ['active' => 'ranks'])

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-sitemap mr-1"></i>
                        {{ __('admin/alliances.al_alliance_ranks', ['alliance' => $alliance->alliance_name]) }}
                        <span class="badge badge-secondary ml-1">{{ count($ranks) }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    <form name="save_ranks" method="POST" action="{{ route('admin.alliances.ranks.update', $alliance->alliance_id) }}">
                        @csrf

                        {{-- Rank cards --}}
                        @forelse ($ranks as $rank)
                            <input type="hidden" name="id[]" value="{{ $rank['i'] }}">
                            <div class="card border mb-3">
                                <div class="card-header py-2 d-flex align-items-center justify-content-between bg-light">
                                    <span class="font-weight-bold text-gray-700">
                                        <i class="fas fa-shield-alt fa-sm mr-1 text-gray-400"></i>
                                        {{ $rank['name'] }}
                                    </span>
                                    <div class="custom-control custom-checkbox mb-0 d-flex align-items-center">
                                        <input type="checkbox" class="custom-control-input check-item"
                                            id="del_{{ $rank['i'] }}"
                                            name="delete_message[{{ $rank['i'] }}]" value="on">
                                        <label class="custom-control-label text-danger small" for="del_{{ $rank['i'] }}">
                                            {{ __('admin/alliances.al_delete_ranks') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="card-body py-3">
                                    <div class="row">
                                        @foreach ($permissions as $rNum => $perm)
                                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-2">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input"
                                                        id="u{{ $rank['i'] }}{{ $rNum }}"
                                                        name="u{{ $rank['i'] }}{{ $rNum }}"
                                                        @checked($rank[$perm['key']])>
                                                    <label class="custom-control-label small" for="u{{ $rank['i'] }}{{ $rNum }}">
                                                        <img src="{{ asset('assets/upload/skins/xgproyect/img/' . $rNum . '.png') }}"
                                                            alt="" class="mr-1">
                                                        {{ __('admin/alliances.' . $perm['label_key']) }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-muted py-3">
                                <i class="fas fa-info-circle mr-1"></i>{{ __('admin/alliances.al_no_ranks') }}
                            </p>
                        @endforelse

                        <div class="d-flex justify-content-between mt-3">
                            <a href="{{ route('admin.alliances') }}" class="btn btn-secondary btn-icon-split">
                                <span class="icon text-white-50"><i class="fas fa-arrow-left"></i></span>
                                <span class="text">{{ __('admin/alliances.al_back') }}</span>
                            </a>
                            <div>
                                <button type="submit" name="save_ranks" class="btn btn-primary btn-icon-split mr-2">
                                    <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                                    <span class="text">{{ __('admin/alliances.al_save_ranks') }}</span>
                                </button>
                                <button type="submit" name="delete_ranks" class="btn btn-danger btn-icon-split">
                                    <span class="icon text-white-50"><i class="fas fa-trash-alt"></i></span>
                                    <span class="text">{{ __('admin/alliances.al_delete_ranks') }}</span>
                                </button>
                            </div>
                        </div>

                        <hr>

                        {{-- Create a new rank --}}
                        <h6 class="font-weight-bold text-gray-700 mb-3">
                            <i class="fas fa-plus-circle fa-sm mr-1 text-success"></i>
                            {{ __('admin/alliances.al_create_ranks') }}
                        </h6>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right" for="rank_name">
                                {{ __('admin/alliances.al_rank_name') }}
                            </label>
                            <div class="col-md-5">
                                <input type="text" id="rank_name" name="rank_name"
                                    class="form-control @error('rank_name') is-invalid @enderror">
                                @error('rank_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mt-2 mt-md-0">
                                <button type="submit" name="create_rank" value="1" class="btn btn-success btn-icon-split">
                                    <span class="icon text-white-50"><i class="fas fa-plus"></i></span>
                                    <span class="text">{{ __('admin/alliances.al_create_ranks') }}</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.check-item').forEach(function (cb) {
        cb.addEventListener('change', function () {
            var card = this.closest('.card.border');
            if (card) {
                card.classList.toggle('border-danger', this.checked);
            }
        });
    });
</script>
@endpush
