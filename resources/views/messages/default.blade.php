@extends('master.game')

@section('content')
<script language="JavaScript">
    function f(target_url, win_name) {
        var new_win = window.open(target_url, win_name, 'resizable=yes,scrollbars=yes,menubar=no,toolbar=no,width=800,height=600,top=0,left=0');
        new_win.focus();
    }
</script>
<form action="game.php?page=messages" method="post" role="form">
    <table width="519">
        <table>
            <tr>
                <td>
                    <input name="messages" value="1" type="hidden">
                    <table width="519">
                        <tr>
                            <td class="c" colspan="4">{{ __('game/messages.mg_title') }}</td></tr><tr>
                            <th>{{ __('game/messages.mg_action') }}</th>
                            <th>{{ __('game/messages.mg_date') }}</th>
                            <th>{{ __('game/messages.mg_from') }}</th>
                            <th>{{ __('game/messages.mg_subject') }}</th>
                        </tr>
                        @foreach ($message_list as $item)
                        <tr>
                            <th role="cell">
                                <input type="hidden" name="showmes{{ $item['message_id'] }}" />
                                <input type="checkbox" name="delmes{{ $item['message_id'] }}" />
                            </th>
                            <th role="cell">{{ $item['message_time'] }}</th>
                            <th role="cell">{!! $item['message_from'] !!} {!! $item['message_reply'] !!}</th>
                            <th role="cell">{!! $item['message_subject'] !!}</th>
                        </tr>
                        <tr>
                            <td class="b"></td>
                            <td colspan="3" class="b">{!! $item['message_text'] !!}</td>
                        </tr>
                        @endforeach
                        <tr>
                            <th role="cell" colspan="4">
                                &nbsp;
                            </th>
                        </tr>
                        <tr>
                            <th role="cell" colspan="4">
                                <select id="deletemessages" name="deletemessages">
                                    <option value="deletemarked">{{ __('game/messages.mg_delete_marked') }}</option>
                                    <option value="deleteunmarked">{{ __('game/messages.mg_delete_unmarked') }}</option>
                                    <option value="deleteall">{{ __('game/messages.mg_delete_all') }}</option>
                                </select>
                                <input value="{{ __('game/messages.mg_confirm_action') }}" type="submit">
                            </th>
                        </tr>
                        <tr>
                            <td colspan="4"></td>
                        </tr>
                    </table>
                    <table width="100%">
                        <tr>
                            <td class="c">{{ __('game/messages.mg_operators') }}</td>
                        </tr>
                        @foreach ($operators_list as $item)
                        <tr>
                            <th role="cell" colspan="4">
                                {{ $item['name'] }} <a href="mailto:{{ $item['email'] }}">
                                    <img src="{{ asset('assets/upload/skins/xgproyect/img/m.gif') }}" alt=""/>
                                </a>
                            </th>
                        </tr>
                        @endforeach
                    </table>
                </td>
            </tr>
        </table>
    </table>
</form>
@endsection