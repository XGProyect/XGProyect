@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('admin/ban.bn_title') }}</h1>
    </div>
    <p class="mb-4">{{ __('admin/ban.bn_sub_title') }}</p>

    <div class="row">
        <div class="col-lg-8 col-xl-6">
            <div class="card shadow mb-4">

                <!-- Card Header -->
                <div class="card-header py-3 d-flex align-items-center">
                    <i class="fas fa-user-slash text-danger mr-2"></i>
                    <h6 class="m-0 font-weight-bold text-primary">
                        {{ __('admin/ban.bn_username') }}: {{ $target_user->name }}
                        <span class="text-muted small ml-2">(ID: {{ $target_user->id }})</span>
                    </h6>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.ban.form.post') }}" method="POST">
                        @csrf
                        <input type="hidden" name="ban_name" value="{{ $target_user->name }}">

                        <table class="table table-borderless table-sm">

                            {{-- Current ban status --}}
                            @if ($existing_ban)
                                <tr>
                                    <th class="w-40">{{ __('admin/ban.bn_banned_until') }}</th>
                                    <td>
                                        <span class="badge badge-danger px-2 py-1">
                                            <i class="fas fa-clock mr-1"></i>
                                            {{ \Carbon\Carbon::parse($existing_ban->until)->format('Y-m-d H:i') }}
                                        </span>
                                    </td>
                                </tr>
                            @endif

                            {{-- Reason --}}
                            <tr>
                                <th class="align-top pt-2">{{ __('admin/ban.bn_reason') }}</th>
                                <td>
                                    <textarea class="form-control" name="text" rows="4"
                                        maxlength="500">{{ $existing_ban->details ?? '' }}</textarea>
                                    <small class="form-text text-muted">
                                        <span id="char-count">0</span> / 500 {{ __('admin/ban.bn_characters') }}
                                    </small>
                                </td>
                            </tr>

                            {{-- Days / Hours header --}}
                            <tr>
                                <th colspan="2" class="pt-3 text-muted small">
                                    @if ($existing_ban)
                                        <i class="fas fa-info-circle mr-1 text-info"></i>
                                        {{ __('admin/ban.bn_edit_ban_help') }}
                                    @else
                                        <i class="fas fa-clock mr-1"></i>
                                        {{ __('admin/ban.bn_auto_lift_ban_message') }}
                                    @endif
                                </th>
                            </tr>

                            {{-- Days --}}
                            <tr>
                                <th class="align-middle">{{ __('admin/ban.bn_time_days') }}</th>
                                <td>
                                    <div class="input-group input-group-sm" style="width: 140px;">
                                        <div class="input-group-prepend">
                                            <button type="button" class="btn btn-outline-secondary stepper-dec"
                                                data-target="days">−</button>
                                        </div>
                                        <input type="number" id="days" name="days"
                                            class="form-control text-center stepper-input"
                                            value="0" min="0" max="36500" readonly>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary stepper-inc"
                                                data-target="days">+</button>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            {{-- Hours --}}
                            <tr>
                                <th class="align-middle">{{ __('admin/ban.bn_time_hours') }}</th>
                                <td>
                                    <div class="input-group input-group-sm" style="width: 140px;">
                                        <div class="input-group-prepend">
                                            <button type="button" class="btn btn-outline-secondary stepper-dec"
                                                data-target="hour">−</button>
                                        </div>
                                        <input type="number" id="hour" name="hour"
                                            class="form-control text-center stepper-input"
                                            value="0" min="0" max="23" readonly>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary stepper-inc"
                                                data-target="hour">+</button>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            {{-- Vacation mode --}}
                            <tr>
                                <th>{{ __('admin/ban.bn_vacation_mode') }}</th>
                                <td>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="vacat"
                                            name="vacat" value="1"
                                            {{ $existing_ban && $existing_ban->preference_vacation_mode ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="vacat">
                                            {{ __('admin/ban.bn_vacation_mode_label') }}
                                        </label>
                                    </div>
                                </td>
                            </tr>

                        </table>

                        <div class="d-flex justify-content-between mt-3">
                            <a href="{{ route('admin.ban') }}" class="btn btn-secondary btn-icon-split">
                                <span class="icon text-white-50"><i class="fas fa-arrow-left"></i></span>
                                <span class="text">{{ __('admin/ban.bn_button_back') }}</span>
                            </a>
                            <button type="submit" name="bannow" value="1" class="btn btn-danger btn-icon-split">
                                <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                                <span class="text">{{ __('admin/ban.bn_ban_user') }}</span>
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        // ── Character counter ──────────────────────────────────────────────
        const textarea  = document.querySelector('textarea[name="text"]');
        const charCount = document.getElementById('char-count');

        if (textarea && charCount) {
            const update = () => { charCount.textContent = textarea.value.length; };
            textarea.addEventListener('input', update);
            update();
        }

        // ── Steppers (+/−) ─────────────────────────────────────────────────
        document.querySelectorAll('.stepper-inc, .stepper-dec').forEach(btn => {
            btn.addEventListener('click', function () {
                const input = document.getElementById(this.dataset.target);
                if (!input) return;

                const step = this.classList.contains('stepper-inc') ? 1 : -1;
                const min  = input.hasAttribute('min') ? parseInt(input.min, 10) : -Infinity;
                const max  = input.hasAttribute('max') ? parseInt(input.max, 10) :  Infinity;
                const next = Math.min(max, Math.max(min, parseInt(input.value, 10) + step));

                input.value = next;

                // Visual feedback: dec button dims at min, inc button dims at max
                const decBtn = input.closest('.input-group').querySelector('.stepper-dec');
                const incBtn = input.closest('.input-group').querySelector('.stepper-inc');
                if (decBtn) decBtn.classList.toggle('disabled', next <= min);
                if (incBtn) incBtn.classList.toggle('disabled', next >= max);
            });
        });

        // Initialise disabled state on page load
        document.querySelectorAll('.stepper-input').forEach(input => {
            const min    = parseInt(input.min, 10);
            const decBtn = input.closest('.input-group').querySelector('.stepper-dec');
            if (decBtn && parseInt(input.value, 10) <= min) decBtn.classList.add('disabled');
        });
    })();
</script>
@endpush
@endsection
