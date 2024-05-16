<?php

declare(strict_types=1);

namespace Xgp\App\Libraries\Alliance;

use Exception;
use JsonException;
use Xgp\App\Core\Enumerators\AllianceRanksEnumerator as AllianceRanks;
use Xgp\App\Core\Enumerators\SwitchIntEnumerator as SwitchInt;
use Xgp\App\Helpers\StringsHelper;

class Ranks
{
    private array $_ranks = [];

    public function __construct(string|array $alliance_ranks)
    {
        try {
            if (is_array($alliance_ranks)) {
                throw new Exception('JSON Expected!');
            }

            $this->setRanks($alliance_ranks);
        } catch (Exception $e) {
            die('Caught exception: ' . $e->getMessage() . "\n");
        }
    }

    private function setRanks(string $ranks): void
    {
        try {
            if (!empty($ranks)) {
                $this->_ranks = json_decode($ranks, true, 512, JSON_THROW_ON_ERROR);
            }
        } catch (JsonException $e) {
            die('JSON Error - ' . $e->getMessage() . ' on ' . __CLASS__ . ', line: ' . $e->getLine());
        }
    }

    private function getRanks(): array
    {
        return $this->_ranks;
    }

    public function addNew(string $name): array
    {
        try {
            if (empty($name) or is_null($name)) {
                throw new Exception('Name cannot be empty or null');
            }

            $filtered_name = StringsHelper::escapeString(strip_tags($name));

            $this->_ranks[] = [
                'rank' => $filtered_name,
                'rights' => [
                    AllianceRanks::DELETE => SwitchInt::off,
                    AllianceRanks::KICK => SwitchInt::off,
                    AllianceRanks::APPLICATIONS => SwitchInt::off,
                    AllianceRanks::VIEW_MEMBER_LIST => SwitchInt::off,
                    AllianceRanks::APPLICATION_MANAGEMENT => SwitchInt::off,
                    AllianceRanks::ADMINISTRATION => SwitchInt::off,
                    AllianceRanks::ONLINE_STATUS => SwitchInt::off,
                    AllianceRanks::SEND_CIRCULAR => SwitchInt::off,
                    AllianceRanks::RIGHT_HAND => SwitchInt::off,
                ],
            ];

            return $this->getRanks();
        } catch (Exception $e) {
            die('Caught exception: ' . $e->getMessage() . "\n");
        }
    }

    public function editRankById(int $rank_id, array $rights)
    {
        try {
            if (!isset($this->getRanks()[$this->validateRankId($rank_id)])) {
                throw new Exception('Rank ID doesn\'t exists');
            }

            if (!is_array($rights) or count($rights) != 9) {
                throw new Exception('Array of rights is invalid, not an array or not 9 elements');
            }

            $this->_ranks[$rank_id]['rights'] = $rights;

            return $this->getRanks();
        } catch (Exception $e) {
            die('Caught exception: ' . $e->getMessage() . "\n");
        }
    }

    public function editRankNameById(int $rank_id, string $name): array
    {
        try {
            if (!isset($this->getRanks()[$this->validateRankId($rank_id)])) {
                throw new Exception('Rank ID doesn\'t exists');
            }

            if (!is_string($name)) {
                throw new Exception('Rank name is not a string.');
            }

            $this->_ranks[$rank_id]['rank'] = $name;

            return $this->getRanks();
        } catch (Exception $e) {
            die('Caught exception: ' . $e->getMessage() . "\n");
        }
    }

    public function deleteRankById(int $rank_id): array
    {
        array_splice($this->_ranks, $this->validateRankId($rank_id), 1);

        return $this->getRanks();
    }

    public function getAllRanksAsArray(): array
    {
        return $this->_ranks;
    }

    public function getAllRanksAsJsonString(): string
    {
        try {
            return json_encode($this->_ranks, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            die('JSON Error - ' . $e->getMessage() . ' on ' . __CLASS__ . ', line: ' . $e->getLine());
        }
    }

    /**
     * Get the permission for a certain rank
     *
     * @param int $rank_id Rank ID
     *
     * @return array
     */
    public function getRankById($rank_id)
    {
        return isset($this->_ranks[$rank_id]) ? $this->_ranks[$rank_id] : $this->_ranks[1];
    }

    private function validateRankId(int $rank_id): int
    {
        if ($rank_id < 0) {
            return 0;
        }

        if ($rank_id > count($this->_ranks)) {
            return count($this->_ranks) - 1;
        }

        return $rank_id;
    }
}
