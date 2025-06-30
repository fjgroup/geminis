<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class ResellerScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Solo aplicar el filtro si hay un usuario autenticado
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();

        // Si es admin, no aplicar filtro (puede ver todo)
        if ($user->role === 'admin') {
            return;
        }

        // Si es reseller, filtrar por sus datos
        if ($user->role === 'reseller') {
            // Para modelos que tienen reseller_id directamente
            if ($model->getTable() === 'client_services') {
                $builder->where('reseller_id', $user->id);
            }
            // Para modelos User (clientes del reseller)
            elseif ($model->getTable() === 'users') {
                $builder->where('reseller_id', $user->id);
            }
            // Para productos del reseller
            elseif ($model->getTable() === 'products') {
                $builder->where(function ($query) use ($user) {
                    $query->where('owner_id', $user->id) // Productos propios del reseller
                          ->orWhere(function ($subQuery) {
                              $subQuery->whereNull('owner_id') // Productos de la plataforma
                                       ->where('is_resellable_by_default', true);
                          });
                });
            }
        }

        // Si es client, filtrar por sus propios datos
        if ($user->role === 'client') {
            if ($model->getTable() === 'client_services') {
                $builder->where('client_id', $user->id);
            }
            elseif ($model->getTable() === 'users') {
                $builder->where('id', $user->id); // Solo puede ver su propio usuario
            }
        }
    }
}
