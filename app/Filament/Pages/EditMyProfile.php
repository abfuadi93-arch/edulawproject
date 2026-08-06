<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Insights\InsightResource;
use App\Filament\Resources\Publications\PublicationResource;
use App\Models\Author;
use App\Models\Insight;
use App\Models\PageVisit;
use App\Models\Program;
use App\Models\Publication;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LogicException;

class EditMyProfile extends Page
{
    protected static ?string $title = 'Profil Saya';

    protected static ?string $navigationLabel = 'Profil Saya';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static string|\UnitEnum|null $navigationGroup = 'Akun';

    protected static ?int $navigationSort = -9;

    protected string $view = 'filament.pages.edit-my-profile';

    protected Width|string|null $maxContentWidth = Width::Full;

    public Author $author;

    /** @var array<string, mixed> */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return Auth::check();
    }

    public function getSubheading(): ?string
    {
        return 'Kelola profil publik kontributor yang terhubung dengan akun Anda.';
    }

    public function mount(): void
    {
        $this->author = $this->getUser()->ensureProfile();

        $this->form->fill([
            ...$this->author->only([
                'photo',
                'name',
                'slug',
                'title',
                'bio',
                'interests',
                'position',
                'institution',
                'location',
                'email',
                'show_in_organization',
                'seo_title',
                'meta_description',
            ]),
            'interests' => $this->author->interests,
            'social_links' => $this->author->socialLinksMap(),
        ]);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->model($this->author)
            ->operation('edit')
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->extraAttributes(['class' => 'edulaw-admin-self-profile-form'])
            ->components([
                Grid::make([
                    'default' => 1,
                    'lg' => 10,
                ])
                    ->schema([
                        Group::make()
                            ->schema([
                                Section::make('Identitas Penulis')
                                    ->icon('heroicon-o-identification')
                                    ->description('Data ini menjadi sumber tunggal nama, foto, dan biografi pada seluruh konten Edulaw.')
                                    ->schema([
                                        FileUpload::make('photo')
                                            ->label('Foto Profil')
                                            ->image()
                                            ->avatar()
                                            ->disk('public')
                                            ->directory('authors')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->imageCropAspectRatio('1:1')
                                            ->imageEditorAspectRatios(['1:1'])
                                            ->imagePreviewHeight('190')
                                            ->maxSize(4096)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                            ->helperText('JPG, PNG, atau WebP. Crop persegi, maksimal 4 MB.')
                                            ->columnSpanFull(),

                                        Grid::make([
                                            'default' => 1,
                                            'md' => 2,
                                        ])->schema([
                                            TextInput::make('name')
                                                ->label('Nama Lengkap')
                                                ->required()
                                                ->maxLength(255)
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(function ($get, $set, ?string $old, ?string $state): void {
                                                    $currentSlug = (string) ($get('slug') ?? '');

                                                    if (filled($currentSlug) && $currentSlug !== Str::slug((string) $old)) {
                                                        return;
                                                    }

                                                    $set('slug', Str::slug((string) $state));
                                                }),

                                            TextInput::make('slug')
                                                ->label('Slug')
                                                ->required()
                                                ->alphaDash()
                                                ->maxLength(255)
                                                ->unique(Author::class, 'slug', ignoreRecord: true)
                                                ->helperText('Dibuat otomatis dari nama dan tetap dapat disunting.'),

                                            TextInput::make('title')
                                                ->label('Gelar')
                                                ->maxLength(100)
                                                ->placeholder('S.H., M.H.')
                                                ->columnSpanFull(),
                                        ])->columnSpanFull(),

                                        Textarea::make('bio')
                                            ->label('Tentang Saya')
                                            ->rows(6)
                                            ->maxLength(1000)
                                            ->live(debounce: 300)
                                            ->hint(fn (?string $state): string => sprintf('%d/1000 karakter', mb_strlen((string) $state)))
                                            ->helperText('Biografi publik singkat. Paragraf dan baris baru akan dipertahankan.')
                                            ->columnSpanFull(),

                                        TagsInput::make('interests')
                                            ->label('Bidang Keahlian')
                                            ->separator(',')
                                            ->splitKeys([',', 'Enter'])
                                            ->placeholder('Tambahkan bidang keahlian')
                                            ->suggestions([
                                                'Hukum Tata Negara',
                                                'Pemilu',
                                                'Mahkamah Konstitusi',
                                                'Legislasi',
                                                'Media Sosial',
                                                'Kebijakan Publik',
                                            ])
                                            ->helperText('Tekan Enter atau koma untuk menambahkan tag.')
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columnSpan([
                                'default' => 1,
                                'lg' => 7,
                            ]),

                        Group::make()
                            ->schema([
                                Section::make('Informasi Profesional')
                                    ->icon('heroicon-o-briefcase')
                                    ->schema([
                                        TextInput::make('position')
                                            ->label('Jabatan')
                                            ->maxLength(255),

                                        TextInput::make('institution')
                                            ->label('Organisasi')
                                            ->maxLength(255),

                                        TextInput::make('location')
                                            ->label('Lokasi')
                                            ->maxLength(255)
                                            ->placeholder('Jakarta, Indonesia'),

                                        TextInput::make('email')
                                            ->label('Email Publik')
                                            ->email()
                                            ->maxLength(255)
                                            ->helperText('Tidak mengubah email login akun.'),

                                        Checkbox::make('show_in_organization')
                                            ->label('Tampilkan di halaman Tentang')
                                            ->helperText('Profil tetap terhubung ke konten meskipun tidak ditampilkan dalam struktur organisasi.'),
                                    ]),

                                Section::make('Tautan Profesional')
                                    ->icon('heroicon-o-link')
                                    ->schema([
                                        TextInput::make('social_links.website')->label('Website')->url()->maxLength(500)->placeholder('https://...'),
                                        TextInput::make('social_links.linkedin')->label('LinkedIn')->url()->maxLength(500)->placeholder('https://linkedin.com/in/...'),
                                        TextInput::make('social_links.google_scholar')->label('Google Scholar')->url()->maxLength(500)->placeholder('https://scholar.google.com/...'),
                                        TextInput::make('social_links.orcid')->label('ORCID')->maxLength(100)->placeholder('0000-0000-0000-0000'),
                                        TextInput::make('social_links.scopus')->label('Scopus ID')->maxLength(100),
                                    ])
                                    ->collapsible()
                                    ->collapsed(),

                                Section::make('Media Sosial')
                                    ->icon('heroicon-o-share')
                                    ->schema([
                                        TextInput::make('social_links.instagram')->label('Instagram')->url()->maxLength(500),
                                        TextInput::make('social_links.twitter')->label('Twitter / X')->url()->maxLength(500),
                                        TextInput::make('social_links.youtube')->label('YouTube')->url()->maxLength(500),
                                        TextInput::make('social_links.researchgate')->label('ResearchGate')->url()->maxLength(500),
                                    ])
                                    ->collapsible()
                                    ->collapsed(),

                                Section::make('SEO (Opsional)')
                                    ->icon('heroicon-o-magnifying-glass')
                                    ->schema([
                                        TextInput::make('seo_title')
                                            ->label('SEO Title')
                                            ->maxLength(300)
                                            ->helperText('Target 45–65 karakter. Gunakan judul natural; nama situs ditambahkan otomatis.'),
                                        Textarea::make('meta_description')
                                            ->label('Meta Description')
                                            ->rows(4)
                                            ->maxLength(180)
                                            ->helperText('Target 120–160 karakter. Jelaskan profil dan bidang kontribusi secara alami.')
                                            ->hint(fn (?string $state): string => sprintf('%d/180', mb_strlen((string) $state))),
                                    ])
                                    ->collapsible()
                                    ->collapsed(),
                            ])
                            ->columnSpan([
                                'default' => 1,
                                'lg' => 3,
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewPublicProfile')
                ->label('Lihat Profil Publik')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn (): string => route('profiles.show', $this->author->slug))
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->author->is_active && filled($this->author->slug)),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $data['slug'] = Str::slug((string) ($data['slug'] ?? ''));

        if (blank($data['slug'])) {
            $data['slug'] = Author::uniqueSlugFor((string) $data['name'], $this->author->id);
        }

        $allowedSocialLinks = [
            'website',
            'linkedin',
            'google_scholar',
            'orcid',
            'scopus',
            'instagram',
            'twitter',
            'youtube',
            'researchgate',
        ];

        $data['social_links'] = collect(Arr::only($data['social_links'] ?? [], $allowedSocialLinks))
            ->map(fn ($value): string => trim((string) $value))
            ->filter()
            ->all();

        DB::transaction(function () use ($data): void {
            $this->author->fill(Arr::only($data, [
                'photo',
                'name',
                'slug',
                'title',
                'bio',
                'interests',
                'position',
                'institution',
                'location',
                'email',
                'social_links',
                'show_in_organization',
                'seo_title',
                'meta_description',
            ]));
            $this->author->save();
        });

        $this->author->refresh();

        Notification::make()
            ->success()
            ->title('Profil publik berhasil diperbarui')
            ->body('Perubahan langsung digunakan pada halaman penulis dan konten terkait.')
            ->send();
    }

    /** @return array{insights: int, publications: int, programs: int, views: int} */
    public function getProfileStatistics(): array
    {
        return [
            'insights' => $this->author->insights()->published()->count(),
            'publications' => $this->author->publications()->count(),
            'programs' => Program::query()->where('created_by', $this->author->user_id)->count(),
            'views' => $this->getTotalViews(),
        ];
    }

    /** @return EloquentCollection<int, Insight> */
    public function getLatestInsights(): EloquentCollection
    {
        return $this->author->insights()
            ->latest('insights.updated_at')
            ->latest('insights.id')
            ->limit(5)
            ->get();
    }

    /** @return EloquentCollection<int, Publication> */
    public function getLatestPublications(): EloquentCollection
    {
        return $this->author->publications()
            ->latest('publications.updated_at')
            ->latest('publications.id')
            ->limit(5)
            ->get();
    }

    public function getInsightEditUrl(Insight $insight): ?string
    {
        return $this->getUser()->can('update', $insight)
            ? InsightResource::getUrl('edit', ['record' => $insight])
            : null;
    }

    public function getPublicationEditUrl(Publication $publication): ?string
    {
        return $this->getUser()->can('update', $publication)
            ? PublicationResource::getUrl('edit', ['record' => $publication])
            : null;
    }

    private function getTotalViews(): int
    {
        $paths = collect(['/profil/'.$this->author->slug])
            ->merge($this->author->insights()->pluck('slug')->map(fn (string $slug): string => '/insight/'.$slug))
            ->merge($this->author->publications()->pluck('slug')->map(fn (string $slug): string => '/riset-publikasi/'.$slug))
            ->merge(Program::query()->where('created_by', $this->author->user_id)->pluck('slug')->map(fn (string $slug): string => '/program/'.$slug))
            ->unique()
            ->values();

        return PageVisit::query()
            ->whereIn('path', $paths)
            ->whereBetween('status_code', [200, 399])
            ->count();
    }

    private function getUser(): User&Model
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            throw new LogicException('Authenticated user must be an App\\Models\\User instance.');
        }

        return $user;
    }
}
