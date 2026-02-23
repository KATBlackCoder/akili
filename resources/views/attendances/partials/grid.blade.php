@php
$startDate = \Carbon\Carbon::parse($month.'-01');
$endDate = $startDate->copy()->endOfMonth();
$daysInMonth = $startDate->daysInMonth;
@endphp

<div class="card bg-base-100 shadow">
    <div class="card-body">
        <div class="grid grid-cols-7 gap-1">
            @foreach(['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $day)
            <div class="text-center text-xs font-medium text-base-content/50 py-2">{{ $day }}</div>
            @endforeach

            @php $firstDayOfWeek = $startDate->dayOfWeek === 0 ? 6 : $startDate->dayOfWeek - 1; @endphp
            @for($i = 0; $i < $firstDayOfWeek; $i++)
            <div></div>
            @endfor

            @for($day = 1; $day <= $daysInMonth; $day++)
            @php
                $date = $startDate->copy()->setDay($day);
                $dateKey = $date->format('Y-m-d');
                $attendance = $attendances->get($dateKey);
                $isToday = $date->isToday();
                $isWeekend = $date->isWeekend();
            @endphp
            <div
                class="relative aspect-square rounded-lg flex flex-col items-center justify-center cursor-pointer
                    {{ $isToday ? 'ring-2 ring-primary' : '' }}
                    {{ $isWeekend ? 'bg-base-200' : 'bg-base-100 hover:bg-base-200' }}
                    {{ $attendance ? 'bg-success/10' : '' }}"
                x-on:click="
                    $dispatch('open-attendance', '{{ $dateKey }}');
                    document.getElementById('attendance-modal').showModal();
                "
                x-data
                @open-attendance.window="if ($event.detail === '{{ $dateKey }}') document.querySelector('#attendance-modal input[name=date]').value = $event.detail"
            >
                <span class="text-sm font-medium {{ $isToday ? 'text-primary font-bold' : '' }}">{{ $day }}</span>
                @if($attendance)
                    <div class="w-2 h-2 bg-success rounded-full mt-1"></div>
                @elseif(!$isWeekend && $date->isPast())
                    <div class="w-2 h-2 bg-base-300 rounded-full mt-1"></div>
                @endif
            </div>
            @endfor
        </div>

        <div class="flex gap-4 mt-4 text-xs">
            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-success rounded-full inline-block"></span> Présent</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-base-300 rounded-full inline-block"></span> Non saisi</span>
        </div>
    </div>
</div>
