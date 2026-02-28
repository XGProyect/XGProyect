@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-1">
        <h1 class="h3 mb-0 text-gray-800">{{ __('admin/alliances.al_title') }}</h1>
    </div>

    @include('admin.partials.alliances_nav', ['active' => 'members'])

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-users mr-1"></i>
                        {{ __('admin/alliances.al_alliance_members', ['alliance' => $alliance->alliance_name]) }}
                        <span class="badge badge-secondary ml-1">{{ count($members) }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    <form name="save_members" method="POST" action="{{ route('admin.alliances.members.remove', $alliance->alliance_id) }}">
                        @csrf

                        {{-- Member list --}}
                        @php $isLastMember = count($members) === 1; @endphp
                        @forelse ($members as $member)
                            <div class="card border mb-2">
                                <div class="card-body py-2 px-3">
                                    <div class="row align-items-center">

                                        {{-- Checkbox + name --}}
                                        <div class="col-12 col-sm-4 col-md-3 d-flex align-items-center mb-2 mb-md-0">
                                            <div class="custom-control custom-checkbox mr-2 mb-0">
                                                <input type="checkbox" class="custom-control-input check-item"
                                                    id="mem_{{ $member['id'] }}"
                                                    name="delete_message[{{ $member['id'] }}]" value="on"
                                                    {{ $isLastMember ? 'disabled' : '' }}>
                                                <label class="custom-control-label" for="mem_{{ $member['id'] }}"></label>
                                            </div>
                                            <div>
                                                <span class="font-weight-bold text-gray-700">
                                                    <i class="fas fa-user fa-sm mr-1 text-gray-400"></i>
                                                    {{ $member['name'] }}
                                                </span>
                                                @if ($isLastMember)
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-lock fa-xs mr-1"></i>{{ __('admin/alliances.al_cant_delete_last_one') }}
                                                    </small>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Rank --}}
                                        <div class="col-6 col-sm-4 col-md-2">
                                            <span class="small text-muted d-block d-md-none">{{ __('admin/alliances.al_alliance_member_rank') }}</span>
                                            <span class="badge badge-info">{{ $member['rank'] ?: __('admin/alliances.al_rank_not_defined') }}</span>
                                        </div>

                                        {{-- Pending request --}}
                                        <div class="col-6 col-sm-4 col-md-2 text-center">
                                            <span class="small text-muted d-block d-md-none">{{ __('admin/alliances.al_alliance_pending_request') }}</span>
                                            @if ($member['pending_request'])
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-clock fa-sm mr-1"></i>{{ __('admin/alliances.al_request_yes') }}
                                                </span>
                                            @else
                                                <span class="badge badge-secondary">{{ __('admin/alliances.al_request_no') }}</span>
                                            @endif
                                        </div>

                                        {{-- Join date --}}
                                        <div class="col-6 col-md-2">
                                            <span class="small text-muted d-block d-md-none">{{ __('admin/alliances.al_inscription_date') }}</span>
                                            <span class="small text-gray-600">{{ $member['register_time'] }}</span>
                                        </div>

                                        {{-- Request text (only if present) --}}
                                        @if ($member['request_text'])
                                            <div class="col-12 col-md-3 mt-1 mt-md-0">
                                                <span class="small text-muted d-block d-md-none">{{ __('admin/alliances.al_alliance_request_text') }}</span>
                                                <span class="small text-gray-600 font-italic">{{ $member['request_text'] }}</span>
                                            </div>
                                        @else
                                            <div class="col-md-3"></div>
                                        @endif

                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-muted py-3">
                                <i class="fas fa-info-circle mr-1"></i>{{ __('admin/alliances.al_no_members') }}
                            </p>
                        @endforelse

                        <div class="d-flex justify-content-between mt-3">
                            <a href="{{ route('admin.alliances') }}" class="btn btn-secondary btn-icon-split">
                                <span class="icon text-white-50"><i class="fas fa-arrow-left"></i></span>
                                <span class="text">{{ __('admin/alliances.al_back') }}</span>
                            </a>
                            <button type="submit" class="btn btn-danger btn-icon-split">
                                <span class="icon text-white-50"><i class="fas fa-trash-alt"></i></span>
                                <span class="text">{{ __('admin/alliances.al_delete_members') }}</span>
                            </button>
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
