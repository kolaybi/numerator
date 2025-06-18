<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        $defaultIsActiveStatus = Config::get('numerator.database.default_profile_is_active');

        Schema::table('numerator_profiles', function (Blueprint $table) use ($defaultIsActiveStatus) {
            $table->boolean('is_active')->default($defaultIsActiveStatus)->after('type_id');
        });
    }

    public function down(): void
    {
        Schema::table('numerator_profiles', function (Blueprint $table) {
            $table->dropColumn(['is_active']);
        });
    }
};
