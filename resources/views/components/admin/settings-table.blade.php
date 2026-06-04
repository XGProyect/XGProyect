{{--
    Admin settings-table component (Tailwind rewrite).

    Wraps the repeated label/input row pattern used across all settings views.
    Existing pages keep working — only the wrapper styling changed.
--}}
<div style="overflow-x: auto;">
    <table class="adm-table adm-table-compact" style="background: transparent;">
        <tbody>
            {!! $slot !!}
        </tbody>
    </table>
</div>
