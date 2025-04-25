<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
            DB::statement("ALTER TABLE candidates DROP COLUMN IF EXISTS status");

            Schema::table('candidates', function (Blueprint $table) {
                $table->enum('status', ['active', 'inactive', 'rejected', 'graduated'])->default('inactive');
                });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

            DB::statement("ALTER TABLE candidates DROP COLUMN IF EXISTS status");
            DB::statement("DROP TYPE IF EXISTS candidate_status");
}
};
