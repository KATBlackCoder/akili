<?php

namespace App\Http\Controllers;

use App\Exports\FormSubmissionsExport;
use App\Models\Form;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function pdf(Form $form): Response
    {
        $form->load([
            'sections.fields',
            'assignments.submission.answers.field',
            'assignments.employee',
        ]);

        $pdf = Pdf::loadView('exports.form-pdf', compact('form'));

        return $pdf->download(str($form->title)->slug().'-submissions.pdf');
    }

    public function excel(Form $form): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(
            new FormSubmissionsExport($form),
            str($form->title)->slug().'-submissions.xlsx'
        );
    }
}
