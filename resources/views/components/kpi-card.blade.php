@props([
    'title',
    'value',
    'desc' => null,
    'icon' => null,
    'color' => 'primary',
])

<div class="stat bg-base-100 rounded-2xl shadow">
    @if($icon)
    <div class="stat-figure text-{{ $color }}">
        {!! $icon !!}
    </div>
    @endif
    <div class="stat-title text-sm">{{ $title }}</div>
    <div class="stat-value text-{{ $color }} text-3xl">{{ $value }}</div>
    @if($desc)
    <div class="stat-desc">{{ $desc }}</div>
    @endif
</div>
