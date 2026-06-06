<?php

namespace Pterodactyl\Http\Controllers\User;

use Illuminate\Http\Request;
use Pterodactyl\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Pterodactyl\Traits\Helpers\AvailableLanguages;

class LanguageController extends Controller
{
    use AvailableLanguages;

    public function index()
    {
        $languages = $this->getAvailableLanguages(true);
        return response()->json($languages);
    }

    public function update(Request $request)
    {
        $availableLocales = array_keys($this->getAvailableLanguages(false));

        $request->validate([
            'language' => ['required', 'string', Rule::in($availableLocales)],
        ]);

        $user = $request->user();
        $user->language = $request->input('language');
        $user->save();

        return response()->json(['success' => true]);
    }
}
