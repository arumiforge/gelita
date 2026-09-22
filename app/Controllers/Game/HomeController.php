<?php

namespace App\Controllers\Game;

use App\Controllers\BaseController;

/**
 * Tahap 2: layar welcome placeholder.
 * start(), intro(), setLocale() ditambahkan pada tahap 4.
 */
class HomeController extends BaseController
{
    public function index(): string
    {
        return view('game/welcome', [
            'locale'     => $this->locale,
            'isLoggedIn' => (int) session('participant_id') > 0,
        ]);
    }
}
