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

    Route::middleware(LegacyAdminBootstrap::class)
        ->group(function () {
            Route::get('/announcement', [Admin\AnnouncementController::class, 'index'])->name('admin.announcement');
            Route::post('/announcement', [Admin\AnnouncementController::class, 'send'])->name('admin.announcement.send');
            Route::any('/alliances', Admin\AlliancesController::class)->name('admin.alliances');
            Route::get('/backup', [Admin\BackupController::class, 'index'])->name('admin.backup');
            Route::post('/backup/settings', [Admin\BackupController::class, 'save'])->name('admin.backup.save');
            Route::post('/backup/create', [Admin\BackupController::class, 'create'])->name('admin.backup.create');
            Route::get('/backup/{file}/download', [Admin\BackupController::class, 'download'])->name('admin.backup.download');
            Route::delete('/backup/{file}', [Admin\BackupController::class, 'destroy'])->name('admin.backup.destroy');
            Route::get('/ban', [Admin\BanController::class, 'index'])->name('admin.ban');
            Route::get('/ban/form', [Admin\BanController::class, 'ban'])->name('admin.ban.form');
            Route::post('/ban/form', [Admin\BanController::class, 'storeBan'])->name('admin.ban.form.post');
            Route::post('/ban/unban', [Admin\BanController::class, 'unban'])->name('admin.ban.unban');
            Route::get('/changelog', [Admin\ChangelogController::class, 'index'])->name('admin.changelog');
            Route::get('/changelog/create', [Admin\ChangelogController::class, 'create'])->name('admin.changelog.create');
            Route::post('/changelog', [Admin\ChangelogController::class, 'store'])->name('admin.changelog.store');
            Route::get('/changelog/{changelog}/edit', [Admin\ChangelogController::class, 'edit'])->name('admin.changelog.edit');
            Route::put('/changelog/{changelog}', [Admin\ChangelogController::class, 'update'])->name('admin.changelog.update');
            Route::delete('/changelog/{changelog}', [Admin\ChangelogController::class, 'destroy'])->name('admin.changelog.destroy');
            Route::any('/errors', Admin\ErrorsController::class)->name('admin.errors');
            Route::any('/fleets', Admin\FleetsController::class)->name('admin.fleets');
            Route::any('/home', Admin\HomeController::class)->name('admin.home');
            Route::any('/languages', Admin\LanguagesController::class)->name('admin.languages');
            Route::any('/mailing', Admin\MailingController::class)->name('admin.mailing');
            Route::any('/maker', Admin\MakerController::class)->name('admin.maker');
            Route::any('/messages', Admin\MessagesController::class)->name('admin.messages');
            Route::any('/modules', Admin\ModulesController::class)->name('admin.modules');
            Route::get('/permissions', [Admin\PermissionsController::class, 'index'])->name('admin.permissions');
            Route::post('/permissions', [Admin\PermissionsController::class, 'save'])->name('admin.permissions.save');
            Route::any('/planets', Admin\PlanetsController::class)->name('admin.planets');
            Route::any('/premium', Admin\PremiumController::class)->name('admin.premium');
            Route::get('/rebuildhighscores', [Admin\RebuildHighscoresController::class, 'index'])->name('admin.rebuildhighscores');
            Route::any('/registration', Admin\RegistrationController::class)->name('admin.registration');
            Route::get('/repair', [Admin\RepairController::class, 'index'])->name('admin.repair');
            Route::post('/repair', [Admin\RepairController::class, 'run'])->name('admin.repair.run');
            Route::get('/reset', [Admin\ResetController::class, 'index'])->name('admin.reset');
            Route::post('/reset', [Admin\ResetController::class, 'reset'])->name('admin.reset.post');
            Route::any('/server', Admin\ServerController::class)->name('admin.server');
            Route::any('/statistics', Admin\StatisticsController::class)->name('admin.statistics');
            Route::any('/tasks', Admin\TasksController::class)->name('admin.tasks');
            Route::get('/update', [Admin\UpdateController::class, 'index'])->name('admin.update');
            Route::post('/update', [Admin\UpdateController::class, 'run'])->name('admin.update.run');
            Route::any('/users', Admin\UsersController::class)->name('admin.users');
        });
});
