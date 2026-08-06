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
            $table->string('workflow_stage')->default('submission')->after('status')->index();
            $table->unsignedInteger('current_review_round')->default(0)->after('workflow_stage');
            $table->unsignedInteger('current_revision_number')->default(0)->after('current_review_round');
        });

        DB::table('insights')->whereIn('status', ['editor_assigned', 'in_review', 'revised'])->update(['workflow_stage' => 'editorial_review']);
        DB::table('insights')->where('status', 'revision_requested')->update(['workflow_stage' => 'author_revision']);
        DB::table('insights')->whereIn('status', ['approved', 'rejected'])->update(['workflow_stage' => 'final_approval']);
        DB::table('insights')->whereIn('status', ['published', 'archived'])->update(['workflow_stage' => 'publication']);
        DB::table('insights')->update(['current_revision_number' => DB::raw('revision_round')]);
    }

    public function down(): void
    {
        Schema::table('insights', function (Blueprint $table): void {
            $table->dropIndex(['workflow_stage']);
            $table->dropColumn(['workflow_stage', 'current_review_round', 'current_revision_number']);
        });
    }
};
