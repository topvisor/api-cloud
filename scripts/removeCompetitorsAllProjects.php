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
$projectsRaw = $projectsPenResult->getResult();

$projects = array_filter($projectsRaw, function ($project) {
	return ($project -> id != '3461307');
});

$competitorsToDelete = [];

foreach ($projects as $project) {
	$competitorsGetPen = new TV\Pen($TVSession, 'get', 'projects_2', 'competitors');
	$competitorsGetPen->setFields(['id', 'site']);
	$competitorsGetPen->setData([
		'project_id' => $project->id,
	]);

	$competitorsGetPenResult = $competitorsGetPen->exec();
	if (is_null($competitorsGetPenResult->getResult())) vd($competitorsGetPenResult->getErrors(), 1);

	$result = $competitorsGetPenResult->getResult();

	$competitorsToDelete = array_filter($result, function ($competitor) {
		return ($competitor -> url != 'domclick.ru' && $competitor -> url != 'domclick.ru' && $competitor -> url != 'm2.ru' && $competitor -> url != 'avito.ru' && $competitor -> url != 'domofond.ru' && $competitor -> url != 'realty.yandex.ru' && $competitor -> url != 'cian.ru');
	});

	foreach ($competitorsToDelete as $competitorToDelete) {
		$compeitiorId = $competitorToDelete->id;
		vd($compeitiorId);

		$delCompetitorsPen = new TV\Pen($TVSession, 'del', 'projects_2', 'competitors');
		$delCompetitorsPen->setData([
			'project_id' => $project->id,
			'ids' => [$compeitiorId],
		]);

		$delCompetitorsPenResult = $delCompetitorsPen->exec();
		if (is_null($delCompetitorsPenResult->getResult())) vd($delCompetitorsPenResult->getErrors(), 1);

		$result = $delCompetitorsPenResult->getResult();
		vd($result);
	}
}
