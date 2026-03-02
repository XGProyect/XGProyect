@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>

    <x-admin.page-header title="{{ __('admin/alliances.al_title') }}" />

    @include('admin.partials.alliances_nav', ['active' => 'members'])

    <div class="row">
        <div class="col-lg-12">
            <x-admin.card
                    title="{{ __('admin/alliances.al_alliance_members', ['alliance' => $alliance->alliance_name]) }}"
                    icon="fas fa-users"
                    :flush="true"
                    badge="{{ count($members) }}"
                >

                    @php $isLastMember = count($members) === 1; @endphp

                    @if (empty($members))
                        <x-admin.empty-state icon="fas fa-users" message="{{ __('admin/alliances.al_no_members') }}" size="sm" />
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="pl-4" style="width: 36px;"></th>
                                        <th>{{ __('admin/alliances.al_alliance_username') }}</th>
                                        <th style="width: 22%;">{{ __('admin/alliances.al_alliance_member_rank') }}</th>
                                        <th class="text-center" style="width: 14%;">{{ __('admin/alliances.al_alliance_pending_request') }}</th>
                                        <th style="width: 16%;">{{ __('admin/alliances.al_inscription_date') }}</th>
                                        <th>{{ __('admin/alliances.al_alliance_request_text') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($members as $member)
                                        <tr>
                                            <td class="pl-4 align-middle">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input check-item"
                                                        id="mem_{{ $member['id'] }}"
                                                        form="removeForm"
                                                        name="delete_message[{{ $member['id'] }}]" value="on"
                                                        {{ $isLastMember ? 'disabled' : '' }}>
                                                    <label class="custom-control-label" for="mem_{{ $member['id'] }}"></label>
                                                </div>
                                            </td>
                                            <td class="align-middle font-weight-bold text-gray-700">
                                                <i class="fas fa-user fa-xs mr-1 text-gray-400"></i>
                                                {{ $member['name'] }}
                                                @if ($isLastMember)
                                                    <small class="text-muted font-weight-normal d-block">
                                                        <i class="fas fa-lock fa-xs mr-1"></i>{{ __('admin/alliances.al_cant_delete_last_one') }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td class="align-middle">
                                                <select name="member_rank[{{ $member['id'] }}]" form="rankForm"
                                                    class="form-control form-control-sm bg-light border-0">
                                                    @foreach ($rank_options as $option)
                                                        <option value="{{ $option['id'] }}" {{ $member['rank_id'] === $option['id'] ? 'selected' : '' }}>
                                                            {{ $option['name'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="align-middle text-center">
                                                @if ($member['pending_request'])
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-clock fa-xs mr-1"></i>{{ __('admin/alliances.al_request_pending') }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="align-middle small text-gray-600">
                                                <i class="fas fa-calendar-alt fa-xs mr-1 text-gray-400"></i>
                                                {{ $member['register_time'] }}
                                            </td>
                                            <td class="align-middle small text-gray-600 font-italic">
                                                {{ $member['request_text'] !== '-' ? $member['request_text'] : '' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between mt-3 px-3 pb-3">
                        <a href="{{ route('admin.alliances') }}" class="btn btn-secondary btn-icon-split">
                            <span class="icon text-white-50"><i class="fas fa-arrow-left"></i></span>
                            <span class="text">{{ __('admin/alliances.al_back') }}</span>
                        </a>
                        <div>
                            <button type="submit" form="rankForm" class="btn btn-primary btn-icon-split mr-2">
                                <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                                <span class="text">{{ __('admin/alliances.al_save_members') }}</span>
                            </button>
                            <button type="submit" form="removeForm" class="btn btn-danger btn-icon-split">
                                <span class="icon text-white-50"><i class="fas fa-trash-alt"></i></span>
                                <span class="text">{{ __('admin/alliances.al_delete_members') }}</span>
                            </button>
                        </div>
                    </div>

                    {{-- Hidden forms --}}
                    <form id="rankForm" method="POST" action="{{ route('admin.alliances.members.update-ranks', $alliance->alliance_id) }}" style="display:none;">
                        @csrf
                        @method('PUT')
                    </form>
                    <form id="removeForm" method="POST" action="{{ route('admin.alliances.members.remove', $alliance->alliance_id) }}" style="display:none;">
                        @csrf
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
            var row = this.closest('tr');
            if (row) {
                row.classList.toggle('table-danger', this.checked);
            }
        });
    });
</script>
@endpush
