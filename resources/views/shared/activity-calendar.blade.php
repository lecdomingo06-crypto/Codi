<section class="content-section activity-calendar stack">
    <div class="section-heading"><div><p class="eyebrow">Consistency</p><h2>{{ $calendar['summary'] }}</h2></div><span class="count-label">Last 12 months</span></div>
    <div class="heatmap" role="grid" aria-label="Exercise activity calendar from {{ $calendar['from'] }} to {{ $calendar['to'] }}">
        <div class="month-row">
            @foreach($calendar['months'] as $month)
                <span>{{ $month }}</span>
            @endforeach
        </div>
        <div class="weeks">
            @foreach($calendar['weeks'] as $week)
                <div class="week" role="row">
                    @foreach($week as $day)
                        @if($day)
                            <span
                                role="gridcell"
                                tabindex="{{ $day['answer_count'] > 0 ? 0 : -1 }}"
                                class="day-cell level-{{ $day['intensity'] }} {{ $day['is_today'] ? 'today' : '' }}"
                                title="{{ $day['label'] }}: {{ $day['answer_count'] }} answers, {{ $day['accepted_answer_count'] }} accepted"
                                aria-label="{{ $day['label'] }}: {{ $day['answer_count'] }} answers, {{ $day['accepted_answer_count'] }} accepted"
                            ></span>
                        @else
                            <span class="day-cell empty" aria-hidden="true"></span>
                        @endif
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
    <p class="legend">Less <span class="day-cell level-0"></span><span class="day-cell level-1"></span><span class="day-cell level-2"></span><span class="day-cell level-3"></span><span class="day-cell level-4"></span> More</p>
</section>
