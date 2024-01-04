<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('numerator_types', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->timestamps();
            $table->softDeletes();
            $table->ulid('name');
            $table->unsignedBigInteger('min')->nullable();
            $table->unsignedBigInteger('max')->nullable();

            $table->unique('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('numerator_types');
    }
};
