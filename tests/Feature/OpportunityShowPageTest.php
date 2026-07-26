<?php

use App\Models\Opportunity;
use Illuminate\Support\Facades\Route;

test('opportunity detail route remains disabled', function () {
    $opportunity = Opportunity::query()->create([
        'title' => 'Fellowship Riset Hukum',
        'slug' => 'fellowship-riset-hukum',
        'type' => 'fellowship',
        'excerpt' => 'Kesempatan riset hukum untuk peneliti muda.',
        'description' => 'Program fellowship untuk memperkuat riset hukum publik.',
        'status' => 'open',
        'deadline' => now()->addDays(10)->toDateString(),
        'application_link' => 'https://example.test/daftar',
    ]);

    expect(Route::has('opportunities.show'))->toBeFalse();

    $this->get('/opportunities/'.$opportunity->slug)->assertNotFound();
});

test('legacy opportunity detail path is not publicly available', function () {
    $this->get('/peluang/peluang-lama')->assertNotFound();
});
