<?php

// отключить сбор сниппетов во всех проектах

include(__DIR__ . '/../common.php');

use Topvisor\TopvisorSDK\V2 as TV;

$userId = 'Ваш ID';
$accessToken = 'Ваш ключ';

$TVSession = new TV\Session(['userId' => $userId, 'accessToken' => $accessToken]);

$projectsPen = new TV\Pen($TVSession, 'get', 'projects_2', 'projects');
$projectsPen->setFields(['id']);

$projectsPenResult = $projectsPen->exec();
$projects = $projectsPenResult->getResult();

foreach ($projects as $project) {
	$setProjectsSettingsPen = new TV\Pen($TVSession, 'edit', 'positions_2', 'settings');
	$setProjectsSettingsPen->setData([
		'project_id' => $project->id,
		'with_snippets' => 0,
	]);
	$setProjectsSettingsPenResult = $setProjectsSettingsPen->exec();
	if (is_null($setProjectsSettingsPenResult->getResult())) vd($setProjectsSettingsPenResult->getErrors(), 1);

	$result = $setProjectsSettingsPenResult->getResult();
	vd($result);
}
