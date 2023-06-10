@extends('master.game')

@section('content')
<script language="JavaScript">
    function f(target_url, win_name) {
        var new_win = window.open(target_url, win_name, 'resizable=yes,scrollbars=yes,menubar=no,toolbar=no,width=800,height=600,top=0,left=0');
        new_win.focus();
    }
</script>
<br>
<div id="content" style="top: 118px; height: 656px;" role="main">
    <center>
        <table class="header">
            <tbody>
                <tr class="header">
                    <td>
                        <table width="519">
                            <tbody>
                                <form action="{{ $form_submit }}" method="POST" role="form">
                                    <tr>
                                        <td colspan="4" class="c">{{ __('game/messages.mg_title') }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('game/messages.mg_show_title') }}</th>
                                        <th colspan="2">{{ __('game/messages.mg_type_title') }}</th>
                                        <th>{{ __('game/messages.mg_amount_title') }} / {{ __('game/messages.mg_unread_title') }}</th>
                                    </tr>
                                    @foreach ($message_type_list as $item)
                                    <tr>
                                        <th scope="row">
                                            <input type="checkbox" name="{{ $item['message_type'] }}" {{ $item['checked'] }}>
                                        </th>
                                        <th role="cell" colspan="2">
                                            <a href="?page=messages&dsp=1&{{ $item['message_type'] }}={{ $item['checked_status'] }}">{{ $item['message_type_name'] }}</a>
                                        </th>
                                        <th role="cell">{{ $item['message_amount'] }} / {{ $item['message_unread'] }}</th>
                                    </tr>
                                    @endforeach
                                    @if ($messages)
                                    <tr>
                                        <td class="c">{{ __('game/messages.mg_action') }}</td>
                                        <td class="c">{{ __('game/messages.mg_date') }}</td>
                                        <td class="c">{{ __('game/messages.mg_from') }}</td>
                                        <td class="c">{{ __('game/messages.mg_subject') }}</td>
                                    </tr>
                                    @foreach ($messages_list as $item)
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
                                        <td class="b"> </td>
                                        <td colspan="3" class="b">{!! $item['message_text'] !!}</td>
                                    </tr>
                                    @endforeach
                                    @endif
                                    <tr>
                                        <th role="cell" colspan="4">
                                            @if ($deleteOptions)
                                            <select name="deletemessages">
                                                <option value="deletemarked">{{ __('game/messages.mg_delete_marked') }}</option>
                                                <option value="deleteunmarked">{{ __('game/messages.mg_delete_unmarked') }}</option>
                                                <option value="deleteallshown">{{ __('game/messages.mg_delete_all_shown') }}</option>
                                                <option value="deleteall">{{ __('game/messages.mg_delete_all') }}</option>
                                            </select>
                                            @endif
                                            <input type="submit" value="{{ __('game/messages.mg_confirm_action') }}">
                                        </th>
                                    </tr>
                                    <input type="hidden" name="messages" value="1" />
                                </form>
                                <form action="game.php?page=messages" method="POST" role="form">
                                    <tr height="20"> </tr>
                                    <tr>
                                        <td colspan="4" class="c">{{ __('game/messages.mg_address_book') }}</td>
                                    </tr>
                                    <tr>
                                        <th role="cell">{{ __('game/messages.mg_show_title') }}</th>
                                        <th role="cell" colspan="2">{{ __('game/messages.mg_type_title') }}</th>
                                        <th role="cell">{{ __('game/messages.mg_amount_title') }}</th>
                                    </tr>
                                    <tr>
                                        <th role="cell"><input type="checkbox" name="owncontactsopen" {{ $owncontactsopen }}></th>
                                        <th role="cell" colspan="2">{{ __('game/messages.mg_friends_list') }} </th>
                                        <th role="cell">{{ $buddys_count }}</th>
                                    </tr>
                                    @foreach ($buddy_list as $item)
                                    <tr>
                                        <th role="cell" colspan="4">
                                            {{ $item['user_name'] }}
                                            <a href="game.php?page=chat&playerId={{ $item['user_id'] }}">
                                                <img src="{{ asset('upload/skins/xgproyect/img/m.gif') }}" alt=""/>
                                            </a>
                                        </th>
                                    </tr>
                                    @endforeach
                                    <tr>
                                        <th role="cell"><input type="checkbox" name="ownallyopen" {{ $ownallyopen }}></th>
                                        <th role="cell" colspan="2">{{ __('game/messages.mg_alliance') }}</th>
                                        <th role="cell">{{ $alliance_count }}</th>
                                    </tr>
                                    @foreach ($members_list as $item)
                                    <tr>
                                        <th role="cell" colspan="4">
                                            {{ $item['user_name'] }}
                                            <a href="game.php?page=chat&playerId={{ $item['user_id'] }}">
                                                <img src="{{ asset('upload/skins/xgproyect/img/m.gif') }}" alt=""/>
                                            </a>
                                        </th>
                                    </tr>
                                    @endforeach
                                    <tr>
                                        <th role="cell"><input type="checkbox" name="gameoperatorsopen" {{ $gameoperatorsopen }}></th>
                                        <th role="cell" colspan="2">{{ __('game/messages.mg_operators') }}</th>
                                        <th role="cell">{{ $operators_count }}</th>
                                    </tr>
                                    @foreach ($operators_list as $item)
                                    <tr>
                                        <th role="cell" colspan="4">
                                            {{ $item['user_name'] }}
                                            <a href="mailto:{{ $item['user_email'] }}">
                                                <img src="{{ asset('upload/skins/xgproyect/img/m.gif') }}" alt=""/>
                                            </a>
                                        </th>
                                    </tr>
                                    @endforeach
                                    <tr>
                                        <th role="cell" colspan="4">
                                            <input type="hidden" name="addressbook" value="1">
                                            <input type="submit" value="{{ __('game/messages.mg_confirm_action') }}">
                                        </th>
                                    </tr>
                                </form>
                                <form action="game.php?page=messages" method="POST" role="form">
                                    <tr height="20">
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="c">{{ __('game/messages.mg_notes') }}</td>
                                    </tr>
                                    <tr>
                                        <th role="cell" colspan="2">{{ __('game/messages.mg_show_title') }}</th>
                                        <th role="cell" colspan="2">{{ __('game/messages.mg_amount_title') }}</th>
                                    </tr>
                                    <tr>
                                        <th role="cell" colspan="2"><input type="checkbox" name="noticesopen" {{ $noticesopen }}></th>
                                        <th role="cell" colspan="2">{{ $notes_count }}</th>
                                    </tr>
                                    @foreach ($notes_list as $item)
                                    <tr>
                                        <th role="cell" colspan="4">
                                            <a href="#" onclick="f('game.php?page=notices&a=2&n={{ $item['note_id'] }}', 'Notes')">
                                                <font color="{{ $item['note_color'] }}">{{ $item['note_title'] }}</font>
                                            </a>
                                        </th>
                                    </tr>
                                    @endforeach
                                    <tr>
                                        <th role="cell" colspan="4">
                                            <input type="hidden" name="notices" value="1">
                                            <input type="submit" value="{{ __('game/messages.mg_confirm_action') }}">
                                        </th>
                                    </tr>
                                </form>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </center>
</div>
@endsection