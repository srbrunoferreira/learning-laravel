<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function listProducts() {
        $busca = request('search');
        return view('product.products', ['busca' => $busca]);
    }
}
