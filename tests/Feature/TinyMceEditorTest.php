<?php

use App\Filament\Forms\Components\TinyMceEditor;
use App\Filament\Resources\Insights\InsightResource\Pages\CreateInsight;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Filament\Facades\Filament;
use Filament\Forms\Components\Field;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('insight writer uses the self hosted TinyMCE field and editorial policy', function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::query()->create([
        'name' => 'TinyMCE Administrator',
        'email' => 'tinymce-admin@example.test',
        'password' => 'password',
        'is_active' => true,
    ]);
    $user->assignRole('super_admin');

    $this->actingAs($user);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(CreateInsight::class)
        ->assertFormFieldExists('content', null, function (Field $field): bool {
            if (! $field instanceof TinyMceEditor) {
                return false;
            }

            $config = $field->getEditorConfig();

            return $field->getHeight() === 650
                && $field->getFileAttachmentsDiskName() === 'public'
                && $field->getFileAttachmentsDirectory() === 'insights/content-images'
                && $field->getFileAttachmentsMaxSize() === 4096
                && str_contains($config['block_formats'], 'Heading 4=h4')
                && ! str_contains($config['block_formats'], 'h1')
                && str_contains($config['toolbar'], 'footnote')
                && str_contains($config['toolbar'], 'code fullscreen');
        })
        ->assertSee('tinyMceEditorFormComponent');

    $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true, flags: JSON_THROW_ON_ERROR);

    expect($manifest)->toHaveKey('resources/js/filament/tinymce-editor.js')
        ->and(public_path('build/tinymce/skins/ui/oxide/skin.min.css'))->toBeFile()
        ->and(public_path('build/tinymce/skins/ui/oxide-dark/skin.min.css'))->toBeFile()
        ->and(public_path('build/tinymce/skins/content/default/content.css'))->toBeFile()
        ->and(public_path('build/tinymce/skins/content/dark/content.css'))->toBeFile();
});

test('TinyMCE image attachments use validated Laravel public storage', function () {
    Storage::fake('public');
    $this->seed(RolePermissionSeeder::class);

    $user = User::query()->create([
        'name' => 'TinyMCE Upload Administrator',
        'email' => 'tinymce-upload@example.test',
        'password' => 'password',
        'is_active' => true,
    ]);
    $user->assignRole('super_admin');

    $this->actingAs($user);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(CreateInsight::class)
        ->set('componentFileAttachments.data.content', UploadedFile::fake()->image('article.png')->size(512))
        ->call('callSchemaComponentMethod', 'form.content', 'saveUploadedFileAttachmentAndGetUrl')
        ->assertHasNoErrors();

    expect(Storage::disk('public')->allFiles('insights/content-images'))
        ->toHaveCount(1)
        ->and(Storage::disk('public')->allFiles('insights/content-images')[0])
        ->toEndWith('.png');
});
