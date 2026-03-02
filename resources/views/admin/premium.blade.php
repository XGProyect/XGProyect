@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <form action="" method="POST">
        @csrf
        <x-admin.page-header
            title="{{ __('admin/premium.pr_title') }}"
            subtitle="{{ __('admin/premium.pr_sub_title') }}"
        >
            <x-slot name="action">
                <button type="submit" class="btn btn-primary btn-icon-split">
                    <span class="icon text-white-50">
                        <i class="fas fa-save"></i>
                    </span>
                    <span class="text">{{ __('admin/premium.pr_save_changes') }}</span>
                </button>
            </x-slot>
        </x-admin.page-header>

        <div class="row">
            <div class="col-lg-6">
                <x-admin.card-collapsible id="collapseGeneral" title="{{ __('admin/premium.pr_general') }}">
                            <div class="table-responsive">
                                <table class="table table-borderless" width="100%" cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <span>
                                                    {{ __('admin/premium.pr_pay_url') }}
                                                </span>
                                            </td>
                                            <td>
                                                <textarea class="form-control" name="premium_url"
                                                    rows="5" placeholder="{{ $premium_url }}">{{ $premium_url }}</textarea>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span>
                                                    {{ __('admin/premium.pr_registration_dark_matter') }}
                                                </span>
                                            </td>
                                            <td>
                                                <input class="form-control" type="number" name="registration_dark_matter" min="0"
                                                    value="{{ $registration_dark_matter }}">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                    </x-admin.card-collapsible>
            </div>
            <div class="col-lg-6">
                <x-admin.card-collapsible id="collapseOtherParameters" title="{{ __('admin/premium.pr_trader') }}">
                            <div class="table-responsive">
                                <table class="table table-borderless" width="100%" cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <span>
                                                    {{ __('admin/premium.pr_trader_price') }}
                                                </span>
                                            </td>
                                            <td>
                                                <input class="form-control" type="number" name="merchant_price"
                                                    value="{{ $merchant_price }}">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span>
                                                    {{ __('admin/premium.pr_merchant_base_min_exchange_rate') }}
                                                    <i class="fas fa-question-circle" data-toggle="popover"
                                                        data-trigger="hover" data-content="{{ __('admin/premium.pr_merchant_explanation') }}"
                                                        data-html="true"></i>
                                                </span>
                                            </td>
                                            <td>
                                                <input class="form-control" type="text"
                                                    name="merchant_base_min_exchange_rate"
                                                    value="{{ $merchant_base_min_exchange_rate }}">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span>
                                                    {{ __('admin/premium.pr_merchant_base_max_exchange_rate') }}
                                                    <i class="fas fa-question-circle" data-toggle="popover"
                                                        data-trigger="hover" data-content="{{ __('admin/premium.pr_merchant_explanation') }}"
                                                        data-html="true"></i>
                                                </span>
                                            </td>
                                            <td>
                                                <input class="form-control" type="text"
                                                    name="merchant_base_max_exchange_rate"
                                                    value="{{ $merchant_base_max_exchange_rate }}">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span>
                                                    {{ __('admin/premium.pr_merchant_metal_multiplier') }}
                                                    <i class="fas fa-question-circle" data-toggle="popover"
                                                        data-trigger="hover" data-content="{{ __('admin/premium.pr_merchant_explanation') }}"
                                                        data-html="true"></i>
                                                </span>
                                            </td>
                                            <td>
                                                <input class="form-control" type="text" name="merchant_metal_multiplier"
                                                    value="{{ $merchant_metal_multiplier }}">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span>
                                                    {{ __('admin/premium.pr_merchant_crystal_multiplier') }}
                                                    <i class="fas fa-question-circle" data-toggle="popover"
                                                        data-trigger="hover" data-content="{{ __('admin/premium.pr_merchant_explanation') }}"
                                                        data-html="true"></i>
                                                </span>
                                            </td>
                                            <td>
                                                <input class="form-control" type="text"
                                                    name="merchant_crystal_multiplier"
                                                    value="{{ $merchant_crystal_multiplier }}">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span>
                                                    {{ __('admin/premium.pr_merchant_deuterium_multiplier') }}
                                                    <i class="fas fa-question-circle" data-toggle="popover"
                                                        data-trigger="hover" data-content="{{ __('admin/premium.pr_merchant_explanation') }}"
                                                        data-html="true"></i>
                                                </span>
                                            </td>
                                            <td>
                                                <input class="form-control" type="text"
                                                    name="merchant_deuterium_multiplier"
                                                    value="{{ $merchant_deuterium_multiplier }}">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                    </x-admin.card-collapsible>
            </div>
        </div>
    </form>
</div>
@endsection