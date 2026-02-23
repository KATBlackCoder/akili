@props([
    'field',
    'value' => null,
    'disabled' => false,
    'namePrefix' => 'fields',
])

@php $max = $field->config['max'] ?? 5; @endphp

<div class="form-control w-full">
    <label class="label">
        <span class="label-text font-medium">
            {{ $field->label }}
            @if($field->is_required) <span class="text-error">*</span> @endif
        </span>
    </label>

    <div class="rating rating-lg gap-1" x-data="{ selected: {{ (int)($value ?? 0) }} }">
        <input type="hidden" name="{{ $namePrefix }}[{{ $field->id }}]" x-bind:value="selected" />
        <input type="radio" name="_rating_display_{{ $field->id }}" class="rating-hidden" @if(!$value) checked @endif @if($disabled) disabled @endif />
        @for($i = 1; $i <= $max; $i++)
            <input
                type="radio"
                name="_rating_display_{{ $field->id }}"
                class="mask mask-star-2 bg-warning"
                value="{{ $i }}"
                {{ (int)$value === $i ? 'checked' : '' }}
                @if($disabled) disabled @endif
                x-on:click="selected = {{ $i }}"
            />
        @endfor
    </div>

    @error("{$namePrefix}.{$field->id}")
        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
    @enderror
</div>
