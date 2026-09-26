<?php

use App\Filament\Resources\Opportunities\OpportunityResource;
use App\Models\Opportunity;
use Carbon\CarbonImmutable;

test('opportunity deadlines switch at midnight WIB even with a UTC server', function (string $instant, bool $open, string $label, string $tomorrowLabel) {
    $originalTimezone = date_default_timezone_get();
    config(['app.timezone' => 'UTC']);
    date_default_timezone_set('UTC');
    $this->travelTo(CarbonImmutable::parse($instant, 'UTC'));

    try {
        $opportunity = Opportunity::create([
            'title' => 'ICCLI timezone regression',
            'slug' => 'iccli-timezone-regression',
            'type' => 'call_for_papers',
            'status' => 'open',
            'deadline' => '2026-09-26',
            'application_link' => 'https://example.test/iccli',
        ]);
        $nextDay = new Opportunity(['status' => 'open', 'deadline' => '2026-09-27']);
        $flexible = new Opportunity(['status' => 'open']);
        $closed = new Opportunity(['status' => 'closed', 'deadline' => '2026-09-28']);

        expect($opportunity->deadline_relative_label)->toBe($label)
            ->and($opportunity->is_open_for_applications)->toBe($open)
            ->and($opportunity->display_status)->toBe($open ? 'Masih Dibuka' : 'Sudah Ditutup')
            ->and(Opportunity::active()->count())->toBe($open ? 1 : 0)
            ->and($nextDay->deadline_relative_label)->toBe($tomorrowLabel)
            ->and($flexible->is_open_for_applications)->toBeTrue()
            ->and($closed->is_open_for_applications)->toBeFalse()
            ->and(OpportunityResource::deadlineRelativeLabel($opportunity->deadline))
            ->toBe($open ? 'Berakhir hari ini' : 'Lewat 1 hari');

        $this->get('/')
            ->assertOk()
            ->assertViewHas('latestOpportunities', fn ($items) => $items->contains('id', $opportunity->id) === $open)
            ->assertViewHas('credibilityStats', fn ($items) => $items->firstWhere('label', 'Peluang Aktif')['value'] === ($open ? 1 : 0));
        $this->get(route('opportunities.index', ['deadline' => '7_days']))
            ->assertOk()
            ->assertViewHas('opportunities', fn ($items) => $items->contains('id', $opportunity->id) === $open)
            ->assertViewHas('statistics', fn ($stats) => $stats['open'] === ($open ? 1 : 0));
    } finally {
        $this->travelBack();
        date_default_timezone_set($originalTimezone);
    }
})->with([
    'last second September 26 WIB' => ['2026-09-26 16:59:59', true, 'Hari ini', 'Besok'],
    'midnight September 27 WIB' => ['2026-09-26 17:00:00', false, 'Deadline berakhir', 'Hari ini'],
    'before UTC midnight' => ['2026-09-26 23:59:59', false, 'Deadline berakhir', 'Hari ini'],
]);
