<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\Permission\Models\Role as SpatieRole;


class Role extends SpatieRole
{
    use HasUuids;

    public const SUPERADMIN = 'superadmin';
    public const ADMIN = 'admin';
    public const STUDENT = 'student';
    public const INSTRUCTOR = 'instructor';
}
