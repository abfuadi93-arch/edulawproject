<?php

use App\Filament\Resources\Insights\InsightResource;
use App\Filament\Widgets\EditorialStatusOverview;
use App\Filament\Widgets\InsightsWithoutCoverWidget;
use App\Filament\Widgets\InsightsWithoutExcerptWidget;
use App\Filament\Widgets\PopularInsightsWidget;
use App\Filament\Widgets\TrafficOverviewWidget;
use App\Models\Insight;
use App\Models\InsightCategory;
use App\Models\PageVisit;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('editorial status widget reports draft reviewed and published counts accurately', function () {
    foreach ([
        ['status' => 'draft', 'count' => 2],
        ['status' => 'reviewed', 'count' => 3],
        ['status' => 'published', 'count' => 4],
        ['status' => 'archived', 'count' => 1],
    ] as $group) {
        foreach (range(1, $group['count']) as $position) {
            Insight::query()->create([
                'title' => ucfirst($group['status'])." {$position}",
                'slug' => "{$group['status']}-dashboard-{$position}",
                'status' => $group['status'],
                'published_at' => $group['status'] === 'published' ? now() : null,
            ]);
        }
    }

    expect(EditorialStatusOverview::statusCounts())->toBe([
        'draft' => 2,
        'reviewed' => 3,
        'published' => 4,
    ]);
});

test('quality control widgets return newest incomplete editorials with safe category fallback', function () {
    $category = InsightCategory::query()->create([
        'name' => 'Legal 101',
        'slug' => 'legal-101-dashboard',
        'is_active' => true,
    ]);

    foreach (range(1, 6) as $position) {
        Insight::query()->create([
            'insight_category_id' => $position === 6 ? null : $category->id,
            'title' => "Editorial Belum Lengkap {$position}",
            'slug' => "editorial-belum-lengkap-{$position}",
            'cover_image' => null,
            'excerpt' => null,
            'status' => $position % 2 === 0 ? 'reviewed' : 'draft',
            'updated_at' => now()->subMinutes(6 - $position),
        ]);
    }

    $withoutCover = InsightsWithoutCoverWidget::items();
    $withoutExcerpt = InsightsWithoutExcerptWidget::items();

    expect($withoutCover)->toHaveCount(5)
        ->and($withoutExcerpt)->toHaveCount(5)
        ->and($withoutCover->first()['title'])->toBe('Editorial Belum Lengkap 6')
        ->and($withoutCover->first()['category'])->toBe('Tanpa kategori')
        ->and($withoutCover->first()['url'])->toBe(
            InsightResource::getUrl('edit', ['record' => Insight::query()->where('slug', 'editorial-belum-lengkap-6')->firstOrFail()])
        );
});

test('popular editorial widget uses real visits and excludes drafts', function () {
    $category = InsightCategory::query()->create([
        'name' => 'Law & Governance',
        'slug' => 'law-governance-dashboard',
        'is_active' => true,
    ]);

    $published = collect(range(1, 6))->map(function (int $position) use ($category): Insight {
        return Insight::query()->create([
            'insight_category_id' => $category->id,
            'title' => "Editorial Populer {$position}",
            'slug' => "editorial-populer-dashboard-{$position}",
            'status' => 'published',
            'published_at' => now()->subDays($position),
        ]);
    });

    $draft = Insight::query()->create([
        'title' => 'Draft dengan Banyak Kunjungan',
        'slug' => 'draft-banyak-kunjungan-dashboard',
        'status' => 'draft',
    ]);

    foreach ($published as $position => $insight) {
        foreach (range(1, $position + 1) as $visitPosition) {
            PageVisit::query()->create([
                'visitor_id' => "published-{$insight->id}-{$visitPosition}",
                'method' => 'GET',
                'path' => "insight/{$insight->slug}",
                'full_url' => route('insights.show', $insight->slug),
                'route_name' => 'insights.show',
                'status_code' => 200,
                'visited_at' => now(),
            ]);
        }
    }

    foreach (range(1, 10) as $visitPosition) {
        PageVisit::query()->create([
            'visitor_id' => "draft-{$visitPosition}",
            'method' => 'GET',
            'path' => "insight/{$draft->slug}",
            'full_url' => url("insight/{$draft->slug}"),
            'route_name' => 'insights.show',
            'status_code' => 200,
            'visited_at' => now(),
        ]);
    }

    $items = PopularInsightsWidget::items();

    expect($items)->toHaveCount(5)
        ->and($items->first()['title'])->toBe('Editorial Populer 6')
        ->and($items->first()['views'])->toBe(6)
        ->and($items->pluck('title'))->not->toContain('Draft dengan Banyak Kunjungan');
});

test('editorial dashboard widgets respect monitoring permission and render safe empty states', function () {
    $regularUser = User::query()->create([
        'name' => 'Admin Tanpa Editorial',
        'email' => 'non-editorial-dashboard@example.test',
        'password' => 'secret-password',
        'is_active' => true,
    ]);

    $this->actingAs($regularUser);
    expect(EditorialStatusOverview::canView())->toBeFalse();

    $role = Role::findOrCreate('super_admin');
    $editor = User::query()->create([
        'name' => 'Editor Dashboard',
        'email' => 'editorial-dashboard@example.test',
        'password' => 'secret-password',
        'is_active' => true,
    ]);
    $editor->assignRole($role);

    $this->actingAs($editor);
    expect(EditorialStatusOverview::canView())->toBeTrue()
        ->and(PopularInsightsWidget::items())->toBeEmpty();

    $this->get('/admin')
        ->assertOk()
        ->assertSee('Website Performance')
        ->assertDontSee('<header class="fi-header">', false)
        ->assertSee('Control Center')
        ->assertSee('Selamat datang, Super Admin.')
        ->assertSee('edulaw-performance-actions', false)
        ->assertSee('lg:grid-cols-[minmax(0,1fr)_minmax(12rem,15rem)]', false)
        ->assertDontSee('lg:min-w-[33rem]', false)
        ->assertSee('--col-span-xl: span 8 / span 8', false)
        ->assertSee('--col-span-xl: span 4 / span 4', false)
        ->assertSee('--col-span-xl: span 6 / span 6', false)
        ->assertSee('Traffic Website')
        ->assertSee('Pageviews')
        ->assertSee('Visitors')
        ->assertSee('Performa Konten')
        ->assertSee('Masih dalam penyusunan')
        ->assertSee('Menunggu keputusan terbit')
        ->assertSee('Sudah diterbitkan')
        ->assertSee('Artikel Tanpa Cover')
        ->assertSee('Semua artikel sudah memiliki cover.')
        ->assertSee('Artikel Tanpa Ringkasan')
        ->assertSee('Semua artikel sudah memiliki ringkasan.')
        ->assertSee('Artikel Populer')
        ->assertSee('Data kunjungan belum tersedia.')
        ->assertSee('Pemeriksaan Panel Admin');
});

test('editor dashboard shows relevant editorial statistics and hides unauthorized modules', function () {
    Cache::flush();

    $role = Role::findOrCreate('editor');
    $role->givePermissionTo(collect([
        'view insights',
        'update all insights',
        'review insights',
        'publish insights',
        'view publications',
    ])->map(fn (string $name) => Permission::findOrCreate($name))->all());

    $editor = User::query()->create([
        'name' => 'Editor Statistik',
        'email' => 'editor-statistik@example.test',
        'password' => 'secret-password',
        'is_active' => true,
    ]);
    $editor->assignRole($role);

    Insight::query()->create([
        'title' => 'Editorial Menunggu Review',
        'slug' => 'editorial-menunggu-review-dashboard',
        'status' => 'reviewed',
    ]);
    $published = Insight::query()->create([
        'title' => 'Editorial Terbit Bulan Ini',
        'slug' => 'editorial-terbit-bulan-ini-dashboard',
        'status' => 'published',
        'published_at' => now(),
    ]);
    $publication = Publication::query()->create([
        'title' => 'Publikasi Dashboard Editor',
        'slug' => 'publikasi-dashboard-editor',
        'status' => 'published',
        'published_at' => today(),
    ]);

    PageVisit::query()->create([
        'visitor_id' => 'editorial-dashboard-visitor',
        'method' => 'GET',
        'path' => "insight/{$published->slug}",
        'full_url' => route('insights.show', $published->slug),
        'route_name' => 'insights.show',
        'status_code' => 200,
        'visited_at' => now(),
    ]);
    PageVisit::query()->create([
        'visitor_id' => 'publication-dashboard-visitor',
        'method' => 'GET',
        'path' => "riset-publikasi/{$publication->slug}/download",
        'full_url' => route('publications.download', $publication->slug),
        'route_name' => 'publications.download',
        'status_code' => 200,
        'visited_at' => now(),
    ]);

    $this->actingAs($editor)
        ->get('/admin')
        ->assertOk()
        ->assertDontSee('<header class="fi-header">', false)
        ->assertSee('Ringkasan Kinerja Editorial')
        ->assertSee('Total Editorial')
        ->assertSee('Terbit Bulan Ini')
        ->assertSee('Menunggu Review')
        ->assertSee('Views Editorial 30 Hari')
        ->assertSee('Total Publikasi')
        ->assertSee('Unduhan Publikasi 30 Hari')
        ->assertSee('Traffic Editorial')
        ->assertSee('Tren Mingguan')
        ->assertSee('Mingguan')
        ->assertSee('Bulanan')
        ->assertSee('Editorial terpopuler')
        ->assertSee('Publikasi paling banyak diunduh')
        ->assertDontSee('Total Program')
        ->assertDontSee('Total Multimedia')
        ->assertDontSee('Program terpopuler')
        ->assertDontSee('Permintaan Kolaborasi')
        ->assertDontSee('Pesan Kontak')
        ->assertDontSee('Pemeriksaan Panel Admin');

    Livewire::test(TrafficOverviewWidget::class)
        ->assertSee('Tren Mingguan')
        ->set('trafficPeriod', 'month')
        ->assertSee('Tren 30 Hari');
});
