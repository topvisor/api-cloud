<?php

// отключить группы во всех проектах по фильтру

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
if (is_null($projects)) {
	vd($projectsPenResult->getErrors());

	exit();
}

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

// отключить и включить группы во всех проектах по фильтру
foreach ($projects as $project) {
	// отключаем группы
	$groupsOnPen = new TV\Pen($TVSession, 'edit', 'keywords_2', 'groups/on');
	$groupsOnPen->setData([
		'project_id' => $project->id,
		'on' => 0,
	]);
	$groupsOnPen->setFilters([
		TV\Fields::genFilterData('name', 'NOT_EQUALS', ['Регион + дилер']),
		TV\Fields::genFilterData('on', 'EQUALS', [1]),
	]);

	$groupsOnPenResult = $groupsOnPen->exec();

	$result = $groupsOnPenResult->getResult();
	if (is_null($result)) {
		vd($groupsOnPenResult->getErrors());

		exit();
	}

	echo "Проект: $project->id, отключено групп: $result<br>";

	// включаем группы
	$groupsOnPen->setData([
		'project_id' => $project->id,
		'on' => 1,
	]);
	$groupsOnPen->setFilters([
		TV\Fields::genFilterData('name', 'EQUALS', ['Регион + дилер']),
		TV\Fields::genFilterData('on', 'EQUALS', [0]),
	]);

	$groupsOnPenResult = $groupsOnPen->exec();

	$result = $groupsOnPenResult->getResult();
	if (is_null($result)) {
		vd($groupsOnPenResult->getErrors());

		exit();
	}

	echo "Проект: $project->id, включено групп: $result<br>";
}
