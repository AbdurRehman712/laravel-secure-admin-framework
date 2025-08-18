<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\ThemeRenderer;

class PublicProfileController extends Controller
{
    public function index(Request $request, ThemeRenderer $renderer): View
    {
        return $renderer->render('profiles', [
            'title' => 'Public Profiles',
        ]);
    }

    public function show(string $username, Request $request, ThemeRenderer $renderer): View
    {
        return $renderer->render('profile-public', [
            'title' => 'Profile',
            'username' => $username,
        ]);
    }
}
