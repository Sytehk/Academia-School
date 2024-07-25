@extends('examination::results.print_layout')

@php $hideToolbar = true; @endphp

@section('content')
    @php($cardView = (($card_settings ?? [])['template'] ?? 'standard') === 'nigerian' ? 'examination::results.partials.result_card_nigerian' : 'examination::results.partials.result_card_body')
    @include($cardView)
@endsection
