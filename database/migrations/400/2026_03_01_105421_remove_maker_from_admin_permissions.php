<?php

declare(strict_types=1);

use App\Models\Options;
use Illuminate\Database\Migrations\Migration;

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

        if (isset($permissions['maker'])) {
            unset($permissions['maker']);
            $option->value = json_encode($permissions);
            $option->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * The maker module has been removed so there is nothing to restore.
     */
    public function down(): void
    {
        //
    }
};
