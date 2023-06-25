@extends('master.game')

@section('content')
<form action="" method="POST" role="form">
    <table width="519" role="presentation">
        <tr>
            <td role="heading" aria-level="2" class="c" colspan="2">{{ __('game/alliance.al_your_request_title') }}</td>
        </tr>
        <tr>
            <th colspan="2">{!! $request_text !!}</th>
        </tr>
        <tr>
            <th colspan="2"><input type="submit" name="bcancel" value="{{ $button_text }}"></th>
        </tr>
    </table>
</form>
@endsection