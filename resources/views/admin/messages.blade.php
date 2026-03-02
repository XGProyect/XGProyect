@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>

    <x-admin.page-header
        title="{{ __('admin/messages.mg_title') }}"
        subtitle="{{ __('admin/messages.mg_sub_title') }}"
    />

    <div class="row">
        <div class="col-lg-12">

            {{-- Filter card --}}
            <x-admin.card title="{{ __('admin/messages.mg_filter_by') }}" icon="fas fa-search">
                <x-slot name="action">
                    @if ($hasSearch)
                        <a href="{{ route('admin.messages') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-times fa-xs mr-1"></i>{{ __('admin/messages.mg_filter_clear') }}
                        </a>
                    @endif
                </x-slot>
                    <form action="{{ route('admin.messages') }}" method="GET">
                        <div class="form-row">
                            <div class="col-md-3 mb-3">
                                <input type="text" name="message_sender" class="form-control bg-light border-0"
                                    placeholder="{{ __('admin/messages.mg_filter_sender') }}"
                                    value="{{ $search['message_sender'] }}" autocomplete="off">
                            </div>
                            <div class="col-md-3 mb-3">
                                <input type="text" name="message_receiver" class="form-control bg-light border-0"
                                    placeholder="{{ __('admin/messages.mg_filter_receiver') }}"
                                    value="{{ $search['message_receiver'] }}" autocomplete="off">
                            </div>
                            <div class="col-md-3 mb-3">
                                <input type="text" name="message_subject" class="form-control bg-light border-0"
                                    placeholder="{{ __('admin/messages.mg_filter_subject') }}"
                                    value="{{ $search['message_subject'] }}" autocomplete="off">
                            </div>
                            <div class="col-md-3 mb-3">
                                <input type="date" name="message_date" class="form-control bg-light border-0"
                                    value="{{ $search['message_date'] }}" min="1000-01-01" max="3000-12-31">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-3 mb-3">
                                <select name="message_type" class="form-control bg-light border-0">
                                    <option value="">{{ __('admin/messages.mg_filter_type') }}</option>
                                    @foreach ($type_options as $item)
                                        <option value="{{ $item['value'] }}" {{ $search['message_type'] !== '' && (int) $search['message_type'] === $item['value'] ? 'selected' : '' }}>
                                            {{ $item['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <input type="text" name="message_text" class="form-control bg-light border-0"
                                    placeholder="{{ __('admin/messages.mg_filter_content') }}"
                                    value="{{ $search['message_text'] }}" autocomplete="off">
                            </div>
                            <div class="col-md-3 mb-3">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-search fa-sm mr-1"></i>{{ __('admin/messages.mg_filter_start_search') }}
                                </button>
                            </div>
                        </div>
                    </form>
            </x-admin.card>

            {{-- Results card --}}
            @if ($hasSearch && !empty($results))
                <form id="batchDeleteForm" action="{{ route('admin.messages.destroy-batch') }}" method="POST"
                    data-confirm="{{ __('admin/messages.mg_delete_confirm') }}">
                    @csrf
                    <x-admin.card
                        title="{{ __('admin/messages.mg_search_results') }}"
                        icon="fas fa-envelope"
                        :badge="count($results)"
                        :flush="true"
                    >
                        <x-slot name="action">
                            <button type="submit" class="btn btn-danger btn-sm btn-icon-split">
                                <span class="icon text-white-50">
                                    <i class="fas fa-trash-alt"></i>
                                </span>
                                <span class="text">{{ __('admin/messages.mg_delete_selected') }}</span>
                            </button>
                        </x-slot>
                        <div class="table-responsive">
                                <table class="table table-hover table-bordered mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="pl-4" style="width: 40px;">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="checkall">
                                                    <label class="custom-control-label" for="checkall"></label>
                                                </div>
                                            </th>
                                            <th>{{ __('admin/messages.mg_sender') }}</th>
                                            <th>{{ __('admin/messages.mg_receiver') }}</th>
                                            <th>{{ __('admin/messages.mg_time') }}</th>
                                            <th>{{ __('admin/messages.mg_type') }}</th>
                                            <th>{{ __('admin/messages.mg_from') }}</th>
                                            <th>{{ __('admin/messages.mg_subject') }}</th>
                                            <th>{{ __('admin/messages.mg_read') }}</th>
                                            <th style="width: 90px;">{{ __('admin/messages.mg_actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($results as $item)
                                            @php
                                                $isPrivate = $item['message_type_key'] === 4;
                                                $typeBadge = match($item['message_type_key']) {
                                                    0 => 'badge-info',
                                                    1 => 'badge-danger',
                                                    2 => 'badge-warning',
                                                    3 => 'badge-success',
                                                    4 => 'badge-primary',
                                                    default => 'badge-secondary',
                                                };
                                            @endphp
                                            @if ($isPrivate)
                                                <tr>
                                            @else
                                                <tr class="cursor-pointer" data-toggle="collapse"
                                                    data-target="#msg-{{ $item['message_id'] }}"
                                                    aria-expanded="false" aria-controls="msg-{{ $item['message_id'] }}">
                                            @endif
                                                <td class="pl-4 align-middle" onclick="event.stopPropagation()">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input msg-check"
                                                            id="msg_{{ $item['message_id'] }}"
                                                            name="delete_messages[{{ $item['message_id'] }}]" value="on">
                                                        <label class="custom-control-label" for="msg_{{ $item['message_id'] }}"></label>
                                                    </div>
                                                </td>
                                                <td class="align-middle font-weight-bold">{{ $item['sender'] }}</td>
                                                <td class="align-middle font-weight-bold">{{ $item['receiver'] }}</td>
                                                <td class="align-middle"><small class="text-muted">{{ $item['message_time'] }}</small></td>
                                                <td class="align-middle">
                                                    <span class="badge {{ $typeBadge }}">{{ $item['message_type'] }}</span>
                                                </td>
                                                <td class="align-middle text-muted small">{!! $item['message_from'] !!}</td>
                                                <td class="align-middle font-weight-bold">
                                                    @if ($isPrivate)
                                                        <i class="fas fa-lock fa-xs text-muted mr-1"></i>
                                                        <span class="text-muted font-weight-normal font-italic small">{{ __('admin/messages.mg_private_content') }}</span>
                                                    @else
                                                        {!! $item['message_subject'] !!}
                                                    @endif
                                                </td>
                                                <td class="align-middle">
                                                    @if ($item['message_read'])
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-envelope-open fa-xs mr-1"></i>{{ __('admin/messages.mg_read_yes') }}
                                                        </span>
                                                    @else
                                                        <span class="badge badge-secondary">
                                                            <i class="fas fa-envelope fa-xs mr-1"></i>{{ __('admin/messages.mg_read_no') }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="align-middle" onclick="event.stopPropagation()">
                                                    <div class="d-flex" style="gap: 0.25rem;">
                                                        @if (!$isPrivate)
                                                            <button type="button" class="btn btn-sm btn-primary"
                                                                title="{{ __('admin/messages.mg_the_message') }}"
                                                                onclick="event.stopPropagation(); $('#msg-{{ $item['message_id'] }}').collapse('toggle');">
                                                                <i class="fas fa-eye fa-sm"></i>
                                                            </button>
                                                        @endif
                                                        <button type="button" class="btn btn-sm btn-danger"
                                                            title="{{ __('admin/messages.mg_delete_this') }}"
                                                            data-confirm="{{ __('admin/messages.mg_delete_confirm') }}"
                                                            onclick="deleteMessage({{ $item['message_id'] }}, this)">
                                                            <i class="fas fa-trash-alt fa-sm"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            @if (!$isPrivate)
                                                <tr>
                                                    <td colspan="9" class="p-0 border-0">
                                                        <div class="collapse" id="msg-{{ $item['message_id'] }}">
                                                            <div class="card border-left-primary m-3">
                                                                <div class="card-header py-2">
                                                                    <small class="font-weight-bold text-primary">
                                                                        <i class="fas fa-envelope-open fa-xs mr-1"></i>
                                                                        {{ __('admin/messages.mg_the_message') }}
                                                                    </small>
                                                                </div>
                                                                <div class="card-body py-3">
                                                                    {!! $item['message_text'] !!}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </x-admin.card>
                </form>

                {{-- Hidden form for single message delete (avoids nested forms) --}}
                <form id="singleDeleteForm" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('checkall')?.addEventListener('change', function () {
        document.querySelectorAll('.msg-check').forEach(function (cb) {
            cb.checked = this.checked;
        }.bind(this));
    });

    function deleteMessage(id, el) {
        if (confirm(el.dataset.confirm)) {
            var form = document.getElementById('singleDeleteForm');
            form.action = '{{ url('/admin/messages') }}/' + id;
            form.submit();
        }
    }
</script>
@endpush
@endsection
