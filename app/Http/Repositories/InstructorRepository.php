<?php

namespace App\Http\Repositories;

use App\Models\Instructor;
use Illuminate\Support\Facades\Auth;

class InstructorRepository extends BaseRepository
{
    public function __construct(Instructor $instructor)
    {
        parent::__construct($instructor);
    }

    public function currentUserInstructorProfile()
    {
        $instructor = Instructor::where('user_id', Auth::user()->id)->firstOrFail();
        return $instructor->load([
            'department:id,name,code'
        ]);
    }
}
