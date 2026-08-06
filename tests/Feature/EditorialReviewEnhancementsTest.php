<?php

use App\Enums\EditorialCommentField;
use App\Enums\EditorialCommentStatus;
use App\Enums\EditorialCommentType;
use App\Enums\InsightStatus;
use App\Models\Author;
use App\Models\Insight;
use App\Models\InsightCategory;
use App\Models\User;
use App\Services\InsightCommentService;
use App\Services\InsightDeadlineService;
use App\Services\InsightEditorialWorkflowService;
use App\Services\InsightNotificationService;
use App\Services\InsightRevisionService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function p1User(string $role, ?string $name = null): User
{
    $name ??= Str::headline($role).' '.Str::random(4);
    $user = User::query()->create([
        'name' => $name,
        'email' => Str::slug($name).'-'.Str::random(6).'@p1.test',
        'password' => 'password',
        'is_active' => true,
    ]);
    $user->assignRole($role);

    return $user;
}

function p1CompleteInsight(User $writer): Insight
{
    $suffix = Str::lower(Str::random(8));
    $category = InsightCategory::query()->create(['name' => 'P1 '.$suffix, 'slug' => 'p1-'.$suffix]);
    $author = Author::query()->create(['user_id' => $writer->id, 'name' => $writer->name, 'slug' => 'p1-author-'.$suffix]);
    $insight = Insight::query()->create([
        'created_by' => $writer->id,
        'insight_category_id' => $category->id,
        'title' => 'Naskah P1 '.$suffix,
        'slug' => 'naskah-p1-'.$suffix,
        'excerpt' => 'Ringkasan awal naskah P1.',
        'content' => '<h2>Versi Awal</h2><p>Isi awal naskah.</p>',
        'cover_image' => 'insights/p1-'.$suffix.'.webp',
        'status' => InsightStatus::Draft,
    ]);
    $insight->authors()->attach($author, ['author_order' => 1, 'role' => 'Penulis']);

    return $insight->refresh();
}

function p1AssignedInsight(User $writer, User $admin, User $editor): Insight
{
    $workflow = app(InsightEditorialWorkflowService::class);
    $insight = p1CompleteInsight($writer);
    $workflow->submit($insight, $writer);

    return $workflow->assignEditor($insight->fresh(), $editor, $admin, now()->addDays(2));
}

test('EditorCanCreateSectionCommentTest', function () {
    $writer = p1User('writer');
    $admin = p1User('super_admin');
    $editor = p1User('editor');
    $insight = p1AssignedInsight($writer, $admin, $editor);

    $comment = app(InsightCommentService::class)->createSectionComment($insight, $editor, 'Perjelas paragraf ini.', EditorialCommentField::Content, 'Isi awal naskah.');

    expect($comment->type)->toBe(EditorialCommentType::Section)
        ->and($comment->field_name)->toBe(EditorialCommentField::Content)
        ->and($comment->status)->toBe(EditorialCommentStatus::Open);
});

test('EditorCannotCommentOnUnassignedInsightTest', function () {
    $writer = p1User('writer');
    $editor = p1User('editor');
    $insight = p1CompleteInsight($writer);

    expect(fn () => app(InsightCommentService::class)->createSectionComment($insight, $editor, 'Komentar.', EditorialCommentField::Content))
        ->toThrow(AuthorizationException::class);
});

test('WriterCanReplyToVisibleCommentTest', function () {
    $writer = p1User('writer');
    $admin = p1User('super_admin');
    $editor = p1User('editor');
    $insight = p1AssignedInsight($writer, $admin, $editor);
    $comment = app(InsightCommentService::class)->createSectionComment($insight, $editor, 'Perjelas.', EditorialCommentField::Content);

    $reply = app(InsightCommentService::class)->reply($comment, $writer, 'Sudah saya perjelas.');

    expect($reply->parent_id)->toBe($comment->id)
        ->and($reply->type)->toBe(EditorialCommentType::AuthorResponse);
});

test('WriterCannotViewInternalCommentTest', function () {
    $writer = p1User('writer');
    $admin = p1User('super_admin');
    $editor = p1User('editor');
    $insight = p1AssignedInsight($writer, $admin, $editor);
    $comment = app(InsightCommentService::class)->createSectionComment($insight, $editor, 'Catatan internal.', EditorialCommentField::Other, null, EditorialCommentType::Internal, true);

    expect($writer->can('view', $comment))->toBeFalse()
        ->and($insight->editorialNotes()->visibleToWriter()->whereKey($comment)->exists())->toBeFalse();
});

test('WriterCanMarkCommentAddressedTest', function () {
    $writer = p1User('writer');
    $admin = p1User('super_admin');
    $editor = p1User('editor');
    $insight = p1AssignedInsight($writer, $admin, $editor);
    $comment = app(InsightCommentService::class)->createSectionComment($insight, $editor, 'Perbaiki.', EditorialCommentField::Excerpt);

    $comment = app(InsightCommentService::class)->markAddressed($comment, $writer);

    expect($comment->status)->toBe(EditorialCommentStatus::Addressed)
        ->and($comment->addressed_by)->toBe($writer->id);
});

test('WriterCannotResolveCommentTest', function () {
    $writer = p1User('writer');
    $admin = p1User('super_admin');
    $editor = p1User('editor');
    $insight = p1AssignedInsight($writer, $admin, $editor);
    $comment = app(InsightCommentService::class)->createSectionComment($insight, $editor, 'Perbaiki.', EditorialCommentField::Excerpt);
    $comment = app(InsightCommentService::class)->markAddressed($comment, $writer);

    expect(fn () => app(InsightCommentService::class)->resolve($comment, $writer))
        ->toThrow(AuthorizationException::class);
});

test('EditorCanResolveAddressedCommentTest', function () {
    $writer = p1User('writer');
    $admin = p1User('super_admin');
    $editor = p1User('editor');
    $insight = p1AssignedInsight($writer, $admin, $editor);
    $comment = app(InsightCommentService::class)->createSectionComment($insight, $editor, 'Perbaiki.', EditorialCommentField::Excerpt);
    $comment = app(InsightCommentService::class)->markAddressed($comment, $writer);

    $comment = app(InsightCommentService::class)->resolve($comment, $editor);

    expect($comment->status)->toBe(EditorialCommentStatus::Resolved)
        ->and($comment->resolved_by)->toBe($editor->id);
});

test('EditorCanReopenCommentTest', function () {
    $writer = p1User('writer');
    $admin = p1User('super_admin');
    $editor = p1User('editor');
    $insight = p1AssignedInsight($writer, $admin, $editor);
    $comments = app(InsightCommentService::class);
    $comment = $comments->createSectionComment($insight, $editor, 'Perbaiki.', EditorialCommentField::Excerpt);
    $comment = $comments->markAddressed($comment, $writer);
    $comment = $comments->resolve($comment, $editor);

    $comment = $comments->reopen($comment, $editor, 'Perubahan belum cukup jelas.');

    expect($comment->status)->toBe(EditorialCommentStatus::Reopened)
        ->and($comment->reopened_by)->toBe($editor->id);
});

test('LegacyOpenSectionCommentDoesNotBlockSimplifiedApprovalTest', function () {
    $writer = p1User('writer');
    $admin = p1User('super_admin');
    $editor = p1User('editor');
    $workflow = app(InsightEditorialWorkflowService::class);
    $insight = p1AssignedInsight($writer, $admin, $editor);
    $insight = $workflow->startReview($insight, $editor);
    app(InsightCommentService::class)->createSectionComment($insight, $editor, 'Perbaiki.', EditorialCommentField::Content);

    $insight = $workflow->approve($insight->fresh(), $editor);

    expect($insight->status)->toBe(InsightStatus::Approved);
});

test('GeneralReviewNoteIsStoredWithoutSectionCommentLifecycleTest', function () {
    $writer = p1User('writer');
    $admin = p1User('super_admin');
    $editor = p1User('editor');
    $insight = p1AssignedInsight($writer, $admin, $editor);
    $note = app(InsightEditorialWorkflowService::class)->addEditorialNote(
        $insight,
        $editor,
        'Argumentasi utama sudah kuat.',
    );

    expect($note->type)->toBe(EditorialCommentType::General)
        ->and($note->field_name)->toBeNull()
        ->and($note->quoted_text)->toBeNull()
        ->and($note->status)->toBe(EditorialCommentStatus::Resolved)
        ->and($note->resolved_by)->toBe($editor->id)
        ->and($note->resolved_at)->not->toBeNull();
});

test('GeneralReviewNoteDoesNotBlockApprovalTest', function () {
    $writer = p1User('writer');
    $admin = p1User('super_admin');
    $editor = p1User('editor');
    $workflow = app(InsightEditorialWorkflowService::class);
    $insight = p1AssignedInsight($writer, $admin, $editor);
    $insight = $workflow->startReview($insight, $editor);
    $workflow->addEditorialNote($insight, $editor, 'Naskah siap disetujui.');

    $insight = $workflow->approve($insight->fresh(), $editor);

    expect($insight->status)->toBe(InsightStatus::Approved);
});

test('RevisionSnapshotIsCreatedOnSubmissionTest', function () {
    $writer = p1User('writer');
    $insight = p1CompleteInsight($writer);

    app(InsightEditorialWorkflowService::class)->submit($insight, $writer);

    expect($insight->revisions()->count())->toBe(1)
        ->and($insight->revisions()->first()->author_snapshot)->not->toBeEmpty();
});

test('RevisionSnapshotIsCreatedOnResubmissionTest', function () {
    $writer = p1User('writer');
    $admin = p1User('super_admin');
    $editor = p1User('editor');
    $workflow = app(InsightEditorialWorkflowService::class);
    $insight = p1AssignedInsight($writer, $admin, $editor);
    $workflow->startReview($insight, $editor);
    $workflow->requestRevision($insight->fresh(), $editor, 'Perbaiki.');
    $insight->update(['content' => '<h2>Versi Dua</h2><p>Isi sudah berubah bermakna.</p>']);

    $workflow->resubmit($insight->fresh(), $writer, 'Memperbarui isi utama.');

    expect($insight->revisions()->count())->toBe(2)
        ->and($insight->revisions()->latest('revision_number')->value('revision_summary'))->toBe('Memperbarui isi utama.');
});

test('WriterMustProvideRevisionSummaryTest', function () {
    $writer = p1User('writer');
    $admin = p1User('super_admin');
    $editor = p1User('editor');
    $workflow = app(InsightEditorialWorkflowService::class);
    $insight = p1AssignedInsight($writer, $admin, $editor);
    $workflow->startReview($insight, $editor);
    $workflow->requestRevision($insight->fresh(), $editor, 'Perbaiki.');
    $insight->update(['content' => '<p>Berubah.</p>']);

    expect(fn () => $workflow->resubmit($insight->fresh(), $writer, ''))->toThrow(ValidationException::class);
});

test('EditorCanCompareTwoRevisionsTest', function () {
    $writer = p1User('writer');
    $admin = p1User('super_admin');
    $editor = p1User('editor');
    $insight = p1AssignedInsight($writer, $admin, $editor);
    $insight->update(['title' => 'Judul Baru', 'content' => '<h2>Konten Baru</h2>']);
    app(InsightRevisionService::class)->createRevisionSnapshot($insight->fresh(), $writer, 'Judul dan konten diperbarui.');
    [$older, $newer] = $insight->revisions()->orderBy('revision_number')->get()->all();

    $comparison = app(InsightRevisionService::class)->compare($older, $newer);

    expect($editor->can('compare', $older))->toBeTrue()
        ->and($comparison['title']['changed'])->toBeTrue()
        ->and($comparison['content']['changed'])->toBeTrue();
});

test('EditorDeadlineCanBeSetTest', function () {
    $writer = p1User('writer');
    $admin = p1User('super_admin');
    $insight = p1CompleteInsight($writer);

    $insight = app(InsightDeadlineService::class)->setEditorDeadline($insight, $admin, now()->addDays(2));

    expect($insight->editor_deadline)->not->toBeNull()
        ->and($insight->editor_deadline_completed_at)->toBeNull();
});

test('WriterDeadlineCanBeSetTest', function () {
    $writer = p1User('writer');
    $admin = p1User('super_admin');
    $editor = p1User('editor');
    $insight = p1AssignedInsight($writer, $admin, $editor);

    $insight = app(InsightDeadlineService::class)->setWriterDeadline($insight, $editor, now()->addDays(2));

    expect($insight->writer_deadline)->not->toBeNull();
});

test('OverdueDeadlineDoesNotChangeStatusTest', function () {
    $writer = p1User('writer');
    $admin = p1User('super_admin');
    $editor = p1User('editor');
    $insight = p1AssignedInsight($writer, $admin, $editor);
    $insight->forceFill(['editor_deadline' => now()->subHour(), 'editor_deadline_completed_at' => null])->save();
    $status = $insight->status;

    expect(app(InsightDeadlineService::class)->isOverdue($insight->fresh(), 'editor'))->toBeTrue()
        ->and($insight->fresh()->status)->toBe($status);
});

test('AssignmentNotificationIsCreatedTest', function () {
    $writer = p1User('writer');
    $admin = p1User('super_admin');
    $editor = p1User('editor');
    p1AssignedInsight($writer, $admin, $editor);

    expect($editor->notifications()->where('data->notification_type', 'assignment')->exists())->toBeTrue();
});

test('RevisionRequestNotificationIsCreatedTest', function () {
    $writer = p1User('writer');
    $admin = p1User('super_admin');
    $editor = p1User('editor');
    $workflow = app(InsightEditorialWorkflowService::class);
    $insight = p1AssignedInsight($writer, $admin, $editor);
    $workflow->startReview($insight, $editor);
    $workflow->requestRevision($insight->fresh(), $editor, 'Perbaiki.');

    expect($writer->notifications()->where('data->notification_type', 'revision_requested')->exists())->toBeTrue();
});

test('RevisionSubmittedNotificationIsCreatedTest', function () {
    $writer = p1User('writer');
    $admin = p1User('super_admin');
    $editor = p1User('editor');
    $workflow = app(InsightEditorialWorkflowService::class);
    $insight = p1AssignedInsight($writer, $admin, $editor);
    $workflow->startReview($insight, $editor);
    $workflow->requestRevision($insight->fresh(), $editor, 'Perbaiki.');
    $insight->update(['content' => '<h2>Berubah</h2><p>Versi baru.</p>']);
    $workflow->resubmit($insight->fresh(), $writer, 'Isi diperbarui.');

    expect($editor->notifications()->where('data->notification_type', 'revision_submitted')->exists())->toBeTrue();
});

test('DuplicateNotificationIsNotCreatedTest', function () {
    $writer = p1User('writer');
    $admin = p1User('super_admin');
    $editor = p1User('editor');
    $insight = p1AssignedInsight($writer, $admin, $editor);
    $notifications = app(InsightNotificationService::class);

    $notifications->notifyAssignment($insight->load(['creator', 'assignedEditor']));
    $notifications->notifyAssignment($insight->load(['creator', 'assignedEditor']));

    expect($editor->notifications()->where('data->notification_type', 'assignment')->count())->toBe(1);
});
