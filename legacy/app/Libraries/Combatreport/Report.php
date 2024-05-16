<?php

declare(strict_types=1);

namespace Xgp\App\Libraries\Combatreport;

use Xgp\App\Core\Entity\ReportEntity;
use Xgp\App\Core\Enumerators\ReportStatusEnumerator as ReportStatus;

class Report
{
    private array $_reports = [];
    private int $_current_user_id = 0;

    public function __construct($reports, $current_user_id)
    {
        if (is_array($reports)) {
            $this->setUp($reports);
            $this->setUserId($current_user_id);
        }
    }

    /**
     * Get all the reports provided by the query result
     *
     * @return array
     */
    public function getAllReports()
    {
        $list_of_reports = [];

        foreach ($this->_reports as $report) {
            if ($report instanceof ReportEntity) {
                $list_of_reports[] = $report;
            }
        }

        return $list_of_reports;
    }

    /**
     * Get all the reports provided by the query result, filtered by current user
     *
     * @return array
     */
    public function getAllReportsOwnedByUserId()
    {
        $list_of_reports = [];

        foreach ($this->_reports as $report) {
            if (($report instanceof ReportEntity) && $this->isOwnRequest($report)) {
                $list_of_reports[] = $report;
            }
        }

        return $list_of_reports;
    }

    /**
     * Get all the reports provided by the query result, that are destroyed
     *
     * @return array
     */
    public function getAllDestroyedReports()
    {
        $list_of_reports = [];

        foreach ($this->_reports as $report) {
            if (($report instanceof ReportEntity) && $this->isDestroyedReport($report)) {
                $list_of_reports[] = $report;
            }
        }

        return $list_of_reports;
    }

    /**
     * Get first report owners as an array
     *
     * @return array
     */
    public function getFirstReportOwnersAsArray(): array
    {
        $owners = [];

        foreach ($this->_reports as $report) {
            if (($report instanceof ReportEntity)) {
                $owners[] = $this->getReportOwnersAsArray($report);
                break;
            }
        }

        return $owners[0] ?? $owners;
    }

    public function getReportOwnersAsArrayByReportId($report_id): array
    {
        $owners = [];

        foreach ($this->_reports as $report) {
            if (($report instanceof ReportEntity) && ($report->getReportId() == $report_id)) {
                $owners[] = $this->getReportOwnersAsArray($report);
                break;
            }
        }

        return $owners;
    }

    /**
     * Get report owners as an array
     *
     * @param ReportEntity $report
     *
     * @return array
     */
    private function getReportOwnersAsArray(ReportEntity $report)
    {
        return explode(',', $report->getReportOwners());
    }

    /**
     * Check if a report is destroyed
     *
     * @param ReportEntity $report Report
     *
     * @return boolean
     */
    private function isDestroyedReport(ReportEntity $report)
    {
        return ($report->getReportDestroyed() == ReportStatus::fleetDestroyed);
    }

    /**
     * Check if is the report owner
     *
     * @param ReportEntity $report Report
     *
     * @return boolean
     */
    private function isOwnRequest(ReportEntity $report)
    {
        return (in_array($this->getUserId(), $this->getReportOwnersAsArray($report)));
    }

    /**
     * Set up the list of reports
     *
     * @param array $reports Reports
     *
     * @return void
     */
    private function setUp($reports)
    {
        foreach ($reports as $report) {
            if (is_array($report)) {
                $this->_reports[] = $this->createNewReportEntity($report);
            }
        }
    }

    private function setUserId(int $userId): void
    {
        $this->_current_user_id = $userId;
    }

    private function getUserId(): int
    {
        return $this->_current_user_id;
    }

    private function createNewReportEntity(array $report): ReportEntity
    {
        return new ReportEntity($report);
    }
}
