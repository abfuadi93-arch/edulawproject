<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Insight;
use App\Models\InsightCategory;
use App\Models\Multimedia;
use App\Models\Opportunity;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\Publication;
use App\Models\PublicationType;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RelevantDataSeeder extends Seeder
{
    private array $dumpCache = [];

    public function run(): void
    {
        $admin = User::where('email', 'admin@edulaw.test')->first() ?? User::query()->first();

        if (! $admin) {
            return;
        }

        $this->seedAuthors($admin);
        $this->seedTags();
        $this->seedAbfuadiUsers();
        $this->seedPrograms($admin);
        $this->seedInsights($admin);
        $this->seedPublications($admin);
        $this->seedMultimedia($admin);
        $this->seedOpportunities($admin);
    }

    private function seedAuthors(User $admin): void
    {
        $this->authorByName('Edulaw Project', [
            'email' => 'projectedulaw@gmail.com',
            'institution' => 'Edulaw Project',
            'position' => 'Editorial Team',
            'profile_type' => 'team',
            'interests' => 'Literasi hukum, publikasi, program edukasi, kebijakan publik.',
            'bio' => 'Tim editorial Edulaw Project yang mengelola publikasi, program, dan literasi hukum untuk publik.',
        ]);

        foreach ($this->projectRows('founders') as $row) {
            $this->authorByName($row['name'] ?? null, [
                'email' => $row['email'] ?? null,
                'bio' => $row['bio'] ?? null,
                'photo' => $row['photo'] ?? null,
                'institution' => $row['affiliation'] ?? 'Edulaw Project',
                'position' => $row['title'] ?? $row['role'] ?? null,
                'profile_type' => Str::contains(Str::lower((string) ($row['title'] ?? $row['role'] ?? '')), 'founder') ? 'founder' : 'team',
                'social_links' => $row['linkedin_url'] ? ['linkedin' => $row['linkedin_url']] : null,
            ]);
        }

        foreach ($this->projectRows('users') as $row) {
            if (($row['name'] ?? null) === 'Edulaw Project') {
                continue;
            }

            $this->authorByName($row['name'] ?? null, [
                'email' => $row['email'] ?? null,
                'bio' => $row['author_bio'] ?? null,
                'photo' => $row['author_photo'] ?? null,
                'institution' => $row['author_affiliation'] ?: 'Edulaw Project',
                'position' => isset($row['role']) ? Str::headline($row['role']) : null,
                'profile_type' => 'internal_author',
            ]);
        }

        foreach ($this->legacyRows('users') as $row) {
            $name = $row['full_name']
                ?: trim(($row['first_name'] ?? '').' '.($row['last_name'] ?? ''));

            $this->authorByName($name, [
                'email' => $row['email'] ?? null,
                'photo' => $row['image'] ?? null,
                'institution' => 'Edulaw Project',
                'position' => isset($row['role']) ? Str::headline($row['role']) : null,
                'profile_type' => 'contributor',
            ]);
        }
    }

    private function seedTags(): void
    {
        foreach ($this->projectRows('insight_topics') as $row) {
            $this->tagByName($row['name'] ?? null);
        }

        foreach ($this->projectRows('research') as $row) {
            foreach ($this->jsonArray($row['keywords'] ?? null) as $keyword) {
                $this->tagByName(is_array($keyword) ? null : $keyword);
            }
        }

        foreach ([...$this->legacyRows('insights'), ...$this->legacyRows('news_posts')] as $row) {
            foreach ($this->jsonArray($row['tags'] ?? null) as $tag) {
                $this->tagByName(is_array($tag) ? null : $tag);
            }
        }
    }

    private function seedAbfuadiUsers(): void
    {
        foreach ($this->abfuadiRows('edulaw2_users') as $row) {
            $this->userFromDump([
                'name' => $row['name'] ?? null,
                'email' => $row['email'] ?? null,
                'password' => $row['password'] ?? null,
                'bio' => $row['author_bio'] ?? null,
                'institution' => $row['author_affiliation'] ?: 'Edulaw Project',
                'position' => $row['role'] ?? null,
                'email_verified_at' => $row['email_verified_at'] ?? null,
                'remember_token' => $row['remember_token'] ?? null,
                'created_at' => $row['created_at'] ?? null,
                'updated_at' => $row['updated_at'] ?? null,
                'role' => $row['role'] ?? null,
            ]);
        }

        foreach ($this->abfuadiRows('users') as $row) {
            $this->userFromDump([
                'name' => $row['full_name'] ?: trim(($row['first_name'] ?? '').' '.($row['last_name'] ?? '')),
                'email' => $row['email'] ?? null,
                'password' => $row['password'] ?? null,
                'bio' => $row['address'] ?? null,
                'institution' => 'Edulaw Project',
                'position' => $row['role'] ?? null,
                'email_verified_at' => $row['email_verified_at'] ?? null,
                'remember_token' => $row['remember_token'] ?? null,
                'created_at' => $row['created_at'] ?? null,
                'updated_at' => $row['updated_at'] ?? null,
                'role' => $row['role'] ?? null,
            ]);
        }
    }

    private function userFromDump(array $data): ?User
    {
        $email = trim((string) ($data['email'] ?? ''));
        $name = trim((string) ($data['name'] ?? ''));

        if ($email === '' || $name === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        $existing = DB::table('users')->where('email', $email)->first();
        $values = [
            'name' => $name,
            'password' => $data['password'] ?: ($existing->password ?? null),
            'bio' => $data['bio'] ?? ($existing->bio ?? null),
            'institution' => $data['institution'] ?? ($existing->institution ?? null),
            'position' => isset($data['position']) ? Str::headline($data['position']) : ($existing->position ?? null),
            'is_active' => true,
            'email_verified_at' => $data['email_verified_at'] ?? ($existing->email_verified_at ?? null),
            'remember_token' => $data['remember_token'] ?? ($existing->remember_token ?? null),
            'updated_at' => $data['updated_at'] ?? now(),
        ];

        if (! $values['password']) {
            return null;
        }

        if ($existing) {
            DB::table('users')->where('email', $email)->update($values);
        } else {
            DB::table('users')->insert([
                ...$values,
                'email' => $email,
                'created_at' => $data['created_at'] ?? now(),
            ]);
        }

        $user = User::where('email', $email)->first();

        $role = $this->panelRoleFor($data['role'] ?? null);

        if ($role) {
            $user->assignRole($role);
        }

        $author = Author::where('email', $email)->first();

        if ($author && ! $author->user_id) {
            $author->forceFill(['user_id' => $user->id])->save();
        }

        $user->ensureProfile();

        return $user;
    }

    private function seedPrograms(User $admin): void
    {
        foreach ($this->projectRows('programs') as $row) {
            if (($row['publication_status'] ?? null) !== 'published') {
                continue;
            }

            $title = $row['title'] ?? null;

            if (! $title) {
                continue;
            }

            $category = $this->programCategoryFor($row);
            $highlights = collect($this->jsonArray($row['highlights'] ?? null))
                ->map(fn ($point) => ['point' => is_array($point) ? (string) reset($point) : (string) $point])
                ->filter(fn ($point) => trim($point['point']) !== '')
                ->values()
                ->all();

            $speakers = collect($this->jsonArray($row['speakers'] ?? null))
                ->map(fn ($speaker) => [
                    'name' => $speaker['name'] ?? null,
                    'title' => $speaker['title'] ?? null,
                ])
                ->filter(fn ($speaker) => trim((string) $speaker['name']) !== '')
                ->values()
                ->all();

            $startDate = $row['start_date'] ?? null;
            $endDate = $row['end_date'] ?? null;

            if ($startDate && $endDate && $endDate < $startDate) {
                $endDate = $startDate;
            }

            Program::updateOrCreate(
                ['slug' => $row['slug'] ?: Str::slug($title)],
                [
                    'program_category_id' => $category->id,
                    'name' => $title,
                    'short_description' => $row['description'] ?? null,
                    'learning_points' => $highlights,
                    'image' => $this->existingStoragePath($row['image'] ?? null),
                    'format' => $this->normalizedFormat($row['format'] ?? null),
                    'level' => $row['level'] ?? null,
                    'audience' => 'Mahasiswa, akademisi, peneliti, praktisi hukum, dan masyarakat umum',
                    'event_date' => $startDate,
                    'end_date' => $endDate,
                    'speakers' => $speakers,
                    'registration_link' => $row['registration_url'] ?? null,
                    'location' => $this->locationFor($row['format'] ?? null),
                    'price_type' => 'Gratis',
                    'certificate_available' => Str::contains(Str::lower((string) ($row['notes'] ?? '')), 'sertifikat'),
                    'status' => 'archived',
                    'featured' => (bool) ($row['featured'] ?? $row['show_on_home'] ?? false),
                    'sort_order' => (int) ($row['sort_order'] ?? 0),
                    'seo_title' => $title,
                    'seo_description' => $row['description'] ?? null,
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                    'created_at' => $row['created_at'] ?? now(),
                    'updated_at' => $row['updated_at'] ?? now(),
                ]
            );
        }

        $this->seedLegacyClasses($admin);
    }

    private function seedInsights(User $admin): void
    {
        foreach ($this->projectRows('insights') as $row) {
            $title = $row['title'] ?? null;

            if (! $title) {
                continue;
            }

            $category = $this->insightCategoryFor($row['category_id'] ?? null);
            $content = $row['content'] ?? null;
            $author = $this->authorByName($row['author_name'] ?: 'Edulaw Project', [
                'institution' => $row['author_affiliation'] ?: 'Edulaw Project',
                'photo' => $row['author_photo'] ?? null,
            ]);

            $insight = Insight::updateOrCreate(
                ['slug' => $row['slug'] ?: Str::slug($title)],
                [
                    'insight_category_id' => $category->id,
                    'title' => $title,
                    'excerpt' => $row['excerpt'] ?: $row['summary'] ?: $this->excerptFromHtml($content),
                    'content' => $content,
                    'cover_image' => $this->existingStoragePath($row['thumbnail'] ?: ($row['image'] ?? null)),
                    'status' => $row['status'] ?? 'draft',
                    'published_at' => $row['published_at'] ?? null,
                    'reading_time' => $this->readingMinutes($content),
                    'featured' => (bool) ($row['featured'] ?? false),
                    'sort_order' => (int) ($row['id'] ?? 0),
                    'seo_title' => $row['seo_title'] ?: $title,
                    'seo_description' => $row['meta_description'] ?: $this->excerptFromHtml($content, 155),
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                    'reviewed_by' => $admin->id,
                    'reviewed_at' => $row['status'] === 'published' ? ($row['updated_at'] ?? now()) : null,
                    'created_at' => $row['created_at'] ?? now(),
                    'updated_at' => $row['updated_at'] ?? now(),
                ]
            );

            $insight->authors()->syncWithoutDetaching([
                $author->id => ['author_order' => 1, 'role' => 'Author'],
            ]);

            $tagIds = collect($this->jsonArray($row['topic'] ?? null))
                ->map(fn ($tag) => $this->tagByName(is_array($tag) ? null : $tag)?->id)
                ->filter()
                ->values()
                ->all();

            if ($tagIds) {
                $insight->tags()->syncWithoutDetaching($tagIds);
            }
        }

        $this->seedLegacyInsights($admin);
    }

    private function seedLegacyClasses(User $admin): void
    {
        foreach ($this->legacyRows('edulaw_classes') as $row) {
            $title = $row['title'] ?? null;

            if (! $title || ($row['status'] ?? null) !== '1') {
                continue;
            }

            $category = $this->programCategoryByName(
                ($row['type'] ?? null) === 'batch' ? 'Kelas Hukum' : 'Workshop / Webinar',
                'Kategori program yang diimpor dari kelas Edulaw lama.'
            );

            Program::updateOrCreate(
                ['slug' => $row['slug'] ?: Str::slug($title)],
                [
                    'program_category_id' => $category->id,
                    'name' => $title,
                    'short_description' => $row['excerpt'] ?? null,
                    'learning_points' => $this->learningPointsFromHtml($row['description'] ?? null),
                    'format' => ($row['type'] ?? null) === 'live' ? 'online' : null,
                    'level' => 'Umum',
                    'audience' => 'Mahasiswa, komunitas, dan masyarakat umum',
                    'location' => ($row['type'] ?? null) === 'live' ? 'Online' : null,
                    'price_type' => 'Gratis',
                    'status' => 'archived',
                    'featured' => false,
                    'sort_order' => 100 + (int) ($row['id'] ?? 0),
                    'seo_title' => $title,
                    'seo_description' => $row['excerpt'] ?? null,
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                    'created_at' => $row['created_at'] ?? now(),
                    'updated_at' => $row['updated_at'] ?? now(),
                ]
            );
        }
    }

    private function seedLegacyInsights(User $admin): void
    {
        foreach ($this->legacyRows('insights') as $row) {
            $title = $row['title'] ?? null;

            if ($this->shouldSkipLegacyTitle($title)) {
                continue;
            }

            $content = $row['description'] ?? null;
            $category = $this->insightCategoryFor(null);
            $author = $this->legacyAuthorById($row['author_id'] ?? null) ?: $this->authorByName('Edulaw Project');
            $status = ($row['status'] ?? null) === '1' ? 'published' : 'draft';

            $insight = Insight::updateOrCreate(
                ['slug' => $row['slug'] ?: Str::slug($title)],
                [
                    'insight_category_id' => $category->id,
                    'title' => $title,
                    'excerpt' => $row['excerpt'] ?: $this->excerptFromHtml($content),
                    'content' => $content,
                    'cover_image' => $row['cover'] ?? null,
                    'status' => $status,
                    'published_at' => $status === 'published' ? ($row['created_at'] ?? now()) : null,
                    'reading_time' => $this->readingMinutes($content),
                    'featured' => false,
                    'sort_order' => 100 + (int) ($row['id'] ?? 0),
                    'seo_title' => $title,
                    'seo_description' => $row['excerpt'] ?: $this->excerptFromHtml($content, 155),
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                    'reviewed_by' => $status === 'published' ? $admin->id : null,
                    'reviewed_at' => $status === 'published' ? ($row['updated_at'] ?? now()) : null,
                    'created_at' => $row['created_at'] ?? now(),
                    'updated_at' => $row['updated_at'] ?? now(),
                ]
            );

            if ($author) {
                $insight->authors()->syncWithoutDetaching([
                    $author->id => ['author_order' => 1, 'role' => 'Author'],
                ]);
            }

            $tagIds = collect($this->jsonArray($row['tags'] ?? null))
                ->map(fn ($tag) => $this->tagByName(is_array($tag) ? null : $tag)?->id)
                ->filter()
                ->values()
                ->all();

            if ($tagIds) {
                $insight->tags()->syncWithoutDetaching($tagIds);
            }
        }
    }

    private function seedPublications(User $admin): void
    {
        foreach ($this->projectRows('research') as $row) {
            $title = $row['title'] ?? null;

            if (! $title) {
                continue;
            }

            $type = $this->publicationTypeFor($row['document_type'] ?? 'Jurnal');
            $publication = Publication::updateOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'publication_type_id' => $type->id,
                    'title' => $title,
                    'excerpt' => $row['abstract'] ? Str::limit($row['abstract'], 240) : null,
                    'description' => $row['abstract'] ?? null,
                    'cover_image' => $this->existingStoragePath($row['cover'] ?? null),
                    'pdf_file' => $row['file'] ?? null,
                    'published_at' => $row['published_at'] ?: ($row['created_at'] ?? null),
                    'status' => $row['status'] ?? 'published',
                    'featured' => true,
                    'seo_title' => $title,
                    'seo_description' => $row['abstract'] ? Str::limit($row['abstract'], 155) : null,
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                    'created_at' => $row['created_at'] ?? now(),
                    'updated_at' => $row['updated_at'] ?? now(),
                ]
            );

            $this->attachPublicationAuthors($publication, $row['authors'] ?? 'Edulaw Project');
            $this->attachPublicationTags($publication, $this->jsonArray($row['keywords'] ?? null));
        }

        $typeNames = [
            1 => 'Policy Brief',
            2 => 'Research Report',
            3 => 'Toolkit',
            4 => 'Materi',
        ];

        foreach ($this->legacyRows('publications') as $row) {
            if (! in_array((int) ($row['id'] ?? 0), [1, 2, 3, 4], true)) {
                continue;
            }

            $type = $this->publicationTypeFor($typeNames[(int) ($row['type'] ?? 1)] ?? 'Policy Brief');
            $publication = Publication::updateOrCreate(
                ['slug' => $row['slug'] ?: Str::slug($row['title'] ?? '')],
                [
                    'publication_type_id' => $type->id,
                    'title' => $row['title'] ?? null,
                    'excerpt' => $row['excerpt'] ?? null,
                    'description' => $row['description'] ?? null,
                    'cover_image' => $row['cover'] ?? null,
                    'pdf_file' => $row['pdf'] ?? null,
                    'published_at' => $row['created_at'] ?? null,
                    'status' => ($row['status'] ?? null) === '1' ? 'published' : 'draft',
                    'featured' => true,
                    'seo_title' => $row['title'] ?? null,
                    'seo_description' => $row['excerpt'] ?? null,
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                    'created_at' => $row['created_at'] ?? now(),
                    'updated_at' => $row['updated_at'] ?? now(),
                ]
            );

            $this->attachPublicationAuthors($publication, 'Edulaw Project');
        }
    }

    private function seedMultimedia(User $admin): void
    {
        $items = [
            [
                'title' => 'Memahami Konstitusi dalam Kehidupan Sehari-hari',
                'type' => 'video',
                'description' => 'Video edukasi singkat tentang bagaimana konstitusi hadir dalam perlindungan hak warga negara.',
                'media_url' => 'https://www.youtube.com/',
                'platform' => 'youtube',
                'duration' => '08:24',
                'serial' => 'kelas_konstitusi',
                'topic' => 'konstitusi',
                'display_section' => 'latest',
                'featured' => true,
            ],
            [
                'title' => 'Apa itu Judicial Review?',
                'type' => 'shorts',
                'description' => 'Konten singkat untuk mengenalkan pengujian undang-undang dan peran Mahkamah Konstitusi.',
                'media_url' => 'https://www.instagram.com/',
                'platform' => 'instagram',
                'duration' => '01:12',
                'serial' => 'hukum_dalam_60_detik',
                'topic' => 'mahkamah_konstitusi',
                'display_section' => 'short_video',
                'featured' => false,
            ],
            [
                'title' => 'Dokumentasi Diskusi Literasi Konstitusi',
                'type' => 'gallery',
                'description' => 'Galeri kegiatan diskusi dan ruang belajar konstitusi Edulaw Project.',
                'media_url' => 'https://photos.google.com/',
                'platform' => 'gallery',
                'duration' => null,
                'photo_count' => 24,
                'serial' => 'kelas_konstitusi',
                'topic' => 'konstitusi',
                'display_section' => 'topic_multimedia',
                'featured' => false,
            ],
        ];

        foreach ($items as $item) {
            Multimedia::updateOrCreate(
                ['slug' => Str::slug($item['title'])],
                [
                    ...$item,
                    'status' => 'published',
                    'published_at' => now()->toDateString(),
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                ]
            );
        }
    }

    private function seedOpportunities(User $admin): void
    {
        $items = [
            [
                'type' => 'beasiswa',
                'title' => 'Beasiswa Studi Hukum dan Kebijakan Publik',
                'excerpt' => 'Kesempatan pendanaan studi untuk bidang hukum, kebijakan publik, dan demokrasi.',
                'description' => 'Kesempatan pengembangan kapasitas bagi pembelajar hukum yang ingin memperdalam studi hukum, kebijakan publik, dan demokrasi konstitusional.',
                'deadline' => '2026-07-30',
                'format' => 'online',
                'location' => 'Online',
                'eligibility' => ['Mahasiswa hukum', 'Peneliti muda', 'Pegiat komunitas literasi hukum'],
                'benefits' => ['Dukungan belajar', 'Mentoring', 'Jejaring komunitas'],
                'featured' => true,
            ],
            [
                'type' => 'magang',
                'title' => 'Program Magang Riset dan Publikasi Hukum',
                'excerpt' => 'Program pengembangan keterampilan riset, penulisan hukum, dan editorial publikasi.',
                'description' => 'Program magang untuk membantu peserta membangun keterampilan riset hukum, pengelolaan naskah, editorial publikasi, dan komunikasi pengetahuan hukum.',
                'deadline' => '2026-08-12',
                'format' => 'hybrid',
                'location' => 'Hybrid',
                'eligibility' => ['Mahasiswa aktif', 'Memiliki minat riset hukum', 'Mampu bekerja kolaboratif'],
                'benefits' => ['Pengalaman editorial', 'Portofolio riset', 'Sertifikat'],
                'featured' => false,
            ],
            [
                'type' => 'call_for_papers',
                'title' => 'Call for Papers: Hukum, Demokrasi, dan Transformasi Digital',
                'excerpt' => 'Panggilan artikel untuk kajian hukum publik, demokrasi konstitusional, dan tata kelola digital.',
                'description' => 'Panggilan tulisan untuk penulis, peneliti, dan mahasiswa yang ingin mengembangkan gagasan tentang hukum publik, demokrasi, dan transformasi digital.',
                'deadline' => '2026-08-25',
                'format' => 'online',
                'location' => 'Online',
                'eligibility' => ['Mahasiswa', 'Akademisi', 'Peneliti', 'Praktisi hukum'],
                'benefits' => ['Publikasi terkurasi', 'Masukan editorial', 'Eksposur komunitas'],
                'featured' => false,
            ],
        ];

        foreach ($items as $item) {
            Opportunity::updateOrCreate(
                ['slug' => Str::slug($item['title'])],
                [
                    ...$item,
                    'status' => 'open',
                    'seo_title' => $item['title'],
                    'seo_description' => $item['excerpt'],
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                ]
            );
        }
    }

    private function attachPublicationAuthors(Publication $publication, ?string $authors): void
    {
        $names = collect(preg_split('/,|;| dan /i', (string) $authors))
            ->map(fn ($name) => trim($name))
            ->filter()
            ->values();

        if ($names->isEmpty()) {
            $names = collect(['Edulaw Project']);
        }

        $names->each(function (string $name, int $index) use ($publication): void {
            $author = $this->authorByName($name, ['institution' => 'Edulaw Project']);

            $publication->authors()->syncWithoutDetaching([
                $author->id => ['author_order' => $index + 1, 'role' => 'Author'],
            ]);
        });
    }

    private function attachPublicationTags(Publication $publication, array $tags): void
    {
        $tagIds = collect($tags)
            ->map(fn ($tag) => $this->tagByName(is_array($tag) ? null : $tag)?->id)
            ->filter()
            ->values()
            ->all();

        if ($tagIds) {
            $publication->tags()->syncWithoutDetaching($tagIds);
        }
    }

    private function authorByName(?string $name, array $attributes = []): ?Author
    {
        $name = trim((string) $name);

        if ($name === '') {
            return null;
        }

        $data = array_filter([
            'user_id' => $attributes['user_id'] ?? null,
            'name' => $name,
            'email' => $attributes['email'] ?? null,
            'bio' => $attributes['bio'] ?? null,
            'interests' => $attributes['interests'] ?? null,
            'photo' => $attributes['photo'] ?? null,
            'institution' => $attributes['institution'] ?? 'Edulaw Project',
            'position' => $attributes['position'] ?? null,
            'profile_type' => $attributes['profile_type'] ?? null,
            'social_links' => $attributes['social_links'] ?? null,
            'is_active' => true,
        ], fn ($value) => $value !== null && $value !== '');

        return Author::updateOrCreate(
            ['slug' => Str::slug($name)],
            $data
        );
    }

    private function tagByName(?string $name): ?Tag
    {
        $name = trim((string) $name);

        if ($name === '') {
            return null;
        }

        return Tag::firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => Str::headline($name)]
        );
    }

    private function programCategoryFor(array $row): ProgramCategory
    {
        $type = (string) ($row['program_type'] ?? $row['program_family'] ?? 'Program Edulaw');
        $typeLower = Str::lower($type);

        $name = match (true) {
            Str::contains($typeLower, 'literasi konstitusi') => 'DIKSI',
            Str::contains($typeLower, 'diseminasi') => 'Diskusi Diseminasi',
            Str::contains($typeLower, 'respons isu') => 'Diskusi Respons Isu',
            Str::contains($typeLower, 'inspiring') => 'Inspiring Lecture',
            Str::contains($typeLower, 'ngabuburit') => 'Ngabuburit Virtual',
            default => $type ?: 'Program Edulaw',
        };

        return $this->programCategoryByName(
            $name,
            'Kategori program Edulaw yang diimpor dari data kegiatan sebelumnya.',
            (int) ($row['sort_order'] ?? 0)
        );
    }

    private function programCategoryByName(string $name, ?string $description = null, int $sortOrder = 0): ProgramCategory
    {
        return ProgramCategory::updateOrCreate(
            ['slug' => Str::slug($name)],
            [
                'name' => $name,
                'description' => $description,
                'is_active' => true,
                'sort_order' => $sortOrder,
            ]
        );
    }

    private function insightCategoryFor(?string $legacyId): InsightCategory
    {
        $name = match ((int) $legacyId) {
            2 => 'Opini Hukum',
            3 => 'Riset Hukum',
            default => 'Legal Insight',
        };

        return InsightCategory::updateOrCreate(
            ['slug' => Str::slug($name)],
            [
                'name' => $name,
                'description' => 'Kategori insight Edulaw yang dipetakan dari data lama.',
                'is_active' => true,
                'sort_order' => match ($name) {
                    'Opini Hukum' => 8,
                    'Riset Hukum' => 9,
                    default => 4,
                },
            ]
        );
    }

    private function publicationTypeFor(?string $source): PublicationType
    {
        $source = trim((string) $source);
        $name = match ($source) {
            'journal_article', 'Jurnal' => 'Jurnal',
            'policy_brief' => 'Policy Brief',
            'research_report', 'Ringkasan Riset' => 'Research Report',
            default => Str::headline($source ?: 'Research Report'),
        };

        return PublicationType::updateOrCreate(
            ['slug' => Str::slug($name)],
            [
                'name' => $name,
                'description' => 'Jenis publikasi Edulaw yang dipetakan dari data lama.',
                'is_active' => true,
            ]
        );
    }

    private function projectRows(string $table): array
    {
        return $this->rowsFromDump('u815125696_edulawproject.sql', $table);
    }

    private function legacyRows(string $table): array
    {
        return $this->rowsFromDump('u815125696_edulaw.sql', $table);
    }

    private function abfuadiRows(string $table): array
    {
        return $this->rowsFromDump('u815125696_abfuadi13.sql', $table);
    }

    private function rowsFromDump(string $filename, string $table): array
    {
        $path = $this->dumpPath($filename);

        if (! $path) {
            return [];
        }

        $cacheKey = $filename.':'.$table;

        if (! array_key_exists($cacheKey, $this->dumpCache)) {
            $this->dumpCache[$cacheKey] = $this->parseInsertRows($path, $table);
        }

        return $this->dumpCache[$cacheKey];
    }

    private function dumpPath(string $filename): ?string
    {
        $home = $_SERVER['HOME'] ?? getenv('HOME') ?: null;
        $candidates = array_filter([
            database_path('dumps/'.$filename),
            $home ? $home.'/Downloads/'.$filename : null,
        ]);

        foreach ($candidates as $candidate) {
            if (is_readable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function parseInsertRows(string $path, string $table): array
    {
        $rows = [];
        $statement = '';
        $collecting = false;
        $prefix = 'INSERT INTO `'.$table.'`';

        foreach (file($path, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
            if (! $collecting && str_starts_with($line, $prefix)) {
                $collecting = true;
                $statement = $line."\n";
            } elseif ($collecting) {
                $statement .= $line."\n";
            }

            if ($collecting && str_ends_with(rtrim($line), ';')) {
                $rows = [...$rows, ...$this->parseInsertStatement($statement)];
                $statement = '';
                $collecting = false;
            }
        }

        return $rows;
    }

    private function parseInsertStatement(string $statement): array
    {
        if (! preg_match('/INSERT INTO `[^`]+` \((.*?)\) VALUES\s*(.*);/s', $statement, $matches)) {
            return [];
        }

        $columns = array_map(
            fn ($column) => trim($column, " `\t\n\r\0\x0B"),
            explode(',', $matches[1])
        );

        return collect($this->splitRows($matches[2]))
            ->map(function (string $row) use ($columns): ?array {
                $values = $this->splitValues($row);

                if (count($values) !== count($columns)) {
                    return null;
                }

                return array_combine($columns, $values);
            })
            ->filter()
            ->values()
            ->all();
    }

    private function splitRows(string $values): array
    {
        $rows = [];
        $buffer = '';
        $depth = 0;
        $inQuote = false;
        $escaped = false;
        $length = strlen($values);

        for ($i = 0; $i < $length; $i++) {
            $char = $values[$i];

            if ($inQuote) {
                $buffer .= $char;

                if ($escaped) {
                    $escaped = false;

                    continue;
                }

                if ($char === '\\') {
                    $escaped = true;

                    continue;
                }

                if ($char === "'") {
                    $inQuote = false;
                }

                continue;
            }

            if ($char === "'") {
                $inQuote = true;

                if ($depth > 0) {
                    $buffer .= $char;
                }

                continue;
            }

            if ($char === '(') {
                if ($depth > 0) {
                    $buffer .= $char;
                }

                $depth++;

                continue;
            }

            if ($char === ')') {
                $depth--;

                if ($depth === 0) {
                    $rows[] = $buffer;
                    $buffer = '';

                    continue;
                }

                $buffer .= $char;

                continue;
            }

            if ($depth > 0) {
                $buffer .= $char;
            }
        }

        return $rows;
    }

    private function splitValues(string $row): array
    {
        $values = [];
        $buffer = '';
        $inQuote = false;
        $escaped = false;
        $length = strlen($row);

        for ($i = 0; $i < $length; $i++) {
            $char = $row[$i];

            if ($inQuote) {
                $buffer .= $char;

                if ($escaped) {
                    $escaped = false;

                    continue;
                }

                if ($char === '\\') {
                    $escaped = true;

                    continue;
                }

                if ($char === "'") {
                    $inQuote = false;
                }

                continue;
            }

            if ($char === "'") {
                $inQuote = true;
                $buffer .= $char;

                continue;
            }

            if ($char === ',') {
                $values[] = $this->parseValue($buffer);
                $buffer = '';

                continue;
            }

            $buffer .= $char;
        }

        $values[] = $this->parseValue($buffer);

        return $values;
    }

    private function parseValue(string $value): mixed
    {
        $value = trim($value);

        if (strcasecmp($value, 'NULL') === 0) {
            return null;
        }

        if (strlen($value) >= 2 && $value[0] === "'" && $value[strlen($value) - 1] === "'") {
            return stripcslashes(substr($value, 1, -1));
        }

        return $value;
    }

    private function jsonArray(?string $value): array
    {
        if (! $value) {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function existingStoragePath(?string $path): ?string
    {
        if (! $path || str_starts_with($path, 'http')) {
            return $path;
        }

        return is_file(storage_path('app/public/'.$path)) ? $path : null;
    }

    private function normalizedFormat(?string $format): ?string
    {
        $format = Str::lower(trim((string) $format));

        return in_array($format, ['online', 'offline', 'hybrid'], true) ? $format : null;
    }

    private function locationFor(?string $format): ?string
    {
        return match ($this->normalizedFormat($format)) {
            'online' => 'Online / Zoom Meeting',
            'hybrid' => 'Hybrid',
            'offline' => 'Offline',
            default => null,
        };
    }

    private function excerptFromHtml(?string $html, int $limit = 260): ?string
    {
        $text = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) $html), ENT_QUOTES, 'UTF-8')));

        return $text ? Str::limit($text, $limit) : null;
    }

    private function readingMinutes(?string $html): int
    {
        $text = trim(strip_tags((string) $html));
        $wordCount = str_word_count($text);

        return max(1, (int) ceil($wordCount / 200));
    }

    private function legacyAuthorById(mixed $id): ?Author
    {
        if (! $id) {
            return null;
        }

        $row = collect($this->legacyRows('users'))->firstWhere('id', (string) $id);

        if (! $row) {
            return null;
        }

        $name = $row['full_name']
            ?: trim(($row['first_name'] ?? '').' '.($row['last_name'] ?? ''));

        return $this->authorByName($name, [
            'email' => $row['email'] ?? null,
            'photo' => $row['image'] ?? null,
            'institution' => 'Edulaw Project',
            'position' => isset($row['role']) ? Str::headline($row['role']) : null,
        ]);
    }

    private function learningPointsFromHtml(?string $html): array
    {
        if (! $html) {
            return [];
        }

        preg_match_all('/<li[^>]*>(.*?)<\/li>/is', $html, $matches);

        return collect($matches[1] ?? [])
            ->map(fn ($item) => ['point' => trim(html_entity_decode(strip_tags($item), ENT_QUOTES, 'UTF-8'))])
            ->filter(fn ($item) => $item['point'] !== '')
            ->values()
            ->all();
    }

    private function shouldSkipLegacyTitle(?string $title): bool
    {
        $title = Str::lower(trim((string) $title));

        return $title === '' || $title === 'test' || Str::startsWith($title, 'test ');
    }

    private function panelRoleFor(?string $role): ?string
    {
        return match (Str::lower(trim((string) $role))) {
            'superadmin', 'super admin', 'super_admin' => 'super_admin',
            'admin', 'editor' => 'editor',
            'program admin', 'program_admin' => 'program_admin',
            'media opportunity admin', 'media & opportunity admin', 'media_opportunity_admin' => 'media_opportunity_admin',
            'user', 'contributor', 'writer' => 'writer',
            default => 'writer',
        };
    }
}
