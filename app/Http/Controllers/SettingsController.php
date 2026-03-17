<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:admin.settings.manage']);
    }

    public function index()
    {
        $settings = Setting::orderBy('setting_group')->orderBy('key')->get();

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request, Setting $setting)
    {
        $request->validate([
            'is_active' => 'required|in:0,1',
            'value' => 'nullable|string|max:2000',
        ]);

        $setting->update([
            'is_active' => (bool) (int) $request->is_active,
            'value' => $request->input('value', $setting->value),
        ]);
        Setting::forgetCache();

        return redirect()->route('settings.index')->with('status', 'Setting updated: '.$setting->key);
    }
}
