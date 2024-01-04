<?php

namespace KolayBi\Numerator\Traits;

use KolayBi\Numerator\Scopes\TenantIdScope;

trait TenantIdScopeTrait
{
    public static function bootTenantIdScopeTrait(): void
    {
        static::addGlobalScope(new TenantIdScope());
    }
}
