<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('numerator_sequences', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->timestamps();
            $table->softDeletes();
            $table->string('model_type');
            $table->ulid('model_id');
            $table->ulid('profile_id');
            $table->string('formatted_number');

            $table->index(['profile_id']);

            $table->unique(['profile_id', 'formatted_number'], 'numerator_sequences_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('numerator_sequences');
    }
};
