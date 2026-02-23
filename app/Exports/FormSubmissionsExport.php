<?php

namespace App\Exports;

use App\Models\Form;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FormSubmissionsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(public readonly Form $form) {}

    public function collection()
    {
        return $this->form->assignments()
            ->with(['employee', 'submission.answers.field'])
            ->whereHas('submission')
            ->get();
    }

    /**
     * @return array<string>
     */
    public function headings(): array
    {
        $headers = ['Employé', 'Date de soumission', 'Statut'];

        foreach ($this->form->fields as $field) {
            $headers[] = $field->label;
        }

        return $headers;
    }

    /**
     * @param  mixed  $assignment
     * @return array<mixed>
     */
    public function map($assignment): array
    {
        $submission = $assignment->submission;
        $row = [
            $assignment->employee->full_name,
            $submission->submitted_at->format('d/m/Y H:i'),
            $submission->status,
        ];

        $answersByFieldId = $submission->answers->keyBy('field_id');

        foreach ($this->form->fields as $field) {
            $answer = $answersByFieldId->get($field->id);
            if ($answer) {
                $row[] = $answer->file_path
                    ? asset('storage/'.$answer->file_path)
                    : $answer->value;
            } else {
                $row[] = '';
            }
        }

        return $row;
    }
}
