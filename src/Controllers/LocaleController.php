<?php

namespace Kho8k\Core\Controllers;
use Illuminate\Http\Request;
class LocaleController extends Controller
{

    public function setLocale($locale)
    {
        if (in_array($locale, ['en', 'vi'])) {
            session(['locale' => $locale]);
        }
        return redirect()->back();
    }
}
