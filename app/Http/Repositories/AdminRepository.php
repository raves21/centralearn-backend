<?php

namespace App\Http\Repositories;

use App\Models\Admin;

class AdminRepository extends BaseRepository
{
    public function __construct(Admin $admin)
    {
        parent::__construct($admin);
    }
}