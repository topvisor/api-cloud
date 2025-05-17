<?php

// запускает проверку частоты во всех личных проектах пользователей по всем добавленным регионам и ПС

use Topvisor\TopvisorSDK\V2 as TV;

include(__DIR__ . '/../common.php');

$userId = 'Ваш ID';
$accessToken = 'Ваш ключ';

$TVSession = new TV\Session(['userId' => $userId, 'accessToken' => $accessToken]);

$projectsFiltersMyOwn = [TV\Fields::genFilterData('user_id', 'EQUALS', [$userId])]; // фильтры для своих проектов

$projectsPen = new TV\Pen($TVSession, 'get', 'projects_2', 'projects');

$projectsPen->setFields(['id']);
$projectsPen->setFilters($projectsFiltersMyOwn);

$projectsPenResult = $projectsPen->exec();
$projects = $projectsPenResult->getResult();

foreach ($projects as $project) {
	$checkWatcherPen = new TV\Pen($TVSession, 'edit', 'audit_2', 'watcher/checker/go');
	$checkWatcherPen->setData([
		'id' => $project->id,
	]);
	$checkWatcherPenResult = $checkWatcherPen->exec();
	if (is_null($checkWatcherPenResult->getResult())) vd($checkWatcherPenResult->getErrors(), 1);

	$result = $checkWatcherPenResult->getResult();
	vd($result);
}
