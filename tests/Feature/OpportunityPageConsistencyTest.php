<?php

use App\Models\Opportunity;
use Carbon\CarbonImmutable;

it('keeps homepage and finder consistent across midnight without retaining yesterday HTML', function () {
    foreach (range(1, 34) as $number) {
        Opportunity::create([
            'title' => "Peluang konsistensi {$number}",
            'slug' => "peluang-konsistensi-{$number}",
            'type' => 'competition',
            'status' => $number > 28 ? 'closed' : 'open',
            'deadline' => $number <= 2 ? '2026-09-26' : '2026-09-30',
            'featured' => $number === 3,
            'application_link' => "https://example.test/opportunity/{$number}",
        ]);
    }

    try {
        foreach ([['2026-09-26 16:59:59', 28], ['2026-09-26 17:00:00', 26]] as [$instant, $count]) {
            $this->travelTo(CarbonImmutable::parse($instant, 'UTC'));
            $home = $this->get(route('home'))->assertOk()
                ->assertViewHas('credibilityStats', fn ($stats) => $stats->firstWhere('label', 'Peluang Aktif')['value'] === $count);
            $finder = $this->get(route('opportunities.index'))->assertOk()
                ->assertViewHas('statistics', fn ($stats) => $stats['total'] === 34 && $stats['open'] === $count)
                ->assertViewHas('opportunities', fn ($items) => $items->total() === $count - 1)
                ->assertSee('Di luar 1 peluang pada Pilihan Edulaw di atas.');

            foreach ([$home, $finder] as $response) {
                expect($response->headers->get('Cache-Control'))->toContain('no-store', 'private', 'max-age=0');
            }

            if ($count === 26) {
                $home->assertViewHas('latestOpportunities', fn ($items) => $items->every(fn ($item) => $item->deadline->toDateString() === '2026-09-30'));
                $finder->assertViewHas('statistics', fn ($stats) => $stats['nearest_deadline'] === '30 September');
                foreach ([$home, $finder] as $response) {
                    $response->assertDontSee('26 September 2026')->assertDontSee('Hari ini');
                }
            }
        }

        $this->get(route('opportunities.index', ['q' => 'no matching opportunities']))
            ->assertOk()
            ->assertViewHas('opportunities', fn ($items) => $items->total() === 0)
            ->assertDontSee('Di luar 1 peluang pada Pilihan Edulaw di atas.');
    } finally {
        $this->travelBack();
    }
});
