<?php

/**
 * Настроить проверку частоты в указанных проектах
 *
 * Регион выбирается автоматически, в соответствие с настройками проверки позиций проекта
 *
 * Проверить расписание и посмотреть API запросы можно тут: https://topvisor.com/project/settings/$projectId/#to_project_interval_volumes
 */

use Topvisor\TopvisorSDK\V2 as TV;

include(__DIR__ . '/../common.php');

$userId = 'Ваш ID';
$accessToken = 'Ваш ключ';

$checkGuestProjectsWithRights = true; // флаг для проверки гостевых
$volumesTypes = [6]; // типы частоты в Яндекс

// список проектов
$projectsIds = [];

$TVSession = new TV\Session(['userId' => $userId, 'accessToken' => $accessToken]);

// получить необходимые проекты
$projectsPen = new TV\Pen($TVSession, 'get', 'projects_2', 'projects');
$projectsPen->setData([
	'show_searchers_and_regions' => true,
]);
$projectsPen->setFields(['id']);
$projectsFiltersPersonal = [
	TV\Fields::genFilterData('id', 'IN', $projectsIds),
];
$projectsPen->setFilters($projectsFiltersPersonal);
$projectsPenResult = $projectsPen->exec();
$projects = $projectsPenResult->getResult();

$yandexRegionsKeys = [];
$googleRegionskeys = [];

// включить автоматическую проверку частоты в проектах
foreach ($projects as $project) {
	// поиск настроенных регионов Яндекса
	$yandexRegionskeys = [];
	foreach ($project->searchers as $searcher) {
		if ($searcher->searcher !== 0) continue;

		foreach ($searcher->regions as $region) {
			if (!$region->enabled) continue;

			$yandexRegionskeys[] = $region->key;
		}
	}
	if (!$yandexRegionskeys) continue;

	$yandexRegionskeys = array_unique($yandexRegionskeys);

	if (count($yandexRegionskeys) > 1) {
		echo "В проекте $project->id найдено несколько регионов Яндекса, в этом скрипте разрешено добавлять не более 1 региона для проверки частоты<br>";
		echo 'Если надо, это ограничение можно убрать';

		return;
	}

	new TV\Pen($TVSession, 'get', 'schedule_2');

	$changeSchedulePen = new TV\Pen($TVSession, 'edit', 'schedule_2');
	$changeSchedulePen->setData([
		'target_id' => $project->id,
		'type' => 'volumes_go',
		'schedule' => [
			[
				'days' => [
					2,
				],
				'times' => [
					[
						'hour' => 2,
						'minute' => 0,
					],
				],
			],
		],
	]);
	$changeSchedulePenResult = $changeSchedulePen->exec();

	$result = $changeSchedulePenResult->getResult();
	if (is_null($result)) vd($changeSchedulePenResult->getErrors(), 1);

	$changeScheduleSettingsPen = new TV\Pen($TVSession, 'edit', 'schedule_2', 'settings');
	$changeScheduleSettingsPen->setData([
		'target_id' => $project->id,
		'type' => 'volumes_go',
		'volumes_types' => $volumesTypes,
		'regions_keys_by_searcher_key' => [
			0 => $yandexRegionskeys,
			1 => $googleRegionskeys,
		],
	]);
	$changeScheduleSettingsPenResult = $changeScheduleSettingsPen->exec();

	$result = $changeScheduleSettingsPenResult->getResult();
	if (is_null($result)) vd($changeScheduleSettingsPenResult->getErrors(), 1);

	echo "Проект: $project->id: $result<br>";
}
