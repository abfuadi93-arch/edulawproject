<?php

use App\Enums\InsightStatus;
use App\Filament\Resources\AssignedInsights\AssignedInsightResource;
use App\Filament\Resources\AssignedInsights\Pages\ListAssignedInsights;
use App\Filament\Resources\Editorial\EditorialResource;
use App\Filament\Resources\Editorial\Pages\ViewEditorialWorkspace;
use App\Filament\Resources\Insights\InsightResource;
use App\Filament\Resources\Insights\InsightResource\Pages\EditInsight;
use App\Models\Author;
use App\Models\Insight;
use App\Models\InsightCategory;
use App\Models\User;
use App\Services\InsightEditorialWorkflowService;
use Database\Seeders\RolePermissionSeeder;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function simpleEditorialUser(string $role, ?string $name = null): User
{
    $name ??= Str::headline($role).' '.Str::random(5);
    $user = User::query()->create([
        'name' => $name,
        'email' => Str::slug($name).Str::random(4).'@example.test',
        'password' => 'password',
        'is_active' => true,
    ]);
    $user->assignRole($role);

    return $user;
}

function simpleEditorialInsight(User $writer, array $overrides = []): Insight
{
    $suffix = Str::lower(Str::random(8));
    $category = InsightCategory::query()->create(['name' => 'Kategori '.$suffix, 'slug' => 'kategori-'.$suffix]);
    $author = Author::query()->firstOrCreate(
        ['user_id' => $writer->id],
        [
            'name' => $writer->name,
            'slug' => 'writer-'.$suffix,
            'is_active' => true,
        ],
    );
    $insight = Insight::query()->create([
        'created_by' => $writer->id,
        'updated_by' => $writer->id,
        'insight_category_id' => $category->id,
        'title' => 'Naskah '.$suffix,
        'slug' => 'naskah-'.$suffix,
        'excerpt' => 'Excerpt artikel yang lengkap.',
        'content' => '<h2>Pembahasan</h2><p>Isi artikel yang lengkap.</p>',
        'cover_image' => 'insights/'.$suffix.'.webp',
        'status' => InsightStatus::Draft,
        ...$overrides,
    ]);
    $insight->authors()->attach($author, ['author_order' => 1, 'role' => 'Penulis']);

    return $insight->refresh();
}

test('writer dapat membuat dan mengedit draft miliknya', function () {
    $writer = simpleEditorialUser('writer');
    $insight = simpleEditorialInsight($writer);

    expect($writer->can('create', Insight::class))->toBeTrue()
        ->and($insight->status)->toBe(InsightStatus::Draft)
        ->and($writer->can('update', $insight))->toBeTrue();
});

test('writer dapat mengirim draft ke review dan mengirim ulang setelah perbaikan', function () {
    $writer = simpleEditorialUser('writer');
    $admin = simpleEditorialUser('super_admin');
    $editor = simpleEditorialUser('editor');
    $service = app(InsightEditorialWorkflowService::class);
    $insight = simpleEditorialInsight($writer);

    $insight = $service->submit($insight, $writer);
    expect($insight->status)->toBe(InsightStatus::Review)
        ->and($insight->submitted_at)->not->toBeNull();

    $service->assignEditor($insight, $editor, $admin);
    $insight = $service->requestRevision($insight->fresh(), $editor, 'Perjelas dasar hukum.');
    $insight->update(['content' => '<h2>Revisi</h2><p>Dasar hukum sudah diperjelas.</p>']);
    $insight = $service->submit($insight->fresh(), $writer);

    expect($insight->status)->toBe(InsightStatus::Review)
        ->and($insight->editorialActivities()->where('event', 'resubmitted_for_review')->exists())->toBeTrue();
});

test('pengiriman ganda dari state halaman lama tetap aman dan tidak mencatat ulang', function () {
    $writer = simpleEditorialUser('writer');
    $service = app(InsightEditorialWorkflowService::class);
    $insight = simpleEditorialInsight($writer);
    $firstRequestRecord = $insight->fresh();
    $delayedRequestRecord = $insight->fresh();

    $firstResult = $service->submit($firstRequestRecord, $writer);
    $secondResult = $service->submit($delayedRequestRecord, $writer);

    expect($firstResult->status)->toBe(InsightStatus::Review)
        ->and($secondResult->status)->toBe(InsightStatus::Review)
        ->and($insight->statusHistories()->where('to_status', InsightStatus::Review->value)->count())->toBe(1)
        ->and($insight->editorialActivities()->where('event', 'submitted_for_review')->count())->toBe(1);
});

test('writer tidak dapat menerbitkan insight', function () {
    $writer = simpleEditorialUser('writer');
    $insight = simpleEditorialInsight($writer, ['status' => InsightStatus::Review]);

    expect(fn () => app(InsightEditorialWorkflowService::class)->publish($insight, $writer))
        ->toThrow(AuthorizationException::class);
});

test('writer tidak dapat mengedit insight orang lain atau insight yang sedang review', function () {
    $writer = simpleEditorialUser('writer');
    $otherWriter = simpleEditorialUser('writer');
    $ownReview = simpleEditorialInsight($writer, ['status' => InsightStatus::Review]);
    $otherDraft = simpleEditorialInsight($otherWriter);

    expect($writer->can('update', $otherDraft))->toBeFalse()
        ->and($writer->can('update', $ownReview))->toBeFalse();
});

test('super admin dapat menugaskan dan mengganti editor tanpa assignment lifecycle', function () {
    $writer = simpleEditorialUser('writer');
    $admin = simpleEditorialUser('super_admin');
    $firstEditor = simpleEditorialUser('editor');
    $secondEditor = simpleEditorialUser('editor');
    $insight = simpleEditorialInsight($writer, ['status' => InsightStatus::Review]);
    $service = app(InsightEditorialWorkflowService::class);

    $assigned = $service->assignEditor($insight, $firstEditor, $admin);
    $changed = $service->assignEditor($assigned, $secondEditor, $admin);

    expect($changed->assigned_editor_id)->toBe($secondEditor->id)
        ->and($changed->assigned_at)->not->toBeNull()
        ->and($changed->editorialActivities()->where('event', 'editor_assigned')->exists())->toBeTrue()
        ->and($changed->editorialActivities()->where('event', 'editor_changed')->exists())->toBeTrue();
});

test('editor hanya melihat Tugas Editor yang ditugaskan kepadanya', function () {
    $writer = simpleEditorialUser('writer');
    $admin = simpleEditorialUser('super_admin');
    $editor = simpleEditorialUser('editor');
    $otherEditor = simpleEditorialUser('editor');
    $mine = simpleEditorialInsight($writer, ['status' => InsightStatus::Review]);
    $other = simpleEditorialInsight($writer, ['status' => InsightStatus::Review]);
    $service = app(InsightEditorialWorkflowService::class);
    $service->assignEditor($mine, $editor, $admin);
    $service->assignEditor($other, $otherEditor, $admin);

    $this->actingAs($editor);
    $ids = AssignedInsightResource::getEloquentQuery()->pluck('id');

    expect($ids)->toContain($mine->id)->not->toContain($other->id);
});

test('role editor tetap dapat melihat naskah tugas saat permission editorial belum tersinkron', function () {
    $writer = simpleEditorialUser('writer');
    $admin = simpleEditorialUser('super_admin');
    $editor = simpleEditorialUser('editor');
    $otherEditor = simpleEditorialUser('editor');
    $mine = simpleEditorialInsight($writer, ['status' => InsightStatus::Review]);
    $other = simpleEditorialInsight($writer, ['status' => InsightStatus::Review]);
    $service = app(InsightEditorialWorkflowService::class);
    $service->assignEditor($mine, $editor, $admin);
    $service->assignEditor($other, $otherEditor, $admin);
    $mine->refresh();
    $other->refresh();

    $editor->roles()->firstOrFail()->revokePermissionTo([
        'view_assigned_editorial_insights',
        'view_assigned_editorial_submissions',
        'access_editorial_workspace',
    ]);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $editor->unsetRelation('roles')->unsetRelation('permissions');

    $this->actingAs($editor);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    expect(AssignedInsightResource::canViewAny())->toBeTrue()
        ->and(EditorialResource::canViewAny())->toBeTrue()
        ->and(AssignedInsightResource::getEloquentQuery()->pluck('id'))
        ->toContain($mine->id)
        ->not->toContain($other->id)
        ->and(InsightResource::getEloquentQuery()->pluck('id'))
        ->not->toContain($mine->id)
        ->not->toContain($other->id)
        ->and($editor->can('view', $mine))->toBeTrue()
        ->and($editor->can('view', $other))->toBeFalse()
        ->and($editor->can('accessEditorialWorkspace', $mine))->toBeTrue()
        ->and($editor->can('accessEditorialWorkspace', $other))->toBeFalse();

    $this->get(EditorialResource::getUrl('workspace', ['record' => $mine]))->assertOk();

    Livewire::test(ListAssignedInsights::class)
        ->assertCanSeeTableRecords([$mine])
        ->assertCanNotSeeTableRecords([$other]);
});

test('permission lama tidak membuka semua insight atau aksi administratif bagi editor', function () {
    $writer = simpleEditorialUser('writer');
    $admin = simpleEditorialUser('super_admin');
    $editor = simpleEditorialUser('editor');
    $mine = simpleEditorialInsight($writer, ['status' => InsightStatus::Review]);
    $other = simpleEditorialInsight($writer, ['status' => InsightStatus::Review]);
    app(InsightEditorialWorkflowService::class)->assignEditor($mine, $editor, $admin);

    $editor->givePermissionTo(['view_all_editorial_insights', 'assign_editor', 'archive_insight', 'delete all insights']);
    $this->actingAs($editor);

    expect(InsightResource::getEloquentQuery()->pluck('id'))
        ->not->toContain($mine->id)
        ->not->toContain($other->id)
        ->and($editor->can('assignEditor', $other))->toBeFalse()
        ->and($editor->can('archive', $mine))->toBeFalse()
        ->and($editor->can('delete', $mine))->toBeFalse();
});

test('editor dapat meminta perbaikan dan naskah kembali menjadi draft', function () {
    $writer = simpleEditorialUser('writer');
    $admin = simpleEditorialUser('super_admin');
    $editor = simpleEditorialUser('editor');
    $service = app(InsightEditorialWorkflowService::class);
    $insight = simpleEditorialInsight($writer, ['status' => InsightStatus::Review]);
    $insight = $service->assignEditor($insight, $editor, $admin);

    $result = $service->requestRevision($insight, $editor, 'Tambahkan rujukan yang relevan.');

    expect($result->status)->toBe(InsightStatus::Draft)
        ->and($result->editor_notes)->toBe('Tambahkan rujukan yang relevan.')
        ->and($writer->can('update', $result))->toBeTrue()
        ->and($result->editorialNotes()->where('type', 'revision_request')->exists())->toBeTrue();
});

test('menyimpan catatan editor membuka kembali akses edit penulis', function () {
    $writer = simpleEditorialUser('writer');
    $admin = simpleEditorialUser('super_admin');
    $editor = simpleEditorialUser('editor');
    $service = app(InsightEditorialWorkflowService::class);
    $insight = $service->assignEditor(
        simpleEditorialInsight($writer, ['status' => InsightStatus::Review]),
        $editor,
        $admin,
    );

    $result = $service->addEditorialNote($insight, $editor, 'Perjelas argumentasi pada bagian penutup.');

    expect($result->status)->toBe(InsightStatus::Draft)
        ->and($result->editor_notes)->toBe('Perjelas argumentasi pada bagian penutup.')
        ->and($result->revision_requested_at)->not->toBeNull()
        ->and($writer->can('update', $result))->toBeTrue()
        ->and($result->editorialNotes()->where('type', 'note')->exists())->toBeTrue()
        ->and($result->statusHistories()->where('from_status', 'review')->where('to_status', 'draft')->exists())->toBeTrue()
        ->and($result->editorialActivities()->where('event', 'editor_note_saved')->where('to_status', 'draft')->exists())->toBeTrue()
        ->and($writer->notifications()->latest()->first()?->data['notification_type'] ?? null)->toBe('revision_requested');
});

test('migrasi membuka catatan lama hanya jika belum ada pengiriman ulang', function () {
    $writer = simpleEditorialUser('writer');
    $admin = simpleEditorialUser('super_admin');
    $editor = simpleEditorialUser('editor');
    $service = app(InsightEditorialWorkflowService::class);

    $waitingForWriter = $service->assignEditor(
        simpleEditorialInsight($writer, ['status' => InsightStatus::Review]),
        $editor,
        $admin,
    );
    $waitingForWriter->editorialActivities()->create([
        'actor_id' => $editor->id,
        'event' => 'editor_note_saved',
        'description' => 'Editor menyimpan catatan untuk Penulis.',
    ]);

    $alreadyResubmitted = $service->assignEditor(
        simpleEditorialInsight($writer, ['status' => InsightStatus::Review]),
        $editor,
        $admin,
    );
    $alreadyResubmitted->editorialActivities()->create([
        'actor_id' => $editor->id,
        'event' => 'editor_note_saved',
        'description' => 'Editor menyimpan catatan untuk Penulis.',
    ]);
    $alreadyResubmitted->editorialActivities()->create([
        'actor_id' => $writer->id,
        'event' => 'resubmitted_for_review',
        'description' => 'Penulis mengirim ulang naskah untuk review.',
    ]);

    $migration = require database_path('migrations/2026_08_17_170000_reopen_insights_after_editor_notes.php');
    $migration->up();

    expect($waitingForWriter->fresh()->status)->toBe(InsightStatus::Draft)
        ->and($waitingForWriter->statusHistories()->where('from_status', 'review')->where('to_status', 'draft')->exists())->toBeTrue()
        ->and($alreadyResubmitted->fresh()->status)->toBe(InsightStatus::Review);
});

test('catatan editor wajib saat meminta perbaikan', function () {
    $writer = simpleEditorialUser('writer');
    $admin = simpleEditorialUser('super_admin');
    $editor = simpleEditorialUser('editor');
    $service = app(InsightEditorialWorkflowService::class);
    $insight = $service->assignEditor(simpleEditorialInsight($writer, ['status' => InsightStatus::Review]), $editor, $admin);

    expect(fn () => $service->requestRevision($insight, $editor, ''))
        ->toThrow(ValidationException::class, 'Catatan untuk Penulis wajib diisi');
});

test('editor dapat menerbitkan dan metadata review terisi', function () {
    $writer = simpleEditorialUser('writer');
    $admin = simpleEditorialUser('super_admin');
    $editor = simpleEditorialUser('editor');
    $service = app(InsightEditorialWorkflowService::class);
    $insight = $service->assignEditor(simpleEditorialInsight($writer, ['status' => InsightStatus::Review]), $editor, $admin);

    $published = $service->publish($insight, $editor);

    expect($published->status)->toBe(InsightStatus::Published)
        ->and($published->reviewed_by)->toBe($editor->id)
        ->and($published->reviewed_at)->not->toBeNull()
        ->and($published->published_at)->not->toBeNull();
});

test('editor dapat menjadwalkan terbit dan artikel baru tampil saat waktunya tiba', function () {
    $writer = simpleEditorialUser('writer');
    $admin = simpleEditorialUser('super_admin');
    $editor = simpleEditorialUser('editor');
    $scheduledFor = now()->addDays(2)->startOfMinute();
    $service = app(InsightEditorialWorkflowService::class);
    $insight = $service->assignEditor(simpleEditorialInsight($writer, [
        'status' => InsightStatus::Review,
        'published_at' => $scheduledFor,
    ]), $editor, $admin);

    $published = $service->publish($insight, $editor);

    expect($published->status)->toBe(InsightStatus::Published)
        ->and($published->published_at?->equalTo($scheduledFor))->toBeTrue()
        ->and($published->editorialActivities()->where('event', 'published')->value('description'))->toContain('menjadwalkan artikel')
        ->and($writer->notifications()->latest()->first()?->data['title'] ?? null)->toBe('Artikel dijadwalkan');

    $this->get(route('insights.show', $published->slug))->assertNotFound();

    $this->travelTo($scheduledFor->copy()->addMinute());
    $this->get(route('insights.show', $published->slug))->assertOk();
    $this->travelBack();
});

test('hanya artikel published yang tampil pada website publik', function () {
    $writer = simpleEditorialUser('writer');
    $published = simpleEditorialInsight($writer, ['status' => InsightStatus::Published, 'published_at' => now()]);
    $draft = simpleEditorialInsight($writer);
    $review = simpleEditorialInsight($writer, ['status' => InsightStatus::Review]);

    $this->get(route('insights.show', $published->slug))->assertOk();
    $this->get(route('insights.show', $draft->slug))->assertNotFound();
    $this->get(route('insights.show', $review->slug))->assertNotFound();
});

test('policy mencegah editor membuka workspace yang tidak ditugaskan', function () {
    $writer = simpleEditorialUser('writer');
    $editor = simpleEditorialUser('editor');
    $insight = simpleEditorialInsight($writer, ['status' => InsightStatus::Review]);

    expect($editor->can('accessEditorialWorkspace', $insight))->toBeFalse();

    $this->actingAs($editor)
        ->get(EditorialResource::getUrl('workspace', ['record' => $insight]))
        ->assertNotFound();
});

test('workspace editor hanya menampilkan action sederhana', function () {
    $writer = simpleEditorialUser('writer');
    $admin = simpleEditorialUser('super_admin');
    $editor = simpleEditorialUser('editor');
    $insight = app(InsightEditorialWorkflowService::class)->assignEditor(
        simpleEditorialInsight($writer, ['status' => InsightStatus::Review]),
        $editor,
        $admin,
    );

    $this->actingAs($editor);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(ViewEditorialWorkspace::class, ['record' => $insight->getRouteKey()])
        ->assertSee('Naskah')
        ->assertSee('Informasi Editorial')
        ->assertSee('Catatan untuk Penulis')
        ->assertFormFieldExists('published_at')
        ->assertActionVisible('save')
        ->assertActionVisible('request_revision')
        ->assertActionVisible('publish')
        ->assertActionHidden('assign_editor');
});

test('tombol simpan workspace mengembalikan naskah ke penulis saat catatan diisi', function () {
    $writer = simpleEditorialUser('writer');
    $admin = simpleEditorialUser('super_admin');
    $editor = simpleEditorialUser('editor');
    $insight = app(InsightEditorialWorkflowService::class)->assignEditor(
        simpleEditorialInsight($writer, ['status' => InsightStatus::Review]),
        $editor,
        $admin,
    );

    $this->actingAs($editor);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(ViewEditorialWorkspace::class, ['record' => $insight->getRouteKey()])
        ->set('data.editor_notes', 'Perbaiki uraian dan tambahkan sumber primer.')
        ->callAction('save')
        ->assertHasNoActionErrors();

    $insight->refresh();

    expect($insight->status)->toBe(InsightStatus::Draft)
        ->and($writer->can('update', $insight))->toBeTrue();

    $this->actingAs($writer)
        ->get(InsightResource::getUrl('edit', ['record' => $insight]))
        ->assertOk();
});

test('action Terbitkan menyimpan Jadwal Terbit dari workspace', function () {
    $writer = simpleEditorialUser('writer');
    $admin = simpleEditorialUser('super_admin');
    $editor = simpleEditorialUser('editor');
    $scheduledFor = now()->addDays(3)->startOfMinute();
    $insight = app(InsightEditorialWorkflowService::class)->assignEditor(
        simpleEditorialInsight($writer, ['status' => InsightStatus::Review]),
        $editor,
        $admin,
    );

    $this->actingAs($editor);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(ViewEditorialWorkspace::class, ['record' => $insight->getRouteKey()])
        ->set('data.published_at', $scheduledFor->copy()->timezone(config('edulaw.timezone'))->format('Y-m-d H:i:s'))
        ->callAction('publish')
        ->assertHasNoActionErrors();

    $insight->refresh();

    expect($insight->status)->toBe(InsightStatus::Published)
        ->and($insight->published_at?->equalTo($scheduledFor))->toBeTrue();
});

test('form writer hanya menyediakan simpan draft dan kirim untuk review', function () {
    $writer = simpleEditorialUser('writer');
    $insight = simpleEditorialInsight($writer);

    $this->actingAs($writer);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(EditInsight::class, ['record' => $insight->getRouteKey()])
        ->assertSee('Konten')
        ->assertSee('Metadata')
        ->assertSee('Editorial')
        ->assertSee('Status')
        ->assertSee('Belum ditugaskan')
        ->assertActionVisible('submit_for_review');
});

test('tabel Tugas Editor memakai kolom ringkas tanpa workflow teknis', function () {
    $writer = simpleEditorialUser('writer');
    $admin = simpleEditorialUser('super_admin');
    $editor = simpleEditorialUser('editor');
    $insight = app(InsightEditorialWorkflowService::class)->assignEditor(
        simpleEditorialInsight($writer, ['status' => InsightStatus::Review]),
        $editor,
        $admin,
    );

    $this->actingAs($editor);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(ListAssignedInsights::class)
        ->assertCanSeeTableRecords([$insight])
        ->assertCanRenderTableColumn('title')
        ->assertCanRenderTableColumn('status')
        ->assertCanRenderTableColumn('submitted_at')
        ->assertCanRenderTableColumn('updated_at')
        ->assertTableColumnDoesNotExist('workflow_stage');
});

test('published tidak dapat kembali ke draft melalui transisi biasa', function () {
    $writer = simpleEditorialUser('writer');
    $published = simpleEditorialInsight($writer, ['status' => InsightStatus::Published, 'published_at' => now()]);

    expect(fn () => app(InsightEditorialWorkflowService::class)->submit($published, $writer))
        ->toThrow(AuthorizationException::class);
});
