@props([
    'field',
    'value' => null,
    'disabled' => false,
    'namePrefix' => 'fields',
])

@php
$options = $field->config['choices'] ?? [];
$isMultiple = $field->type === 'checkbox';
$selectedValues = $isMultiple ? (is_array($value) ? $value : json_decode($value ?? '[]', true)) : [$value];
@endphp

<div class="form-control w-full">
    <label class="label">
        <span class="label-text font-medium">
            {{ $field->label }}
            @if($field->is_required) <span class="text-error">*</span> @endif
        </span>
    </label>

    @if($field->type === 'select')
        <select
            name="{{ $namePrefix }}[{{ $field->id }}]"
            class="select select-bordered w-full"
            @if($field->is_required) required @endif
            @if($disabled) disabled @endif
        >
            <option value="">Choisir...</option>
            @foreach($options as $option)
                <option value="{{ $option }}" {{ in_array($option, $selectedValues) ? 'selected' : '' }}>{{ $option }}</option>
            @endforeach
        </select>
    @elseif($field->type === 'radio')
        <div class="flex flex-col gap-2 mt-1">
            @foreach($options as $option)
                <label class="label cursor-pointer justify-start gap-3">
                    <input
                        type="radio"
                        name="{{ $namePrefix }}[{{ $field->id }}]"
                        value="{{ $option }}"
                        class="radio radio-primary"
                        {{ in_array($option, $selectedValues) ? 'checked' : '' }}
                        @if($field->is_required) required @endif
                        @if($disabled) disabled @endif
                    />
                    <span class="label-text">{{ $option }}</span>
                </label>
            @endforeach
        </div>
    @elseif($field->type === 'checkbox')
        <div class="flex flex-col gap-2 mt-1">
            @foreach($options as $option)
                <label class="label cursor-pointer justify-start gap-3">
                    <input
                        type="checkbox"
                        name="{{ $namePrefix }}[{{ $field->id }}][]"
                        value="{{ $option }}"
                        class="checkbox checkbox-primary"
                        {{ in_array($option, $selectedValues) ? 'checked' : '' }}
                        @if($disabled) disabled @endif
                    />
                    <span class="label-text">{{ $option }}</span>
                </label>
            @endforeach
        </div>
    @endif

    @error("{$namePrefix}.{$field->id}")
        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
    @enderror
</div>
