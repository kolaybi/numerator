<?php

namespace KolayBi\Numerator\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Request;

class TenantIdScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $scopeKey = config('numerator.database.tenant_id_column');

        if ($tenantId = Request::input($scopeKey)) {
            $builder->where($scopeKey, '=', $tenantId);
        }
    }
}
