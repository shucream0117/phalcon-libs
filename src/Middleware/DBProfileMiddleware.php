<?php

declare(strict_types=1);

namespace Shucream0117\PhalconLib\Middleware;

use Phalcon\Db\Adapter\AdapterInterface;
use Phalcon\Db\Profiler;
use Phalcon\Events\Event;
use Phalcon\Mvc\Micro;
use Shucream0117\PhalconLib\Utils\Json;

class DBProfileMiddleware implements Micro\MiddlewareInterface
{
    private string $outputFile;
    private Profiler $profiler;

    public function __construct(string $outputFile, ?Profiler $profiler = null)
    {
        if (!$profiler) {
            $profiler = new Profiler();
        }
        $this->outputFile = $outputFile;
        $this->profiler = $profiler;
    }

    public function call(Micro $application)
    {
        return true;
    }

    public function beforeQuery(Event $event, AdapterInterface $connection)
    {
        $sql = $connection->getSQLStatement();
        $this->profiler->startProfile($sql);
    }

    public function afterQuery(Event $event, AdapterInterface $connection)
    {
        $this->profiler->stopProfile();

        $profile = $this->profiler->getLastProfile();
        $paramsStr = Json::encode($connection->getSqlVariables() ?: []);
        $profileStr = <<<PROFILE
SQL Statement: {$connection->getSQLStatement()}
SQL VARIABLES: {$paramsStr}
Start Time: {$profile->getInitialTime()}
Final Time: {$profile->getFinalTime()}
Total Elapsed Time: {$profile->getTotalElapsedSeconds()}
=====================================
PROFILE;

        file_put_contents($this->outputFile, $profileStr . PHP_EOL, FILE_APPEND);
    }
}
