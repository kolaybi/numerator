<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        $defaultReuseNumberStatus = Config::get('numerator.database.default_reuse_if_deleted');

        Schema::table('numerator_profiles', function (Blueprint $table) use ($defaultReuseNumberStatus) {
            $table->boolean('reuse_if_deleted')->default($defaultReuseNumberStatus)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('numerator_profiles', function (Blueprint $table) {
            $table->dropColumn(['reuse_if_deleted']);
        });
    }
};
