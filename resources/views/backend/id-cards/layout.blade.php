{{--
    Shared page shell for every ID card sheet (student / teacher / staff).

    Child views supply:
      $cardTitle    string  Used for the page title and the PDF file name.
      $cardCount    int     Shown in the preview strip.
      $validityNote string  Optional extra text in the preview strip.
      @section('cards')     The <x-id-card.front /> + <x-id-card.back /> pairs.
--}}
@extends('layouts.pdf.index')

@section('title', $cardTitle)

@section('content-download')
    <div class="noprint print-download-buttons idc-toolbar">
        @include('layouts.pdf.back-button')

        <span class="idc-segment">
            <button type="button" class="idc-btn-outline" data-idc-side="front">{{ _lang('Front') }}</button>
            <button type="button" class="idc-btn-outline" data-idc-side="back">{{ _lang('Back') }}</button>
            <button type="button" class="idc-btn-outline" data-idc-side="both">{{ _lang('Both') }}</button>
        </span>

        <button type="button" onclick="window.print()">
            <i class="fa fa-print"></i> {{ _lang('Print') }}
        </button>
        <button type="button" onclick="download('{{ $cardTitle }}', 'print_vh')">
            <i class="fa fa-download"></i> {{ _lang('Download PDF') }}
        </button>
    </div>
@endsection

@section('styles')
    @include('backend.id-cards.partials.styles')
@endsection

@section('content')
    <p class="idc-preview-label noprint">
        {{ _lang('Preview') }} &middot; {{ $cardCount }} {{ $cardCount === 1 ? _lang('card') : _lang('cards') }}
        @isset($validityNote)
            &middot; {{ $validityNote }}
        @endisset
    </p>

    <div class="idc-sheet">
        @yield('cards')
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            var buttons = document.querySelectorAll('[data-idc-side]');

            /**
             * Switch which side of the card is visible. Both sides stay in the DOM,
             * so whatever is on screen is exactly what Print and Download PDF emit.
             *
             * @param {string} side one of 'front' | 'back' | 'both'
             */
            function showSide(side) {
                document.body.classList.remove('idc-mode-front', 'idc-mode-back');

                if (side !== 'both') {
                    document.body.classList.add('idc-mode-' + side);
                }

                buttons.forEach(function (button) {
                    button.classList.toggle('is-active', button.dataset.idcSide === side);
                });
            }

            buttons.forEach(function (button) {
                button.addEventListener('click', function () {
                    showSide(button.dataset.idcSide);
                });
            });

            showSide('front');
        })();
    </script>
@endsection
