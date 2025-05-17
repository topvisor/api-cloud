<?php

/**
 * Скрипт удаляет запросы во всех проектах пользователя по указанному фильтру
 * Установить $action = 'test' для выполнения теста
 * Установить $action = 'delete' для выполнения удаления
 *
 * SQL для выполнения ручного бекапа перед выполнением запроса, информация для админов:
 * INSERT INTO `backup`.`mod_keywords`
 * SELECT * FROM `topvisor`.`mod_keywords` WHERE `project_id` IN(SELECT `id` FROM `topvisor`.`mod_projects` WHERE `user` = {{ USER_ID }})
 */

use Topvisor\TopvisorSDK\V2 as TV;

include(__DIR__ . '/../common.php');

$userId = 'Ваш ID';
$accessToken = 'Ваш ключ';

// параметры выполнения скрипта
$action = ''; // test | delete
$filtersProjects = [
//	TV\Fields::genFilterData('id', 'EQUALS', [0])
];
$filtersKeywords = [
//	TV\Fields::genFilterData('name', 'CONTAINS', ['test 12345'])
];
// /параметры выполнения скрипта

if (!$action ||
	!$userId ||
	!$accessToken ||
	!$filtersProjects ||
	!$filtersKeywords
) {
	echo 'Установите необходимые параметры в файле скрипта';

	exit();
}

$TVSession = new TV\Session(['userId' => $userId, 'accessToken' => $accessToken]);

$projectsPen = new TV\Pen($TVSession, 'get', 'projects_2', 'projects');
$projectsPen->setFields(['id', 'name']);
$projectsPen->setFilters($filtersProjects);
$projectsPenResult = $projectsPen->exec();

if (is_null($projectsPenResult->getResult())) vd($projectsPenResult->getErrors(), 1);

$projects = $projectsPenResult->getResult();

foreach ($projects as $project) {
	echo '<br>';
	echo "Проект $project->name<br>";

	if ($action === 'test') {
		$keywordsGetPen = new TV\Pen($TVSession, 'get', 'keywords_2', 'keywords');
		$keywordsGetPen->setData(['project_id' => $project->id]);
		$projectsPen->setFields(['name']);
		$keywordsGetPen->setFilters($filtersKeywords);
		$keywordsGetPenResult = $keywordsGetPen->exec();

		if (is_null($keywordsGetPenResult->getResult())) vd($keywordsGetPenResult->getErrors(), 1);

		$keywords = $keywordsGetPenResult->getResult();
		if ($keywords) {
			echo '- Запросы для удаления: ' . json_encode($keywords, JSON_UNESCAPED_UNICODE) . '<br>';
		} else {
			echo '- Запросов для удаления не найдено<br>';
		}
	}

	if ($action === 'delete') {
		$keywordsDelPen = new TV\Pen($TVSession, 'del', 'keywords_2', 'keywords');
		$keywordsDelPen->setData(['project_id' => $project->id]);
		$keywordsDelPen->setFilters($filtersKeywords);
		$keywordsDelPenResult = $keywordsDelPen->exec();

		if (is_null($keywordsDelPenResult->getResult())) vd($keywordsDelPenResult->getErrors(), 1);

		$res = $keywordsDelPenResult->getResult();
		echo "- Результат удаления: $res<br>";
	}
}
