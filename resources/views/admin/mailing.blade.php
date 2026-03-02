@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <form action="{{ route('admin.mailing.update') }}" method="POST">
        @csrf
        <x-admin.page-header
            :title="__('admin/mailing.ma_title')"
            sub:title="__('admin/mailing.ma_sub_title')"
        >
            <x-slot name="action">
                <button type="submit" class="btn btn-primary btn-icon-split">
                    <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                    <span class="text">{{ __('admin/mailing.ma_save_changes') }}</span>
                </button>
            </x-slot>
        </x-admin.page-header>

        <div class="row">
            <div class="col-lg-6">
                <x-admin.card-collapsible id="collapseProtocol" :title="__('admin/mailing.ma_general')" icon="fas fa-envelope">
                    <x-admin.settings-table>
                        <tr>
                            <td>{{ __('admin/mailing.ma_mailing_protocol') }}</td>
                            <td>
                                <select class="form-control" name="mailing_protocol">
                                    @foreach ($protocol_options as $item)
                                        <option value="{{ $item['value'] }}" {{ $item['selected'] ? 'selected' : '' }}>
                                            {{ strtoupper($item['label']) }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    </x-admin.settings-table>
                    <p class="small text-gray-500 px-3 pb-2">{{ __('admin/mailing.ma_smtp_warning') }}</p>
                </x-admin.card-collapsible>
            </div>

            <div class="col-lg-6">
                <x-admin.card-collapsible id="collapseSmtp" :title="__('admin/mailing.ma_smtp_title')" icon="fas fa-server">
                    <x-admin.settings-table>
                        <tr>
                            <td>{{ __('admin/mailing.ma_smtp_host') }}</td>
                            <td>
                                <input class="form-control" type="text" name="mailing_smtp_host"
                                    value="{{ $mailing_smtp_host ?? '' }}" placeholder="smtp.example.com">
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __('admin/mailing.ma_smtp_user') }}</td>
                            <td>
                                <input class="form-control" type="text" name="mailing_smtp_user"
                                    value="{{ $mailing_smtp_user ?? '' }}" autocomplete="off">
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __('admin/mailing.ma_smtp_pass') }}</td>
                            <td>
                                <input class="form-control" type="password" name="mailing_smtp_pass"
                                    value="{{ $mailing_smtp_pass ?? '' }}" autocomplete="new-password">
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __('admin/mailing.ma_smtp_port') }}</td>
                            <td>
                                <input class="form-control" type="number" name="mailing_smtp_port"
                                    value="{{ $mailing_smtp_port ?? 25 }}" min="1" max="65535">
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __('admin/mailing.ma_smtp_timeout') }}</td>
                            <td>
                                <input class="form-control" type="number" name="mailing_smtp_timeout"
                                    value="{{ $mailing_smtp_timeout ?? 5 }}" min="1">
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __('admin/mailing.ma_smtp_crypto') }}</td>
                            <td>
                                <select class="form-control" name="mailing_smtp_crypto">
                                    @foreach ($smtp_crypto_options as $item)
                                        <option value="{{ $item['value'] }}" {{ $item['selected'] ? 'selected' : '' }}>
                                            {{ $item['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    </x-admin.settings-table>
                </x-admin.card-collapsible>
            </div>
        </div>
    </form>
</div>
@endsection
