<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class ProductVisibilityScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // No filtering for unauthenticated contexts (console, seeders, etc.)
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();

        // Admin sees everything — no filtering
        if ($user->role === 'admin') {
            return;
        }

        // Technician sees only their own products
        if ($user->role === 'technician') {
            $builder->where('products.user_id', $user->id);
            return;
        }

        // Staff and any other role see only shared/regular products (user_id is NULL)
        $builder->whereNull('products.user_id');
    }
}
