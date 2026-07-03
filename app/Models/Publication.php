<?php

namespace App\Models;

use App\Services\PdfCoverGenerator;
use App\Support\EdulawSite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Publication extends Model
{
    use HasFactory;

    protected $fillable = [
        'publication_type_id',
        'title',
        'slug',
        'excerpt',
        'description',
        'cover_image',
        'pdf_file',
        'external_url',
        'source_name',
        'published_at',
        'page_count',
        'status',
        'featured',
        'seo_title',
        'seo_description',
        'og_image',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'published_at' => 'date',
        'featured' => 'boolean',
        'page_count' => 'integer',
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

    private function needsPdfCover(): bool
    {
        if (blank($this->pdf_file)) {
            return false;
        }

        return $this->isDirty('pdf_file') || blank($this->cover_image);
    }
}
