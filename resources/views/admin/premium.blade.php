@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <form action="{{ route('admin.premium.update') }}" method="POST">
        @csrf
        <x-admin.page-header
            :title="__('admin/premium.pr_title')"
            :subtitle="__('admin/premium.pr_sub_title')"
        >
            <x-slot name="action">
                <button type="submit" class="btn btn-primary btn-icon-split">
                    <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                    <span class="text">{{ __('admin/premium.pr_save_changes') }}</span>
                </button>
            </x-slot>
        </x-admin.page-header>

        {{-- Row 1: General (Dark Matter) | Resource Market --}}
        <div class="row">
            <div class="col-lg-6">
                <x-admin.card-collapsible id="collapseGeneral" :title="__('admin/premium.pr_general')" icon="fas fa-gem">
                    <x-admin.settings-table>
                        <tr>
                            <td>{{ __('admin/premium.pr_registration_dark_matter') }}</td>
                            <td>
                                <input class="form-control" type="number" name="registration_dark_matter"
                                    value="{{ $registration_dark_matter }}" min="0">
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __('admin/premium.pr_pay_url') }}</td>
                            <td>
                                <input class="form-control" type="url" name="premium_url"
                                    value="{{ $premium_url ?? '' }}" placeholder="https://...">
                            </td>
                        </tr>
                    </x-admin.settings-table>
                </x-admin.card-collapsible>
            </div>

            <div class="col-lg-6">
                <x-admin.card-collapsible id="collapseTrader" :title="__('admin/premium.pr_trader')" icon="fas fa-exchange-alt">
                    <x-admin.settings-table>
                        <tr>
                            <td>{{ __('admin/premium.pr_trader_price') }}</td>
                            <td>
                                <input class="form-control" type="number" name="merchant_price"
                                    value="{{ $merchant_price }}" min="0" step="1">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                {{ __('admin/premium.pr_merchant_base_min_exchange_rate') }}
                                <i class="fas fa-question-circle text-gray-400" data-toggle="popover"
                                    data-trigger="hover" data-content="{{ __('admin/premium.pr_merchant_explanation') }}"
                                    data-html="true"></i>
                            </td>
                            <td>
                                <input class="form-control" type="text" name="merchant_base_min_exchange_rate"
                                    value="{{ $merchant_base_min_exchange_rate }}">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                {{ __('admin/premium.pr_merchant_base_max_exchange_rate') }}
                                <i class="fas fa-question-circle text-gray-400" data-toggle="popover"
                                    data-trigger="hover" data-content="{{ __('admin/premium.pr_merchant_explanation') }}"
                                    data-html="true"></i>
                            </td>
                            <td>
                                <input class="form-control" type="text" name="merchant_base_max_exchange_rate"
                                    value="{{ $merchant_base_max_exchange_rate }}">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="text-warning"><i class="fas fa-circle fa-xs mr-1"></i></span>
                                {{ __('admin/premium.pr_merchant_metal_multiplier') }}
                            </td>
                            <td>
                                <input class="form-control" type="text" name="merchant_metal_multiplier"
                                    value="{{ $merchant_metal_multiplier }}">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="text-info"><i class="fas fa-circle fa-xs mr-1"></i></span>
                                {{ __('admin/premium.pr_merchant_crystal_multiplier') }}
                            </td>
                            <td>
                                <input class="form-control" type="text" name="merchant_crystal_multiplier"
                                    value="{{ $merchant_crystal_multiplier }}">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="text-primary"><i class="fas fa-circle fa-xs mr-1"></i></span>
                                {{ __('admin/premium.pr_merchant_deuterium_multiplier') }}
                            </td>
                            <td>
                                <input class="form-control" type="text" name="merchant_deuterium_multiplier"
                                    value="{{ $merchant_deuterium_multiplier }}">
                            </td>
                        </tr>
                    </x-admin.settings-table>
                </x-admin.card-collapsible>
            </div>
        </div>
    </form>
</div>
@endsection