<div class="noprint print-download-buttons">
    @include('layouts.pdf.back-button')
    @include('layouts.pdf.download-button')
    @include('layouts.pdf.print-button')
    <button type="button" class="btn-hide-header" style="padding: 10px" onclick="hideHeaderFooter()">
        {{ __('Hide header') }}
    </button>
    <button type="button" class="btn-hide-header" style="padding: 10px" onclick="showHeaderFooter()">
        {{ __('Show header') }}
    </button>
</div>
<div style="clear: both;"></div>
