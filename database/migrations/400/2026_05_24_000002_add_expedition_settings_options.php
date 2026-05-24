<?php

declare(strict_types=1);

use App\Models\Options;
use Illuminate\Database\Migrations\Migration;
use Xgp\App\Core\Enumerators\UserRanksEnumerator as UserRanks;

return new class () extends Migration {
    /** @var array<string, string> */
    private array $options = [
        'expedition_result_dark_matter_weight' => '900',
        'expedition_result_ships_weight' => '2200',
        'expedition_result_resources_weight' => '3250',
        'expedition_result_pirates_weight' => '560',
        'expedition_result_aliens_weight' => '260',
        'expedition_result_delay_weight' => '700',
        'expedition_result_early_weight' => '200',
        'expedition_result_nothing_weight' => '1880',
        'expedition_result_merchant_weight' => '17',
        'expedition_result_black_hole_weight' => '33',
        'expedition_dark_matter_source_small_weight' => '8900',
        'expedition_dark_matter_source_medium_weight' => '1000',
        'expedition_dark_matter_source_large_weight' => '100',
        'expedition_resource_type_metal_weight' => '6850',
        'expedition_resource_type_crystal_weight' => '2400',
        'expedition_resource_type_deuterium_weight' => '750',
        'expedition_resource_source_normal_weight' => '8900',
        'expedition_resource_source_large_weight' => '1000',
        'expedition_resource_source_xl_weight' => '100',
        'expedition_fleet_delay_2_weight' => '8900',
        'expedition_fleet_delay_3_weight' => '1000',
        'expedition_fleet_delay_5_weight' => '100',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->options as $name => $value) {
            Options::updateOrCreate(
                ['name' => $name],
                ['value' => $value, 'type' => 'int']
            );
        }

        $this->addPermission();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Options::whereIn('name', array_keys($this->options))->delete();
        $this->removePermission();
    }

    private function addPermission(): void
    {
        $option = Options::where('name', 'admin_permissions')->first();

        if ($option === null) {
            return;
        }

        $permissions = json_decode($option->value, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($permissions)) {
            return;
        }

        $permissions['expeditions'] = [UserRanks::GO => 0, UserRanks::SGO => 0, UserRanks::ADMIN => 1];
        $option->value = json_encode($permissions, JSON_THROW_ON_ERROR);
        $option->save();
    }

    private function removePermission(): void
    {
        $option = Options::where('name', 'admin_permissions')->first();

        if ($option === null) {
            return;
        }

        $permissions = json_decode($option->value, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($permissions) || !isset($permissions['expeditions'])) {
            return;
        }

        unset($permissions['expeditions']);
        $option->value = json_encode($permissions, JSON_THROW_ON_ERROR);
        $option->save();
    }
};
