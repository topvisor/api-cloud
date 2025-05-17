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
if (is_null($projectsPenResult->getResult())) vd($projectsPenResult->getErrors(), 1);
$projects = $projectsPenResult->getResult();

foreach ($projects as $project) {
	$competitorsAddPen = new TV\Pen($TVSession, 'add', 'projects_2', 'competitors');
	$competitorsAddPen->setData([
		'project_id' => $project->id,
		'urls' => ['mlsn.ru'],
	]);
	$competitorsAddPenResult = $competitorsAddPen->exec();
	if (is_null($competitorsAddPenResult->getResult())) vd($competitorsAddPenResult->getErrors(), 1);

	$result = $competitorsAddPenResult->getResult();
	vd($result);
}
