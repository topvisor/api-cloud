<?php

// добавить регион во все проекты

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
	$addLocationPen = new TV\Pen($TVSession, 'add', 'positions_2', 'searchers/regions');
	$addLocationPen->setData([
		'project_id' => $project->id,
		'searcher_key' => 1, // указать нужный id
		'region_key' => 213, // указать нужный key
	]);
	$addLocationPenResult = $addLocationPen->exec();
	if (is_null($addLocationPenResult->getResult())) vd($addLocationPenResult->getErrors(), 1);

	$result = $addLocationPenResult->getResult();
	vd($result);
}
