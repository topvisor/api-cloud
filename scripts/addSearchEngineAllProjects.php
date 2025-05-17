<?php

// добавить ПС во все проекты

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
	$addSearcherPen = new TV\Pen($TVSession, 'add', 'positions_2', 'searchers');
	$addSearcherPen->setData([
		'project_id' => $project->id,
		'searcher_key' => 5, // указать нужный id
	]);
	$addSearcherPenResult = $addSearcherPen->exec();
	if (is_null($addSearcherPenResult->getResult())) vd($addSearcherPenResult->getErrors(), 1);

	$result = $addSearcherPenResult->getResult();
	vd($result);
}
