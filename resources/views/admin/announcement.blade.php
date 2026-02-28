@extends('master.admin')

@section('content')
<div class="container-fluid">
    <script src="{{ asset('assets/js/cntchar-min.js') }}" type="text/javascript"></script>
    <x-alert/>

    <form action="{{ route('admin.announcement.send') }}" method="POST" name="announcement">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">{{ __('admin/announcement.an_title') }}</h1>
            <button type="submit" class="btn btn-primary btn-icon-split">
                <span class="icon text-white-50"><i class="fas fa-envelope"></i></span>
                <span class="text">{{ __('admin/announcement.an_send_message') }}</span>
            </button>
        </div>
        <p class="mb-4">{!! __('admin/announcement.an_sub_title') !!}</p>

        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-10">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">{{ __('admin/announcement.an_general') }}</h6>
                    </div>
                    <div class="card-body">

                        {{-- Subject --}}
                        <div class="form-group">
                            <input class="form-control" name="subject" type="text" maxlength="100"
                                value="{{ __('admin/announcement.an_none') }}"
                                placeholder="{{ __('admin/announcement.an_subject') }}">
                        </div>

                        {{-- Toolbar: color + send-as --}}
                        <div class="an-toolbar d-flex align-items-center flex-wrap mb-3">
                            <label class="an-toolbar-label mb-0 mr-2" for="colorPickerInput">
                                {{ __('admin/announcement.an_color') }}
                            </label>
                            <input type="color" name="color-picker" id="colorPickerInput"
                                class="an-color-native mr-2" value="#ff0000">
                            <code class="small mr-3" id="colorHexLabel">#ff0000</code>

                            <div class="an-toolbar-divider d-none d-sm-block"></div>

                            <span class="an-toolbar-label mr-3">{{ __('admin/announcement.an_send_as') }}</span>

                            <div class="custom-control custom-checkbox mr-3">
                                <input type="checkbox" class="custom-control-input" id="chkMessage" name="message" checked>
                                <label class="custom-control-label" for="chkMessage">
                                    {{ __('admin/announcement.an_send_as_message') }}
                                </label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="chkMail" name="mail">
                                <label class="custom-control-label" for="chkMail">
                                    {{ __('admin/announcement.an_send_as_email') }}
                                </label>
                            </div>
                        </div>

                        {{-- Color warning --}}
                        <div id="colorWarning" class="d-none mb-2">
                            <small class="text-warning">
                                <i class="fas fa-exclamation-triangle mr-1"></i>{{ __('admin/announcement.an_color_warning') }}
                            </small>
                        </div>

                        {{-- Message text --}}
                        <div class="form-group mb-1">
                            <i class="fas fa-question-circle text-info mr-1" data-toggle="popover"
                                data-trigger="hover"
                                data-content="{{ __('admin/announcement.an_info') }}"
                                data-html="true"></i>
                            <textarea class="form-control an-textarea mt-1" name="text" rows="10"
                                onkeyup="cntChars('announcement', 5000);"></textarea>
                        </div>
                        <small class="text-muted">
                            <span id="cntChars">0</span> / 5000 {{ __('admin/announcement.an_characters') }}
                        </small>

                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
    .an-toolbar {
        background: #f8f9fc;
        border: 1px solid #e3e6f0;
        border-radius: .35rem;
        padding: .4rem .75rem;
        gap: .25rem;
    }
    .an-toolbar-label {
        font-size: .7rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #858796;
        white-space: nowrap;
    }
    .an-toolbar-divider {
        width: 1px;
        height: 18px;
        background: #d1d3e2;
        margin: 0 .75rem;
    }
    .an-color-native {
        width: 32px;
        height: 24px;
        padding: 1px 2px;
        border: 1px solid #d1d3e2;
        border-radius: .25rem;
        cursor: pointer;
        vertical-align: middle;
    }
    .an-textarea {
        transition: background-color 0.25s, color 0.25s;
    }
</style>
@endpush

@push('scripts')
<script>
    $(function () {
        var $picker   = $('#colorPickerInput');
        var $label    = $('#colorHexLabel');
        var $textarea = $('[name=text]');

        function applyColor(hex) {
            var r = parseInt(hex.slice(1, 3), 16);
            var g = parseInt(hex.slice(3, 5), 16);
            var b = parseInt(hex.slice(5, 7), 16);
            var luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;

            $textarea.css({
                color: hex,
                backgroundColor: luminance > 0.55 ? '#1e1e2e' : '#ffffff'
            });
            $label.text(hex);

            $('#colorWarning').toggleClass('d-none', luminance >= 0.12 && luminance <= 0.88);
        }

        $picker.on('input change', function () {
            applyColor($(this).val());
        });
    });
</script>
@endpush

@endsection
