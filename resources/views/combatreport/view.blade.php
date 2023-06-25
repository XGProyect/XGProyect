@extends('master.game', ['noTopnav' => true, 'noLeftMenu' => true])

@section('content')
<br>
<div id="content" role="main">
    {!! $report !!}
</div>
@endsection
