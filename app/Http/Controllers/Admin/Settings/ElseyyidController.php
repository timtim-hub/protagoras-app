<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ElseyyidController extends Controller
{
    public function index()
    {
        return view('admin.settings.languages.index');
    }

    public function lang($lang)
    {
        return view('admin.settings.languages.lang', compact('lang'));
    }

    public function generateJson($lang)
    {
        // Implementation for generating JSON language files
        return back()->with('success', __('Language JSON file generated successfully'));
    }

    public function newLang()
    {
        return view('admin.settings.languages.new');
    }

    public function newString()
    {
        return view('admin.settings.languages.new-string');
    }

    public function search(Request $request)
    {
        // Implementation for searching language strings
        return view('admin.settings.languages.search');
    }

    public function publishAll()
    {
        // Implementation for publishing all language files
        return back()->with('success', __('All language files published successfully'));
    }
} 