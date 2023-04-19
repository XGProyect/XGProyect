<x-mail::message>
    <table width="100%">
        <tr>
            <th align="center">
                <img src="https://xgproyect.org/wp-content/uploads/2019/10/xgp-new-logo-black.png" alt="XG Proyect Logo" width="250px"/>
            </th>
        </tr>
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>{!! __('emails/recover.re_mail_text_part1') !!}</td>
        </tr>
        <tr>
            <td>{{ __('emails/recover.re_mail_text_part2') }}: <strong>{{ $userPass }}</strong></td>
        </tr>
        <tr>
            <td>
                <x-mail::button :url="$gameUrl">
                    {{ __('emails/recover.we_mail_text_part3') }}
                </x-mail::button>
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>{{ __('emails/recover.re_mail_text_part4') }}</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>{!! __('emails/recover.re_mail_text_part5', ['game' => $gameName]) !!}</td>
        </tr>
    </table>
</x-mail::message>