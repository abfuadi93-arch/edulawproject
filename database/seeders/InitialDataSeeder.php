<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\InsightCategory;
use App\Models\ProgramCategory;
use App\Models\PublicationType;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedRoles();
        $admin = $this->seedAdminUser();
        $this->seedAuthor($admin);
        $this->seedInsightCategories();
        $this->seedProgramCategories();
        $this->seedPublicationTypes();
        $this->seedTags();
    }

    private function seedRoles(): void
    {
        $roles = [
            'Super Admin',
            'Editor',
            'Writer',
            'Program Admin',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }
    }

    private function seedAdminUser(): User
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@edulaw.test'],
            [
                'name' => 'Edulaw Admin',
                'password' => Hash::make('password'),
                'is_active' => true,
                'institution' => 'Edulaw Project',
                'position' => 'Administrator',
            ]
        );

        if (! $admin->hasRole('Super Admin')) {
            $admin->assignRole('Super Admin');
        }

        return $admin;
    }

    private function seedAuthor(User $admin): void
    {
        Author::firstOrCreate(
            ['slug' => 'abdul-basid-fuadi'],
            [
                'user_id' => $admin->id,
                'name' => 'Abdul Basid Fuadi',
                'email' => 'admin@edulaw.test',
                'institution' => 'Edulaw Project',
                'position' => 'Founder',
                'is_active' => true,
            ]
        );
    }

    private function seedInsightCategories(): void
    {
        $categories = [
            ['name' => 'Law 101', 'description' => 'Konten pengantar hukum dengan bahasa sederhana.'],
            ['name' => 'Regulatory Update', 'description' => 'Pembaruan regulasi dan kebijakan hukum.'],
            ['name' => 'Constitution & Governance', 'description' => 'Kajian konstitusi, kelembagaan negara, dan tata kelola.'],
            ['name' => 'Legal Insight', 'description' => 'Analisis hukum atas isu publik aktual.'],
            ['name' => 'Hukum dan Teknologi', 'description' => 'Isu hukum dalam perkembangan teknologi digital.'],
            ['name' => 'Kebijakan Publik', 'description' => 'Analisis kebijakan publik dari perspektif hukum.'],
            ['name' => 'Hak Asasi Manusia', 'description' => 'Kajian hak asasi manusia dan perlindungan warga negara.'],
        ];

        foreach ($categories as $index => $category) {
            InsightCategory::firstOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }

    private function seedProgramCategories(): void
    {
        $categories = [
            ['name' => 'DIKSI', 'description' => 'Diskusi Literasi Konstitusi.'],
            ['name' => 'Kelas Hukum', 'description' => 'Kelas pembelajaran hukum tematik.'],
            ['name' => 'Workshop / Webinar', 'description' => 'Forum pelatihan dan diskusi daring maupun luring.'],
            ['name' => 'Pelatihan', 'description' => 'Program peningkatan kapasitas hukum.'],
            ['name' => 'Bootcamp', 'description' => 'Program intensif berbasis keterampilan hukum.'],
            ['name' => 'Klinik Hukum', 'description' => 'Ruang pembelajaran dan konsultasi literasi hukum.'],
            ['name' => 'Short Course', 'description' => 'Kursus singkat hukum dan kebijakan publik.'],
        ];

        foreach ($categories as $index => $category) {
            ProgramCategory::firstOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }

    private function seedPublicationTypes(): void
    {
        $types = [
            ['name' => 'Policy Brief', 'description' => 'Ringkasan kebijakan berbasis riset dan analisis hukum.'],
            ['name' => 'Kajian Hukum', 'description' => 'Kajian hukum terhadap isu regulasi dan kebijakan.'],
            ['name' => 'Naskah Akademik', 'description' => 'Dokumen akademik untuk kebutuhan pembentukan atau evaluasi regulasi.'],
            ['name' => 'Working Paper', 'description' => 'Kertas kerja untuk pengembangan gagasan hukum.'],
            ['name' => 'Research Report', 'description' => 'Laporan riset hukum dan kebijakan publik.'],
            ['name' => 'Buku Digital', 'description' => 'Publikasi digital dalam bentuk buku atau modul.'],
        ];

        foreach ($types as $index => $type) {
            PublicationType::firstOrCreate(
                ['slug' => Str::slug($type['name'])],
                [
                    'name' => $type['name'],
                    'description' => $type['description'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }

    private function seedTags(): void
    {
        $tags = [
            'Konstitusi',
            'Kebijakan Publik',
            'Hak Asasi Manusia',
            'Pemilu',
            'Pilkada',
            'Hukum dan Teknologi',
            'Tata Kelola',
            'Regulasi',
            'Advokasi',
            'Literasi Hukum',
        ];

        foreach ($tags as $tag) {
            Tag::firstOrCreate(
                ['slug' => Str::slug($tag)],
                ['name' => $tag]
            );
        }
    }
}