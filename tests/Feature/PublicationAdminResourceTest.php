<?php

use App\Filament\Resources\Publications\PublicationResource;
use App\Models\Author;
use App\Models\Publication;
use App\Models\PublicationType;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

test('publication schema exposes citation share and language metadata safely', function () {
    expect(Schema::hasColumns('publications', [
        'citation_text',
        'share_title',
        'share_description',
        'language',
    ]))->toBeTrue();

    $publication = Publication::query()->create([
        'title' => 'Draft Metadata Publikasi',
        'slug' => 'draft-metadata-publikasi',
    ])->fresh();

    expect($publication->language)->toBe('id')
        ->and($publication->citation_text)->toBeNull()
        ->and($publication->share_title)->toBeNull()
        ->and($publication->share_description)->toBeNull();
});

test('draft publication derives a safe excerpt but may remain without a document', function () {
    $description = '<p>Kajian ini membahas reformasi hukum acara, akses keadilan, dan penguatan literasi hukum publik untuk masyarakat luas.</p>';

    $data = PublicationResource::prepareFormDataForPersistence([
        'title' => 'Reformasi Hukum Acara',
        'slug' => '',
        'status' => 'draft',
        'description' => $description,
        'excerpt' => null,
        'source_name' => null,
        'language' => null,
        'external_url' => null,
        'pdf_file' => null,
    ]);

    expect($data['slug'])->toBe('reformasi-hukum-acara')
        ->and($data['excerpt'])->toStartWith('Kajian ini membahas')
        ->and(mb_strlen($data['excerpt']))->toBeLessThanOrEqual(220)
        ->and($data['source_name'])->toBeNull()
        ->and($data['language'])->toBe('id')
        ->and($data['external_url'])->toBeNull()
        ->and($data['pdf_file'])->toBeNull();
});

test('published publication rejects incomplete required metadata and document', function () {
    try {
        PublicationResource::prepareFormDataForPersistence([
            'title' => 'Publikasi Belum Siap',
            'status' => 'published',
            'publication_type_id' => null,
            'authors' => [],
            'excerpt' => null,
            'published_at' => null,
            'pdf_file' => null,
            'external_url' => null,
        ]);

        $this->fail('ValidationException was not thrown.');
    } catch (ValidationException $exception) {
        expect($exception->errors())
            ->toHaveKeys([
                'publication_type_id',
                'authors',
                'excerpt',
                'published_at',
                'pdf_file',
            ])
            ->and($exception->errors()['excerpt'][0])
            ->toBe('Publikasi yang diterbitkan wajib memiliki ringkasan.')
            ->and($exception->errors()['published_at'][0])
            ->toBe('Publikasi yang diterbitkan wajib memiliki tanggal publikasi.')
            ->and($exception->errors()['pdf_file'][0])
            ->toBe('Publikasi yang diterbitkan wajib memiliki PDF atau External URL.');
    }
});

test('published publication accepts an external source when pdf is unavailable', function () {
    $data = PublicationResource::prepareFormDataForPersistence([
        'title' => 'Publikasi dari Sumber Resmi',
        'slug' => '',
        'status' => 'published',
        'publication_type_id' => 1,
        'authors' => [1],
        'excerpt' => 'Ringkasan publikasi yang siap diterbitkan.',
        'published_at' => '2026-07-31',
        'pdf_file' => null,
        'external_url' => 'https://example.test/publikasi',
    ]);

    expect($data['slug'])->toBe('publikasi-dari-sumber-resmi')
        ->and($data['external_url'])->toBe('https://example.test/publikasi');
});

test('publication builds citation and share preview from safe fallbacks', function () {
    $type = PublicationType::query()->create([
        'name' => 'Policy Brief',
        'slug' => 'policy-brief',
        'is_active' => true,
    ]);
    $author = Author::query()->create([
        'name' => 'Nadia Peneliti',
        'slug' => 'nadia-peneliti-publikasi',
    ]);
    $publication = Publication::query()->create([
        'publication_type_id' => $type->id,
        'title' => 'Reformasi Kebijakan Hukum',
        'slug' => 'reformasi-kebijakan-hukum',
        'excerpt' => 'Ringkasan kebijakan hukum untuk pembaca.',
        'cover_image' => 'publications/covers/reformasi.webp',
        'source_name' => null,
        'published_at' => '2026-07-31',
        'status' => 'published',
    ]);
    $publication->authors()->attach($author->id, ['author_order' => 1, 'role' => 'Penulis']);
    $publication->refresh()->load('authors');

    expect($publication->citation)
        ->toContain('Nadia Peneliti. (2026). Reformasi Kebijakan Hukum. Edulaw Project.')
        ->toContain(route('publications.show', $publication->slug))
        ->and($publication->share_preview_title)->toBe('Reformasi Kebijakan Hukum')
        ->and($publication->share_preview_description)->toBe('Ringkasan kebijakan hukum untuk pembaca.')
        ->and($publication->share_preview_image_url)->toContain('publications/covers/reformasi.webp')
        ->and($publication->public_url)->toBe(route('publications.show', $publication->slug));

    $publication->update([
        'citation_text' => 'Sitasi khusus redaksi.',
        'seo_title' => 'SEO Kebijakan Hukum',
        'share_title' => 'Judul Khusus untuk Share',
        'share_description' => 'Deskripsi khusus untuk media sosial.',
    ]);

    expect($publication->fresh()->citation)->toBe('Sitasi khusus redaksi.')
        ->and($publication->fresh()->share_preview_title)->toBe('Judul Khusus untuk Share')
        ->and($publication->fresh()->share_preview_description)->toBe('Deskripsi khusus untuk media sosial.');
});

test('publication provides five citation formats with safe metadata fallbacks', function () {
    $publication = Publication::query()->create([
        'title' => 'Kajian Hukum Tanpa Metadata Lengkap',
        'slug' => 'kajian-hukum-tanpa-metadata-lengkap',
        'status' => 'draft',
    ]);

    $formats = $publication->citationFormats();

    expect(array_keys($formats))->toBe(['apa', 'chicago', 'mla', 'ieee', 'harvard'])
        ->and($formats['apa'])
        ->toContain('Edulaw Project. (n.d.). Kajian Hukum Tanpa Metadata Lengkap.')
        ->toContain(route('publications.show', $publication->slug))
        ->and($formats['chicago'])->toContain('"Kajian Hukum Tanpa Metadata Lengkap."')
        ->and($formats['mla'])->toContain('Edulaw Project, n.d.')
        ->and($formats['ieee'])->toContain('[Online]. Available:')
        ->and($formats['harvard'])->toContain('viewed '.route('publications.show', $publication->slug));
});

test('publication admin share preview is never empty', function () {
    $preview = (string) PublicationResource::sharePreviewHtml([
        'title' => null,
        'seo_title' => null,
        'share_title' => null,
        'excerpt' => null,
        'description' => null,
        'seo_description' => null,
        'share_description' => null,
        'og_image' => null,
        'cover_image' => null,
        'slug' => null,
    ]);

    expect($preview)
        ->toContain('Publikasi Edulaw Project')
        ->toContain('Baca publikasi hukum, riset, dan kebijakan')
        ->toContain('/riset-publikasi');
});

test('publication admin resource exposes simplified statuses and keeps archived as legacy label', function () {
    expect(PublicationResource::statusOptions())->toBe([
        'draft' => 'Draft',
        'reviewed' => 'Reviewed',
        'published' => 'Published',
    ])
        ->and(PublicationResource::statusLabel('archived'))->toBe('Archived')
        ->and(PublicationResource::statusLabel('reviewed'))->toBe('Reviewed')
        ->and(PublicationResource::normalizeStatusForForm('archived'))->toBe('draft');
});

test('super admin can open publication list create and edit forms', function () {
    $role = Role::findOrCreate('super_admin');
    $user = User::query()->create([
        'name' => 'Super Admin Publikasi',
        'email' => 'publication-admin@example.test',
        'password' => 'secret-password',
        'is_active' => true,
    ]);
    $user->assignRole($role);

    $publication = Publication::query()->create([
        'title' => 'Publikasi untuk Diedit',
        'slug' => 'publikasi-untuk-diedit',
        'status' => 'draft',
        'citation_text' => 'Sitasi tersimpan.',
        'share_title' => 'Judul Share Tersimpan',
        'share_description' => 'Deskripsi share tersimpan.',
    ]);

    $this->actingAs($user)
        ->get(PublicationResource::getUrl('index'))
        ->assertOk()
        ->assertSee('Tambah Publikasi')
        ->assertSee('Kelola riset, publikasi, dokumen, dan metadata penerbitan.');

    $this->actingAs($user)
        ->get(PublicationResource::getUrl('create'))
        ->assertOk()
        ->assertSee('Sitasi dan Share Preview')
        ->assertSee('SEO Publikasi')
        ->assertSee('Isi jika ingin menggunakan sitasi khusus');

    $this->actingAs($user)
        ->get(PublicationResource::getUrl('edit', ['record' => $publication]))
        ->assertOk()
        ->assertSee('Sitasi tersimpan.')
        ->assertSee('Judul Share Tersimpan');
});
