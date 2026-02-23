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
    <input
        type="date"
        name="{{ $namePrefix }}[{{ $field->id }}]"
        class="input input-bordered w-full"
        value="{{ $value }}"
        @if(isset($field->config['min'])) min="{{ $field->config['min'] }}" @endif
        @if(isset($field->config['max'])) max="{{ $field->config['max'] }}" @endif
        @if($field->is_required) required @endif
        @if($disabled) disabled @endif
    />
    @error("{$namePrefix}.{$field->id}")
        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
    @enderror
</div>
