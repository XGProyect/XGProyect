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
            Route::get('/alliances', [Admin\AlliancesController::class, 'index'])->name('admin.alliances');
            Route::get('/alliances/{alliance}', [Admin\AlliancesController::class, 'showInfo'])->name('admin.alliances.info');
            Route::post('/alliances/{alliance}', [Admin\AlliancesController::class, 'updateInfo'])->name('admin.alliances.info.update');
            Route::get('/alliances/{alliance}/ranks', [Admin\AlliancesController::class, 'showRanks'])->name('admin.alliances.ranks');
            Route::post('/alliances/{alliance}/ranks', [Admin\AlliancesController::class, 'updateRanks'])->name('admin.alliances.ranks.update');
            Route::get('/alliances/{alliance}/members', [Admin\AlliancesController::class, 'showMembers'])->name('admin.alliances.members');
            Route::post('/alliances/{alliance}/members', [Admin\AlliancesController::class, 'removeMembers'])->name('admin.alliances.members.remove');
            Route::delete('/alliances/{alliance}', [Admin\AlliancesController::class, 'destroy'])->name('admin.alliances.destroy');
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
            Route::get('/languages', [Admin\LanguagesController::class, 'index'])->name('admin.languages');
            Route::post('/languages', [Admin\LanguagesController::class, 'update'])->name('admin.languages.update');
            Route::any('/mailing', Admin\MailingController::class)->name('admin.mailing');
            Route::any('/maker', Admin\MakerController::class)->name('admin.maker');
            Route::any('/messages', Admin\MessagesController::class)->name('admin.messages');
            Route::any('/modules', Admin\ModulesController::class)->name('admin.modules');
            Route::get('/permissions', [Admin\PermissionsController::class, 'index'])->name('admin.permissions');
            Route::post('/permissions', [Admin\PermissionsController::class, 'save'])->name('admin.permissions.save');
            Route::any('/planets', Admin\PlanetsController::class)->name('admin.planets');
            Route::any('/premium', Admin\PremiumController::class)->name('admin.premium');
            Route::get('/rebuildhighscores', [Admin\RebuildHighscoresController::class, 'index'])->name('admin.rebuildhighscores');
            Route::post('/rebuildhighscores', [Admin\RebuildHighscoresController::class, 'run'])->name('admin.rebuildhighscores.run');
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
            // Users
            Route::get('/users', [Admin\UsersController::class, 'index'])->name('admin.users');
            Route::get('/users/{user}', [Admin\UsersController::class, 'showInfo'])->name('admin.users.info');
            Route::post('/users/{user}', [Admin\UsersController::class, 'updateInfo'])->name('admin.users.info.update');
            Route::get('/users/{user}/settings', [Admin\UsersController::class, 'showSettings'])->name('admin.users.settings');
            Route::post('/users/{user}/settings', [Admin\UsersController::class, 'updateSettings'])->name('admin.users.settings.update');
            Route::get('/users/{user}/research', [Admin\UsersController::class, 'showResearch'])->name('admin.users.research');
            Route::post('/users/{user}/research', [Admin\UsersController::class, 'updateResearch'])->name('admin.users.research.update');
            Route::get('/users/{user}/premium', [Admin\UsersController::class, 'showPremium'])->name('admin.users.premium');
            Route::post('/users/{user}/premium', [Admin\UsersController::class, 'updatePremium'])->name('admin.users.premium.update');
            Route::get('/users/{user}/planets', [Admin\UsersController::class, 'showPlanets'])->name('admin.users.planets');
            Route::get('/users/{user}/planets/{planet}', [Admin\UsersController::class, 'showPlanet'])->name('admin.users.planet.edit');
            Route::post('/users/{user}/planets/{planet}', [Admin\UsersController::class, 'updatePlanet'])->name('admin.users.planet.update');
            Route::get('/users/{user}/planets/{planet}/buildings', [Admin\UsersController::class, 'showPlanetBuildings'])->name('admin.users.planet.buildings');
            Route::post('/users/{user}/planets/{planet}/buildings', [Admin\UsersController::class, 'updatePlanetBuildings'])->name('admin.users.planet.buildings.update');
            Route::get('/users/{user}/planets/{planet}/ships', [Admin\UsersController::class, 'showPlanetShips'])->name('admin.users.planet.ships');
            Route::post('/users/{user}/planets/{planet}/ships', [Admin\UsersController::class, 'updatePlanetShips'])->name('admin.users.planet.ships.update');
            Route::get('/users/{user}/planets/{planet}/defenses', [Admin\UsersController::class, 'showPlanetDefenses'])->name('admin.users.planet.defenses');
            Route::post('/users/{user}/planets/{planet}/defenses', [Admin\UsersController::class, 'updatePlanetDefenses'])->name('admin.users.planet.defenses.update');
            Route::post('/users/{user}/planets/{planet}/soft-delete', [Admin\UsersController::class, 'softDeletePlanet'])->name('admin.users.planet.soft-delete');
            Route::delete('/users/{user}/planets/{planet}', [Admin\UsersController::class, 'hardDeletePlanet'])->name('admin.users.planet.destroy');
            Route::get('/users/{user}/moons', [Admin\UsersController::class, 'showMoons'])->name('admin.users.moons');
            Route::get('/users/{user}/moons/{moon}', [Admin\UsersController::class, 'showMoon'])->name('admin.users.moon.edit');
            Route::post('/users/{user}/moons/{moon}', [Admin\UsersController::class, 'updateMoon'])->name('admin.users.moon.update');
            Route::get('/users/{user}/moons/{moon}/buildings', [Admin\UsersController::class, 'showMoonBuildings'])->name('admin.users.moon.buildings');
            Route::post('/users/{user}/moons/{moon}/buildings', [Admin\UsersController::class, 'updateMoonBuildings'])->name('admin.users.moon.buildings.update');
            Route::get('/users/{user}/moons/{moon}/ships', [Admin\UsersController::class, 'showMoonShips'])->name('admin.users.moon.ships');
            Route::post('/users/{user}/moons/{moon}/ships', [Admin\UsersController::class, 'updateMoonShips'])->name('admin.users.moon.ships.update');
            Route::get('/users/{user}/moons/{moon}/defenses', [Admin\UsersController::class, 'showMoonDefenses'])->name('admin.users.moon.defenses');
            Route::post('/users/{user}/moons/{moon}/defenses', [Admin\UsersController::class, 'updateMoonDefenses'])->name('admin.users.moon.defenses.update');
            Route::post('/users/{user}/moons/{moon}/soft-delete', [Admin\UsersController::class, 'softDeleteMoon'])->name('admin.users.moon.soft-delete');
            Route::delete('/users/{user}/moons/{moon}', [Admin\UsersController::class, 'hardDeleteMoon'])->name('admin.users.moon.destroy');
            Route::delete('/users/{user}', [Admin\UsersController::class, 'destroy'])->name('admin.users.destroy');
        });
});
