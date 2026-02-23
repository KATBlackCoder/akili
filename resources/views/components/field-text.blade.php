@props([
    'field',
    'value' => null,
    'disabled' => false,
    'namePrefix' => 'fields',
])

<div class="form-control w-full">
    <label class="label">
        <span class="label-text font-medium">
            {{ $field->label }}
            @if($field->is_required) <span class="text-error">*</span> @endif
        </span>
    </label>
    @if($field->type === 'textarea')
        <textarea
            name="{{ $namePrefix }}[{{ $field->id }}]"
            class="textarea textarea-bordered w-full"
            placeholder="{{ $field->placeholder ?? '' }}"
            rows="4"
            @if($field->is_required) required @endif
            @if($disabled) disabled @endif
            x-data="{ chars: {{ strlen($value ?? '') }} }"
            x-on:input="chars = $el.value.length"
        >{{ $value }}</textarea>
        @if(isset($field->config['maxlength']))
            <label class="label">
                <span class="label-text-alt" x-text="{{ $field->config['maxlength'] }} - chars + ' caractères restants'"></span>
            </label>
        @endif
    @else
        <input
            type="text"
            name="{{ $namePrefix }}[{{ $field->id }}]"
            class="input input-bordered w-full"
            placeholder="{{ $field->placeholder ?? '' }}"
            value="{{ $value }}"
            @if($field->is_required) required @endif
            @if($disabled) disabled @endif
        />
    @endif
    @error("{$namePrefix}.{$field->id}")
        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
    @enderror
</div>
