<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', fn (Blueprint $table) => $table->json('tags')->nullable());

        DB::transaction(function (): void {
            $groups = [
                'diskusi' => ['Diskusi', ['diksi', 'diskusi-respons-isu', 'bedah-putusan', 'diseminasi-riset']],
                'pelatihan' => ['Pelatihan', ['training', 'bootcamp-short-course', 'community', 'expert-class']],
                'internship' => ['Internship', ['magang', 'magang-internship']],
                'workshop-webinar' => ['Workshop/Webinar', ['general-lecture']],
            ];
            foreach ($groups as $slug => [$name, $aliases]) {
                $target = DB::table('program_categories')->where('slug', $slug)->first();
                $values = ['name' => $name, 'is_active' => true, 'sort_order' => array_search($slug, array_keys($groups)) + 1, 'updated_at' => now()];
                if ($target) {
                    $id = $target->id;
                    DB::table('program_categories')->where('id', $id)->update($values);
                } else {
                    $id = DB::table('program_categories')->insertGetId([...$values, 'slug' => $slug, 'created_at' => now()]);
                }
                foreach (DB::table('program_categories')->whereIn('slug', $aliases)->get() as $old) {
                    DB::table('programs')->where('program_category_id', $old->id)->orderBy('id')->each(function ($program) use ($id, $old): void {
                        $tags = json_decode($program->tags ?? '[]', true) ?: [];
                        DB::table('programs')->where('id', $program->id)->update([
                            'program_category_id' => $id,
                            'tags' => json_encode(array_values(array_unique([...$tags, $old->name])), JSON_UNESCAPED_UNICODE),
                        ]);
                    });
                    DB::table('program_categories')->where('id', $old->id)->update(['is_active' => false]);
                }
            }
        });
    }

    public function down(): void
    {
        // Category merges preserve their source labels as tags. Reverting them
        // automatically could overwrite later editorial changes.
        throw new RuntimeException('Restore category assignments and tags from a backup before rolling back this data migration.');
    }
};
