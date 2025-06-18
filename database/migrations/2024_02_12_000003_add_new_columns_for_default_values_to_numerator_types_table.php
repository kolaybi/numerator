<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        $prefixColumnLength = Config::get('numerator.database.prefix_length');
        $suffixColumnLength = Config::get('numerator.database.suffix_length');

        Schema::table('numerator_types', function (Blueprint $table) use ($prefixColumnLength, $suffixColumnLength) {
            $table->string('prefix', $prefixColumnLength)->nullable();
            $table->string('suffix', $suffixColumnLength)->nullable();
            $table->string('format')->nullable();
            $table->unsignedTinyInteger('pad_length')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('numerator_types', function (Blueprint $table) {
            $table->dropColumn(['prefix', 'suffix', 'format', 'pad_length']);
        });
    }
};
