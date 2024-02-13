<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        $suffixColumnLength = Config::get('numerator.database.suffix_length', 3);

        Schema::table('numerator_profiles', function (Blueprint $table) use ($suffixColumnLength) {
            $table->string('suffix', $suffixColumnLength)->nullable()->after('prefix');
            $table->unsignedTinyInteger('pad_length')->nullable()->after('format');
        });
    }

    public function down(): void
    {
        Schema::table('numerator_profiles', function (Blueprint $table) {
            $table->dropColumn(['suffix', 'pad_length']);
        });
    }
};
