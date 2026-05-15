<?php
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BarangController;
Route::get('/', function () {
    return view('welcome');
});
Route::get('/password/reset/{token}', function (Request $request, $token) {
    
    $email = $request -> query('email');
    return response()->json([
        'message' => 'Reset route OK',
        'token' => $token,
        'email'=> $email,
    ]);
})->name('password.reset');


