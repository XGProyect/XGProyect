@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <form action="" method="POST">
        @csrf
        <x-admin.page-header
            title="{{ __('admin/mailing.ma_title') }}"
            subtitle="{{ __('admin/mailing.ma_sub_title') }}"
        >
            <x-slot name="action">
                <button type="submit" class="btn btn-primary btn-icon-split">
                    <span class="icon text-white-50">
                        <i class="fas fa-save"></i>
                    </span>
                    <span class="text">{{ __('admin/mailing.ma_save_changes') }}</span>
                </button>
            </x-slot>
        </x-admin.page-header>

        <div class="row">
            <div class="col-lg-6">
                <x-admin.card-collapsible id="collapseGeneral" title="{{ __('admin/mailing.ma_general') }}">
                            <x-admin.settings-table>
                                        <tr>
                                            <td>
                                                <span>
                                                    {{ __('admin/mailing.ma_mailing_protocol') }}
                                                </span>
                                            </td>
                                            <td>
                                                <select class="form-control"  name="mailing_protocol">
                                                    @foreach ($protocol_options as $item)
                                                    <option value="{{ $item['value'] }}"{{ $item['selected'] }}>{{ $item['option'] }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                    </x-admin.settings-table>
                        </x-admin.card-collapsible>
            </div>
            <div class="col-lg-6">
                <x-admin.card-collapsible id="collapseOtherParameters" title="{{ __('admin/mailing.ma_smtp_title') }}">
                            <x-admin.settings-table>
                                        <tr>
                                            <td colspan="2">{{ __('admin/mailing.ma_smtp_warning') }}</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span>
                                                    {{ __('admin/mailing.ma_smtp_host') }}
                                                </span>
                                            </td>
                                            <td>
                                                <input class="form-control" type="text" name="mailing_smtp_host"
                                                    value="{{ $mailing_smtp_host }}">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span>
                                                    {{ __('admin/mailing.ma_smtp_user') }}
                                                </span>
                                            </td>
                                            <td>
                                                <input class="form-control" type="text"
                                                    name="mailing_smtp_user"
                                                    value="{{ $mailing_smtp_user }}">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span>
                                                    {{ __('admin/mailing.ma_smtp_pass') }}
                                                </span>
                                            </td>
                                            <td>
                                                <input class="form-control" type="text"
                                                    name="mailing_smtp_pass"
                                                    value="{{ $mailing_smtp_pass }}">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span>
                                                    {{ __('admin/mailing.ma_smtp_port') }}
                                                </span>
                                            </td>
                                            <td>
                                                <input class="form-control" type="number" name="mailing_smtp_port"
                                                    value="{{ $mailing_smtp_port }}">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span>
                                                    {{ __('admin/mailing.ma_smtp_timeout') }}
                                                </span>
                                            </td>
                                            <td>
                                                <input class="form-control" type="number"
                                                    name="mailing_smtp_timeout"
                                                    value="{{ $mailing_smtp_timeout }}">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span>
                                                    {{ __('admin/mailing.ma_smtp_crypto') }}
                                                </span>
                                            </td>
                                            <td>
                                                <select class="form-control" name="mailing_smtp_crypto">
                                                    @foreach ($smtp_crypto_options as $item)
                                                    <option value="{{ $item['value'] }}"{{ $item['selected'] }}>{{ $item['option'] }}</option>
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