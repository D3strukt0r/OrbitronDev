<?php

namespace App\Core;

use App\Core\Entity\CronJob as CronJobEntity;

class CronJob
{
    public static function execute()
    {
        $em = \Kernel::getIntent()->getEntityManager();

        $cronJobs = $em->getRepository(CronJobEntity::class)->findBy(
            array('enabled' => true),
            array('priority' => 'ASC')
        );

        if (count($cronJobs)) {
            foreach ($cronJobs as $job) {
                if (self::getNextExec($job) <= time()) {
                    self::runJob($job);
                }
            }
        }
    }

    /**
     * @param \App\Core\Entity\CronJob $job
     *
     * @return integer
     */
    public static function getNextExec(CronJobEntity $job)
    {
        if ($job) {
            $lastExec = $job->getLastExec()->getTimestamp();
            $execEvery = $job->getExecEvery();

            return $lastExec + $execEvery;
        }

        return -1;
    }

    /**
     * @param \App\Core\Entity\CronJob $job
     *
     * @throws \Exception
     */
    public static function runJob(CronJobEntity $job)
    {
        if ($job) {
            $fileDir = \Kernel::getIntent()->getRootDir().'/src/App/Core/cron_job/'.$job->getScriptFile();

            if (file_exists($fileDir)) {
                include $fileDir;

                $em = \Kernel::getIntent()->getEntityManager();
                $job->setLastExec(new \DateTime());
                $em->flush();
            } else {
                throw new \Exception('[CronJob][Fatal Error]: Could not execute cron job. Could not locate script file ("'.$fileDir.'")');
            }
        }
    }
}
