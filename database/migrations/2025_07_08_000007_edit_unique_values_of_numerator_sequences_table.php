<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('numerator_sequences', function (Blueprint $table) {
            $table->dropUnique('numerator_sequences_unique');
            $table->unique(['profile_id', 'model_type', 'model_id'], 'numerator_sequences_unique');
        });
    }

    public function down(): void
    {
        Schema::table('numerator_sequences', function (Blueprint $table) {
            $table->dropUnique('numerator_sequences_unique');
            $table->unique(['profile_id', 'formatted_number'], 'numerator_sequences_unique');
        });
    }
};
