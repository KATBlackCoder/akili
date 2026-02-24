<tr data-row-index="{{ $rowIndex }}"
    x-on:change="saveToLocalStorage()">
    <td class="text-xs text-base-content/40 font-mono">{{ $rowIndex }}</td>
    @foreach($form->sections->flatMap->fields->sortBy('order') as $field)
    <td class="p-1">
        @php
            $fieldName = "rows[{$rowIndex}][{$field->id}]";
        @endphp
        @switch($field->type)
            @case('text')
                <input type="text" name="{{ $fieldName }}"
                       class="input input-bordered input-sm w-full min-w-24"
                       placeholder="{{ $field->placeholder }}"
                       {{ $field->is_required ? 'required' : '' }} />
                @break
            @case('number')
                <input type="number" name="{{ $fieldName }}"
                       class="input input-bordered input-sm w-24"
                       placeholder="0"
                       {{ $field->is_required ? 'required' : '' }} />
                @break
            @case('date')
                <input type="date" name="{{ $fieldName }}"
                       class="input input-bordered input-sm"
                       {{ $field->is_required ? 'required' : '' }} />
                @break
            @case('select')
                <select name="{{ $fieldName }}" class="select select-bordered select-sm">
                    <option value="">—</option>
                    @foreach($field->config['options'] ?? [] as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
                @break
            @case('textarea')
                <textarea name="{{ $fieldName }}" rows="1"
                          class="textarea textarea-bordered textarea-sm w-full min-w-32"
                          {{ $field->is_required ? 'required' : '' }}></textarea>
                @break
            @default
                <input type="text" name="{{ $fieldName }}"
                       class="input input-bordered input-sm w-full"
                       {{ $field->is_required ? 'required' : '' }} />
        @endswitch
    </td>
    @endforeach
    <td class="p-1">
        <button type="button"
                hx-delete="{{ route('reports.type1.rows.delete', [$form, $rowIndex]) }}"
                hx-target="[data-row-index='{{ $rowIndex }}']"
                hx-swap="outerHTML"
                x-on:htmx:after-request="rowCount = Math.max(0, rowCount - 1); saveToLocalStorage()"
                class="btn btn-ghost btn-xs text-error">
            ✕
        </button>
    </td>
</tr>
