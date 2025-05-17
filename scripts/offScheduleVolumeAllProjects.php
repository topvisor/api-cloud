<?php

// отключить автоматическую проверку позиций в проектах пользователя

use Topvisor\TopvisorSDK\V2 as TV;

include(__DIR__ . '/../common.php');

$userId = 'Ваш ID';
$accessToken = 'Ваш ключ';

$checkGuestProjectsWithRights = true; // флаг для проверки гостевых

$TVSession = new TV\Session(['userId' => $userId, 'accessToken' => $accessToken]);

$projectsPen = new TV\Pen($TVSession, 'get', 'projects_2', 'projects');
$projectsPen->setFields(['id']);

// получить свои проекты
$projectsFiltersPersonal = [TV\Fields::genFilterData('user_id', 'EQUALS', [$userId])];
$projectsPen->setFilters($projectsFiltersPersonal);
$projectsPenResult = $projectsPen->exec();
$projects = $projectsPenResult->getResult();

// добавить к полученным проектам гостевые проекты c правом редактирования
if ($checkGuestProjectsWithRights) {
	$projectsFiltersGuest = [
		TV\Fields::genFilterData('user_id', 'NOT_EQUALS', [$userId]),
		TV\Fields::genFilterData('right', 'REGEXP', ['^011']),
	];
	$projectsPen->setFilters($projectsFiltersGuest);
	$projectsPenResult = $projectsPen->exec();
	$projectsGuest = $projectsPenResult->getResult();

	$projects = array_merge($projects, $projectsGuest);
}

// отключить автоматическую проверку частоты в проектах
foreach ($projects as $project) {
	$changeSchedulePen = new TV\Pen($TVSession, 'del', 'schedule_2');
	$changeSchedulePen->setData([
		'target_id' => $project->id,
		'type' => 'volumes_go',
	]);
	$changeSchedulePenResult = $changeSchedulePen->exec();

	$result = $changeSchedulePenResult->getResult();
	if (is_null($result)) vd($changeSchedulePenResult->getErrors(), 1);

	echo "Проект: $project->id: $result<br>";
}
