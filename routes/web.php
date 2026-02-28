<?php

declare(strict_types=1);

use App\Http\Controllers\Account\RecoverController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Home\Ajax\HomeController;
use App\Http\Controllers\Home\Ajax\InfoController;
use App\Http\Controllers\Home\Ajax\MediaController;
use App\Http\Controllers\Home\LoginController;
use App\Http\Controllers\Home\RegisterController;
use App\Http\Controllers\Home\WelcomeController;
use App\Http\Middleware\LegacyAdminBootstrap;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::prefix('/')->group(function () {
    Route::get('/', WelcomeController::class)->name('home.index');
    Route::post('/login', LoginController::class)->name('home.login');
    Route::post('/register', RegisterController::class)->name('home.register');
});

Route::prefix('home/ajax')->group(function () {
    Route::get('/home', HomeController::class)->name('ajax.home');
    Route::get('/info', InfoController::class)->name('ajax.info');
    Route::get('/media', MediaController::class)->name('ajax.media');
});

Route::prefix('account')->group(function () {
    Route::get('/recover', RecoverController::class)->name('account.recover');
    Route::post('/recover', [RecoverController::class, 'recover']);
});

Route::prefix('admin')->group(function () {
    Route::get('/', Admin\IndexController::class)->name('admin.index');
    Route::post('/login', Admin\LoginController::class)->name('admin.login');
    Route::get('/logout', Admin\LogoutController::class)->name('admin.logout');

    Route::withoutMiddleware(VerifyCsrfToken::class)
        ->middleware(LegacyAdminBootstrap::class)
        ->group(function () {
            Route::get('/announcement', [Admin\AnnouncementController::class, 'index'])->name('admin.announcement');
            Route::post('/announcement', [Admin\AnnouncementController::class, 'send'])->name('admin.announcement.send');
            Route::any('/alliances', Admin\AlliancesController::class)->name('admin.alliances');
            Route::any('/backup', Admin\BackupController::class)->name('admin.backup');
            Route::get('/ban', [Admin\BanController::class, 'index'])->name('admin.ban');
            Route::get('/ban/form', [Admin\BanController::class, 'ban'])->name('admin.ban.form');
            Route::post('/ban/form', [Admin\BanController::class, 'ban'])->name('admin.ban.form.post');
            Route::post('/ban/unban', [Admin\BanController::class, 'unban'])->name('admin.ban.unban');
            Route::any('/changelog', Admin\ChangelogController::class)->name('admin.changelog');
            Route::get('/encrypter', [Admin\EncrypterController::class, 'index'])->name('admin.encrypter');
            Route::post('/encrypter', [Admin\EncrypterController::class, 'encrypt'])->name('admin.encrypter.encrypt');
            Route::any('/errors', Admin\ErrorsController::class)->name('admin.errors');
            Route::any('/fleets', Admin\FleetsController::class)->name('admin.fleets');
            Route::any('/home', Admin\HomeController::class)->name('admin.home');
            Route::any('/languages', Admin\LanguagesController::class)->name('admin.languages');
            Route::any('/mailing', Admin\MailingController::class)->name('admin.mailing');
            Route::any('/maker', Admin\MakerController::class)->name('admin.maker');
            Route::any('/messages', Admin\MessagesController::class)->name('admin.messages');
            Route::any('/modules', Admin\ModulesController::class)->name('admin.modules');
            Route::any('/permissions', Admin\PermissionsController::class)->name('admin.permissions');
            Route::any('/planets', Admin\PlanetsController::class)->name('admin.planets');
            Route::any('/premium', Admin\PremiumController::class)->name('admin.premium');
            Route::any('/rebuildhighscores', Admin\RebuildHighscoresController::class)->name('admin.rebuildhighscores');
            Route::any('/registration', Admin\RegistrationController::class)->name('admin.registration');
            Route::any('/repair', Admin\RepairController::class)->name('admin.repair');
            Route::any('/reset', Admin\ResetController::class)->name('admin.reset');
            Route::any('/server', Admin\ServerController::class)->name('admin.server');
            Route::any('/statistics', Admin\StatisticsController::class)->name('admin.statistics');
            Route::any('/tasks', Admin\TasksController::class)->name('admin.tasks');
            Route::any('/update', Admin\UpdateController::class)->name('admin.update');
            Route::any('/users', Admin\UsersController::class)->name('admin.users');
        });
});
