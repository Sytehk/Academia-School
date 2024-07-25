@extends('examination::results.print_layout')

@php $hideToolbar = $hideToolbar ?? true; @endphp

{{-- Every student's term card for one class, one card per page. --}}
@section('content')
    @foreach ($cards as $card)
        <div style="{{ $loop->last ? '' : 'page-break-after: always;' }}">
            @php($cardView = ($card['card_settings']['template'] ?? 'standard') === 'nigerian' ? 'examination::results.partials.result_card_nigerian' : 'examination::results.partials.result_card_body')
            @include($cardView, $card)
        </div>
    @endforeach
@endsection
