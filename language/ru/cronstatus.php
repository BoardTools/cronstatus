<?php
/**
*
* @package cronstatus
* @copyright (c) 2014 John Peskens (http://ForumHulp.com) and Igor Lavrov (https://github.com/LavIgor)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'CRON'								=> 'Cron',
	'CRON_LOCKED'						=> 'Cron заблокирован',
	'CRON_TIME_LOCKED'					=> 'Время блокировки cron',
	'ACP_CRON_STATUS_TITLE'				=> 'Статус Cron',
	'ACP_CRON_STATUS_CONFIG_TITLE'		=> 'Проверить Статус Cron',
	'ACP_CRON_STATUS_EXPLAIN'			=> 'Статус Cron - это страница вашей конференции phpBB, где вы можете проверить доступные задания cron. Дата следующего задания “Авто” означает, что данное задание имеет особый механизм контроля времени, который не может быть распознан расширением Cron Status. Красным цветом выделены задания, которые никогда не выполнялись или с которыми возникли проблемы. Красный замок показывает задание, при выполнении которого cron был заблокирован планировщиком заданий.',
	'CRON_STATUS_REFRESH'				=> 'Секунды до обновления',
	'CRON_TASK_LOCKED'					=> 'Задание cron заблокировано',
	'CRON_STATUS_READY_TASKS'			=> 'Задания, готовые для выполнения',
	'CRON_STATUS_NOT_READY_TASKS'		=> 'Неготовые задания',
	'CRON_STATUS_NO_TASKS'				=> 'Нет доступных заданий cron',
	'CRON_STATUS_DATE_FORMAT'			=> 'Формат даты для страницы Статуса Cron',
	'CRON_STATUS_DATE_FORMAT_EXPLAIN'	=> 'Синтаксис идентичен функции <code>date</code> языка PHP.',
	'CRON_STATUS_MAIN_NOTICE'			=> 'Уведомление на главной странице администраторского раздела',
	'CRON_STATUS_MAIN_NOTICE_EXPLAIN'	=> 'Отображать Уведомление о Статусе Cron на главной странице администраторского раздела, если Cron заблокирован.',
	'CRON_TASK_NAME'					=> 'Задание',
	'CRON_TASK_DATE'					=> 'Дата предыдущего задания',
	'CRON_NEW_DATE'						=> 'Дата следующего задания',
	'CRON_TASK_NEVER_STARTED'			=> 'Никогда не выполнялось',
	'CRON_TASK_AUTO'					=> 'Авто',
	'CRON_TASK_DATE_TIME'				=> 'Текущие дата и время',
	'CRON_STATUS_ERROR'					=> 'Ошибка обновления',
	'CRON_STATUS_TIMEOUT'				=> 'Тайм-аут обновления',
	'CRON_STATUS_ERROR_EXPLAIN'			=> 'Во время обновления страницы произошла ошибка.',
	'CRON_STATUS_DEVELOPERS'			=> 'Разработчики',
	'CRON_TASK_RUN'						=> 'Выполнить',
	'CRON_TASK_RUNNING'					=> 'Выполняется...',
));

// Description of Cron Status extension
$lang = array_merge($lang, array(
	'CRONSTATUS_DESCRIPTION_PAGE'				=> 'Страница Статуса Cron',
	'CRONSTATUS_DESCRIPTION_PAGE_OVERVIEW'		=> 'Перечень заданий Cron (с сортировкой)',
	'CRONSTATUS_DESCRIPTION_PAGE_STATUS'		=> 'Отображает статус каждого задания Cron',
	'CRONSTATUS_DESCRIPTION_PAGE_ABILITY'		=> 'Вы можете вручную выполнить любое готовое для выполнения задание',
	'CRONSTATUS_DESCRIPTION_NOTICE'				=> 'Уведомление о Статусе Cron (опционально)',
	'CRONSTATUS_DESCRIPTION_NOTICE_OVERVIEW'	=> 'Отображается на главной странице администраторского раздела, когда cron заблокирован',
	'CRONSTATUS_DESCRIPTION_NOTICE_SETTINGS'	=> 'Может быть выключено в разделе Настройки конференции',
));
