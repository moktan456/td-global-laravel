<?php

namespace App\Support;

use App\Models\User;

/** The people a shipment can be assigned to: everyone who can sign in (plus the current assignee, if they have since been switched off). */
class Assignees
{
    /** @return list<array{value: string, label: string}> */
    public static function options(?int $alsoInclude = null): array
    {
        return User::query()
            ->where(fn ($q) => $q->where('active', true)->when($alsoInclude, fn ($w) => $w->orWhere('id', $alsoInclude)))
            ->orderBy('name')->orderBy('id')->get(['id', 'name', 'active'])
            ->map(fn ($u) => ['value' => (string) $u->id, 'label' => $u->name.($u->active ? '' : ' (switched off)')])
            ->all();
    }
}
