<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/Settings', [
            'settings' => [
                'show_request_list' => Setting::get('show_request_list', true),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'show_request_list' => 'sometimes|boolean',
        ]);

        $showRequestList = $validated['show_request_list'] ?? false;

        Setting::set('show_request_list', $showRequestList, 'boolean');

        return back()->with('success', 'Settings updated successfully.');
    }
}
