<?php

use App\Filament\Resources\Authors\AuthorResource;
use App\Filament\Resources\Authors\Pages\ListAuthors;
use App\Filament\Resources\CollaborationSubmissions\CollaborationSubmissionResource;
use App\Filament\Resources\CollaborationSubmissions\Pages\EditCollaborationSubmission;
use App\Filament\Resources\CollaborationSubmissions\Pages\ListCollaborationSubmissions;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\ContactMessages\Pages\EditContactMessage;
use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Filament\Resources\InsightCategories\InsightCategoryResource;
use App\Filament\Resources\InsightCategories\Pages\EditInsightCategory;
use App\Filament\Resources\InsightCategories\Pages\ListInsightCategories;
use App\Filament\Resources\Permissions\Pages\ListPermissions;
use App\Filament\Resources\Permissions\PermissionResource;
use App\Filament\Resources\ProgramCategories\Pages\ListProgramCategories;
use App\Filament\Resources\ProgramCategories\ProgramCategoryResource;
use App\Filament\Resources\PublicationTypes\Pages\ListPublicationTypes;
use App\Filament\Resources\PublicationTypes\PublicationTypeResource;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Filament\Resources\Roles\RoleResource;
use App\Filament\Resources\Tags\Pages\ListTags;
use App\Filament\Resources\Tags\TagResource;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\UserResource;
use App\Models\Author;
use App\Models\CollaborationSubmission;
use App\Models\ContactMessage;
use App\Models\Insight;
use App\Models\InsightCategory;
use App\Models\ProgramCategory;
use App\Models\PublicationType;
use App\Models\Tag;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function makeSupportingAdmin(string $email = 'supporting-admin@example.test'): User
{
    $role = Role::findOrCreate('super_admin');
    $user = User::query()->create([
        'name' => 'Super Admin Supporting Resource',
        'email' => $email,
        'password' => 'secret-password',
        'is_active' => true,
    ]);
    $user->assignRole($role);

    return $user;
}

test('supporting admin lists render localized headings and descriptions', function () {
    $user = makeSupportingAdmin();

    $expectations = [
        [CollaborationSubmissionResource::getUrl('index'), 'Kolaborasi', 'Kelola pengajuan kerja sama, tindak lanjut, dan catatan internal.'],
        [ContactMessageResource::getUrl('index'), 'Pesan', 'Kelola pesan masuk, respons, dan catatan tindak lanjut.'],
        [InsightCategoryResource::getUrl('index'), 'Kategori Editorial', 'Kelola klasifikasi artikel dan urutan tampil pada halaman editorial.'],
        [ProgramCategoryResource::getUrl('index'), 'Kategori Program', 'Kelola klasifikasi program dan urutan tampil.'],
        [PublicationTypeResource::getUrl('index'), 'Tipe Publikasi', 'Kelola klasifikasi riset dan publikasi.'],
        [TagResource::getUrl('index'), 'Tag', 'Kelola tag yang digunakan pada artikel dan publikasi.'],
        [AuthorResource::getUrl('index'), 'Profil Kontributor', 'Kelola profil penulis, penyusun, dan kontributor Edulaw.'],
        [UserResource::getUrl('index'), 'Akun Admin', 'Kelola akun yang dapat mengakses panel administrasi.'],
        [RoleResource::getUrl('index'), 'Role', 'Kelola kelompok kewenangan untuk akun admin.'],
        [PermissionResource::getUrl('index'), 'Permission', 'Kelola izin teknis yang digunakan oleh role admin.'],
    ];

    foreach ($expectations as [$url, $heading, $description]) {
        $this->actingAs($user)->get($url)
            ->assertOk()
            ->assertSee($heading)
            ->assertSee($description);
    }

    $this->actingAs($user)->get(CollaborationSubmissionResource::getUrl('index'))->assertDontSee('Tambah Kolaborasi');
    $this->actingAs($user)->get(ContactMessageResource::getUrl('index'))->assertDontSee('Tambah Pesan');
});

test('supporting resource tables search records and handle null relations', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $user = makeSupportingAdmin('supporting-search@example.test');

    $records = [
        [ListCollaborationSubmissions::class, CollaborationSubmission::query()->create(['name' => 'Kolaborasi Jarum Unik', 'email' => 'kolaborasi@example.test', 'subject' => 'Kerja Sama', 'message' => 'Pesan', 'status' => 'new'])],
        [ListContactMessages::class, ContactMessage::query()->create(['name' => 'Pesan Jarum Unik', 'email' => 'pesan@example.test', 'subject' => 'Pertanyaan', 'message' => 'Pesan', 'status' => 'new'])],
        [ListInsightCategories::class, InsightCategory::query()->create(['name' => 'Editorial Jarum Unik', 'slug' => 'editorial-jarum-unik', 'is_active' => true])],
        [ListProgramCategories::class, ProgramCategory::query()->create(['name' => 'Program Jarum Unik', 'slug' => 'program-jarum-unik', 'is_active' => true])],
        [ListPublicationTypes::class, PublicationType::query()->create(['name' => 'Tipe Jarum Unik', 'slug' => 'tipe-jarum-unik', 'is_active' => true])],
        [ListTags::class, Tag::query()->create(['name' => 'Tag Jarum Unik', 'slug' => 'tag-jarum-unik'])],
        [ListAuthors::class, Author::query()->create(['name' => 'Profil Jarum Unik', 'slug' => 'profil-jarum-unik', 'is_active' => true])],
        [ListUsers::class, User::query()->create(['name' => 'Akun Jarum Unik', 'email' => 'akun-jarum@example.test', 'password' => 'secret-password', 'is_active' => true])],
        [ListRoles::class, Role::findOrCreate('role jarum unik')],
        [ListPermissions::class, Permission::findOrCreate('permission jarum unik')],
    ];

    foreach ($records as [$component, $record]) {
        Livewire::actingAs($user)
            ->test($component)
            ->searchTable('Jarum Unik')
            ->assertCanSeeTableRecords([$record]);
    }
});

test('inbox tabs and status filters scope records', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $user = makeSupportingAdmin('supporting-inbox@example.test');

    $newCollaboration = CollaborationSubmission::query()->create(['name' => 'Baru', 'email' => 'baru@example.test', 'subject' => 'Baru', 'message' => 'Baru', 'status' => 'new']);
    $acceptedCollaboration = CollaborationSubmission::query()->create(['name' => 'Diterima', 'email' => 'diterima@example.test', 'subject' => 'Diterima', 'message' => 'Diterima', 'status' => 'accepted']);

    Livewire::actingAs($user)
        ->test(ListCollaborationSubmissions::class)
        ->set('activeTab', 'accepted')
        ->assertCanSeeTableRecords([$acceptedCollaboration])
        ->assertCanNotSeeTableRecords([$newCollaboration])
        ->set('activeTab', 'all')
        ->filterTable('status', 'new')
        ->assertCanSeeTableRecords([$newCollaboration])
        ->assertCanNotSeeTableRecords([$acceptedCollaboration]);

    $newMessage = ContactMessage::query()->create(['name' => 'Baru', 'email' => 'pesan-baru@example.test', 'subject' => 'Baru', 'message' => 'Baru', 'status' => 'new']);
    $repliedMessage = ContactMessage::query()->create(['name' => 'Dibalas', 'email' => 'dibalas@example.test', 'subject' => 'Dibalas', 'message' => 'Dibalas', 'status' => 'replied']);

    Livewire::actingAs($user)
        ->test(ListContactMessages::class)
        ->set('activeTab', 'replied')
        ->assertCanSeeTableRecords([$repliedMessage])
        ->assertCanNotSeeTableRecords([$newMessage]);
});

test('inbox public fields are read only and update access follows policy', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $admin = makeSupportingAdmin('supporting-readonly@example.test');
    $collaboration = CollaborationSubmission::query()->create(['name' => 'Pengirim', 'email' => 'pengirim@example.test', 'subject' => 'Subjek', 'message' => 'Pesan', 'status' => 'new']);
    $message = ContactMessage::query()->create(['name' => 'Pengirim', 'email' => 'kontak@example.test', 'subject' => 'Subjek', 'message' => 'Pesan', 'status' => 'new']);

    Livewire::actingAs($admin)->test(EditCollaborationSubmission::class, ['record' => $collaboration->getRouteKey()])
        ->assertFormFieldDisabled('name')
        ->assertFormFieldDisabled('email')
        ->assertFormFieldDisabled('message');

    Livewire::actingAs($admin)->test(EditContactMessage::class, ['record' => $message->getRouteKey()])
        ->assertFormFieldDisabled('name')
        ->assertFormFieldDisabled('email')
        ->assertFormFieldDisabled('message');

    $viewerRole = Role::findOrCreate('inbox_viewer');
    $viewerPermission = Permission::findOrCreate('view collaboration submissions');
    $viewerRole->givePermissionTo($viewerPermission);
    $viewer = User::query()->create(['name' => 'Viewer', 'email' => 'viewer@example.test', 'password' => 'secret-password', 'is_active' => true]);
    $viewer->assignRole($viewerRole);

    $this->actingAs($viewer)
        ->get(CollaborationSubmissionResource::getUrl('edit', ['record' => $collaboration]))
        ->assertForbidden();
});

test('relationship counts and protected deletion rules are safe', function () {
    $admin = makeSupportingAdmin('supporting-protection@example.test');
    $lastSuperAdmin = $admin;

    expect(UserResource::isLastSuperAdmin($lastSuperAdmin))->toBeTrue()
        ->and(UserResource::isLastActiveSuperAdmin($lastSuperAdmin))->toBeTrue()
        ->and(UserResource::canSafelyDelete($lastSuperAdmin))->toBeFalse();

    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Livewire::actingAs($lastSuperAdmin)
        ->test(EditUser::class, ['record' => $lastSuperAdmin->getRouteKey()])
        ->assertFormFieldDisabled('is_active')
        ->assertFormFieldDisabled('roles')
        ->assertActionHidden('delete');

    $otherSuperAdmin = User::query()->create(['name' => 'Super Admin Kedua', 'email' => 'second-super@example.test', 'password' => 'secret-password', 'is_active' => true]);
    $otherSuperAdmin->assignRole('super_admin');

    expect(UserResource::isLastSuperAdmin($lastSuperAdmin))->toBeFalse()
        ->and(UserResource::canSafelyDelete($otherSuperAdmin))->toBeTrue()
        ->and(PermissionResource::groupLabel('publish insights'))->toBe('Editorial')
        ->and(PermissionResource::groupLabel('update contact messages'))->toBe('Pesan');
});

test('supporting resource create and edit forms render without adding fields', function () {
    $admin = makeSupportingAdmin('supporting-forms@example.test');
    $programCategory = ProgramCategory::query()->create(['name' => 'Kategori Form', 'slug' => 'kategori-form', 'is_active' => true]);
    $publicationType = PublicationType::query()->create(['name' => 'Tipe Form', 'slug' => 'tipe-form', 'is_active' => true]);
    $tag = Tag::query()->create(['name' => 'Tag Form', 'slug' => 'tag-form']);
    $author = Author::query()->create(['name' => 'Profil Form', 'slug' => 'profil-form', 'is_active' => true]);
    $managedUser = User::query()->create(['name' => 'Akun Form', 'email' => 'akun-form@example.test', 'password' => 'secret-password', 'is_active' => true]);
    $role = Role::findOrCreate('role form');
    $permission = Permission::findOrCreate('permission form');

    $urls = [
        ProgramCategoryResource::getUrl('create'),
        ProgramCategoryResource::getUrl('edit', ['record' => $programCategory]),
        PublicationTypeResource::getUrl('create'),
        PublicationTypeResource::getUrl('edit', ['record' => $publicationType]),
        TagResource::getUrl('create'),
        TagResource::getUrl('edit', ['record' => $tag]),
        AuthorResource::getUrl('create'),
        AuthorResource::getUrl('edit', ['record' => $author]),
        UserResource::getUrl('create'),
        UserResource::getUrl('edit', ['record' => $managedUser]),
        RoleResource::getUrl('create'),
        RoleResource::getUrl('edit', ['record' => $role]),
        PermissionResource::getUrl('create'),
        PermissionResource::getUrl('edit', ['record' => $permission]),
    ];

    foreach ($urls as $url) {
        $this->actingAs($admin)->get($url)->assertOk();
    }
});

test('a reference record linked to content cannot expose its delete action', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $admin = makeSupportingAdmin('supporting-reference-delete@example.test');
    $category = InsightCategory::query()->create(['name' => 'Kategori Terpakai', 'slug' => 'kategori-terpakai', 'is_active' => true]);
    Insight::query()->create([
        'title' => 'Artikel Terhubung',
        'slug' => 'artikel-terhubung',
        'insight_category_id' => $category->id,
        'status' => 'draft',
        'created_by' => $admin->id,
    ]);

    Livewire::actingAs($admin)
        ->test(EditInsightCategory::class, ['record' => $category->getRouteKey()])
        ->assertActionHidden('delete');
});
