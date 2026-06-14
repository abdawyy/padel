<?php

namespace App\Models\Scopes;

use App\Support\AdminClubContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class AdminClubScope implements Scope
{
    public function __construct(
        private readonly string $column = 'club_id',
    ) {}

    public function apply(Builder $builder, Model $model): void
    {
        if (! AdminClubContext::shouldFilter()) {
            return;
        }

        $clubId = AdminClubContext::id();

        if ($clubId) {
            $builder->where($model->getTable().'.'.$this->column, $clubId);
        }
    }
}
