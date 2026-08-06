<?php

use App\Enums\InsightStatus;
use App\Filament\Resources\Editorial\EditorialResource;
use App\Filament\Resources\Editorial\Pages\ListEditorialInsights;
use App\Filament\Resources\Insights\InsightResource;
use App\Filament\Resources\Insights\InsightResource\Pages\EditInsight;
use App\Filament\Resources\Insights\InsightResource\Pages\ListInsights;
use App\Models\Author;
use App\Models\Insight;
use App\Models\InsightCategory;
use App\Models\User;
use App\Services\InsightCommentService;
use App\Services\InsightEditorialWorkflowService;
use Database\Seeders\RolePermissionSeeder;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function editorialUser(string $role, ?string $name = null): User
{
    $name ??= Str::headline($role);
    $user = User::query()->create([
        'name' => $name,
        'email' => Str::slug($name).'-'.Str::random(6).'@example.test',
        'password' => 'password',
        'is_active' => true,
    ]);
    $user->assignRole($role);

    return $user;
}

function completeEditorialInsight(User $writer, array $overrides = []): Insight
{
    $suffix = Str::lower(Str::random(8));
    $category = InsightCategory::query()->create([
        'name' => 'Kategori '.$suffix,
        'slug' => 'kategori-'.$suffix,
    ]);
    $author = Author::query()->create([
        'user_id' => $writer->id,
        'name' => $writer->name,
        'slug' => 'penulis-'.$suffix,
        'is_active' => true,
    ]);
    $insight = Insight::query()->create([
        'created_by' => $writer->id,
        'updated_by' => $writer->id,
        'insight_category_id' => $category->id,
        'title' => 'Naskah Editorial '.$suffix,
        'slug' => 'naskah-editorial-'.$suffix,
        'excerpt' => 'Ringkasan naskah editorial yang lengkap.',
        'content' => '<h2>Pembahasan</h2><p>Isi naskah editorial yang lengkap.</p>',
        'cover_image' => 'insights/cover-'.$suffix.'.webp',
        'status' => InsightStatus::Draft,
        ...$overrides,
    ]);
    $insight->authors()->attach($author, ['author_order' => 1, 'role' => 'Penulis']);

    return $insight->refresh();
}

function assignedEditorialInsight(User $writer, User $admin, User $editor): Insight
{
    $service = app(InsightEditorialWorkflowService::class);
    $insight = completeEditorialInsight($writer);
    $service->submit($insight, $writer);

    return $service->assignEditor($insight->fresh(), $editor, $admin);
}

test('WriterCanSubmitOwnDraftTest', function () {
    $writer = editorialUser('writer');
    $insight = completeEditorialInsight($writer);

    $result = app(InsightEditorialWorkflowService::class)->submit($insight, $writer);

    expect($result->status)->toBe(InsightStatus::Submitted)
        ->and($result->submitted_at)->not->toBeNull();
});

test('SuperAdminCanSubmitAnyCompleteDraftTest', function () {
    $writer = editorialUser('writer');
    $admin = editorialUser('super_admin');
    $insight = completeEditorialInsight($writer);

    $result = app(InsightEditorialWorkflowService::class)->submit($insight, $admin);

    expect($result->status)->toBe(InsightStatus::Submitted)
        ->and($result->submitted_at)->not->toBeNull();
});

test('DraftCanBeSubmittedFromEditPageTest', function () {
    $writer = editorialUser('writer');
    $insight = completeEditorialInsight($writer);
    Storage::fake('public');
    Storage::disk('public')->put($insight->cover_image, 'test-cover');

    $this->actingAs($writer);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $component = Livewire::test(EditInsight::class, ['record' => $insight->getRouteKey()])
        ->assertActionVisible('submit_for_review')
        ->callAction('submit_for_review');

    expect($insight->fresh()->status)->toBe(InsightStatus::Submitted);

    $component->assertNotified('Naskah berhasil dikirim');
});

test('SubmitActionIsHiddenAfterDraftHasBeenSubmittedTest', function () {
    $writer = editorialUser('writer');
    $admin = editorialUser('super_admin');
    $insight = completeEditorialInsight($writer);
    app(InsightEditorialWorkflowService::class)->submit($insight, $admin);

    $this->actingAs($admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    expect(InsightResource::canSubmitRecord($insight->fresh()))->toBeFalse();
});

test('WriterCannotSubmitIncompleteInsightTest', function () {
    $writer = editorialUser('writer');
    $insight = Insight::query()->create([
        'created_by' => $writer->id,
        'title' => 'Belum Lengkap',
        'slug' => 'belum-lengkap',
    ]);

    expect(fn () => app(InsightEditorialWorkflowService::class)->submit($insight, $writer))
        ->toThrow(ValidationException::class);
});

test('WriterGetsClearNotificationWhenSubmitIsIncompleteTest', function () {
    $writer = editorialUser('writer');
    $insight = completeEditorialInsight($writer, ['cover_image' => null]);

    $this->actingAs($writer);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(ListInsights::class)
        ->assertTableActionVisible('submit', $insight)
        ->callTableAction('submit', $insight)
        ->assertNotified('Naskah belum dapat dikirim');

    expect($insight->fresh()->status)->toBe(InsightStatus::Draft);
});

test('WriterRevisionFlowDoesNotExposePerSectionCommentActionsTest', function () {
    $writer = editorialUser('writer');
    $admin = editorialUser('super_admin');
    $editor = editorialUser('editor');
    $workflow = app(InsightEditorialWorkflowService::class);
    $insight = assignedEditorialInsight($writer, $admin, $editor);
    $workflow->startReview($insight, $editor);
    $workflow->requestRevision($insight->fresh(), $editor, 'Perbaiki naskah secara menyeluruh.');

    $this->actingAs($writer);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(ListInsights::class)
        ->assertCanSeeTableRecords([$insight])
        ->assertTableActionDoesNotExist('reply_comment', record: $insight)
        ->assertTableActionDoesNotExist('mark_comment_addressed', record: $insight);
});

test('EditorialArticleTableUsesCompactDefaultColumnsTest', function () {
    $admin = editorialUser('super_admin');
    $writer = editorialUser('writer');
    $insight = completeEditorialInsight($writer);
    $this->actingAs($admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(ListInsights::class)
        ->assertCanSeeTableRecords([$insight])
        ->assertCanRenderTableColumn('article')
        ->assertCanRenderTableColumn('status')
        ->assertCanRenderTableColumn('published_at')
        ->assertCanNotRenderTableColumn('category.name')
        ->assertCanNotRenderTableColumn('workflow_stage')
        ->assertCanNotRenderTableColumn('activeEditorAssignment.editor.name')
        ->assertCanNotRenderTableColumn('placement');
});

test('SuperAdminCanAssignEditorTest', function () {
    $writer = editorialUser('writer');
    $admin = editorialUser('super_admin');
    $editor = editorialUser('editor');
    $insight = completeEditorialInsight($writer);
    $service = app(InsightEditorialWorkflowService::class);
    $service->submit($insight, $writer);

    $result = $service->assignEditor($insight->fresh(), $editor, $admin);

    expect($result->status)->toBe(InsightStatus::EditorAssigned)
        ->and($result->assigned_editor_id)->toBe($editor->id)
        ->and($result->assigned_by)->toBe($admin->id);
});

test('SuperAdminCanAssignEditorThroughFilamentActionTest', function () {
    $writer = editorialUser('writer');
    $admin = editorialUser('super_admin');
    $editor = editorialUser('editor');
    $insight = completeEditorialInsight($writer);
    app(InsightEditorialWorkflowService::class)->submit($insight, $writer);

    $this->actingAs($admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(ListEditorialInsights::class)
        ->assertCanSeeTableRecords([$insight])
        ->assertTableActionVisible('assign_editor', $insight)
        ->callTableAction('assign_editor', $insight, [
            'editor_id' => $editor->id,
            'due_at' => null,
            'assignment_note' => 'Ditugaskan dari action Filament.',
            'send_notification' => true,
        ])
        ->assertHasNoTableActionErrors();

    expect($insight->fresh()->assigned_editor_id)->toBe($editor->id)
        ->and($insight->fresh()->status)->toBe(InsightStatus::EditorAssigned);
});

test('DraftCanBeSubmittedFromEditorialQueueTest', function () {
    $writer = editorialUser('writer');
    $admin = editorialUser('super_admin');
    $insight = completeEditorialInsight($writer);

    $this->actingAs($admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(ListEditorialInsights::class)
        ->assertCanSeeTableRecords([$insight])
        ->assertTableActionVisible('submit_for_review', $insight)
        ->assertTableActionVisible('edit_draft', $insight)
        ->assertTableActionHidden('assign_editor', $insight)
        ->callTableAction('submit_for_review', $insight)
        ->assertNotified('Naskah berhasil dikirim');

    expect($insight->fresh()->status)->toBe(InsightStatus::Submitted);

    Livewire::test(ListEditorialInsights::class)
        ->assertTableActionHidden('submit_for_review', $insight->fresh())
        ->assertTableActionVisible('assign_editor', $insight->fresh());
});

test('EditorialManagementTableUsesCompactDefaultColumnsTest', function () {
    $admin = editorialUser('super_admin');
    $writer = editorialUser('writer');
    $insight = completeEditorialInsight($writer);
    $this->actingAs($admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(ListEditorialInsights::class)
        ->assertCanSeeTableRecords([$insight])
        ->assertCanRenderTableColumn('title')
        ->assertCanRenderTableColumn('status')
        ->assertCanRenderTableColumn('activeEditorAssignment.editor.name')
        ->assertCanNotRenderTableColumn('activeEditorAssignment.due_at')
        ->assertCanNotRenderTableColumn('editorial_notes_count')
        ->assertTableColumnDoesNotExist('workflow_stage')
        ->assertTableColumnDoesNotExist('activeEditorAssignment.status')
        ->assertTableColumnDoesNotExist('authors.name')
        ->assertTableColumnDoesNotExist('category.name');
});

test('SuperAdminCanReassignEditorTest', function () {
    $writer = editorialUser('writer');
    $admin = editorialUser('super_admin');
    $firstEditor = editorialUser('editor', 'Editor Pertama');
    $replacementEditor = editorialUser('editor', 'Editor Pengganti');
    $service = app(InsightEditorialWorkflowService::class);
    $insight = assignedEditorialInsight($writer, $admin, $firstEditor);

    $result = $service->assignEditor(
        $insight->fresh(),
        $replacementEditor,
        $admin,
        now()->addDays(5),
        'Menyesuaikan bidang keahlian Editor.',
    );

    expect($result->status)->toBe(InsightStatus::EditorAssigned)
        ->and($result->assigned_editor_id)->toBe($replacementEditor->id)
        ->and($result->assigned_by)->toBe($admin->id)
        ->and($result->statusHistories()->latest('id')->value('notes'))
        ->toContain('Editor diganti', 'Menyesuaikan bidang keahlian Editor.');
});

test('EditorListOnlyContainsActiveEditorsTest', function () {
    $activeEditor = editorialUser('editor', 'Editor Aktif');
    $inactiveEditor = editorialUser('editor', 'Editor Nonaktif');
    $inactiveEditor->update(['is_active' => false]);
    $writer = editorialUser('writer', 'Bukan Editor');

    Role::findOrCreate('Editor', 'web');
    $legacyEditor = User::query()->create([
        'name' => 'Editor Legacy Aktif',
        'email' => 'editor-legacy-aktif@example.test',
        'password' => 'password',
        'is_active' => true,
    ]);
    $legacyEditor->assignRole('Editor');

    $options = EditorialResource::editorOptions();

    expect($options)
        ->toHaveKey($activeEditor->id)
        ->toHaveKey($legacyEditor->id)
        ->not->toHaveKey($inactiveEditor->id)
        ->not->toHaveKey($writer->id);
});

test('NonSuperAdminCannotAssignEditorTest', function () {
    $writer = editorialUser('writer');
    $editor = editorialUser('editor');
    $otherEditor = editorialUser('editor', 'Editor Lain');
    $insight = completeEditorialInsight($writer);
    $service = app(InsightEditorialWorkflowService::class);
    $service->submit($insight, $writer);

    expect(fn () => $service->assignEditor($insight->fresh(), $otherEditor, $editor))
        ->toThrow(AuthorizationException::class);
});

test('UserWithAssignEditorPermissionCanAssignEditorTest', function () {
    $writer = editorialUser('writer');
    $manager = editorialUser('writer', 'Manajer Editorial');
    $manager->givePermissionTo('assign_editor');
    $editor = editorialUser('editor');
    $service = app(InsightEditorialWorkflowService::class);
    $insight = completeEditorialInsight($writer);
    $service->submit($insight, $writer);

    $result = $service->assignEditor($insight->fresh(), $editor, $manager);

    expect($result->assigned_editor_id)->toBe($editor->id)
        ->and($manager->can('assignEditor', $result))->toBeFalse()
        ->and($manager->can('reassignEditor', $result))->toBeFalse();
});

test('RolePermissionSeederPreservesExistingPermissionsTest', function () {
    $customPermission = Permission::findOrCreate('custom_editorial_permission', 'web');
    $editorRole = Role::findByName('editor', 'web');
    $editorRole->givePermissionTo($customPermission);

    $this->seed(RolePermissionSeeder::class);
    $this->seed(RolePermissionSeeder::class);

    expect($editorRole->fresh()->hasPermissionTo($customPermission))->toBeTrue()
        ->and(Role::query()->where('name', 'super_admin')->where('guard_name', 'web')->count())->toBe(1)
        ->and(Permission::query()->where('name', 'assign_editor')->where('guard_name', 'web')->count())->toBe(1)
        ->and(Permission::query()->where('name', 'reassign_editor')->where('guard_name', 'web')->count())->toBe(1);
});

test('AssignmentChangesStatusToEditorAssignedTest', function () {
    $writer = editorialUser('writer');
    $admin = editorialUser('super_admin');
    $editor = editorialUser('editor');
    $service = app(InsightEditorialWorkflowService::class);
    $insight = completeEditorialInsight($writer);
    $service->submit($insight, $writer);

    $result = $service->assignEditor($insight->fresh(), $editor, $admin);

    expect($result->status)->toBe(InsightStatus::EditorAssigned)
        ->and($result->assigned_at)->not->toBeNull();
});

test('AssignmentCreatesStatusHistoryTest', function () {
    $writer = editorialUser('writer');
    $admin = editorialUser('super_admin');
    $editor = editorialUser('editor');
    $service = app(InsightEditorialWorkflowService::class);
    $insight = completeEditorialInsight($writer);
    $service->submit($insight, $writer);

    $service->assignEditor($insight->fresh(), $editor, $admin, null, 'Prioritas pekan ini.');

    $this->assertDatabaseHas('insight_status_histories', [
        'insight_id' => $insight->id,
        'changed_by' => $admin->id,
        'from_status' => InsightStatus::Submitted->value,
        'to_status' => InsightStatus::EditorAssigned->value,
    ]);

    expect($insight->statusHistories()->latest('id')->value('notes'))
        ->toContain('ditugaskan', 'Prioritas pekan ini.');
});

test('CannotAssignEditorToInvalidStatusTest', function () {
    $writer = editorialUser('writer');
    $admin = editorialUser('super_admin');
    $editor = editorialUser('editor');
    $insight = completeEditorialInsight($writer);

    expect(fn () => app(InsightEditorialWorkflowService::class)->assignEditor($insight, $editor, $admin))
        ->toThrow(LogicException::class);
});

test('EditorCanViewAssignedInsightTest', function () {
    $writer = editorialUser('writer');
    $admin = editorialUser('super_admin');
    $editor = editorialUser('editor');
    $insight = assignedEditorialInsight($writer, $admin, $editor);

    expect($editor->can('view', $insight))->toBeTrue();
});

test('EditorCannotViewUnassignedInsightTest', function () {
    $writer = editorialUser('writer');
    $editor = editorialUser('editor');
    $insight = completeEditorialInsight($writer);

    expect($editor->can('view', $insight))->toBeFalse();
});

test('EditorCanStartReviewTest', function () {
    $writer = editorialUser('writer');
    $admin = editorialUser('super_admin');
    $editor = editorialUser('editor');
    $insight = assignedEditorialInsight($writer, $admin, $editor);

    $result = app(InsightEditorialWorkflowService::class)->startReview($insight, $editor);

    expect($result->status)->toBe(InsightStatus::InReview)
        ->and($result->review_started_at)->not->toBeNull();
});

test('EditorCanRequestRevisionTest', function () {
    $writer = editorialUser('writer');
    $admin = editorialUser('super_admin');
    $editor = editorialUser('editor');
    $service = app(InsightEditorialWorkflowService::class);
    $insight = assignedEditorialInsight($writer, $admin, $editor);
    $service->startReview($insight, $editor);

    $result = $service->requestRevision($insight->fresh(), $editor, 'Perjelas dasar hukumnya.', now()->addDays(3));

    expect($result->status)->toBe(InsightStatus::RevisionRequested)
        ->and($result->editorialNotes()->where('type', 'revision_request')->where('is_visible_to_writer', true)->exists())->toBeTrue();
});

test('WriterCanEditInsightWhenRevisionRequestedTest', function () {
    $writer = editorialUser('writer');
    $admin = editorialUser('super_admin');
    $editor = editorialUser('editor');
    $service = app(InsightEditorialWorkflowService::class);
    $insight = assignedEditorialInsight($writer, $admin, $editor);
    $service->startReview($insight, $editor);
    $insight = $service->requestRevision($insight->fresh(), $editor, 'Perbaiki analisis.');

    expect($writer->can('update', $insight))->toBeTrue();
});

test('WriterCannotEditInsightWhileInReviewTest', function () {
    $writer = editorialUser('writer');
    $admin = editorialUser('super_admin');
    $editor = editorialUser('editor');
    $insight = assignedEditorialInsight($writer, $admin, $editor);
    $insight = app(InsightEditorialWorkflowService::class)->startReview($insight, $editor);

    expect($writer->can('update', $insight))->toBeFalse();
});

test('WriterCanResubmitRevisionTest', function () {
    $writer = editorialUser('writer');
    $admin = editorialUser('super_admin');
    $editor = editorialUser('editor');
    $service = app(InsightEditorialWorkflowService::class);
    $insight = assignedEditorialInsight($writer, $admin, $editor);
    $service->startReview($insight, $editor);
    $service->requestRevision($insight->fresh(), $editor, 'Perbaiki analisis.');
    $insight->update(['content' => '<h2>Pembahasan Diperbarui</h2><p>Isi dan dasar hukum telah diperbaiki.</p>']);

    $result = $service->resubmit($insight->fresh(), $writer, 'Dasar hukum dan analisis telah diperbarui.');

    expect($result->status)->toBe(InsightStatus::Revised)
        ->and($result->revision_round)->toBe(1)
        ->and($result->revised_at)->not->toBeNull();
});

test('EditorCanApproveRevisedInsightTest', function () {
    $writer = editorialUser('writer');
    $admin = editorialUser('super_admin');
    $editor = editorialUser('editor');
    $service = app(InsightEditorialWorkflowService::class);
    $insight = assignedEditorialInsight($writer, $admin, $editor);
    $service->startReview($insight, $editor);
    $service->requestRevision($insight->fresh(), $editor, 'Perbaiki analisis.');
    $insight->update(['content' => '<h2>Pembahasan Diperbarui</h2><p>Analisis telah diperbaiki.</p>']);
    $service->resubmit($insight->fresh(), $writer, 'Analisis diperbarui.');
    $service->startReview($insight->fresh(), $editor);
    $revisionRequest = $insight->editorialNotes()->where('type', 'revision_request')->firstOrFail();
    app(InsightCommentService::class)->resolve($revisionRequest, $editor);

    $result = $service->approve($insight->fresh(), $editor);

    expect($result->status)->toBe(InsightStatus::Approved)
        ->and($result->approved_by)->toBe($editor->id)
        ->and($result->reviewed_by)->toBe($editor->id);
});

test('OnlyApprovedInsightCanBePublishedTest', function () {
    $writer = editorialUser('writer');
    $admin = editorialUser('super_admin');
    $insight = completeEditorialInsight($writer);

    expect(fn () => app(InsightEditorialWorkflowService::class)->publish($insight, $admin))
        ->toThrow(LogicException::class);
});

test('PublishedInsightAppearsOnPublicPageTest', function () {
    $writer = editorialUser('writer');
    $admin = editorialUser('super_admin');
    $editor = editorialUser('editor');
    $service = app(InsightEditorialWorkflowService::class);
    $insight = assignedEditorialInsight($writer, $admin, $editor);
    $service->startReview($insight, $editor);
    $service->approve($insight->fresh(), $editor);
    $insight = $service->publish($insight->fresh(), $admin);

    $this->get(route('insights.show', $insight->slug))
        ->assertOk()
        ->assertSee($insight->title);
});

test('EditorialStatusHistoryIsCreatedTest', function () {
    $writer = editorialUser('writer');
    $insight = completeEditorialInsight($writer);

    app(InsightEditorialWorkflowService::class)->submit($insight, $writer);

    $this->assertDatabaseHas('insight_status_histories', [
        'insight_id' => $insight->id,
        'changed_by' => $writer->id,
        'from_status' => 'draft',
        'to_status' => 'submitted',
    ]);
});
