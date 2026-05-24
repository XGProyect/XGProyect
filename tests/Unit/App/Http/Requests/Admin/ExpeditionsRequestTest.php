<?php

declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests\Admin;

use App\Http\Requests\Admin\ExpeditionsRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;
use Tests\TestCase;

class ExpeditionsRequestTest extends TestCase
{
    public function testValidPercentagesPassValidation(): void
    {
        $validator = $this->validator($this->validPayload());

        $this->assertTrue($validator->passes());
    }

    public function testPercentagesConvertToBasisPointSettings(): void
    {
        $request = ExpeditionsRequest::create('/admin/expeditions', 'POST', $this->validPayload());
        $settings = $request->toSettings();

        $this->assertSame(900, $settings['expedition_result_dark_matter_weight']);
        $this->assertSame(3250, $settings['expedition_result_resources_weight']);
        $this->assertSame(17, $settings['expedition_result_merchant_weight']);
        $this->assertSame(33, $settings['expedition_result_black_hole_weight']);
        $this->assertSame(100, $settings['expedition_resource_source_xl_weight']);
    }

    public function testValidationFailsWhenGroupTotalExceedsOneHundredPercent(): void
    {
        $payload = $this->validPayload();
        $payload['expedition_result_nothing_weight'] = '18.81';

        $validator = $this->validator($payload);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('expedition_result_dark_matter_weight'));
    }

    public function testValidationFailsWhenPercentageHasMoreThanTwoDecimals(): void
    {
        $payload = $this->validPayload();
        $payload['expedition_result_merchant_weight'] = '0.171';

        $validator = $this->validator($payload);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('expedition_result_merchant_weight'));
    }

    /**
     * @param array<string, string> $payload
     */
    private function validator(array $payload): ValidationValidator
    {
        $request = ExpeditionsRequest::create('/admin/expeditions', 'POST', $payload);
        $validator = Validator::make($payload, $request->rules());

        foreach ($request->after() as $callback) {
            $validator->after($callback);
        }

        return $validator;
    }

    /**
     * @return array<string, string>
     */
    private function validPayload(): array
    {
        return [
            'expedition_result_dark_matter_weight' => '9',
            'expedition_result_ships_weight' => '22',
            'expedition_result_resources_weight' => '32.5',
            'expedition_result_pirates_weight' => '5.6',
            'expedition_result_aliens_weight' => '2.6',
            'expedition_result_delay_weight' => '7',
            'expedition_result_early_weight' => '2',
            'expedition_result_nothing_weight' => '18.8',
            'expedition_result_merchant_weight' => '0.17',
            'expedition_result_black_hole_weight' => '0.33',
            'expedition_dark_matter_source_small_weight' => '89',
            'expedition_dark_matter_source_medium_weight' => '10',
            'expedition_dark_matter_source_large_weight' => '1',
            'expedition_resource_type_metal_weight' => '68.5',
            'expedition_resource_type_crystal_weight' => '24',
            'expedition_resource_type_deuterium_weight' => '7.5',
            'expedition_resource_source_normal_weight' => '89',
            'expedition_resource_source_large_weight' => '10',
            'expedition_resource_source_xl_weight' => '1',
            'expedition_fleet_delay_2_weight' => '89',
            'expedition_fleet_delay_3_weight' => '10',
            'expedition_fleet_delay_5_weight' => '1',
        ];
    }
}
