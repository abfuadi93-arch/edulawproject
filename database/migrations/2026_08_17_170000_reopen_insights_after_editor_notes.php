<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('insights')
            || ! Schema::hasTable('insight_editorial_activities')
            || ! Schema::hasTable('insight_status_histories')) {
            return;
        }

        DB::table('insights')
            ->where('status', 'review')
            ->orderBy('id')
            ->chunkById(100, function ($insights): void {
                foreach ($insights as $insight) {
                    $latestActivity = DB::table('insight_editorial_activities')
                        ->where('insight_id', $insight->id)
                        ->orderByDesc('created_at')
                        ->orderByDesc('id')
                        ->first();

                    if ($latestActivity?->event !== 'editor_note_saved') {
                        continue;
                    }

                    $changedAt = $latestActivity->created_at ?? now();

                    DB::table('insights')->where('id', $insight->id)->update([
                        'status' => 'draft',
                        'revision_requested_at' => $changedAt,
                        'updated_at' => now(),
                    ]);

                    DB::table('insight_status_histories')->insert([
                        'insight_id' => $insight->id,
                        'changed_by' => $latestActivity->actor_id,
                        'from_status' => 'review',
                        'to_status' => 'draft',
                        'notes' => 'Akses edit Penulis dibuka kembali setelah catatan Editor disimpan.',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Tidak mengunci kembali naskah karena Penulis mungkin sudah menyuntingnya.
    }
};
