<?php

// перенести самые популярные запросы из всех групп в Новую группу

use Topvisor\TopvisorSDK\V2 as TV;

include(__DIR__ . '/../common.php');

$userId = 'Ваш ID';
$accessToken = 'Ваш ключ';

$project_id = 'Проект';

$TVSession = new TV\Session(['userId' => $userId, 'accessToken' => $accessToken]);

// создание группы
$pen = new TV\Pen($TVSession, 'add', 'keywords_2', 'groups');
$pen->setData([
	'project_id' => $project_id,
	'name' => 'Высокочастотные',
]);
$newGroups = $pen->exec()->getResult();
if (!$newGroups) vd('Не удалось создать группу', 1);

$newGroup = $newGroups[0];

// получение группы
$pen = new TV\Pen($TVSession, 'get', 'keywords_2', 'groups');
$pen->setFields(['id']);
$pen->setData(['project_id' => $project_id]);

$penResultPage = $pen->exec();
if (is_null($penResultPage->getResult())) vd($penResultPage->getErrors(), 1);

$groups = $penResultPage->getResult();
foreach ($groups as $group) {
	// получение самого высокочастотного запрос в группе
	$pen = new TV\Pen($TVSession, 'get', 'keywords_2', 'keywords');
	$pen->setData(['project_id' => $project_id]);
	$pen->setFilters([TV\Fields::genFilterData('group_id', 'EQUALS', [$group->id])]);
	$pen->setOrders([TV\Fields::genOrderData('volume:143:1:3', 'DESC')]);
	$pen->setLimit(1);

	$penResultPage = $pen->exec();
	if (is_null($penResultPage->getResult())) vd($penResultPage->getErrors(), 1);
	$keywords = $penResultPage->getResult();

	if (!$keywords) continue;

	$keyword = $keywords[0];

	// перемещение запроса в новую группу
	$pen = new TV\Pen($TVSession, 'edit', 'keywords_2', 'keywords/move');
	$pen->setData([
		'project_id' => $project_id,
		'to_id' => $newGroup->id,
	]);
	$pen->setFilters([TV\Fields::genFilterData('id', 'EQUALS', [$keyword->id])]);
	$pageOfPen = $pen->exec();
	if (is_null($pageOfPen->getResult())) vd($pageOfPen->getErrors(), 1);

	$pageOfPen->getResult();
}
