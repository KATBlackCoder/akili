<div id="draft-badge"
     hx-post="{{ route('reports.type1.draft', request()->route('form')) }}"
     hx-trigger="every 30s"
     hx-vals="js:{draft_data: JSON.stringify(getDraftData()), form_assignment_id: document.querySelector('[name=form_assignment_id]')?.value}"
     hx-target="#draft-badge"
     hx-swap="outerHTML">
    <span class="badge badge-success text-xs">Sauvegardé à {{ $time }}</span>
</div>
