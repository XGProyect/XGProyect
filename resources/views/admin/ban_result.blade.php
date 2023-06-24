@extends('master.admin')

@section('content')
<script type="text/javascript" src="{{ asset('assets/js/cntchar-min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/filterlist-min.js') }}"></script>
<div class="container-fluid">
    <x-alert/>
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('admin/ban.bn_title') }}</h1>
    </div>
    <p class="mb-4">{{ __('admin/ban.bn_sub_title') }}</p>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <!-- Card Header - Accordion -->
                <a href="#collapseGeneral" class="d-block card-header py-3" data-toggle="collapse" role="button"
                    aria-expanded="true" aria-controls="collapseGeneral">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('admin/ban.bn_username') }}: {{ $name }}</h6>
                </a>
                <!-- Card Content - Collapse -->
                <div class="collapse show" id="collapseGeneral" style="">
                    <div class="card-body">
                        <div class="table-responsive">
                            <form action="" method="POST" name="frm_ban">
                                <input type="hidden" name="ban_name" value="{{ $name }}">
                                <table class="table table-borderless" width="100%" cellspacing="0">
                                    <tr>
                                        <th>
                                            {{ __('admin/ban.bn_reason') }}
                                        </th>
                                        <td colspan="2">
                                            <textarea class="form-control" name="text" rows="5"
                                                onkeyup="javascript:cntChars('frm_ban', 50);">{{ $reason }}</textarea>
                                            (<span id="cntChars">0</span> / 50 {{ __('admin/ban.bn_characters') }})
                                        </td>
                                    </tr>
                                    {!! $banned_until !!}
                                    <tr>
                                        <th colspan="2">{!! $changedate !!}</th>
                                    </tr>
                                    <tr>
                                        <th>{{ __('admin/ban.bn_time_days') }}</th>
                                        <td><input name="days" class="form-control" type="number" value="0" min="0" max="1000"></td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('admin/ban.bn_time_hours') }}</th>
                                        <td><input name="hour" class="form-control" type="number" value="0" min="0" max="1000"></td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('admin/ban.bn_vacation_mode') }}</th>
                                        <td>
                                            <input name="vacat" class="form-control-check" type="checkbox" {{ $vacation }} />
                                        </td>
                                    </tr>
                                </table>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-icon-split"
                                        name="bannow"
                                        value="{{ __('admin/ban.bn_ban_user') }}">
                                        <span class="icon text-white-50">
                                            <i class="fas fa-save"></i>
                                        </span>
                                        <span class="text">{{ __('admin/ban.bn_ban_user') }}</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection