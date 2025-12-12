<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // <-- Tambahkan ini
use Illuminate\Foundation\Validation\ValidatesRequests; // Ini biasanya sudah ada
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests; // <-- Tambahkan AuthorizesRequests di sini
}
