<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Insight;
use App\Models\InsightCategory;
use App\Models\PageVisit;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InsightController extends Controller
{
    private const ITEMS_PER_PAGE = 12;

    private const CATEGORY_PAGES = [
        'law-governance' => [
            'name' => 'Law & Governance',
            'title' => 'Law & Governance: Hukum, Negara, dan Tata Kelola',
            'seo_title' => 'Law & Governance: Hukum dan Tata Kelola',
            'seo_description' => 'Analisis hukum, konstitusi, kebijakan publik, demokrasi, dan tata kelola pemerintahan untuk memahami hubungan negara dengan masyarakat.',
            'introduction' => 'Kanal Law & Governance membahas hubungan antara hukum, kekuasaan, institusi negara, dan kepentingan publik. Di sini, pembaca dapat mengikuti analisis mengenai konstitusi, demokrasi, kebijakan publik, administrasi pemerintahan, pembentukan regulasi, serta akuntabilitas lembaga negara. Setiap tulisan disusun untuk membantu pembaca memahami bukan hanya bunyi aturan, tetapi juga alasan kebijakan dibuat, cara kewenangan dijalankan, dan dampaknya terhadap kehidupan masyarakat. Kanal ini relevan bagi mahasiswa, akademisi, praktisi, pengelola organisasi, maupun warga yang ingin membaca persoalan hukum tata negara dan tata kelola secara lebih kontekstual. Artikel-artikel di dalamnya menghubungkan perkembangan regulasi dengan prinsip negara hukum, perlindungan hak, partisipasi publik, dan kualitas pengambilan keputusan. Dengan pendekatan yang jernih dan berbasis sumber, Law & Governance menjadi ruang untuk menilai bagaimana hukum bekerja dalam praktik serta bagaimana tata kelola dapat diperkuat agar lebih terbuka, adil, dan bertanggung jawab.',
            'aliases' => ['law governance', 'law and governance', 'constitution governance', 'constitution and governance', 'kebijakan publik'],
        ],
        'legal-101' => [
            'name' => 'Legal 101',
            'title' => 'Legal 101: Memahami Dasar-Dasar Hukum',
            'seo_title' => 'Legal 101: Panduan Dasar-Dasar Hukum',
            'seo_description' => 'Panduan dasar hukum dengan bahasa yang mudah dipahami, mulai dari konsep, hak, prosedur, hingga istilah penting dalam kehidupan sehari-hari.',
            'introduction' => 'Legal 101 adalah kanal pengantar untuk memahami konsep dan mekanisme hukum yang sering dijumpai dalam kehidupan sehari-hari. Pembahasannya dirancang dengan bahasa yang mudah diikuti tanpa menghilangkan ketepatan istilah dan konteks hukumnya. Pembaca dapat mempelajari dasar negara hukum, hierarki peraturan, hak dan kewajiban warga, proses peradilan, kontrak, perlindungan konsumen, hingga cara membaca dokumen atau persoalan hukum secara lebih sistematis. Kanal ini cocok bagi pelajar, mahasiswa lintas disiplin, komunitas, pelaku usaha, dan masyarakat umum yang ingin membangun fondasi literasi hukum sebelum mendalami isu yang lebih kompleks. Setiap artikel berusaha menjawab pertanyaan mendasar: aturan apa yang berlaku, siapa yang berwenang, hak apa yang dilindungi, dan langkah apa yang dapat dipertimbangkan. Legal 101 tidak menggantikan nasihat hukum profesional, tetapi menyediakan pengetahuan awal yang membantu pembaca mengenali masalah, memahami istilah, dan mengambil keputusan secara lebih sadar.',
            'aliases' => ['legal 101', 'law 101', 'dasar hukum', 'hukum dasar', 'literasi hukum dasar'],
        ],
        'regulatory-update' => [
            'name' => 'Regulatory Update',
            'title' => 'Regulatory Update: Perkembangan Regulasi Terkini',
            'seo_title' => 'Regulatory Update: Perkembangan Regulasi',
            'seo_description' => 'Ikuti perkembangan regulasi terbaru beserta konteks, perubahan utama, dan dampaknya bagi masyarakat, institusi, serta praktik hukum.',
            'introduction' => 'Regulatory Update menyajikan perkembangan peraturan dan kebijakan terbaru dengan penjelasan mengenai konteks, perubahan utama, serta konsekuensinya. Kanal ini membantu pembaca mengikuti regulasi tanpa harus berhenti pada nomor, tanggal, atau judul peraturan. Setiap tulisan menyoroti persoalan yang hendak dijawab, ruang lingkup pengaturan, pihak yang terdampak, hubungan dengan aturan sebelumnya, dan hal-hal yang perlu diperhatikan dalam penerapannya. Pembahasan mencakup peraturan perundang-undangan, kebijakan pemerintah, putusan penting, pedoman lembaga, serta perubahan tata kelola yang relevan bagi publik. Kanal ini ditujukan bagi praktisi, peneliti, mahasiswa, organisasi masyarakat, pelaku usaha, dan warga yang membutuhkan pemahaman awal secara cepat namun tetap bertanggung jawab. Dengan menghubungkan teks regulasi dan dampak praktisnya, Regulatory Update membantu pembaca menilai apakah suatu perubahan memperluas perlindungan, menambah kewajiban, mengubah prosedur, atau memunculkan persoalan implementasi yang perlu diawasi.',
            'aliases' => ['regulatory update', 'regulation update', 'regulasi', 'pembaruan regulasi'],
        ],
        'edulaw-insight' => [
            'name' => 'Edulaw Insight',
            'title' => 'Edulaw Insight: Analisis Hukum untuk Publik',
            'seo_title' => 'Edulaw Insight: Analisis Hukum untuk Publik',
            'seo_description' => 'Baca analisis hukum Edulaw Project mengenai isu aktual, putusan, kebijakan, dan persoalan publik berdasarkan riset serta argumentasi yang jernih.',
            'introduction' => 'Edulaw Insight merupakan ruang analisis utama Edulaw Project untuk membaca persoalan hukum yang berkembang di tengah masyarakat. Kanal ini memadukan riset, penalaran hukum, konteks kebijakan, dan perhatian terhadap dampak sosial agar isu yang kompleks dapat dipahami secara lebih utuh. Tulisan dapat membahas putusan pengadilan, perdebatan konstitusional, perubahan kebijakan, hak warga, perkembangan institusi, maupun hubungan hukum dengan teknologi, ekonomi, dan kehidupan publik. Setiap artikel diarahkan untuk menghadirkan argumentasi yang jelas, sumber yang dapat ditelusuri, serta sudut pandang yang berguna bagi pembaca. Kanal ini ditujukan bagi siapa pun yang membutuhkan pemahaman lebih mendalam daripada ringkasan berita, termasuk mahasiswa, peneliti, praktisi, organisasi, dan masyarakat umum. Melalui Edulaw Insight, pembaca diajak melihat apa masalah hukumnya, mengapa isu tersebut penting, bagaimana aturan dan putusan dapat ditafsirkan, serta konsekuensi yang mungkin muncul bagi kebijakan dan perlindungan hak.',
            'aliases' => ['edulaw insight', 'insight', 'editorial', 'legal insight', 'legal editorial', 'opini hukum', 'riset hukum'],
        ],
    ];

    private const LEGACY_SLUG_REDIRECTS = [
        'worklife-balance-di-era-hustle-culture-menakar-perlindungan-hukum-terhadap-hak-atas-kesehatan-mental' => 'work-life-balance-di-era-hustle-culture-menakar-perlindungan-hukum-terhadap-hak-atas-kesehatan-mental',
    ];

    public function index(Request $request): View|RedirectResponse
    {
        $category = $request->query('category');
        $author = $request->query('author');
        $search = trim((string) $request->query('q', ''));
        $featuredOnly = $request->boolean('featured');
        $sort = in_array($request->query('sort'), ['latest', 'oldest', 'title'], true)
            ? (string) $request->query('sort')
            : 'latest';

        if ($category && blank($author) && $search === '' && ! $featuredOnly) {
            $categoryPageSlug = $this->categoryPageSlug((string) $category);

            if ($categoryPageSlug) {
                $parameters = ['categorySlug' => $categoryPageSlug];

                if ((int) $request->query('page', 1) > 1) {
                    $parameters['page'] = (int) $request->query('page');
                }

                return redirect()->route('insights.categories.show', $parameters, 301);
            }
        }

        $query = Insight::query()
            ->with(['categoryRelation', 'authors.user'])
            ->published()
            ->when($category, fn ($query) => $query->whereHas('categoryRelation', fn ($categoryQuery) => $categoryQuery->where('slug', $category)))
            ->when($author, fn ($query) => $query->whereHas('authors', fn ($authorQuery) => $authorQuery->where('slug', $author)))
            ->when($featuredOnly, fn ($query) => $query->featured())
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            });

        $insightCategories = InsightCategory::query()
            ->visibleOnEditorialIndex()
            ->withCount([
                'insights as published_insights_count' => fn ($query) => $query->published(),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $latestInsights = Insight::query()
            ->with(['categoryRelation', 'authors.user'])
            ->published()
            ->orderByDesc('published_at')
            ->latest('id')
            ->take(40)
            ->get();

        $featuredMain = Insight::query()
            ->with(['categoryRelation', 'authors.user'])
            ->published()
            ->featured()
            ->orderByDesc('published_at')
            ->latest('id')
            ->first() ?: $latestInsights->first();

        $featuredEditorials = collect([$featuredMain])
            ->filter()
            ->concat($latestInsights->whereNotIn('id', [$featuredMain?->id])->take(2))
            ->unique('id')
            ->take(3)
            ->values();

        $shownIds = $featuredEditorials->pluck('id')->filter()->values();
        $editorialPicks = $this->editorialPicks($shownIds->all());
        $shownIds = $shownIds->merge($editorialPicks->pluck('id'))->unique()->values();

        $insightChannels = $this->insightChannels($insightCategories);
        $categorySections = $this->categorySections($insightChannels, $shownIds);

        $latestEditorials = $latestInsights
            ->whereNotIn('id', [$featuredMain?->id])
            ->take(4)
            ->values();

        $popularInsights = $this->popularInsights();
        $popularEditorials = $popularInsights->isNotEmpty()
            ? $popularInsights->take(10)->values()
            : $latestInsights->take(10)->values();

        $query = match ($sort) {
            'oldest' => $query->orderBy('published_at')->orderBy('id'),
            'title' => $query->orderBy('title')->orderBy('id'),
            default => $query->orderByDesc('published_at')->latest('id'),
        };

        return view('insights.index', [
            'latestInsights' => $latestInsights,
            'featuredEditorials' => $featuredEditorials,
            'editorialPicks' => $editorialPicks,
            'categorySections' => $categorySections,
            'latestEditorials' => $latestEditorials,
            'popularEditorials' => $popularEditorials,
            'popularHasViews' => $popularInsights->isNotEmpty(),
            'insightChannels' => $insightChannels,
            'popularInsights' => $popularInsights,
            'popularTags' => $this->popularTags(),
            'editorialContributors' => $this->editorialContributors(),
            'insights' => $query
                ->paginate(self::ITEMS_PER_PAGE)
                ->withQueryString(),
            'publishedEditorialCount' => Insight::query()->published()->count(),
            'editorialCategoryCount' => $insightCategories->count(),
            'insightCategories' => $insightCategories,
            'selectedCategory' => $category,
            'selectedAuthor' => $author,
            'search' => $search,
            'featuredOnly' => $featuredOnly,
            'selectedSort' => $sort,
            'showFilteredArchive' => $search !== '' || filled($category) || filled($author) || $featuredOnly || $request->filled('archive') || (int) $request->query('page', 1) > 1,
        ]);
    }

    public function category(Request $request, string $categorySlug): View
    {
        $definition = self::CATEGORY_PAGES[$categorySlug] ?? abort(404);
        $categories = InsightCategory::query()
            ->where('is_active', true)
            ->withCount([
                'insights as published_insights_count' => fn ($query) => $query->published(),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        $category = $this->resolveInsightCategory($categories, $definition['aliases']);

        $insights = Insight::query()
            ->with(['categoryRelation', 'authors.user'])
            ->published()
            ->when(
                $category,
                fn ($query) => $query->where('insight_category_id', $category->id),
                fn ($query) => $query->whereRaw('1 = 0'),
            )
            ->orderByDesc('published_at')
            ->latest('id')
            ->paginate(self::ITEMS_PER_PAGE);

        abort_if($insights->currentPage() > $insights->lastPage(), 404);

        $currentPage = $insights->currentPage();
        $categoryUrl = route('insights.categories.show', $categorySlug);
        $canonicalUrl = $categoryUrl
            .($currentPage > 1 ? '?page='.$currentPage : '');
        $previousPageUrl = $currentPage > 1
            ? $categoryUrl.($currentPage > 2 ? '?page='.($currentPage - 1) : '')
            : null;
        $nextPageUrl = $currentPage < $insights->lastPage()
            ? $categoryUrl.'?page='.($currentPage + 1)
            : null;
        $relatedCategories = collect(self::CATEGORY_PAGES)
            ->except($categorySlug)
            ->map(fn (array $related, string $slug): array => [
                ...$related,
                'slug' => $slug,
                'url' => route('insights.categories.show', $slug),
                'article_count' => (int) ($this->resolveInsightCategory($categories, $related['aliases'])?->published_insights_count ?? 0),
            ])
            ->values();

        return view('insights.category', [
            'definition' => $definition,
            'category' => $category,
            'categorySlug' => $categorySlug,
            'insights' => $insights,
            'canonicalUrl' => $canonicalUrl,
            'previousPageUrl' => $previousPageUrl,
            'nextPageUrl' => $nextPageUrl,
            'relatedCategories' => $relatedCategories,
        ]);
    }

    public function show(string $slug): View|RedirectResponse
    {
        if (array_key_exists($slug, self::LEGACY_SLUG_REDIRECTS)) {
            return redirect()->route('insights.show', ['slug' => self::LEGACY_SLUG_REDIRECTS[$slug]], 301);
        }

        $insight = Insight::query()
            ->with(['categoryRelation', 'authors.user', 'tags', 'creator', 'reviewer', 'assignedEditor', 'footnotes'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedInsights = Insight::query()
            ->with(['categoryRelation', 'authors.user'])
            ->published()
            ->whereKeyNot($insight->id)
            ->when($insight->insight_category_id, fn ($query) => $query->where('insight_category_id', $insight->insight_category_id))
            ->latest('published_at')
            ->take(3)
            ->get();

        if ($relatedInsights->count() < 3) {
            $excludedIds = $relatedInsights
                ->pluck('id')
                ->push($insight->id)
                ->filter()
                ->all();

            $fallbackInsights = Insight::query()
                ->with(['categoryRelation', 'authors.user'])
                ->published()
                ->whereNotIn('id', $excludedIds)
                ->latest('published_at')
                ->take(3 - $relatedInsights->count())
                ->get();

            $relatedInsights = $relatedInsights
                ->concat($fallbackInsights)
                ->take(3)
                ->values();
        }

        return view('insights.show', [
            'insight' => $insight,
            'relatedInsights' => $relatedInsights,
        ]);
    }

    private function editorialPicks(array $excludedIds = []): Collection
    {
        $excludedIds = collect($excludedIds)
            ->filter()
            ->unique()
            ->values()
            ->all();

        return Insight::query()
            ->with(['categoryRelation', 'authors.user'])
            ->published()
            ->editorPick()
            ->whereNotIn('id', $excludedIds)
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->latest('id')
            ->take(4)
            ->get();
    }

    private function popularInsights(): Collection
    {
        $visitsBySlug = PageVisit::query()
            ->selectRaw('path, COUNT(*) as visit_count')
            ->where('route_name', 'insights.show')
            ->where('status_code', 200)
            ->since(now()->subDays(30)->startOfDay())
            ->groupBy('path')
            ->orderByDesc('visit_count')
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (PageVisit $visit): array => [
                Str::afterLast(rawurldecode($visit->path), '/') => (int) $visit->visit_count,
            ]);

        if ($visitsBySlug->isEmpty()) {
            return collect();
        }

        $insightsBySlug = Insight::query()
            ->with(['categoryRelation', 'authors.user'])
            ->published()
            ->whereIn('slug', $visitsBySlug->keys()->all())
            ->get()
            ->keyBy('slug');

        return $visitsBySlug
            ->map(function (int $visitCount, string $slug) use ($insightsBySlug): ?Insight {
                $insight = $insightsBySlug->get($slug);

                if ($insight) {
                    $insight->setAttribute('visit_count', $visitCount);
                }

                return $insight;
            })
            ->filter()
            ->take(20)
            ->values();
    }

    private function popularTags(): Collection
    {
        return Tag::query()
            ->select('tags.*')
            ->selectSub(function ($query) {
                $query
                    ->from('insight_tag')
                    ->join('insights', 'insights.id', '=', 'insight_tag.insight_id')
                    ->whereColumn('insight_tag.tag_id', 'tags.id')
                    ->where('insights.status', 'published')
                    ->whereNotNull('insights.published_at')
                    ->where('insights.published_at', '<=', now())
                    ->selectRaw('count(*)');
            }, 'published_insights_count')
            ->whereExists(function ($query) {
                $query
                    ->from('insight_tag')
                    ->join('insights', 'insights.id', '=', 'insight_tag.insight_id')
                    ->whereColumn('insight_tag.tag_id', 'tags.id')
                    ->where('insights.status', 'published')
                    ->whereNotNull('insights.published_at')
                    ->where('insights.published_at', '<=', now());
            })
            ->orderByDesc('published_insights_count')
            ->orderBy('name')
            ->take(12)
            ->get();
    }

    private function editorialContributors(): Collection
    {
        return Author::query()
            ->visibleInContributorSection()
            ->whereNotNull('slug')
            ->withCount([
                'insights as published_insights_count' => fn ($query) => $query->published(),
            ])
            ->orderByDesc('published_insights_count')
            ->orderByRaw('CASE WHEN sort_order IS NULL THEN 1 ELSE 0 END')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->take(10)
            ->get();
    }

    private function insightChannels(Collection $categories): Collection
    {
        $definitions = collect([
            [
                'label' => 'Law & Governance',
                'icon' => 'column',
                'description' => 'Kajian hukum tata negara, kebijakan publik, dan perkembangan regulasi.',
                'aliases' => ['law governance', 'law and governance', 'constitution governance', 'constitution and governance', 'kebijakan publik'],
            ],
            [
                'label' => 'Legal 101',
                'icon' => 'book',
                'description' => 'Dasar-dasar hukum yang penting untuk dipahami semua orang.',
                'aliases' => ['legal 101', 'law 101'],
            ],
            [
                'label' => 'Regulatory Update',
                'icon' => 'document',
                'description' => 'Update regulasi terbaru dan dampaknya terhadap masyarakat.',
                'aliases' => ['regulatory update', 'regulation update', 'regulasi', 'pembaruan regulasi'],
            ],
            [
                'label' => 'Edulaw Insight',
                'icon' => 'spark',
                'description' => 'Analisis hukum terkini dari riset dan pengalaman tim Edulaw Project.',
                'aliases' => ['edulaw insight', 'insight', 'editorial', 'legal insight', 'legal editorial', 'opini hukum', 'riset hukum'],
            ],
            [
                'label' => 'Teknologi Hukum',
                'icon' => 'tech',
                'description' => 'Perkembangan teknologi, data, AI, dan transformasi digital dalam hukum.',
                'aliases' => ['teknologi hukum', 'law technology', 'legal tech', 'technology law'],
            ],
            [
                'label' => 'Ekonomi & Bisnis',
                'icon' => 'briefcase',
                'description' => 'Analisis hukum bisnis, ekonomi, pasar, dan regulasi dunia usaha.',
                'aliases' => ['ekonomi bisnis', 'ekonomi and bisnis', 'business law', 'ekonomi'],
            ],
            [
                'label' => 'International Law',
                'icon' => 'globe',
                'description' => 'Catatan hukum internasional, diplomasi, hak asasi, dan isu lintas negara.',
                'aliases' => ['international law', 'hukum internasional', 'internasional'],
            ],
        ]);

        $articlesByCategory = $categories->isNotEmpty()
            ? Insight::query()
                ->with(['categoryRelation', 'authors.user'])
                ->published()
                ->whereIn('insight_category_id', $categories->pluck('id')->all())
                ->orderByDesc('published_at')
                ->latest('id')
                ->get()
                ->groupBy('insight_category_id')
                ->map(fn (Collection $items): Collection => $items->take(8)->values())
            : collect();

        return $categories->map(function (InsightCategory $category) use ($definitions, $articlesByCategory): array {
            $normalizedCategory = collect([$category->name, $category->slug])
                ->map(fn (string $value): string => $this->normalizeCategoryName($value));
            $definition = $definitions->first(function (array $candidate) use ($normalizedCategory): bool {
                $aliases = collect([$candidate['label'], ...$candidate['aliases']])
                    ->map(fn (string $alias): string => $this->normalizeCategoryName($alias));

                return $normalizedCategory->contains(fn (string $value): bool => $aliases->contains($value));
            });
            $label = $definition['label'] ?? $category->name;
            $pageSlug = $this->categoryPageSlug($category->slug)
                ?? $this->categoryPageSlug($category->name);

            return [
                'label' => $label,
                'icon' => $definition['icon'] ?? 'column',
                'description' => $category->description ?: ($definition['description'] ?? 'Kumpulan editorial pilihan Edulaw Project.'),
                'aliases' => $definition['aliases'] ?? [$category->slug],
                'category' => $category,
                'article_count' => (int) ($category->published_insights_count ?? 0),
                'articles' => $articlesByCategory->get($category->id, collect()),
                'url' => $pageSlug
                    ? route('insights.categories.show', $pageSlug)
                    : route('insights.index', ['category' => $category->slug]),
            ];
        })->values();
    }

    private function categorySections(Collection $channels, Collection $shownIds): Collection
    {
        return $channels
            ->take(4)
            ->map(function (array $channel) use ($shownIds): array {
                $allArticles = collect($channel['articles'] ?? [])->unique('id')->values();
                $articles = $allArticles
                    ->whereNotIn('id', $shownIds->all())
                    ->take(3)
                    ->values();

                if ($articles->count() < 3) {
                    $articles = $articles
                        ->concat($allArticles->whereNotIn('id', $articles->pluck('id')->all()))
                        ->unique('id')
                        ->take(3)
                        ->values();
                }

                return [
                    'title' => $channel['label'],
                    'description' => $channel['category']?->description ?: ($channel['description'] ?? null),
                    'article_count' => (int) ($channel['article_count'] ?? $allArticles->count()),
                    'items' => $articles,
                    'url' => ($channel['url'] ?? route('insights.index')).'#insight-archive',
                ];
            })
            ->filter(fn (array $section): bool => collect($section['items'])->isNotEmpty())
            ->values();
    }

    private function resolveInsightCategory(Collection $categories, array $aliases): ?InsightCategory
    {
        $normalizedAliases = collect($aliases)
            ->map(fn (string $alias): string => $this->normalizeCategoryName($alias))
            ->filter()
            ->values();

        return $categories->first(function (InsightCategory $category) use ($normalizedAliases): bool {
            $name = $this->normalizeCategoryName($category->name);
            $slug = $this->normalizeCategoryName($category->slug);

            return $normalizedAliases->contains($name)
                || $normalizedAliases->contains($slug)
                || $normalizedAliases->contains(fn (string $alias): bool => Str::contains($name, $alias) || Str::contains($slug, $alias));
        });
    }

    private function categoryPageSlug(string $category): ?string
    {
        $normalizedCategory = $this->normalizeCategoryName($category);

        foreach (self::CATEGORY_PAGES as $slug => $definition) {
            $aliases = collect([$slug, $definition['name'], ...$definition['aliases']])
                ->map(fn (string $value): string => $this->normalizeCategoryName($value));

            if ($aliases->contains($normalizedCategory)) {
                return $slug;
            }
        }

        return null;
    }

    private function normalizeCategoryName(?string $value): string
    {
        return Str::of((string) $value)
            ->lower()
            ->replace('&', ' and ')
            ->replace('-', ' ')
            ->replace('_', ' ')
            ->squish()
            ->toString();
    }
}
