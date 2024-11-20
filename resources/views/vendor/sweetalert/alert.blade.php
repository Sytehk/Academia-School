@if (config('sweetalert.alwaysLoadJS') === true || Session::has('alert.config') || Session::has('alert.delete'))
    @if (config('sweetalert.animation.enable'))
        <link rel="stylesheet" href="{{ asset(config('sweetalert.animatecss')) }}">
    @endif

    @if (config('sweetalert.theme') != 'default')
        {{-- Theme styles are bundled in local sweetalert2 build --}}
    @endif

    @if (config('sweetalert.neverLoadJS') === false)
        <script src="{{ asset('assets/vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
    @endif

    @if (Session::has('alert.delete') || Session::has('alert.config'))
        <script>
            document.addEventListener('click', function(event) {
                // Check if the clicked element or its parent has the attribute
                var target = event.target;
                var confirmDeleteElement = target.closest('[data-confirm-delete]');

                if (confirmDeleteElement) {
                    event.preventDefault();
                    Swal.fire({!! Session::pull('alert.delete') !!}).then(function(result) {
                        if (result.isConfirmed) {
                            var form = document.createElement('form');
                            form.action = confirmDeleteElement.href;
                            form.method = 'POST';
                            form.innerHTML = `
                            @csrf
                            @method('DELETE')
                        `;
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                }
            });

            @if (Session::has('alert.config'))
                Swal.fire({!! Session::pull('alert.config') !!});
            @endif
        </script>
    @endif
@endif
