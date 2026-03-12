<?php

declare(strict_types=1);

use App\Models\Options;
use Illuminate\Database\Migrations\Migration;
use Xgp\App\Core\Enumerators\UserRanksEnumerator as UserRanks;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $option = Options::where('name', 'admin_permissions')->first();

        if ($option === null) {
            return;
        }

        $permissions = json_decode($option->value, true);

        $expected = [UserRanks::GO => 0, UserRanks::SGO => 0, UserRanks::ADMIN => 1];

        if (!isset($permissions['mailing']) || $permissions['mailing'] !== $expected) {
            $permissions['mailing'] = $expected;
            $option->value = json_encode($permissions);
            $option->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $option = Options::where('name', 'admin_permissions')->first();

        if ($option === null) {
            return;
        }

        $permissions = json_decode($option->value, true);

        if (isset($permissions['mailing'])) {
            unset($permissions['mailing']);
            $option->value = json_encode($permissions);
            $option->save();
        }
    }
};
