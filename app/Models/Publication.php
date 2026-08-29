<?php

namespace App\Models;

use App\Services\PdfCoverGenerator;
use App\Support\EdulawSite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Publication extends Model
{
    use HasFactory;

    protected $fillable = [
        'publication_type_id',
        'title',
        'slug',
        'excerpt',
        'description',
        'research_questions',
        'key_findings',
        'methodology',
        'contribution',
        'implications',
        'cover_image',
        'pdf_file',
        'external_url',
        'source_name',
        'citation_text',
        'language',
        'published_at',
        'publication_date_text',
        'page_count',
        'status',
        'featured',
        'seo_title',
        'seo_description',
        'og_image',
        'share_title',
        'share_description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'published_at' => 'date',
        'featured' => 'boolean',
        'page_count' => 'integer',
        'research_questions' => 'array',
        'key_findings' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (Publication $publication): void {
            if (! $publication->needsPdfCover()) {
                return;
            }

            $coverImage = app(PdfCoverGenerator::class)->generate(
                $publication->pdf_file,
                $publication->slug ?: $publication->title,
            );

            if ($coverImage !== null) {
                $publication->cover_image = $coverImage;
            }
        });
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(PublicationType::class, 'publication_type_id');
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'author_publication')
            ->withPivot(['author_order', 'role'])
            ->orderByPivot('author_order')
            ->withTimestamps();
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'publication_tag')
            ->withTimestamps();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->where(function (Builder $query) {
                $query->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        return EdulawSite::assetUrl($this->attributes['cover_image'] ?? null);
    }

    public function getDownloadUrlAttribute(): ?string
    {
        return EdulawSite::assetUrl($this->attributes['pdf_file'] ?? null)
            ?: EdulawSite::resolveUrl($this->attributes['external_url'] ?? null);
    }

    public function getCitationAttribute(): string
    {
        return $this->getCitation();
    }

    public function getCitation(string $style = 'apa'): string
    {
        $style = Str::lower(trim($style));

        if ($style === 'apa' && filled($this->citation_text)) {
            return trim((string) $this->citation_text);
        }

        $authorLabel = $this->authors
            ->sortBy(fn (Author $author): int => (int) ($author->pivot?->author_order ?? PHP_INT_MAX))
            ->pluck('name')
            ->map(fn ($name): string => trim((string) $name))
            ->filter()
            ->join(', ');
        $authorLabel = $authorLabel !== '' ? $authorLabel : 'Edulaw Project';
        $year = $this->publication_year ?: 'n.d.';
        $publisher = trim((string) ($this->source_name ?: 'Edulaw Project'));
        $title = trim((string) ($this->title ?: 'Publikasi Edulaw Project'));
        $url = filled($this->slug)
            ? route('publications.show', $this->slug)
            : url('/riset-publikasi');

        return match ($style) {
            'chicago' => "{$authorLabel}. {$year}. \"{$title}.\" {$publisher}. {$url}.",
            'mla' => "{$authorLabel}. \"{$title}.\" {$publisher}, {$year}, {$url}.",
            'ieee' => "{$authorLabel}, \"{$title},\" {$publisher}, {$year}. [Online]. Available: {$url}",
            'harvard' => "{$authorLabel} {$year}, {$title}, {$publisher}, viewed {$url}.",
            default => "{$authorLabel}. ({$year}). {$title}. {$publisher}. {$url}",
        };
    }

    /**
     * @return array<string, string>
     */
    public function citationFormats(): array
    {
        return collect(['apa', 'chicago', 'mla', 'ieee', 'harvard'])
            ->mapWithKeys(fn (string $style): array => [$style => $this->getCitation($style)])
            ->all();
    }

    public function getSharePreviewTitleAttribute(): string
    {
        return trim((string) ($this->share_title ?: $this->seo_title ?: $this->title ?: 'Publikasi Edulaw Project'));
    }

    public function getSharePreviewDescriptionAttribute(): string
    {
        $description = $this->share_description
            ?: $this->seo_description
            ?: $this->excerpt
            ?: $this->description;
        $description = trim(preg_replace('/\s+/', ' ', strip_tags((string) $description)) ?? '');

        return $description !== ''
            ? Str::limit($description, 220)
            : 'Baca publikasi hukum, riset, dan kebijakan dari Edulaw Project.';
    }

    public function getSharePreviewImageUrlAttribute(): ?string
    {
        return EdulawSite::assetUrl($this->og_image ?: $this->cover_image);
    }

    public function getPublicUrlAttribute(): ?string
    {
        return filled($this->slug) ? route('publications.show', $this->slug) : null;
    }

    public function getPublicationDateDisplayAttribute(): string
    {
        if (filled($this->publication_date_text)) {
            return trim((string) $this->publication_date_text);
        }

        return $this->published_at?->locale('id')->translatedFormat('d F Y') ?: 'Belum diketahui';
    }

    public function getPublicationYearAttribute(): ?string
    {
        if ($this->published_at) {
            return $this->published_at->format('Y');
        }

        preg_match('/\b(?:19|20)\d{2}\b/', (string) $this->publication_date_text, $matches);

        return $matches[0] ?? null;
    }

    private function needsPdfCover(): bool
    {
        if (blank($this->pdf_file)) {
            return false;
        }

        return $this->isDirty('pdf_file') || blank($this->cover_image);
    }
}
