<?php

namespace App\Core;

use Container\DatabaseContainer;
use Exception;
use Kernel;
use PDO;

class CronJob
{
    /**
     * @throws \Exception
     */
    public static function execute()
    {
        $database = DatabaseContainer::getDatabase();

        $getCronJobs = $database->prepare('SELECT * FROM `app_cronjob` WHERE `enabled`=\'1\' ORDER BY `priority` ASC');
        $sqlSuccess = $getCronJobs->execute();

        if ($sqlSuccess) {
            $cronJobList = $getCronJobs->fetchAll(PDO::FETCH_ASSOC);

            foreach ($cronJobList as $key => $value) {
                if (self::getNextExec($value['id']) <= time()) {
                    self::runJob($value['id']);
                }
            }
        }
    }

    /**
     * @param int $job_id
     *
     * @return int
     * @throws \Exception
     */
    public static function getNextExec($job_id)
    {
        $database = DatabaseContainer::getDatabase();

        $getCronJobInfo = $database->prepare('SELECT `last_exec`,`exec_every` FROM `app_cronjob` WHERE `id`=:job_id LIMIT 1');
        $getCronJobInfo->bindValue(':job_id', $job_id, PDO::PARAM_INT);
        $sqlSuccessful = $getCronJobInfo->execute();

        if ($sqlSuccessful) {
            if ($getCronJobInfo->rowCount() > 0) {
                $jobInfo = $getCronJobInfo->fetchAll(PDO::FETCH_ASSOC);
                return $jobInfo[0]['last_exec'] + $jobInfo[0]['exec_every'];
            }
        }
        return -1;
    }

    /**
     * @param int $job_id
     *
     * @throws \Exception
     */
    public static function runJob($job_id)
    {
        $database = DatabaseContainer::getDatabase();

        $getCronJobInfo = $database->prepare('SELECT `scriptfile` FROM `app_cronjob` WHERE `id`=:job_id LIMIT 1');
        $getCronJobInfo->bindValue(':job_id', $job_id, PDO::PARAM_INT);
        $sqlSuccessful = $getCronJobInfo->execute();

        if ($sqlSuccessful) {
            $aJobInfo = $getCronJobInfo->fetchAll(PDO::FETCH_ASSOC);
            $sFileDir = Kernel::getIntent()->getRootDir() . '/src/App/Core/cron_job/' . $aJobInfo[0]['scriptfile'];

            if (file_exists($sFileDir)) {
                include $sFileDir;

                $updateCronJob = $database->prepare('UPDATE `app_cronjob` SET `last_exec`=:time WHERE `id`=:job_id LIMIT 1');
                $updateCronJob->bindValue(':job_id', $job_id, PDO::PARAM_INT);
                $updateCronJob->bindValue(':time', time(), PDO::PARAM_INT);
                $updateCronJob->execute();
            } else {
                throw new Exception('[CronJob][Fatal Error]: ' . 'Could not execute cron job. Could not locate script file ("' . $sFileDir . '")');
            }
        }
    }
}
