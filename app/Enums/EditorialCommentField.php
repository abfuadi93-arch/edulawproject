<?php

namespace App\Enums;

enum EditorialCommentField: string
{
    case Title = 'title';
    case Excerpt = 'excerpt';
    case Content = 'content';
    case CoverImage = 'cover_image';
    case Category = 'category';
    case Authors = 'authors';
    case Tags = 'tags';
    case SeoTitle = 'seo_title';
    case SeoDescription = 'seo_description';
    case References = 'references';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Title => 'Judul',
            self::Excerpt => 'Ringkasan',
            self::Content => 'Isi Artikel',
            self::CoverImage => 'Gambar Utama',
            self::Category => 'Kategori',
            self::Authors => 'Penulis',
            self::Tags => 'Topik',
            self::SeoTitle => 'SEO Title',
            self::SeoDescription => 'Meta Description',
            self::References => 'Referensi',
            self::Other => 'Lainnya',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $field): array => [$field->value => $field->label()])->all();
    }
}
