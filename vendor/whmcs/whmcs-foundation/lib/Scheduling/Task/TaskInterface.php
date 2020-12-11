<?php 
namespace WHMCS\Scheduling\Task;


interface TaskInterface
{
    const ACCESS_DEBUG = 256;
    const ACCESS_SYSTEM = 128;
    const ACCESS_HOOK = 64;
    const ACCESS_USER = 32;

    public function getName();

    public function setName($name);

    public function run();

    public function getOutputKeys();

    public function getLatestOutputs(array $outputKeys);

    public function getOutputsSince(\Carbon\Carbon $since, array $outputKeys);

    public function getPriority();

    public function setPriority($priority);

    public function getDescription();

    public function setDescription($description);

    public function getFrequencyMinutes();

    public function setFrequencyMinutes($minutes);

    public function anticipatedNextRun(\Carbon\Carbon $date);

    public function isEnabled();

    public function setEnabled($state);

    public function isPeriodic();

    public function setPeriodic($state);

    public function getStatus();

    public function getSystemName();

    public function getAccessLevel();

    public static function all();

    public static function register();

    public function output($key);

    public function isDailyTask();

    public function monthlyDayOfExecution();

}


