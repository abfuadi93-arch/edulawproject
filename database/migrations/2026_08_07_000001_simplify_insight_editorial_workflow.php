<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insights', function (Blueprint $table): void {
            if (! Schema::hasColumn('insights', 'editor_notes')) {
                $table->text('editor_notes')->nullable()->after('reviewed_at');
            }

            if (! Schema::hasColumn('insights', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('published_at')->index();
            }
        });

        if (Schema::hasTable('insight_editorial_notes')) {
            DB::table('insights')
                ->where('status', 'revision_requested')
                ->whereNull('editor_notes')
                ->orderBy('id')
                ->chunkById(100, function ($insights): void {
                    foreach ($insights as $insight) {
                        $note = DB::table('insight_editorial_notes')
                            ->where('insight_id', $insight->id)
                            ->where('type', 'revision_request')
                            ->latest('id')
                            ->value('note');

                        if (filled($note)) {
                            DB::table('insights')->where('id', $insight->id)->update(['editor_notes' => $note]);
                        }
                    }
                });
        }

        if (Schema::hasTable('insight_editor_assignments')) {
            DB::table('insights')
                ->whereNull('assigned_editor_id')
                ->orderBy('id')
                ->chunkById(100, function ($insights): void {
                    foreach ($insights as $insight) {
                        $assignment = DB::table('insight_editor_assignments')
                            ->where('insight_id', $insight->id)
                            ->whereIn('status', ['assigned', 'accepted', 'active'])
                            ->latest('id')
                            ->first();

                        if ($assignment) {
                            DB::table('insights')->where('id', $insight->id)->update([
                                'assigned_editor_id' => $assignment->editor_id,
                                'assigned_by' => $assignment->assigned_by,
                                'assigned_at' => $assignment->assigned_at,
                            ]);
                        }
                    }
                });
        }

        DB::table('insights')
            ->where('status', 'revision_requested')
            ->update(['status' => 'draft']);

        DB::table('insights')
            ->whereIn('status', [
                'submitted',
                'editor_assigned',
                'in_review',
                'revised',
                'approved',
                'rejected',
                'reviewed',
            ])
            ->update(['status' => 'review']);

        DB::table('insights')
            ->where('status', 'archived')
            ->whereNull('archived_at')
            ->update(['archived_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        DB::table('insights')->where('status', 'review')->update(['status' => 'submitted']);

        Schema::table('insights', function (Blueprint $table): void {
            if (Schema::hasColumn('insights', 'archived_at')) {
                $table->dropIndex(['archived_at']);
                $table->dropColumn('archived_at');
            }

            if (Schema::hasColumn('insights', 'editor_notes')) {
                $table->dropColumn('editor_notes');
            }
        });
    }
};
