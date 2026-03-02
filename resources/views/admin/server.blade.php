@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <form action="{{ route('admin.server.update') }}" method="POST">
        @csrf
        <x-admin.page-header
            :title="__('admin/server.se_server_parameters')"
            sub:title="__('admin/server.se_sub_title')"
        >
            <x-slot name="action">
                <button type="submit" class="btn btn-primary btn-icon-split">
                    <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                    <span class="text">{{ __('admin/server.se_save_parameters') }}</span>
                </button>
            </x-slot>
        </x-admin.page-header>

        {{-- Row 1: Identity | Speed & Economy --}}
        <div class="row">
            <div class="col-lg-6">
                <x-admin.card-collapsible id="collapseIdentity" :title="__('admin/server.se_section_identity')" icon="fas fa-globe">
                    <x-admin.settings-table>
                        <tr>
                            <td>
                                {{ __('admin/server.se_name') }}
                                <i class="fas fa-question-circle text-gray-400" data-toggle="popover"
                                    data-trigger="hover" data-content="{{ __('admin/server.se_server_name') }}"
                                    data-html="true"></i>
                            </td>
                            <td>
                                <input class="form-control" type="text" name="game_name"
                                    value="{{ $game_name }}" maxlength="60">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                {{ __('admin/server.se_logo') }}
                                <i class="fas fa-question-circle text-gray-400" data-toggle="popover"
                                    data-trigger="hover" data-content="{{ __('admin/server.se_server_logo') }}"
                                    data-html="true"></i>
                            </td>
                            <td>
                                <input class="form-control" type="text" name="game_logo"
                                    value="{{ $game_logo }}" placeholder="https://...">
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __('admin/server.se_lang') }}</td>
                            <td>
                                <select class="form-control" name="language">
                                    {!! $language_settings !!}
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __('admin/server.se_admin_email') }}</td>
                            <td>
                                <input class="form-control" type="email" name="admin_email"
                                    maxlength="254" value="{{ $admin_email }}">
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __('admin/server.se_forum_link') }}</td>
                            <td>
                                <input class="form-control" type="url" name="forum_url"
                                    maxlength="254" value="{{ $forum_url }}" placeholder="https://...">
                            </td>
                        </tr>
                    </x-admin.settings-table>
                </x-admin.card-collapsible>
            </div>

            <div class="col-lg-6">
                <x-admin.card-collapsible id="collapseSpeed" :title="__('admin/server.se_section_speed')" icon="fas fa-tachometer-alt">
                    <x-admin.settings-table>
                        <tr>
                            <td>
                                {{ __('admin/server.se_general_speed') }}
                                <i class="fas fa-question-circle text-gray-400" data-toggle="popover"
                                    data-trigger="hover" data-content="{{ __('admin/server.se_normal_speed') }}"
                                    data-html="true"></i>
                            </td>
                            <td>
                                <input class="form-control" type="number" name="game_speed"
                                    value="{{ $game_speed }}" min="1" max="100">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                {{ __('admin/server.se_fleet_speed') }}
                                <i class="fas fa-question-circle text-gray-400" data-toggle="popover"
                                    data-trigger="hover" data-content="{{ __('admin/server.se_normal_speed_fleett') }}"
                                    data-html="true"></i>
                            </td>
                            <td>
                                <input class="form-control" type="number" name="fleet_speed"
                                    value="{{ $fleet_speed }}" min="1" max="100">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                {{ __('admin/server.se_resources_producion_speed') }}
                                <i class="fas fa-question-circle text-gray-400" data-toggle="popover"
                                    data-trigger="hover" data-content="{{ __('admin/server.se_normal_speed_resoruces') }}"
                                    data-html="true"></i>
                            </td>
                            <td>
                                <input class="form-control" type="number" name="resource_multiplier"
                                    value="{{ $resource_multiplier }}" min="1" max="100">
                            </td>
                        </tr>
                    </x-admin.settings-table>
                </x-admin.card-collapsible>
            </div>
        </div>

        {{-- Row 2: Server Access | Date & Time --}}
        <div class="row">
            <div class="col-lg-6">
                <x-admin.card-collapsible id="collapseAccess" :title="__('admin/server.se_section_access')" icon="fas fa-power-off">
                    <x-admin.settings-table>
                        <tr>
                            <td>{{ __('admin/server.se_server_op_close') }}</td>
                            <td>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="game_enable"
                                        name="game_enable" {{ $game_enable ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="game_enable"></label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __('admin/server.se_server_status_message') }}</td>
                            <td>
                                <textarea class="form-control" name="close_reason"
                                    rows="4">{{ $close_reason }}</textarea>
                            </td>
                        </tr>
                    </x-admin.settings-table>
                </x-admin.card-collapsible>
            </div>

            <div class="col-lg-6">
                <x-admin.card-collapsible id="collapseDateTime" :title="__('admin/server.se_section_datetime')" icon="fas fa-clock">
                    <x-admin.settings-table>
                        <tr>
                            <td>{{ __('admin/server.se_date_time_zone') }}</td>
                            <td>
                                <select class="form-control" name="date_time_zone">
                                    @foreach ($timezone_options as $group)
                                        <optgroup label="{{ $group['group'] }}">
                                            @foreach ($group['zones'] as $zone)
                                                <option value="{{ $zone['value'] }}" {{ $zone['selected'] ? 'selected' : '' }}>
                                                    {{ $zone['label'] }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __('admin/server.se_date_format') }}</td>
                            <td>
                                <input class="form-control" type="text" name="date_format"
                                    value="{{ $date_format }}" placeholder="d/m/Y">
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __('admin/server.se_date_format_extended') }}</td>
                            <td>
                                <input class="form-control" type="text" name="date_format_extended"
                                    value="{{ $date_format_extended }}" placeholder="d/m/Y H:i:s">
                            </td>
                        </tr>
                    </x-admin.settings-table>
                </x-admin.card-collapsible>
            </div>
        </div>

        {{-- Row 3: Combat Rules | Noob Protection --}}
        <div class="row">
            <div class="col-lg-6">
                <x-admin.card-collapsible id="collapseCombat" :title="__('admin/server.se_section_combat')" icon="fas fa-crosshairs">
                    <x-admin.settings-table>
                        <tr>
                            <td>
                                {{ __('admin/server.se_admin_protection') }}
                                <i class="fas fa-question-circle text-gray-400" data-toggle="popover"
                                    data-trigger="hover" data-content="{{ __('admin/server.se_title_admins_protection') }}"
                                    data-html="true"></i>
                            </td>
                            <td>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="adm_attack"
                                        name="adm_attack" {{ $adm_attack ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="adm_attack"></label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                {{ __('admin/server.se_ships_cdr') }}
                                <i class="fas fa-question-circle text-gray-400" data-toggle="popover"
                                    data-trigger="hover" data-content="{{ __('admin/server.se_ships_cdr_message') }}"
                                    data-html="true"></i>
                            </td>
                            <td>
                                <select name="fleet_cdr" class="form-control">
                                    @foreach ($fleet_cdr_options as $option)
                                        <option value="{{ $option['value'] }}" {{ $option['selected'] ? 'selected' : '' }}>
                                            {{ $option['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                {{ __('admin/server.se_def_cdr') }}
                                <i class="fas fa-question-circle text-gray-400" data-toggle="popover"
                                    data-trigger="hover" data-content="{{ __('admin/server.se_def_cdr_message') }}"
                                    data-html="true"></i>
                            </td>
                            <td>
                                <select name="defs_cdr" class="form-control">
                                    @foreach ($defs_cdr_options as $option)
                                        <option value="{{ $option['value'] }}" {{ $option['selected'] ? 'selected' : '' }}>
                                            {{ $option['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    </x-admin.settings-table>
                </x-admin.card-collapsible>
            </div>

            <div class="col-lg-6">
                <x-admin.card-collapsible id="collapseNoob" :title="__('admin/server.se_noob_protect')" icon="fas fa-shield-alt">
                    <x-admin.settings-table>
                        <tr>
                            <td>{{ __('admin/server.se_noob_protect_active') }}</td>
                            <td>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="noobprotection"
                                        name="noobprotection" {{ $noobprotection ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="noobprotection"></label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                {{ __('admin/server.se_noob_protect2') }}
                                <i class="fas fa-question-circle text-gray-400" data-toggle="popover"
                                    data-trigger="hover" data-content="{{ __('admin/server.se_noob_protect_e2') }}"
                                    data-html="true"></i>
                            </td>
                            <td>
                                <input class="form-control" type="number" name="noobprotectiontime"
                                    value="{{ $noobprotectiontime }}" min="0" max="999999999">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                {{ __('admin/server.se_noob_protect3') }}
                                <i class="fas fa-question-circle text-gray-400" data-toggle="popover"
                                    data-trigger="hover" data-content="{{ __('admin/server.se_noob_protect_e3') }}"
                                    data-html="true"></i>
                            </td>
                            <td>
                                <input class="form-control" type="number" name="noobprotectionmulti"
                                    value="{{ $noobprotectionmulti }}" min="0" max="99">
                            </td>
                        </tr>
                    </x-admin.settings-table>
                </x-admin.card-collapsible>
            </div>
        </div>

    </form>
</div>
@endsection
