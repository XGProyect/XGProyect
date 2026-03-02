{{--
    Admin settings-table component.

    Wraps the repeated label/input row pattern used across all settings views.

    Usage:
        <x-admin.settings-table>
            <tr>
                <td><span>{{ __('...label...') }}</span></td>
                <td><input class="form-control" ...></td>
            </tr>
            ...
        </x-admin.settings-table>
--}}
<div class="table-responsive">
    <table class="table table-borderless">
        <tbody>
            {!! $slot !!}
        </tbody>
    </table>
</div>
