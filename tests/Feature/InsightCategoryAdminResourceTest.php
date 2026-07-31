<?php

use App\Filament\Resources\InsightCategories\InsightCategoryResource;
use App\Models\InsightCategory;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

test('insight category schema exposes editorial curation fields with safe defaults', function () {
    expect(Schema::hasColumns('insight_categories', [
        'description',
        'sort_order',
        'show_on_editorial_index',
    ]))->toBeTrue();

    $category = InsightCategory::query()->create([
        'name' => 'Hukum dan Masyarakat',
        'slug' => 'hukum-dan-masyarakat',
        'description' => 'Analisis hubungan hukum dengan kehidupan masyarakat.',
    ])->fresh();

    expect($category->description)->toBe('Analisis hubungan hukum dengan kehidupan masyarakat.')
        ->and($category->sort_order)->toBe(0)
        ->and($category->show_on_editorial_index)->toBeTrue()
        ->and($category->is_active)->toBeTrue();
});

test('visible editorial category scope requires both active and editorial visibility flags', function () {
    InsightCategory::query()->create([
        'name' => 'Kategori Kedua',
        'slug' => 'kategori-kedua',
        'sort_order' => 20,
        'is_active' => true,
        'show_on_editorial_index' => true,
    ]);

    InsightCategory::query()->create([
        'name' => 'Kategori Pertama',
        'slug' => 'kategori-pertama',
        'sort_order' => 10,
        'is_active' => true,
        'show_on_editorial_index' => true,
    ]);

    InsightCategory::query()->create([
        'name' => 'Kategori Disembunyikan',
        'slug' => 'kategori-disembunyikan',
        'sort_order' => 1,
        'is_active' => true,
        'show_on_editorial_index' => false,
    ]);

    InsightCategory::query()->create([
        'name' => 'Kategori Nonaktif',
        'slug' => 'kategori-nonaktif',
        'sort_order' => 0,
        'is_active' => false,
        'show_on_editorial_index' => true,
    ]);

    $categories = InsightCategory::query()
        ->visibleOnEditorialIndex()
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

    expect($categories->pluck('slug')->all())->toBe([
        'kategori-pertama',
        'kategori-kedua',
    ]);
});

test('super admin can open create and edit insight category forms', function () {
    $role = Role::findOrCreate('super_admin');
    $user = User::query()->create([
        'name' => 'Super Admin Kategori',
        'email' => 'category-admin@example.test',
        'password' => 'secret-password',
        'is_active' => true,
    ]);
    $user->assignRole($role);

    $category = InsightCategory::query()->create([
        'name' => 'Kategori untuk Diedit',
        'slug' => 'kategori-untuk-diedit',
        'description' => 'Deskripsi singkat kategori.',
        'sort_order' => 3,
        'show_on_editorial_index' => true,
    ]);

    $this->actingAs($user)
        ->get(InsightCategoryResource::getUrl('create'))
        ->assertOk();

    $this->actingAs($user)
        ->get(InsightCategoryResource::getUrl('edit', ['record' => $category]))
        ->assertOk()
        ->assertSee('Deskripsi singkat kategori.');
});
