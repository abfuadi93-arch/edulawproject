<?php

use App\Filament\Widgets\EditorialStatusOverview;
use App\Models\Insight;

test('dashboard editorial hanya menghitung Draft Review dan Published', function () {
    foreach (['draft' => 2, 'review' => 3, 'published' => 4, 'archived' => 1] as $status => $count) {
        foreach (range(1, $count) as $position) {
            Insight::query()->create([
                'title' => "{$status} {$position}",
                'slug' => "{$status}-{$position}",
                'status' => $status,
                'published_at' => $status === 'published' ? now() : null,
            ]);
        }
    }

    expect(EditorialStatusOverview::statusCounts())->toBe([
        'draft' => 2,
        'review' => 3,
        'published' => 4,
    ]);
});
