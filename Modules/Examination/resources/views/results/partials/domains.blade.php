@php($settings = (isset($card_settings) && is_array($card_settings) ? $card_settings : []))
@php
    $showMap = [
        'cognitive' => $settings['show_cognitive'] ?? true,
        'affective' => $settings['show_affective'] ?? true,
        'psychomotor' => $settings['show_psychomotor'] ?? true,
    ];
    $domainLabels = [
        'cognitive' => _lang('Cognitive Domain'),
        'affective' => _lang('Affective Domain'),
        'psychomotor' => _lang('Psychomotor Domain'),
    ];
@endphp
@foreach ($domains as $domain)
    @php
        $hasData = !empty($domain['items']) && collect($domain['items'])->contains(fn ($item) => $item['rating'] || $item['score'] !== null);
    @endphp
    @if (($showMap[$domain['type']] ?? true) && $hasData)
        <div class="domain-title">{{ $domain['name'] ?: ($domainLabels[$domain['type']] ?? $domain['type']) }}</div>
        <table class="marks-table">
            <thead>
                <tr>
                    <th>{{ _lang('Assessment Area') }}</th>
                    <th>{{ _lang('Rating / Score') }}</th>
                    <th>{{ _lang('Remarks') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($domain['items'] as $item)
                    @if ($item['rating'] || $item['score'] !== null)
                        <tr>
                            <td class="left">{{ $item['name'] }}</td>
                            <td>{{ $item['rating'] ?: ($item['score'] !== null ? $item['score'] : '-') }}</td>
                            <td class="left">{{ $item['remarks'] ?: '-' }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    @endif
@endforeach
