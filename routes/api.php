<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/', fn() => response()->json(['message' => 'hello from /api']));
