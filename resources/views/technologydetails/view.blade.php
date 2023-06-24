@extends('master.game')

@section('content')
<br>
<div id="content" role="main">
    <table role="presentation" width="519px">
        <tbody>
            <tr>
                <td class="c">{{ $name }}</td>
            </tr>
            <tr>
                <td>
                    <table role="presentation">
                        <tbody>
                            <tr>
                                <td>
                                    <img src="{{ asset('assets/upload/skins/xgproyect/elements/' . $id . '.gif') }}" align="top" border="0" height="120px" width="120px" alt="{{ $name }}"/>
                                </td>
                                <td style="vertical-align: top">{{ $description }}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
@endsection