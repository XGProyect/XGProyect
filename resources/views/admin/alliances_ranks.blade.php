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
    <x-admin.page-header title="{{ __('admin/alliances.al_title') }}" />

    @include('admin.partials.alliances_nav', ['active' => 'ranks'])

    <div class="row">
        <div class="col-lg-12">
            <x-admin.card
                title="{{ __('admin/alliances.al_alliance_ranks', ['alliance' => $alliance->alliance_name]) }}"
                icon="fas fa-sitemap"
                :badge="count($ranks)"
            >
                <form name="save_ranks" method="POST" action="{{ route('admin.alliances.ranks.update', $alliance->alliance_id) }}">
                        @csrf

                        {{-- Rank cards --}}
                        @forelse ($ranks as $rank)
                            <input type="hidden" name="id[]" value="{{ $rank['i'] }}">
                            <div class="card border mb-3">
                                <div class="card-header py-2 bg-light">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: 0.5rem;">
                                        <div class="d-flex align-items-center" style="min-width: 0; flex: 1 1 200px; max-width: 300px;">
                                            <i class="fas fa-shield-alt fa-sm mr-2 text-gray-400 flex-shrink-0"></i>
                                            <input type="text" name="rank_name_{{ $rank['i'] }}"
                                                value="{{ $rank['name'] }}"
                                                class="form-control form-control-sm bg-white border-0 font-weight-bold text-gray-700">
                                        </div>
                                        @if ($rank['i'] >= 2)
                                            <div class="custom-control custom-checkbox flex-shrink-0 mb-0">
                                                <input type="checkbox" class="custom-control-input check-item"
                                                    id="del_{{ $rank['i'] }}"
                                                    name="delete_message[]" value="{{ $rank['i'] }}">
                                                <label class="custom-control-label text-danger" for="del_{{ $rank['i'] }}">
                                                    {{ __('admin/alliances.al_delete_ranks') }}
                                                </label>
                                            </div>
                                        @else
                                            <span class="badge badge-secondary flex-shrink-0">
                                                <i class="fas fa-lock fa-xs mr-1"></i>{{ __('admin/alliances.al_rank_protected') }}
                                            </span>
                                        @endif
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
                            <x-admin.empty-state icon="fas fa-sitemap" message="{{ __('admin/alliances.al_no_ranks') }}" size="sm" />
                        @endforelse

                        <div class="d-flex flex-wrap justify-content-between mt-3" style="gap: 0.5rem;">
                            <a href="{{ route('admin.alliances') }}" class="btn btn-secondary btn-icon-split">
                                <span class="icon text-white-50"><i class="fas fa-arrow-left"></i></span>
                                <span class="text">{{ __('admin/alliances.al_back') }}</span>
                            </a>
                            <div class="d-flex flex-wrap" style="gap: 0.5rem;">
                                <button type="submit" name="save_ranks" class="btn btn-primary btn-icon-split">
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
            </x-admin.card>
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
