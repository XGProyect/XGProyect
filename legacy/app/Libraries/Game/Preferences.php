<?php

declare(strict_types=1);

namespace Xgp\App\Libraries\Game;

use Xgp\App\Core\Entity\PreferencesEntity;

class Preferences
{
    private array $preferences = [];
    private int $current_user_id = 0;

    public function __construct(array $preferences, int $current_user_id)
    {
        $this->setUp($preferences);
        $this->setUserId($current_user_id);
    }

    /**
     * Get all the preferences
     *
     * @return array
     */
    public function getPreferences(): array
    {
        $list_of_preferences = [];

        foreach ($this->preferences as $preference) {
            if (($preference instanceof PreferencesEntity)) {
                $list_of_preferences[] = $preference;
            }
        }

        return $list_of_preferences;
    }

    public function getCurrentPreference(): PreferencesEntity
    {
        return $this->preferences[0];
    }

    public function isOwner(): bool
    {
        return ($this->getCurrentPreference()->getPreferenceUsedId() === $this->getUserId());
    }

    public function isNickNameChangeAllowed(): bool
    {
        return (($this->getCurrentPreference()->getPreferenceNicknameChange() + ONE_WEEK) < time());
    }

    public function isVacationModeOn(): bool
    {
        return ($this->getCurrentPreference()->getPreferenceVacationMode() > 0);
    }

    public function isVacationModeRemovalAllowed(): bool
    {
        return (($this->getCurrentPreference()->getPreferenceVacationMode() + ONE_DAY * 2) < time());
    }

    private function setUp(array $preferences): void
    {
        foreach ($preferences as $preference) {
            $this->preferences[] = $this->createNewPreferencesEntity($preference);
        }
    }

    private function setUserId(int $userId): void
    {
        $this->current_user_id = $userId;
    }

    private function getUserId(): int
    {
        return $this->current_user_id;
    }

    private function createNewPreferencesEntity(array $preference): PreferencesEntity
    {
        return new PreferencesEntity($preference);
    }
}
