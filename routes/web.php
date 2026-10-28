<?php

use App\Http\Controllers\FormController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\Staff;
use App\Http\Controllers\TelegramWebhookController;
use App\Http\Controllers\TrackController;
use Illuminate\Support\Facades\Route;

/* Public site (no closures in the routes, so `route:cache` stays safe on the server). */
Route::view('/', 'home')->name('home');
Route::view('/services', 'pages.services')->name('services');
Route::get('/services/{slug}', [PageController::class, 'service'])->name('services.show');
Route::view('/clearing-agent-gelephu', 'pages.clearing-agent-gelephu')->name('clearing');
Route::get('/search', [PageController::class, 'search'])->name('search');
Route::view('/credits', 'pages.credits')->name('credits');
Route::view('/about', 'pages.about')->name('about');
Route::view('/calculator', 'pages.calculator')->name('calculator');
Route::view('/blog', 'blog.index')->name('blog');
Route::get('/blog/{slug}', [PageController::class, 'article'])->name('blog.show');
Route::view('/pricing', 'pages.pricing')->name('pricing');
Route::view('/contact', 'pages.contact')->name('contact');
Route::get('/quote', [PageController::class, 'quote'])->name('quote');
Route::post('/contact', [FormController::class, 'contact'])->name('forms.contact');
Route::post('/quote', [FormController::class, 'quote'])->name('forms.quote');

Route::get('/robots.txt', [SeoController::class, 'robots']);
Route::get('/sitemap.xml', [SeoController::class, 'sitemap']);

/* Tracking: the tracking number alone is enough. */
Route::get('/track', [TrackController::class, 'show'])->name('track');
Route::post('/track', [TrackController::class, 'lookup'])->name('track.lookup');
Route::post('/track/reminder', [TrackController::class, 'remind'])->name('track.remind');

/* The old "Client login" address now leads to the staff sign-in (clients don't sign in). */
Route::redirect('/login', '/staff/login');

/* Telegram bot webhook (CSRF-exempt; proves itself with a secret header). */
Route::post('/api/telegram/webhook', [TelegramWebhookController::class, 'handle'])->name('telegram.webhook');

/* Photos added in the admin panel (kept in storage/app/public/uploads, so a deploy never touches them). */
Route::get('/media/{path}', [MediaController::class, 'show'])->where('path', 'uploads/[A-Za-z0-9._\\-]+')->name('media');

/* Staff. */
Route::prefix('staff')->group(function () {
    Route::get('login', [Staff\LoginController::class, 'show'])->name('login');
    Route::post('login', [Staff\LoginController::class, 'login'])->name('staff.login');

    Route::middleware('staff')->group(function () {
        Route::post('logout', [Staff\LoginController::class, 'logout'])->name('staff.logout');
        Route::get('password', [Staff\PasswordController::class, 'show'])->name('staff.password');
        Route::post('password', [Staff\PasswordController::class, 'update']);

        Route::get('/', [Staff\DashboardController::class, 'index'])->name('staff.dashboard');
        Route::get('go', [Staff\DashboardController::class, 'go'])->name('staff.go');
        Route::get('shipments/new', [Staff\ShipmentController::class, 'create'])->name('staff.shipments.new');
        Route::post('shipments', [Staff\ShipmentController::class, 'store'])->name('staff.shipments.store');
        Route::get('shipments/{tracking}', [Staff\ShipmentController::class, 'show'])->name('staff.shipments.show');
        Route::post('shipments/{tracking}/status', [Staff\ShipmentController::class, 'updateStatus'])->name('staff.shipments.status');
        Route::post('shipments/{tracking}/tracks', [Staff\TransportController::class, 'tracks'])->name('staff.shipments.tracks');
        Route::post('shipments/{tracking}/transport-status', [Staff\TransportController::class, 'status'])->name('staff.shipments.transport');
        Route::post('shipments/{tracking}/transport-details', [Staff\TransportController::class, 'details'])->name('staff.shipments.transport-details');
        Route::post('shipments/{tracking}/assign', [Staff\ShipmentController::class, 'assign'])->name('staff.shipments.assign');
        Route::post('shipments/{tracking}/resend', [Staff\ShipmentController::class, 'resendTracking'])->name('staff.shipments.resend');
        Route::get('export/shipments.csv', [Staff\ExportController::class, 'shipments'])->name('staff.export.shipments');
        Route::get('export/clients.csv', [Staff\ExportController::class, 'clients'])->name('staff.export.clients');
        Route::get('clients', [Staff\ClientController::class, 'index'])->name('staff.clients');
        Route::post('clients', [Staff\ClientController::class, 'store'])->name('staff.clients.store');
        Route::get('clients/{client}', [Staff\ClientController::class, 'show'])->whereNumber('client')->name('staff.clients.show');
        Route::post('clients/{client}/telegram-link', [Staff\ClientController::class, 'telegramLink'])->name('staff.clients.telegram-link');

        Route::middleware('admin')->group(function () {
            Route::get('team', [Staff\TeamController::class, 'index'])->name('staff.team');
            Route::post('team', [Staff\TeamController::class, 'store'])->name('staff.team.store');
            Route::post('team/{user}/password', [Staff\TeamController::class, 'resetPassword'])->name('staff.team.password');
            Route::post('team/{user}/active', [Staff\TeamController::class, 'setActive'])->name('staff.team.active');
            Route::get('telegram', [Staff\TelegramController::class, 'index'])->name('staff.telegram');
            Route::post('telegram/connect', [Staff\TelegramController::class, 'connect'])->name('staff.telegram.connect');
        });
    });
});
