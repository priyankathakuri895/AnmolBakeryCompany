<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('van_loads', function (Blueprint $table) {
            // The composite unique index doubles as the van_id foreign key's supporting
            // index — MySQL won't let it be dropped until a replacement index exists.
            $table->index('van_id', 'van_loads_van_id_fk_index');

            // MySQL has no partial/filtered unique index, so a soft-deleted row would
            // otherwise permanently block reloading the same van on the same date.
            // Uniqueness is enforced at the application level instead (see
            // VanLoadController::store(), which already excludes soft-deleted rows).
            $table->dropUnique(['van_id', 'load_date']);

            $table->text('delete_reason')->nullable()->after('notes');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('van_loads', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('delete_reason');
            $table->unique(['van_id', 'load_date']);
            $table->dropIndex('van_loads_van_id_fk_index');
        });
    }
};
