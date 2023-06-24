<div id="leftmenu">
    <script language="JavaScript">
        function f(target_url, win_name) {
            var new_win = window.open(target_url, win_name, 'resizable=yes,scrollbars=yes,menubar=no,toolbar=no,width=550,height=280,top=0,left=0');
            new_win.focus();
        }
    </script>
    <center>
        <div id="menu">
            <p style="width:110px;">
                <NOBR>
                    {{ __('game/menu.lm_players') }} <strong>{!! $userName !!}</strong>
                </NOBR>
            </p>
            <table width="110" cellspacing="0" cellpadding="0">
                @foreach ($blocks as $blockIndex => $block)
                <tr>
                    <td>
                        <img src="{{ asset('assets/upload/skins/xgproyect/menu/' . $block[0]) }}" width="{{ $block[1] }}" height="{{ $block[2] }}" />
                    </td>
                </tr>
                @foreach ($menu[$blockIndex] as $item)
                <tr>
                    <td>
                        <div align="center">
                            {!! $item['link'] !!}
                        </div>
                    </td>
                </tr>
                @endforeach
                @endforeach
                @if ($isAdmin)
                <tr>
                    <td>
                        <div align="center">
                            <a href="admin" target="_blank" title="{{ __('game/menu.lm_administration') }}">
                                <span style="color: lime;">{{ __('game/menu.lm_administration') }}</span>
                            </a>
                        </div>
                    </td>
                </tr>
                @endif
                <tr>
                    <td>
                        <img src="{{ asset('assets/upload/skins/xgproyect/menu/info-help.jpg') }}" width="110" height="19">
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="text-align:center">
                            {{ $servername }} ({!! $changelog !!})
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="text-align:center">
                            <span style="color:#FFFFFF">
                                <a href="#" title="Powered by XG Proyect {{ config('version.files') }} &copy; 2008 - {{ $year }} GNU General Public License">&copy; 2008 - {{ $year }}</a>
                            </span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </center>
</div>