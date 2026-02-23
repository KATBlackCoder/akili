@props([
    'field',
    'value' => null,
    'disabled' => false,
    'namePrefix' => 'fields',
])

<div
    class="form-control w-full"
    x-data="{ preview: null, fileName: null }"
>
    <label class="label">
        <span class="label-text font-medium">
            {{ $field->label }}
            @if($field->is_required) <span class="text-error">*</span> @endif
        </span>
    </label>

    @if($value && $disabled)
        <div class="flex items-center gap-2 p-3 bg-base-200 rounded-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
            <span class="text-sm">Fichier joint</span>
        </div>
    @else
        <input
            type="file"
            name="{{ $namePrefix }}[{{ $field->id }}]"
            class="file-input file-input-bordered w-full"
            @if(isset($field->config['accept'])) accept="{{ $field->config['accept'] }}" @endif
            @if($field->is_required && !$value) required @endif
            @if($disabled) disabled @endif
            x-on:change="
                const file = $event.target.files[0];
                if (file) {
                    fileName = file.name;
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = e => preview = e.target.result;
                        reader.readAsDataURL(file);
                    } else {
                        preview = null;
                    }
                }
            "
        />

        <template x-if="preview">
            <div class="mt-2">
                <img :src="preview" class="max-h-32 rounded-lg object-cover" />
            </div>
        </template>
        <template x-if="fileName && !preview">
            <div class="mt-2 text-sm text-base-content/60" x-text="'📎 ' + fileName"></div>
        </template>
    @endif

    @error("{$namePrefix}.{$field->id}")
        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
    @enderror
</div>
