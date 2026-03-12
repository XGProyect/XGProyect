{{--
    Admin status-alert component (view-data driven, not session-flash).

    Renders a dismissible Bootstrap alert whose style/type/message are computed
    by the controller and passed as view data. Use <x-alert/> for session flashes.

    Usage:
        <x-admin.status-alert
            :message="$errorMessage"
            :style="$secondStyle"
            :type="$errorType"
        />

    Props:
        message  (string)  - alert body text (rendered as raw HTML, may contain <br>)
        style    (string)  - Bootstrap alert modifier class, e.g. "alert-success"
        type     (string)  - bold prefix label, e.g. "OK", "Warning", "Error"
--}}
@props(['message', 'style', 'type'])

<div class="row mb-3">
    <div class="col">
        <div class="alert {{ $style }} alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>{{ $type }}</strong> {!! $message !!}
        </div>
    </div>
</div>
