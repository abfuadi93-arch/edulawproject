<?php

use App\Models\Opportunity;

test('sitemap remains available when open opportunities exist without public detail routes', function () {
    $opportunity = Opportunity::query()->create([
        'title' => 'Fellowship Riset Hukum',
        'slug' => 'fellowship-riset-hukum',
        'type' => 'fellowship',
        'status' => 'open',
        'deadline' => now()->addWeek()->toDateString(),
        'application_link' => 'https://example.test/daftar',
    ]);

    $this->get(route('sitemap'))
        ->assertOk()
        ->assertHeader('content-type', 'application/xml; charset=UTF-8')
        ->assertSee(route('opportunities.index'), false)
        ->assertDontSee('/opportunities/'.$opportunity->slug, false);
});
