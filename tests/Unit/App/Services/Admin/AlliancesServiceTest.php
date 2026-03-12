<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Admin;

use App\Services\Admin\AlliancesService;
use PHPUnit\Framework\TestCase;
use Xgp\App\Core\Enumerators\AllianceRanksEnumerator as AllianceRanks;
use Xgp\App\Core\Enumerators\SwitchIntEnumerator as SwitchInt;

class AlliancesServiceTest extends TestCase
{
    private AlliancesService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AlliancesService();
    }

    public function testBuildRanksViewDataMapsIndexToI(): void
    {
        $data = $this->service->buildRanksViewData([$this->founderRank()]);

        $this->assertSame(0, $data[0]['i']);
    }

    public function testBuildRanksViewDataMapsRankName(): void
    {
        $data = $this->service->buildRanksViewData([$this->founderRank()]);

        $this->assertSame('Founder', $data[0]['name']);
    }

    public function testBuildRanksViewDataCastsRightsToBoolean(): void
    {
        $rank = $this->rankWithRights([
            AllianceRanks::DELETE => SwitchInt::on,
            AllianceRanks::KICK => SwitchInt::off,
            AllianceRanks::APPLICATIONS => SwitchInt::on,
            AllianceRanks::VIEW_MEMBER_LIST => SwitchInt::off,
            AllianceRanks::APPLICATION_MANAGEMENT => SwitchInt::on,
            AllianceRanks::ADMINISTRATION => SwitchInt::off,
            AllianceRanks::ONLINE_STATUS => SwitchInt::on,
            AllianceRanks::SEND_CIRCULAR => SwitchInt::off,
            AllianceRanks::RIGHT_HAND => SwitchInt::on,
        ]);

        $data = $this->service->buildRanksViewData([$rank]);

        $this->assertTrue($data[0]['delete']);
        $this->assertFalse($data[0]['kick']);
        $this->assertTrue($data[0]['applications']);
        $this->assertFalse($data[0]['memberlist']);
        $this->assertTrue($data[0]['app_management']);
        $this->assertFalse($data[0]['administration']);
        $this->assertTrue($data[0]['online_status']);
        $this->assertFalse($data[0]['send_circular']);
        $this->assertTrue($data[0]['right_hand']);
    }

    public function testBuildRanksViewDataHandlesMultipleRanks(): void
    {
        $ranks = [
            $this->founderRank(),
            ['rank' => 'Newcomer', 'rights' => $this->allOffRights()],
        ];

        $data = $this->service->buildRanksViewData($ranks);

        $this->assertCount(2, $data);
        $this->assertSame(0, $data[0]['i']);
        $this->assertSame(1, $data[1]['i']);
        $this->assertSame('Founder', $data[0]['name']);
        $this->assertSame('Newcomer', $data[1]['name']);
    }

    public function testBuildRanksViewDataReturnsReIndexedArray(): void
    {
        $data = $this->service->buildRanksViewData([$this->founderRank()]);

        $this->assertArrayHasKey(0, $data);
    }

    public function testBuildRankOptionsReturnsIdFromIndex(): void
    {
        $ranks = [
            ['rank' => 'Founder', 'rights' => $this->allOffRights()],
            ['rank' => 'Newcomer', 'rights' => $this->allOffRights()],
        ];

        $options = $this->service->buildRankOptions($ranks);

        $this->assertSame(0, $options[0]['id']);
        $this->assertSame(1, $options[1]['id']);
    }

    public function testBuildRankOptionsReturnsNameFromRank(): void
    {
        $ranks = [
            ['rank' => 'Founder', 'rights' => $this->allOffRights()],
        ];

        $options = $this->service->buildRankOptions($ranks);

        $this->assertSame('Founder', $options[0]['name']);
    }

    public function testBuildRankOptionsReturnsEmptyArrayForNoRanks(): void
    {
        $this->assertSame([], $this->service->buildRankOptions([]));
    }

    public function testBuildRankOptionsCountMatchesInput(): void
    {
        $ranks = array_fill(0, 5, ['rank' => 'Test', 'rights' => $this->allOffRights()]);

        $this->assertCount(5, $this->service->buildRankOptions($ranks));
    }

    /**
     * @return array<string, mixed>
     */
    private function founderRank(): array
    {
        return [
            'rank' => 'Founder',
            'rights' => [
                AllianceRanks::DELETE => SwitchInt::on,
                AllianceRanks::KICK => SwitchInt::on,
                AllianceRanks::APPLICATIONS => SwitchInt::on,
                AllianceRanks::VIEW_MEMBER_LIST => SwitchInt::on,
                AllianceRanks::APPLICATION_MANAGEMENT => SwitchInt::on,
                AllianceRanks::ADMINISTRATION => SwitchInt::on,
                AllianceRanks::ONLINE_STATUS => SwitchInt::on,
                AllianceRanks::SEND_CIRCULAR => SwitchInt::on,
                AllianceRanks::RIGHT_HAND => SwitchInt::on,
            ],
        ];
    }

    /**
     * @param array<int, int> $rights
     *
     * @return array<string, mixed>
     */
    private function rankWithRights(array $rights): array
    {
        return ['rank' => 'TestRank', 'rights' => $rights];
    }

    /**
     * @return array<int, int>
     */
    private function allOffRights(): array
    {
        return [
            AllianceRanks::DELETE => SwitchInt::off,
            AllianceRanks::KICK => SwitchInt::off,
            AllianceRanks::APPLICATIONS => SwitchInt::off,
            AllianceRanks::VIEW_MEMBER_LIST => SwitchInt::off,
            AllianceRanks::APPLICATION_MANAGEMENT => SwitchInt::off,
            AllianceRanks::ADMINISTRATION => SwitchInt::off,
            AllianceRanks::ONLINE_STATUS => SwitchInt::off,
            AllianceRanks::SEND_CIRCULAR => SwitchInt::off,
            AllianceRanks::RIGHT_HAND => SwitchInt::off,
        ];
    }
}
