<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $form->title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        h1 { font-size: 20px; color: #1a1a1a; }
        h2 { font-size: 14px; color: #444; margin-top: 20px; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 11px; }
        th { background: #f4f4f4; padding: 6px 8px; text-align: left; border: 1px solid #ddd; }
        td { padding: 5px 8px; border: 1px solid #eee; }
        tr:nth-child(even) { background: #fafafa; }
        .header { margin-bottom: 20px; }
        .meta { color: #666; font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $form->title }}</h1>
        @if($form->description)
        <p class="meta">{{ $form->description }}</p>
        @endif
        <p class="meta">Généré le {{ now()->format('d/m/Y H:i') }} · {{ $form->assignments->whereNotNull('submission')->count() }} soumission(s)</p>
    </div>

    @foreach($form->sections as $section)
    <h2>{{ $section->title }}</h2>

    @php $fields = $section->fields; @endphp

    @foreach($form->assignments as $assignment)
    @if($assignment->submission)
    <h3 style="font-size:12px; color:#666; margin: 10px 0 5px;">{{ $assignment->employee->full_name }} — {{ $assignment->submission->submitted_at->format('d/m/Y') }}</h3>

    @php $answersByFieldId = $assignment->submission->answers->keyBy('field_id'); @endphp

    <table>
        <thead>
            <tr>
                <th style="width:40%">Question</th>
                <th>Réponse</th>
            </tr>
        </thead>
        <tbody>
            @foreach($fields as $field)
            <tr>
                <td>{{ $field->label }}@if($field->is_required)<span style="color:red">*</span>@endif</td>
                <td>
                    @php $answer = $answersByFieldId->get($field->id); @endphp
                    {{ $answer?->value ?? '—' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    @endforeach
    @endforeach
</body>
</html>
