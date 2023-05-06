@extends('master.game')

@section('metatags')
@if($dest)
<meta http-equiv="refresh" content="{{ $time }};URL={{ $dest }}">
@endif
@endsection

@section('content')
    <br>
    {!! $middle1 !!}
    <table role="presentation" width="519px">
        <tr>
            <th class="errormessage">{{ $mes }}</th>
        </tr>
    </table>
    {!! $middle2 !!}
@endsection