<?php

namespace App\Core;

use PDO;

class CronJob
{
    /**
     * @throws \Exception
     */
    public static function execute()
    {
        /** @var \PDO $database */
        $database = DatabaseConnection::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $oGetCronJobs = $database->prepare('SELECT * FROM `app_cronjob` WHERE `enabled`="1" ORDER BY `priority` ASC');
        if ($oGetCronJobs->execute()) {
            foreach ($oGetCronJobs->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {
                if (self::getNextExec($value['id']) <= time()) {
                    self::runJob($value['id']);
                }
            }
        }
    }

    /**
     * @param $job_id
     *
     * @return int
     * @throws \Exception
     */
    public static function getNextExec($job_id)
    {
        /** @var \PDO $database */
        $database = DatabaseConnection::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $oGetCronJobInfo = $database->prepare('SELECT `last_exec`,`exec_every` FROM `app_cronjob` WHERE `id`=:job_id LIMIT 1');
        $oGetCronJobInfoQuerySuccessful = $oGetCronJobInfo->execute(array(
            ':job_id' => $job_id,
        ));
        if ($oGetCronJobInfoQuerySuccessful) {
            if ($oGetCronJobInfo->rowCount() > 0) {
                $aJobInfo = $oGetCronJobInfo->fetchAll(PDO::FETCH_ASSOC);
                return $aJobInfo[0]['last_exec'] + $aJobInfo[0]['exec_every'];
            }
        }
        return -1;
    }

    /**
     * @param $job_id
     *
     * @throws \Exception
     */
    public static function runJob($job_id)
    {
        /** @var \PDO $database */
        $database = DatabaseConnection::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $oGetCronJobInfo = $database->prepare('SELECT `scriptfile` FROM `app_cronjob` WHERE `id`=:job_id LIMIT 1');
        $oGetCronJobInfoQuerySuccessful = $oGetCronJobInfo->execute(array(
            ':job_id' => $job_id,
        ));
        if ($oGetCronJobInfoQuerySuccessful) {
            $aJobInfo = $oGetCronJobInfo->fetchAll(PDO::FETCH_ASSOC);
            $sFileDir = \Kernel::$rootDir2 . '/src/App/Core/cron_job/' . $aJobInfo[0]['scriptfile'];

            if (file_exists($sFileDir)) {
                include $sFileDir;

                $oUpdateCronJob = $database->prepare('UPDATE `app_cronjob` SET `last_exec`=:time WHERE `id`=:job_id LIMIT 1');
                $oUpdateCronJob->execute(array(
                    ':time'   => time(),
                    ':job_id' => $job_id,
                ));
            } else {
                throw new \Exception('[CronJob][Fatal Error]: ' . 'Could not execute cron job. Could not locate script file ("' . $sFileDir . '")');
            }
        }
    }
}
