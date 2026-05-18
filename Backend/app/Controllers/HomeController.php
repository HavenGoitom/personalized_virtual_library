<?php

namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller
{
    public function index(): void
    {
        $this->render('home', [
            'title' => 'Woodland Library',
            'active' => 'home'
        ]);
    }
}
