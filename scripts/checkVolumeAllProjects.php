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
	$checkVolumePen = new TV\Pen($TVSession, 'edit', 'keywords_2', 'volumes/go');
	$checkVolumePen->setData([
		'project_id' => $project->id,
		'check_all_regions' => 1,
	]);
	$checkVolumePenResult = $checkVolumePen->exec();
	if (is_null($checkVolumePenResult->getResult())) vd($checkVolumePenResult->getErrors(), 1);

	$result = $checkVolumePenResult->getResult();
	vd($result);
}
