<?php

// отключить сбор сниппетов во всех проектах

use Topvisor\TopvisorSDK\V2 as TV;

include(__DIR__ . '/../common.php');

$userId = 'Ваш ID';
$accessToken = 'Ваш ключ';

$TVSession = new TV\Session(['userId' => $userId, 'accessToken' => $accessToken]);

$projectsPen = new TV\Pen($TVSession, 'get', 'projects_2', 'projects');
$projectsPen->setFields(['id']);

$projectsPenResult = $projectsPen->exec();
$projects = $projectsPenResult->getResult();

foreach ($projects as $project) {
	$positionsDisableSnippetsCheckerPen = new TV\Pen($TVSession, 'edit', 'positions_2', 'settings');
	$positionsDisableSnippetsCheckerPen->setData([
		'project_id' => $project->id,
		'with_snippets' => 0,
	]);
	$positionsDisableSnippetsCheckerPenResult = $positionsDisableSnippetsCheckerPen->exec();
	if (is_null($positionsDisableSnippetsCheckerPenResult->getResult())) vd($positionsDisableSnippetsCheckerPenResult->getErrors(), 1);

	$result = $positionsDisableSnippetsCheckerPenResult->getResult();
	vd($result);
}
