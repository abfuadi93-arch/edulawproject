<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DiksiEditorialDescriptionsSeeder extends Seeder
{
    public function run(): void
    {
        $descriptions = [
            13 => 'Diskusi membahas kemerdekaan kekuasaan kehakiman sebagai fondasi negara hukum demokratis, termasuk kedudukan peradilan dalam menjaga konstitusi dan melindungi hak warga negara. I Dewa Gede Palguna dan Wahiduddin Adams berbagi perspektif mengenai independensi peradilan dari sisi kelembagaan, etik, dan praktik ketatanegaraan.',
            12 => 'Diskusi membahas perkembangan perjanjian dalam ekonomi digital serta tantangan hukum yang lahir dari pemanfaatan big data. Pembahasan mencakup kontrak elektronik, perlindungan konsumen, alat bukti elektronik, privasi, keamanan informasi, dan tata kelola data.',
            11 => 'Diskusi membahas perubahan konstitusi sebagai bagian dari perkembangan ketatanegaraan serta konsekuensinya terhadap desain kelembagaan negara. Pembahasan juga menempatkan amandemen konstitusi dalam perbandingan antara bentuk negara kesatuan dan negara federal.',
            10 => 'Diskusi membahas perkembangan hukum pidana Indonesia, KUHP baru, serta politik hukum pidana dalam praktik Mahkamah Konstitusi. Perhatian khusus diberikan pada pengaruh putusan MK terhadap penormaan hukum pidana dan perkembangan kebijakan kriminal di Indonesia.',
            9 => 'Diskusi membahas dua isu hukum yang berkembang dalam praktik: persyaratan formil dalam penyelesaian sengketa hasil Pilkada di Mahkamah Konstitusi dan perkembangan perlindungan konsumen. Pembahasan menempatkan keduanya dalam konteks penerapan hukum dan perlindungan hak.',
            8 => 'Diskusi membahas konsep dan cara menafsirkan hak konstitusional, termasuk perbedaannya dengan hak asasi manusia serta kaitannya dengan kedudukan hukum dalam pengujian undang-undang. Pembahasan juga mengulas ruang lingkup dan batas pembatasan hak konstitusional dalam praktik peradilan konstitusi.',
        ];

        DB::transaction(function () use ($descriptions): void {
            $videos = DB::table('multimedia')->where('type', 'video')->where('platform', 'youtube')
                ->where('title', 'like', '%DIKSI%')->lockForUpdate()->get();

            foreach ($descriptions as $series => $description) {
                $matches = $videos->filter(fn ($video): bool => preg_match('/(?:#\s*|ke-?\s*)'.$series.'\b/i', $video->title) === 1);

                if ($matches->count() !== 1) {
                    throw new RuntimeException("Expected exactly one YouTube video for DIKSI #{$series}; no descriptions were saved.");
                }

                DB::table('multimedia')->where('id', $matches->first()->id)->update([
                    'description' => $description,
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
