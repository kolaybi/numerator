<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use KolayBi\Numerator\Enums\NumeratorFormatVariable;

return new class () extends Migration {
    public function up(): void
    {
        $tenantIdColumn = Config::get('numerator.database.tenant_id_column', 'tenant_id');
        $prefixColumnLength = Config::get('numerator.database.prefix_length', 3);

        Schema::create('numerator_profiles', function (Blueprint $table) use ($tenantIdColumn, $prefixColumnLength) {
            $table->ulid('id')->primary();
            $table->timestamps();
            $table->softDeletes();
            $table->ulid($tenantIdColumn);
            $table->ulid('type_id');
            $table->string('prefix', $prefixColumnLength)->nullable();
            $table->string('format')->default(NumeratorFormatVariable::NUMBER->value);
            $table->unsignedBigInteger('start');
            $table->unsignedBigInteger('counter');

            $table->index([$tenantIdColumn]);
            $table->index('type_id');

            $table->unique([$tenantIdColumn, 'type_id'], 'numerator_profiles_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('numerator_profiles');
    }
};
