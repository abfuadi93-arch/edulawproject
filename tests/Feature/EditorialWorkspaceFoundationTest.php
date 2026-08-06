<?php

use App\Enums\EditorAssignmentStatus;
use App\Enums\EditorialActivityType;
use App\Enums\EditorialCommentStatus;
use App\Enums\EditorialCommentType;
use App\Enums\EditorialDecisionType;
use App\Enums\EditorialWorkflowStage;
use App\Enums\InsightStatus;
use App\Filament\Resources\AssignedInsights\AssignedInsightResource;
use App\Filament\Resources\AssignedInsights\Pages\ListAssignedInsights;
use App\Filament\Resources\Editorial\EditorialResource;
use App\Filament\Resources\Editorial\Pages\ViewEditorialWorkspace;
use App\Models\Author;
use App\Models\Insight;
use App\Models\InsightCategory;
use App\Models\InsightEditorAssignment;
use App\Models\User;
use App\Services\Editorial\InsightAssignmentService;
use App\Services\Editorial\InsightNotificationService;
use App\Services\Editorial\InsightWorkflowService;
use Database\Seeders\RolePermissionSeeder;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function ewUser(?string $role = null, ?string $name = null): User
{
    $name ??= Str::headline($role ?: 'user').' '.Str::random(5);
    $user = User::query()->create([
        'name' => $name,
        'email' => Str::slug($name).'-'.Str::random(8).'@workspace.test',
        'password' => 'password',
        'is_active' => true,
    ]);

    if ($role) {
        $user->assignRole($role);
    }

    return $user;
}

function ewInsight(User $writer, InsightStatus $status = InsightStatus::Submitted, ?EditorialWorkflowStage $stage = null): Insight
{
    $suffix = Str::lower(Str::random(10));
    $category = InsightCategory::query()->create([
        'name' => 'Workspace '.$suffix,
        'slug' => 'workspace-'.$suffix,
    ]);
    $author = Author::query()->firstOrCreate(
        ['user_id' => $writer->id],
        [
            'name' => $writer->name,
            'slug' => 'workspace-author-'.$suffix,
            'is_active' => true,
        ],
    );
    $stage ??= match ($status) {
        InsightStatus::EditorAssigned, InsightStatus::InReview, InsightStatus::Revised => EditorialWorkflowStage::EditorialReview,
        InsightStatus::RevisionRequested => EditorialWorkflowStage::AuthorRevision,
        InsightStatus::Approved, InsightStatus::Rejected => EditorialWorkflowStage::FinalApproval,
        InsightStatus::Published, InsightStatus::Archived => EditorialWorkflowStage::Publication,
        default => EditorialWorkflowStage::Submission,
    };
    $insight = Insight::query()->create([
        'created_by' => $writer->id,
        'updated_by' => $writer->id,
        'insight_category_id' => $category->id,
        'title' => 'Naskah Workspace '.$suffix,
        'slug' => 'naskah-workspace-'.$suffix,
        'excerpt' => 'Ringkasan lengkap untuk pengujian workspace editorial.',
        'content' => '<h2>Analisis</h2><p>Isi lengkap naskah editorial.</p>',
        'cover_image' => 'insights/workspace-'.$suffix.'.webp',
        'status' => $status,
        'workflow_stage' => $stage,
        'submitted_at' => $status === InsightStatus::Draft ? null : now(),
        'published_at' => $status === InsightStatus::Published ? now()->subMinute() : null,
    ]);
    $insight->authors()->attach($author, ['author_order' => 1, 'role' => 'Penulis']);

    return $insight->refresh();
}

function ewAssign(Insight $insight, User $editor, User $admin): InsightEditorAssignment
{
    return app(InsightAssignmentService::class)->assignEditor($insight, $editor, $admin, now()->addDays(2), 'Penugasan fondasi editorial.');
}

test('NonAuthorizedUserCannotAssignEditorTest', function () {
    $writer = ewUser('writer');
    $actor = ewUser();
    $editor = ewUser('editor');

    expect(fn () => ewAssign(ewInsight($writer), $editor, $actor))
        ->toThrow(AuthorizationException::class);
});

test('WriterCannotAssignEditorTest', function () {
    $writer = ewUser('writer');
    $editor = ewUser('editor');

    expect(fn () => ewAssign(ewInsight($writer), $editor, $writer))
        ->toThrow(AuthorizationException::class);
});

test('EditorCannotAssignThemselfWithoutPermissionTest', function () {
    $writer = ewUser('writer');
    $editor = ewUser('editor');

    expect(fn () => ewAssign(ewInsight($writer), $editor, $editor))
        ->toThrow(AuthorizationException::class);
});

test('OnlyActiveEditorCanBeAssignedTest', function () {
    $writer = ewUser('writer');
    $admin = ewUser('super_admin');
    $editor = ewUser('editor');
    $editor->update(['is_active' => false]);

    expect(fn () => ewAssign(ewInsight($writer), $editor->fresh(), $admin))
        ->toThrow(ValidationException::class);
});

test('UserWithoutEditorRoleCannotBeAssignedTest', function () {
    $writer = ewUser('writer');
    $admin = ewUser('super_admin');
    $notEditor = ewUser();

    expect(fn () => ewAssign(ewInsight($writer), $notEditor, $admin))
        ->toThrow(ValidationException::class);
});

test('CannotCreateTwoActiveAssignmentsTest', function () {
    $writer = ewUser('writer');
    $admin = ewUser('super_admin');
    $first = ewUser('editor');
    $second = ewUser('editor');
    $insight = ewInsight($writer);
    ewAssign($insight, $first, $admin);

    expect(fn () => ewAssign($insight->fresh(), $second, $admin))
        ->toThrow(LogicException::class);

    expect($insight->editorAssignments()->active()->count())->toBe(1);
});

test('AssignmentChangesInsightStatusTest', function () {
    $writer = ewUser('writer');
    $assignment = ewAssign(ewInsight($writer), ewUser('editor'), ewUser('super_admin'));

    expect($assignment->insight->status)->toBe(InsightStatus::EditorAssigned);
});

test('AssignmentChangesWorkflowStageTest', function () {
    $writer = ewUser('writer');
    $assignment = ewAssign(ewInsight($writer), ewUser('editor'), ewUser('super_admin'));

    expect($assignment->insight->workflow_stage)->toBe(EditorialWorkflowStage::EditorialReview)
        ->and($assignment->workflow_stage)->toBe(EditorialWorkflowStage::EditorialReview);
});

test('ReassignmentPreservesOldAssignmentTest', function () {
    $writer = ewUser('writer');
    $admin = ewUser('super_admin');
    $old = ewAssign(ewInsight($writer), ewUser('editor'), $admin);
    $new = app(InsightAssignmentService::class)->reassignEditor($old->insight, ewUser('editor'), $admin, 'Penyesuaian keahlian.');

    expect($old->fresh()->status)->toBe(EditorAssignmentStatus::Reassigned)
        ->and($old->fresh()->completed_at)->not->toBeNull()
        ->and($new->insight->editorAssignments()->count())->toBe(2);
});

test('ReassignmentRequiresReasonTest', function () {
    $writer = ewUser('writer');
    $admin = ewUser('super_admin');
    $assignment = ewAssign(ewInsight($writer), ewUser('editor'), $admin);

    expect(fn () => app(InsightAssignmentService::class)->reassignEditor($assignment->insight, ewUser('editor'), $admin, ''))
        ->toThrow(ValidationException::class);
});

test('AssignmentCreatesDecisionRecordTest', function () {
    $writer = ewUser('writer');
    $assignment = ewAssign(ewInsight($writer), ewUser('editor'), ewUser('super_admin'));

    expect($assignment->insight->editorialDecisions()->where('decision', EditorialDecisionType::AssignEditor->value)->exists())->toBeTrue();
});

test('AssignmentCreatesAuditActivityTest', function () {
    $writer = ewUser('writer');
    $assignment = ewAssign(ewInsight($writer), ewUser('editor'), ewUser('super_admin'));

    expect($assignment->insight->editorialActivities()->where('event', EditorialActivityType::EditorAssigned->value)->exists())->toBeTrue();
});

test('AssignmentCreatesNotificationTest', function () {
    $writer = ewUser('writer');
    $editor = ewUser('editor');
    ewAssign(ewInsight($writer), $editor, ewUser('super_admin'));

    expect($editor->notifications()->where('data->type', 'editor_assigned')->count())->toBe(1);
});

test('EditorSeesOnlyAssignedInsightsTest', function () {
    $writer = ewUser('writer');
    $admin = ewUser('super_admin');
    $editor = ewUser('editor');
    $assigned = ewAssign(ewInsight($writer), $editor, $admin)->insight;
    $unassigned = ewInsight($writer);
    $this->actingAs($editor);

    expect(AssignedInsightResource::getEloquentQuery()->pluck('insights.id')->all())
        ->toContain($assigned->id)
        ->not->toContain($unassigned->id);
});

test('EditorCannotSeeOtherEditorAssignmentTest', function () {
    $writer = ewUser('writer');
    $assigned = ewAssign(ewInsight($writer), ewUser('editor'), ewUser('super_admin'))->insight;
    $otherEditor = ewUser('editor');
    $this->actingAs($otherEditor);

    expect(AssignedInsightResource::getEloquentQuery()->whereKey($assigned)->exists())->toBeFalse();
});

test('CompletedAssignmentAppearsInCompletedTabTest', function () {
    $writer = ewUser('writer');
    $admin = ewUser('super_admin');
    $editor = ewUser('editor');
    $assignment = ewAssign(ewInsight($writer), $editor, $admin);
    $assignment->insight->forceFill([
        'status' => InsightStatus::Approved,
        'workflow_stage' => EditorialWorkflowStage::FinalApproval,
    ])->save();
    app(InsightAssignmentService::class)->completeAssignment($assignment, $editor);

    expect($editor->editorialAssignments()->where('status', EditorAssignmentStatus::Completed->value)->where('insight_id', $assignment->insight_id)->exists())->toBeTrue();
});

test('ReassignedInsightDisappearsFromOldEditorActiveListTest', function () {
    $writer = ewUser('writer');
    $admin = ewUser('super_admin');
    $oldEditor = ewUser('editor');
    $old = ewAssign(ewInsight($writer), $oldEditor, $admin);
    app(InsightAssignmentService::class)->reassignEditor($old->insight, ewUser('editor'), $admin, 'Rotasi Editor.');

    expect($oldEditor->assignedEditorialInsights()->whereKey($old->insight_id)->exists())->toBeFalse();
});

test('SuperAdminCanAccessAnyEditorialWorkspaceTest', function () {
    $admin = ewUser('super_admin');
    $insight = ewInsight(ewUser('writer'));

    expect($admin->can('accessEditorialWorkspace', $insight))->toBeTrue();

    $this->actingAs($admin)
        ->get(EditorialResource::getUrl('workspace', ['record' => $insight]))
        ->assertOk();
});

test('LegacyEditorialManagerCanAccessWorkspaceTest', function () {
    $manager = ewUser();
    $manager->givePermissionTo('view_all_editorial_insights');
    $insight = ewInsight(ewUser('writer'));

    expect($manager->can('accessEditorialWorkspace', $insight))->toBeTrue();

    $this->actingAs($manager)
        ->get(EditorialResource::getUrl('workspace', ['record' => $insight]))
        ->assertOk();
});

test('AssignedEditorCanAccessWorkspaceTest', function () {
    $writer = ewUser('writer');
    $admin = ewUser('super_admin');
    $editor = ewUser('editor');
    $insight = ewAssign(ewInsight($writer), $editor, $admin)->insight;

    expect($editor->can('accessEditorialWorkspace', $insight))->toBeTrue();

    $this->actingAs($editor)
        ->get(EditorialResource::getUrl('workspace', ['record' => $insight]))
        ->assertOk();
});

test('UnassignedEditorCannotAccessWorkspaceTest', function () {
    $insight = ewInsight(ewUser('writer'));
    $editor = ewUser('editor');

    expect($editor->can('accessEditorialWorkspace', $insight))->toBeFalse();

    $this->actingAs($editor)
        ->get(EditorialResource::getUrl('workspace', ['record' => $insight]))
        ->assertNotFound();
});

test('WriterCanAccessOwnWorkspaceTest', function () {
    $writer = ewUser('writer');
    $insight = ewInsight($writer);

    expect($writer->can('accessEditorialWorkspace', $insight))->toBeTrue();

    $this->actingAs($writer)
        ->get(EditorialResource::getUrl('workspace', ['record' => $insight]))
        ->assertOk();
});

test('DualRoleEditorWriterCanAccessOwnWorkspaceTest', function () {
    $writer = ewUser('editor');
    $writer->assignRole('writer');
    $insight = ewInsight($writer);

    expect($writer->can('accessEditorialWorkspace', $insight))->toBeTrue();

    $this->actingAs($writer)
        ->get(EditorialResource::getUrl('workspace', ['record' => $insight]))
        ->assertOk();
});

test('WriterCannotAccessOtherWriterWorkspaceTest', function () {
    $owner = ewUser('writer');
    $other = ewUser('writer');
    $insight = ewInsight($owner);

    expect($other->can('accessEditorialWorkspace', $insight))->toBeFalse();

    $this->actingAs($other)
        ->get(EditorialResource::getUrl('workspace', ['record' => $insight]))
        ->assertNotFound();
});

test('WorkspaceDisplaysActiveAssignmentTest', function () {
    $writer = ewUser('writer');
    $admin = ewUser('super_admin');
    $editor = ewUser('editor', 'Editor Workspace Aktif');
    $insight = ewAssign(ewInsight($writer), $editor, $admin)->insight;
    $this->actingAs($admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(ViewEditorialWorkspace::class, ['record' => $insight->id])
        ->assertActionHidden('assign_editor')
        ->assertActionVisible('reassign_editor')
        ->assertSee('Editor Workspace Aktif')
        ->assertSee('Ditugaskan')
        ->assertSee('Naskah Insight')
        ->assertSee('Ringkasan Alur Editorial')
        ->assertDontSeeHtml('aria-label="Tab Editorial Workspace"');
});

test('WorkspaceUsesOneGeneralReviewNoteInsteadOfSectionCommentsTest', function () {
    $writer = ewUser('writer');
    $admin = ewUser('super_admin');
    $editor = ewUser('editor');
    $insight = ewAssign(ewInsight($writer), $editor, $admin)->insight;
    $this->actingAs($editor);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(ViewEditorialWorkspace::class, ['record' => $insight->id])
        ->assertActionVisible('review_note')
        ->callAction('review_note', [
            'note' => 'Catatan untuk keseluruhan naskah.',
            'is_visible_to_writer' => true,
        ])
        ->assertNotified('Catatan review disimpan.');

    $note = $insight->editorialNotes()->latest('id')->first();

    expect($note->type)->toBe(EditorialCommentType::General)
        ->and($note->status)->toBe(EditorialCommentStatus::Resolved)
        ->and($note->field_name)->toBeNull();
});

test('AssignedInsightTableDoesNotExposeSectionCommentActionsTest', function () {
    $writer = ewUser('writer');
    $admin = ewUser('super_admin');
    $editor = ewUser('editor');
    $insight = ewAssign(ewInsight($writer), $editor, $admin)->insight;
    $this->actingAs($editor);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(ListAssignedInsights::class)
        ->assertCanSeeTableRecords([$insight])
        ->assertTableActionDoesNotExist('add_note', record: $insight)
        ->assertTableActionDoesNotExist('comments', record: $insight)
        ->assertTableActionDoesNotExist('resolve_comment', record: $insight)
        ->assertTableActionDoesNotExist('reopen_comment', record: $insight);
});

test('AssignedInsightTableRowLinksDirectlyToWorkspaceTest', function () {
    $writer = ewUser('writer');
    $admin = ewUser('super_admin');
    $editor = ewUser('editor');
    $insight = ewAssign(ewInsight($writer), $editor, $admin)->insight;
    $this->actingAs($editor);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $component = Livewire::test(ListAssignedInsights::class)
        ->assertCanSeeTableRecords([$insight]);

    expect($component->instance()->getTable()->getRecordUrl($insight))
        ->toBe(EditorialResource::getUrl('workspace', ['record' => $insight]));
});

test('WorkspaceDisplaysDecisionHistoryTest', function () {
    $writer = ewUser('writer');
    $admin = ewUser('super_admin');
    $insight = ewAssign(ewInsight($writer), ewUser('editor'), $admin)->insight;
    $this->actingAs($admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(ViewEditorialWorkspace::class, ['record' => $insight->id])
        ->assertSee('Keputusan Editorial')
        ->assertSee('Editor Ditugaskan');
});

test('WorkspaceDisplaysAuditHistoryTest', function () {
    $writer = ewUser('writer');
    $admin = ewUser('super_admin');
    $insight = ewAssign(ewInsight($writer), ewUser('editor'), $admin)->insight;
    $this->actingAs($admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(ViewEditorialWorkspace::class, ['record' => $insight->id])
        ->assertSee('Audit History')
        ->assertSee('Editor Assigned');
});

test('SubmittedInsightCanMoveToEditorAssignedTest', function () {
    $writer = ewUser('writer');
    $assignment = ewAssign(ewInsight($writer), ewUser('editor'), ewUser('super_admin'));

    expect($assignment->insight->status)->toBe(InsightStatus::EditorAssigned);
});

test('InvalidWorkflowTransitionIsRejectedTest', function () {
    $admin = ewUser('super_admin');
    $insight = ewInsight(ewUser('writer'));

    expect(fn () => app(InsightWorkflowService::class)->moveToPublication($insight, $admin))
        ->toThrow(LogicException::class);
});

test('StatusCannotBeChangedThroughUnauthorizedDirectActionTest', function () {
    $writer = ewUser('writer');
    $insight = ewInsight($writer);

    expect($writer->can('update', $insight))->toBeFalse()
        ->and($writer->can('publishApproved', $insight))->toBeFalse();
});

test('StartingReviewChangesStatusToInReviewTest', function () {
    $writer = ewUser('writer');
    $editor = ewUser('editor');
    $assignment = ewAssign(ewInsight($writer), $editor, ewUser('super_admin'));

    app(InsightAssignmentService::class)->startAssignment($assignment, $editor);

    expect($assignment->insight->fresh()->status)->toBe(InsightStatus::InReview)
        ->and($assignment->fresh()->status)->toBe(EditorAssignmentStatus::Active)
        ->and($assignment->fresh()->started_at)->not->toBeNull();
});

test('DecisionRecordIsImmutableTest', function () {
    $writer = ewUser('writer');
    $decision = ewAssign(ewInsight($writer), ewUser('editor'), ewUser('super_admin'))->insight->editorialDecisions()->first();

    expect(fn () => $decision->update(['decision_note' => 'Diubah']))
        ->toThrow(LogicException::class);
});

test('AuditActivityIsImmutableTest', function () {
    $writer = ewUser('writer');
    $activity = ewAssign(ewInsight($writer), ewUser('editor'), ewUser('super_admin'))->insight->editorialActivities()->first();

    expect(fn () => $activity->update(['description' => 'Diubah']))
        ->toThrow(LogicException::class);
});

test('EveryAssignmentActionCreatesAuditTest', function () {
    $writer = ewUser('writer');
    $admin = ewUser('super_admin');
    $first = ewAssign(ewInsight($writer), ewUser('editor'), $admin);
    $second = app(InsightAssignmentService::class)->reassignEditor($first->insight, ewUser('editor'), $admin, 'Rotasi.');
    app(InsightAssignmentService::class)->acceptAssignment($second, $second->editor);

    $events = $second->insight->editorialActivities()->pluck('event');

    expect($events)->toContain(
        EditorialActivityType::EditorAssigned->value,
        EditorialActivityType::EditorReassigned->value,
        EditorialActivityType::AssignmentAccepted->value,
    );
});

test('EveryWorkflowTransitionCreatesDecisionWhenRequiredTest', function () {
    $writer = ewUser('writer');
    $admin = ewUser('super_admin');
    $editor = ewUser('editor');
    $draft = ewInsight($writer, InsightStatus::Draft, EditorialWorkflowStage::Submission);
    app(InsightWorkflowService::class)->submit($draft, $writer);
    $assignment = ewAssign($draft->fresh(), $editor, $admin);
    app(InsightAssignmentService::class)->startAssignment($assignment, $editor);

    expect($draft->editorialDecisions()->where('decision', EditorialDecisionType::Submit->value)->exists())->toBeTrue()
        ->and($draft->editorialDecisions()->where('decision', EditorialDecisionType::AssignEditor->value)->exists())->toBeTrue()
        ->and($draft->editorialDecisions()->where('decision', EditorialDecisionType::StartReview->value)->exists())->toBeTrue();
});

test('AssignedEditorReceivesNotificationTest', function () {
    $editor = ewUser('editor');
    ewAssign(ewInsight(ewUser('writer')), $editor, ewUser('super_admin'));

    expect($editor->notifications()->where('data->type', 'editor_assigned')->exists())->toBeTrue();
});

test('ReassignedEditorsReceiveCorrectNotificationsTest', function () {
    $admin = ewUser('super_admin');
    $oldEditor = ewUser('editor');
    $newEditor = ewUser('editor');
    $assignment = ewAssign(ewInsight(ewUser('writer')), $oldEditor, $admin);
    app(InsightAssignmentService::class)->reassignEditor($assignment->insight, $newEditor, $admin, 'Rotasi.');

    expect($oldEditor->notifications()->where('data->type', 'editor_reassigned')->exists())->toBeTrue()
        ->and($newEditor->notifications()->where('data->type', 'editor_reassigned')->exists())->toBeTrue();
});

test('DuplicateAssignmentNotificationIsNotCreatedTest', function () {
    $admin = ewUser('super_admin');
    $editor = ewUser('editor');
    $assignment = ewAssign(ewInsight(ewUser('writer')), $editor, $admin);

    app(InsightNotificationService::class)->editorAssigned($assignment->insight, $assignment, $admin);

    expect($editor->notifications()->where('data->type', 'editor_assigned')->count())->toBe(1);
});

test('NotificationContainsWorkspaceUrlTest', function () {
    $editor = ewUser('editor');
    $assignment = ewAssign(ewInsight(ewUser('writer')), $editor, ewUser('super_admin'));
    $data = $editor->notifications()->where('data->type', 'editor_assigned')->firstOrFail()->data;

    expect($data['url'])->toContain("/admin/editorial/{$assignment->insight_id}/workspace");
});

test('LegacyAssignedEditorCanBeBackfilledTest', function () {
    $writer = ewUser('writer');
    $editor = ewUser('editor');
    $legacy = ewInsight($writer, InsightStatus::InReview, EditorialWorkflowStage::EditorialReview);
    $legacy->forceFill([
        'assigned_editor_id' => $editor->id,
        'assigned_at' => now()->subDay(),
        'review_started_at' => now()->subHours(12),
    ])->save();

    $this->artisan('editorial:backfill-assignments')->assertSuccessful();

    expect($legacy->editorAssignments()->count())->toBe(1)
        ->and($legacy->editorAssignments()->first()->status)->toBe(EditorAssignmentStatus::Active);
});

test('BackfillCommandIsIdempotentTest', function () {
    $writer = ewUser('writer');
    $editor = ewUser('editor');
    $legacy = ewInsight($writer, InsightStatus::EditorAssigned, EditorialWorkflowStage::EditorialReview);
    $legacy->forceFill(['assigned_editor_id' => $editor->id])->save();

    $this->artisan('editorial:backfill-assignments')->assertSuccessful();
    $this->artisan('editorial:backfill-assignments')->assertSuccessful();

    expect($legacy->editorAssignments()->count())->toBe(1);
});

test('ExistingPublishedInsightsRemainVisibleTest', function () {
    $published = ewInsight(ewUser('writer'), InsightStatus::Published, EditorialWorkflowStage::Publication);

    $this->get(route('insights.show', $published->slug))
        ->assertOk()
        ->assertSee($published->title);
});

test('PublicInsightQueryStillOnlyShowsPublishedContentTest', function () {
    $writer = ewUser('writer');
    $published = ewInsight($writer, InsightStatus::Published, EditorialWorkflowStage::Publication);
    $submitted = ewInsight($writer);
    $ids = Insight::query()->published()->pluck('id');

    expect($ids)->toContain($published->id)
        ->not->toContain($submitted->id);
});
