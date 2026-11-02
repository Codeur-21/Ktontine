<?php

use App\Http\Controllers\ClientTicketController;
use App\Http\Controllers\ReportPrintController;
use App\Http\Controllers\SubscriptionPaymentController;
use App\Http\Controllers\TicketSheetPrintController;
use App\Http\Middleware\EnsureTenantAccess;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Calendrier;
use App\Livewire\Client;
use App\Livewire\Cotisation;
use App\Livewire\Dashboard;
use App\Livewire\Equipe;
use App\Livewire\Parametre;
use App\Livewire\Profile;
use App\Livewire\Rapport;
use App\Livewire\Retard;
use App\Livewire\TicketSheets;
use App\Livewire\Tontine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// routes libres
Route::get('/login', Login::class)->name('login');
Route::get('/register', Register::class)->name('register');

Route::get('/reset_password', function () {
    return view('front/connexion/reset-password');
})->name('reset_password');

// routes sécurisées

Route::middleware('auth')->group(function () {

    Route::post('/logout', function (Request $request): RedirectResponse {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');

    Route::middleware(EnsureTenantAccess::class)->group(function (): void {
        Route::get('/dashboard', Dashboard::class)->name('dashboard');
        Route::get('/clients', Client::class)->name('clients');
        Route::get('/clients/{customer}/ticket', ClientTicketController::class)->whereNumber('customer')->name('clients.ticket');
        Route::get('/tontines', Tontine::class)->name('tontines');
        Route::get('/equipe', Equipe::class)->name('equipe');
        Route::get('/profile', Profile::class)->name('profile');
        Route::get('/abonnement', SubscriptionPaymentController::class)->name('abonnement');
        Route::get('/tickets/planches', TicketSheets::class)->name('tickets.sheets');
        Route::get('/tickets/planches/imprimer', TicketSheetPrintController::class)->name('tickets.sheets.print');
        Route::get('/cotisations', Cotisation::class)->name('cotisations');
        Route::get('/parametres', Parametre::class)->name('parametres');
        Route::get('/calendrier', Calendrier::class)->name('calendrier');
        Route::get('/retards', Retard::class)->name('retards');
        Route::get('/rapports', Rapport::class)->name('rapports');
        Route::get('/rapports/export', ReportPrintController::class)->name('rapports.export');
        Route::redirect('/backend', '/dashboard')->name('backend');
    });

});

Route::redirect('/connexion', '/login')->name('connexion');
