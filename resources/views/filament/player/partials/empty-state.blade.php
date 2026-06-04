@props([
    'icon' => '📭',
    'title' => 'Nothing here yet',
    'body' => '',
])

<div {{ $attributes->merge(['class' => 'player-empty']) }}>
    <div style="font-size:40px;">{{ $icon }}</div>
    <div style="font-weight:700; font-size:16px; margin-top:8px;">{{ $title }}</div>
    @if($body)
        <div style="font-size:13px; margin-top:4px;">{{ $body }}</div>
    @endif
</div>
