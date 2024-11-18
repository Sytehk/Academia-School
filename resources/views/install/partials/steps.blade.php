@php
    $installSteps = [
        'start' => _lang('Welcome'),
        'requirements' => _lang('Requirements'),
        'permissions' => _lang('Permissions'),
        'database' => _lang('Database'),
        'installation' => _lang('Install'),
        'complete' => _lang('Complete'),
    ];

    $installStepKeys = array_keys($installSteps);
    $currentIndex = array_search($current, $installStepKeys, true);
    $currentIndex = $currentIndex === false ? 0 : $currentIndex;
@endphp

<ol class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto pb-1">
    @foreach ($installSteps as $key => $label)
        @php
            $index = $loop->index;
            $isDone = $index < $currentIndex;
            $isActive = $index === $currentIndex;
        @endphp

        <li class="flex items-center gap-1.5 sm:gap-2 flex-shrink-0">
            <span
                class="flex items-center justify-center h-6 w-6 rounded-full text-[11px] font-semibold flex-shrink-0
                    {{ $isDone ? 'bg-emerald-500 text-white' : ($isActive ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-400') }}"
            >
                @if ($isDone)
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0l-3.5-3.5a1 1 0 111.4-1.4l2.8 2.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd" />
                    </svg>
                @else
                    {{ $index + 1 }}
                @endif
            </span>
            <span class="text-xs font-medium whitespace-nowrap {{ $isActive ? 'text-slate-900' : 'text-slate-400' }}">
                {{ $label }}
            </span>
        </li>

        @if (! $loop->last)
            <li class="h-px w-4 sm:w-8 flex-shrink-0 {{ $isDone ? 'bg-emerald-400' : 'bg-slate-200' }}"></li>
        @endif
    @endforeach
</ol>
