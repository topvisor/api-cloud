<?php

use Topvisor\TopvisorSDK\V2 as TV;

include(__DIR__ . '/../common.php');

$userId = 'Ваш ID';
$accessToken = 'Ваш ключ';

$TVSession = new TV\Session(['userId' => $userId, 'accessToken' => $accessToken]);

$projectsPen = new TV\Pen($TVSession, 'get', 'projects_2', 'projects');
$projectsPen->setFields(['id']);
$projectsPen->setFilters([TV\Fields::genFilterData('on', 'GREATER_THAN_EQUALS', [0])]);

$projectsPenResult = $projectsPen->exec();
if (is_null($projectsPenResult->getResult())) vd($projectsPenResult->getErrors());
$projects = $projectsPenResult->getResult();
