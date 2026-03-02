<?php

declare(strict_types=1);

use App\Http\Controllers\Account\RecoverController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\Ajax as AdminAjax;
use App\Http\Controllers\Admin\Users as AdminUsers;
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
            Route::get('/alliances/create', [Admin\AlliancesController::class, 'create'])->name('admin.alliances.create');
            Route::post('/alliances', [Admin\AlliancesController::class, 'store'])->name('admin.alliances.store');
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
            Route::get('/messages', [Admin\MessagesController::class, 'index'])->name('admin.messages');
            Route::delete('/messages/{message}', [Admin\MessagesController::class, 'destroy'])->name('admin.messages.destroy');
            Route::post('/messages/delete-batch', [Admin\MessagesController::class, 'destroyBatch'])->name('admin.messages.destroy-batch');
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
            // Ajax
            Route::get('/ajax/update/check', AdminAjax\UpdateCheckController::class)->name('admin.update.check');
            // Users — core (search, create, info, settings, delete)
            Route::get('/users', [AdminUsers\UsersController::class, 'index'])->name('admin.users');
            Route::get('/users/create', [AdminUsers\UsersController::class, 'create'])->name('admin.users.create');
            Route::post('/users', [AdminUsers\UsersController::class, 'store'])->name('admin.users.store');
            Route::get('/users/{user}', [AdminUsers\UsersController::class, 'showInfo'])->name('admin.users.info');
            Route::post('/users/{user}', [AdminUsers\UsersController::class, 'updateInfo'])->name('admin.users.info.update');
            Route::get('/users/{user}/settings', [AdminUsers\UsersController::class, 'showSettings'])->name('admin.users.settings');
            Route::post('/users/{user}/settings', [AdminUsers\UsersController::class, 'updateSettings'])->name('admin.users.settings.update');
            Route::delete('/users/{user}', [AdminUsers\UsersController::class, 'destroy'])->name('admin.users.destroy');
            // Users — research & premium
            Route::get('/users/{user}/research', [AdminUsers\UserProgressController::class, 'showResearch'])->name('admin.users.research');
            Route::post('/users/{user}/research', [AdminUsers\UserProgressController::class, 'updateResearch'])->name('admin.users.research.update');
            Route::get('/users/{user}/premium', [AdminUsers\UserProgressController::class, 'showPremium'])->name('admin.users.premium');
            Route::post('/users/{user}/premium', [AdminUsers\UserProgressController::class, 'updatePremium'])->name('admin.users.premium.update');
            // Users — planets
            Route::get('/users/{user}/planets', [AdminUsers\UserPlanetController::class, 'showPlanets'])->name('admin.users.planets');
            Route::get('/users/{user}/planets/create', [AdminUsers\UserPlanetController::class, 'createPlanet'])->name('admin.users.planet.create');
            Route::post('/users/{user}/planets', [AdminUsers\UserPlanetController::class, 'storePlanet'])->name('admin.users.planet.store');
            Route::get('/users/{user}/planets/{planet}', [AdminUsers\UserPlanetController::class, 'showPlanet'])->name('admin.users.planet.edit');
            Route::post('/users/{user}/planets/{planet}', [AdminUsers\UserPlanetController::class, 'updatePlanet'])->name('admin.users.planet.update');
            Route::get('/users/{user}/planets/{planet}/buildings', [AdminUsers\UserPlanetController::class, 'showPlanetBuildings'])->name('admin.users.planet.buildings');
            Route::post('/users/{user}/planets/{planet}/buildings', [AdminUsers\UserPlanetController::class, 'updatePlanetBuildings'])->name('admin.users.planet.buildings.update');
            Route::get('/users/{user}/planets/{planet}/ships', [AdminUsers\UserPlanetController::class, 'showPlanetShips'])->name('admin.users.planet.ships');
            Route::post('/users/{user}/planets/{planet}/ships', [AdminUsers\UserPlanetController::class, 'updatePlanetShips'])->name('admin.users.planet.ships.update');
            Route::get('/users/{user}/planets/{planet}/defenses', [AdminUsers\UserPlanetController::class, 'showPlanetDefenses'])->name('admin.users.planet.defenses');
            Route::post('/users/{user}/planets/{planet}/defenses', [AdminUsers\UserPlanetController::class, 'updatePlanetDefenses'])->name('admin.users.planet.defenses.update');
            Route::post('/users/{user}/planets/{planet}/soft-delete', [AdminUsers\UserPlanetController::class, 'softDeletePlanet'])->name('admin.users.planet.soft-delete');
            Route::delete('/users/{user}/planets/{planet}', [AdminUsers\UserPlanetController::class, 'hardDeletePlanet'])->name('admin.users.planet.destroy');
            // Users — moons
            Route::get('/users/{user}/moons', [AdminUsers\UserMoonController::class, 'showMoons'])->name('admin.users.moons');
            Route::get('/users/{user}/moons/create', [AdminUsers\UserMoonController::class, 'createMoon'])->name('admin.users.moon.create');
            Route::post('/users/{user}/moons', [AdminUsers\UserMoonController::class, 'storeMoon'])->name('admin.users.moon.store');
            Route::get('/users/{user}/moons/{moon}', [AdminUsers\UserMoonController::class, 'showMoon'])->name('admin.users.moon.edit');
            Route::post('/users/{user}/moons/{moon}', [AdminUsers\UserMoonController::class, 'updateMoon'])->name('admin.users.moon.update');
            Route::get('/users/{user}/moons/{moon}/buildings', [AdminUsers\UserMoonController::class, 'showMoonBuildings'])->name('admin.users.moon.buildings');
            Route::post('/users/{user}/moons/{moon}/buildings', [AdminUsers\UserMoonController::class, 'updateMoonBuildings'])->name('admin.users.moon.buildings.update');
            Route::get('/users/{user}/moons/{moon}/ships', [AdminUsers\UserMoonController::class, 'showMoonShips'])->name('admin.users.moon.ships');
            Route::post('/users/{user}/moons/{moon}/ships', [AdminUsers\UserMoonController::class, 'updateMoonShips'])->name('admin.users.moon.ships.update');
            Route::get('/users/{user}/moons/{moon}/defenses', [AdminUsers\UserMoonController::class, 'showMoonDefenses'])->name('admin.users.moon.defenses');
            Route::post('/users/{user}/moons/{moon}/defenses', [AdminUsers\UserMoonController::class, 'updateMoonDefenses'])->name('admin.users.moon.defenses.update');
            Route::post('/users/{user}/moons/{moon}/soft-delete', [AdminUsers\UserMoonController::class, 'softDeleteMoon'])->name('admin.users.moon.soft-delete');
            Route::delete('/users/{user}/moons/{moon}', [AdminUsers\UserMoonController::class, 'hardDeleteMoon'])->name('admin.users.moon.destroy');
        });
});
