<?php

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

// включить учет поддоменов
foreach ($projects as $project) {
	$subdomainsOnPen = new TV\Pen($TVSession, 'edit', 'positions_2', 'settings');
	$subdomainsOnPen->setData([
		'project_id' => $project->id,
		'subdomains' => 1,
	]);

	$subdomainsOnPenResult = $subdomainsOnPen->exec();

	$result = $subdomainsOnPenResult->getResult();
	if (is_null($result)) vd($subdomainsOnPenResult->getErrors(), 1);

	echo "Проект: $project->id, учет поддоменов включили: $result<br>";
}
