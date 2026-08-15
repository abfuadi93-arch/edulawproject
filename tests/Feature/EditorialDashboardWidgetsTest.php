<?php

use App\Filament\Resources\AssignedInsights\AssignedInsightResource;
use App\Filament\Resources\Insights\InsightResource;
use App\Filament\Widgets\AdminStatsOverview;
use App\Filament\Widgets\EditorialWorkQueueWidget;
use App\Filament\Widgets\EditorialStatusOverview;
use App\Models\Insight;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Filament\Facades\Filament;
use Livewire\Livewire;

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

test('dashboard memisahkan tulisan writer dan tugas editor untuk akun dengan dua role', function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::query()->create([
        'name' => 'Writer Editor',
        'email' => 'writer-editor@example.test',
        'password' => 'password',
        'is_active' => true,
    ]);
    $user->assignRole(['writer', 'editor']);

    $otherWriter = User::query()->create([
        'name' => 'Writer Lain',
        'email' => 'writer-lain@example.test',
        'password' => 'password',
        'is_active' => true,
    ]);
    $otherWriter->assignRole('writer');

    $otherEditor = User::query()->create([
        'name' => 'Editor Lain',
        'email' => 'editor-lain@example.test',
        'password' => 'password',
        'is_active' => true,
    ]);
    $otherEditor->assignRole('editor');

    $written = Insight::query()->create([
        'title' => 'Tulisan Buatan Saya',
        'slug' => 'tulisan-buatan-saya',
        'status' => 'draft',
        'created_by' => $user->id,
    ]);
    $assigned = Insight::query()->create([
        'title' => 'Naskah Tugas Editor Saya',
        'slug' => 'naskah-tugas-editor-saya',
        'status' => 'review',
        'created_by' => $otherWriter->id,
        'assigned_editor_id' => $user->id,
    ]);
    $unrelated = Insight::query()->create([
        'title' => 'Naskah Milik Pengguna Lain',
        'slug' => 'naskah-milik-pengguna-lain',
        'status' => 'review',
        'created_by' => $otherWriter->id,
        'assigned_editor_id' => $otherEditor->id,
    ]);

    $this->actingAs($user);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    expect(InsightResource::getNavigationLabel())->toBe('Tulisan Saya')
        ->and(AssignedInsightResource::getNavigationLabel())->toBe('Tugas Editor')
        ->and(InsightResource::getEloquentQuery()->pluck('id'))
        ->toContain($written->id)
        ->not->toContain($assigned->id, $unrelated->id)
        ->and(AssignedInsightResource::getEloquentQuery()->pluck('id'))
        ->toContain($assigned->id)
        ->not->toContain($written->id, $unrelated->id)
        ->and(EditorialStatusOverview::currentUserStatusCounts())->toBe([
            'draft' => 0,
            'review' => 1,
            'published' => 0,
        ]);

    Livewire::test(AdminStatsOverview::class)
        ->assertSee('Pekerjaan Editorial Saya')
        ->assertSee('Tulisan Saya')
        ->assertSee('Tugas Editor');

    Livewire::test(EditorialWorkQueueWidget::class)
        ->assertSee('Ruang Kerja Editorial Saya')
        ->assertSee('Tulisan Saya')
        ->assertSee('Tugas Editor')
        ->assertSee('Tulisan Buatan Saya')
        ->assertSee('Naskah Tugas Editor Saya')
        ->assertDontSee('Naskah Milik Pengguna Lain');
});
