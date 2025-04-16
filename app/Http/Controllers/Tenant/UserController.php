<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index($slug)
    {
        $tenant = Tenant::where('slug', $slug)->firstOrFail();
        return view('tenant.users.index', compact('tenant', 'slug'));
    }
} 