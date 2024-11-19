<ul class="hidden lg:flex items-center gap-8 font-medium">

    @foreach ($menus as $menu)
        {{-- If external link --}}
        @if ($menu->type == 'url')
            <li>
                <a href="{{ $menu->url }}" class="nav-link">
                    {{ $menu->title }}
                </a>
            </li>

            {{-- If internal page --}}
        @elseif($menu->type == 'page')
            <li>
                <a href="{{ route('page.show', $menu->slug) }}" class="nav-link">
                    {{ $menu->title }}
                </a>
            </li>

            {{-- If section anchor --}}
        @elseif($menu->type == 'section')
            <li>
                <a href="#{{ $menu->section_id }}" class="nav-link">
                    {{ $menu->title }}
                </a>
            </li>
        @endif
    @endforeach

</ul>
