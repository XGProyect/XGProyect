<?php

declare(strict_types=1);

namespace Tests\Unit\App\Core\GameObjects;

use App\Core\GameObjects\DriveSpec;
use App\Enums\Game\DriveType;
use PHPUnit\Framework\TestCase;

class DriveSpecTest extends TestCase
{
    public function testPrimaryOnlyDrive(): void
    {
        $spec = new DriveSpec(primary: DriveType::Combustion);

        $this->assertSame(DriveType::Combustion, $spec->getPrimary());
        $this->assertNull($spec->getSecondary());
        $this->assertNull($spec->getSecondaryMinLevel());
        $this->assertNull($spec->getTertiary());
        $this->assertNull($spec->getTertiaryMinLevel());
    }

    public function testTwoStageDrive(): void
    {
        $spec = new DriveSpec(
            primary: DriveType::Combustion,
            secondary: DriveType::Impulse,
            secondaryMinLevel: 5,
        );

        $this->assertSame(DriveType::Combustion, $spec->getPrimary());
        $this->assertSame(DriveType::Impulse, $spec->getSecondary());
        $this->assertSame(5, $spec->getSecondaryMinLevel());
    }

    public function testThreeStageDrive(): void
    {
        $spec = new DriveSpec(
            primary: DriveType::Combustion,
            secondary: DriveType::Impulse,
            secondaryMinLevel: 5,
            tertiary: DriveType::Hyperspace,
            tertiaryMinLevel: 15,
        );

        $this->assertSame(DriveType::Combustion, $spec->getPrimary());
        $this->assertSame(DriveType::Impulse, $spec->getSecondary());
        $this->assertSame(5, $spec->getSecondaryMinLevel());
        $this->assertSame(DriveType::Hyperspace, $spec->getTertiary());
        $this->assertSame(15, $spec->getTertiaryMinLevel());
    }

    public function testGetActiveDriveReturnsPrimaryWhenNoUpgrades(): void
    {
        $spec = new DriveSpec(primary: DriveType::Combustion);

        $this->assertSame(DriveType::Combustion, $spec->getActiveDrive());
        $this->assertSame(DriveType::Combustion, $spec->getActiveDrive(10, 10));
    }

    public function testGetActiveDriveReturnsPrimaryWhenBelowSecondaryLevel(): void
    {
        $spec = new DriveSpec(
            primary: DriveType::Combustion,
            secondary: DriveType::Impulse,
            secondaryMinLevel: 5,
        );

        $this->assertSame(DriveType::Combustion, $spec->getActiveDrive(4));
    }

    public function testGetActiveDriveReturnsSecondaryAtExactLevel(): void
    {
        $spec = new DriveSpec(
            primary: DriveType::Combustion,
            secondary: DriveType::Impulse,
            secondaryMinLevel: 5,
        );

        $this->assertSame(DriveType::Impulse, $spec->getActiveDrive(5));
    }

    public function testGetActiveDriveReturnsSecondaryAboveLevel(): void
    {
        $spec = new DriveSpec(
            primary: DriveType::Combustion,
            secondary: DriveType::Impulse,
            secondaryMinLevel: 5,
        );

        $this->assertSame(DriveType::Impulse, $spec->getActiveDrive(10));
    }

    public function testGetActiveDriveReturnsTertiaryWhenQualified(): void
    {
        // Recycler: Combustion -> Impulse@5 -> Hyperspace@15
        $spec = new DriveSpec(
            primary: DriveType::Combustion,
            secondary: DriveType::Impulse,
            secondaryMinLevel: 5,
            tertiary: DriveType::Hyperspace,
            tertiaryMinLevel: 15,
        );

        // Below all thresholds
        $this->assertSame(DriveType::Combustion, $spec->getActiveDrive(0, 0));

        // Secondary qualifies, tertiary does not
        $this->assertSame(DriveType::Impulse, $spec->getActiveDrive(5, 0));

        // Tertiary qualifies (takes precedence)
        $this->assertSame(DriveType::Hyperspace, $spec->getActiveDrive(5, 15));

        // Tertiary qualifies even if secondary also does
        $this->assertSame(DriveType::Hyperspace, $spec->getActiveDrive(10, 20));
    }

    public function testUsesUpgradedSpeedReturnsFalseForPrimary(): void
    {
        $spec = new DriveSpec(
            primary: DriveType::Combustion,
            secondary: DriveType::Impulse,
            secondaryMinLevel: 5,
        );

        $this->assertFalse($spec->usesUpgradedSpeed(4));
    }

    public function testUsesUpgradedSpeedReturnsTrueForSecondary(): void
    {
        $spec = new DriveSpec(
            primary: DriveType::Combustion,
            secondary: DriveType::Impulse,
            secondaryMinLevel: 5,
        );

        $this->assertTrue($spec->usesUpgradedSpeed(5));
    }

    public function testUsesUpgradedSpeedReturnsTrueForTertiary(): void
    {
        $spec = new DriveSpec(
            primary: DriveType::Combustion,
            secondary: DriveType::Impulse,
            secondaryMinLevel: 5,
            tertiary: DriveType::Hyperspace,
            tertiaryMinLevel: 15,
        );

        $this->assertTrue($spec->usesUpgradedSpeed(5, 15));
    }

    public function testPrimaryOnlyNeverUsesUpgradedSpeed(): void
    {
        $spec = new DriveSpec(primary: DriveType::Hyperspace);

        $this->assertFalse($spec->usesUpgradedSpeed(99, 99));
    }
}
