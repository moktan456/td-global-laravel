<?php

use App\Http\Controllers\FormController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::view('/services', 'pages.services')->name('services');
Route::get('/services/{slug}', [PageController::class, 'service'])->name('services.show');
Route::view('/about', 'pages.about')->name('about');
Route::view('/calculator', 'pages.calculator')->name('calculator');
Route::view('/blog', 'blog.index')->name('blog');
Route::get('/blog/{slug}', [PageController::class, 'article'])->name('blog.show');
Route::view('/clearing-agent-gelephu', 'pages.clearing-agent-gelephu')->name('clearing');
Route::get('/search', [PageController::class, 'search'])->name('search');
Route::view('/credits', 'pages.credits')->name('credits');
Route::view('/pricing', 'pages.pricing')->name('pricing');
Route::view('/contact', 'pages.contact')->name('contact');
Route::get('/quote', [PageController::class, 'quote'])->name('quote');
Route::post('/contact', [FormController::class, 'contact'])->name('forms.contact');
Route::post('/quote', [FormController::class, 'quote'])->name('forms.quote');
