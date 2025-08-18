<?php

namespace App\Http\Controllers\AppSite;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\ThemeRenderer;

class ProfileController extends Controller
{
    public function show(Request $request, ThemeRenderer $renderer): View
    {
        return $renderer->render('my-profile', ['title' => 'My Profile']);
    }

    public function edit(Request $request, ThemeRenderer $renderer): View
    {
        return $renderer->render('my-profile-edit', ['title' => 'Edit Profile']);
    }

    public function photos(Request $request, ThemeRenderer $renderer): View
    {
        return $renderer->render('my-profile-photos', ['title' => 'My Photos']);
    }

    public function settings(Request $request, ThemeRenderer $renderer): View
    {
        return $renderer->render('my-profile-settings', ['title' => 'Profile Settings']);
    }

    public function publicView(Request $request, ThemeRenderer $renderer): View
    {
        return $renderer->render('my-profile-public-view', ['title' => 'Public Profile Preview']);
    }
}
