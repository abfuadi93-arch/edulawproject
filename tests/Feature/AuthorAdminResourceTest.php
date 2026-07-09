<?php

use App\Filament\Resources\Authors\AuthorResource;
use App\Models\Author;

test('author admin resource derives slug from name when slug is empty', function () {
    $data = AuthorResource::prepareFormDataForPersistence([
        'name' => 'Nabila Rahma',
        'slug' => '',
    ]);

    expect($data['slug'])->toBe('nabila-rahma');
});

test('author bio supports one thousand characters', function () {
    $bio = str_repeat('a', 1000);

    $author = Author::create([
        'name' => 'Profil Bio Panjang',
        'slug' => 'profil-bio-panjang',
        'profile_type' => 'team',
        'bio' => $bio,
        'is_active' => true,
    ]);

    expect(mb_strlen((string) $author->fresh()->bio))->toBe(1000);
});
