<?php
/**
 *
 * @package       cronstatus
 * @copyright (c) 2014 John Peskens (http://ForumHulp.com) and Igor Lavrov (https://github.com/LavIgor)
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
	'CRON'                            => 'Cron',
	'CRON_LOCKED'                     => 'Cron Bloqueado',
	'CRON_TIME_LOCKED'                => 'Fecha del bloqueo del Cron',
	'ACP_CRON_STATUS_TITLE'           => 'Estado del Cron',
	'ACP_CRON_STATUS_CONFIG_TITLE'    => 'Coprobar el estado del Cron',
	'ACP_CRON_STATUS_EXPLAIN'         => 'Estado del Cron es una página de su foro phpBB donde se puede comprobar si las tareas Cron están preparados para hacer. El “Auto” de la tarea con la última fecha, significa que la tarea tiene una opción de control de tiempo específico que no pudo ser reconocido por la extensión Estado del Cron. Una tarea marcada en rojo, significa una tarea que nunca se ha iniciado o que tiene un problema. Un bloqueo rojo, significa que esta tarea está bloqueada por el gerente Cron y bloquea otras tareas.',
	'CRON_STATUS_REFRESH'             => 'Segundos para refrescar',
	'CRON_TASK_LOCKED'                => 'Tarea Cron bloqueada',
	'CRON_STATUS_READY_TASKS'         => 'Tareas preparadas para ejecutar',
	'CRON_STATUS_NOT_READY_TASKS'     => 'No hay tareas preparadas',
	'CRON_STATUS_NO_TASKS'            => 'No hay tareas Cron disponibles',
	'CRON_STATUS_DATE_FORMAT'         => 'Formato de fecha para la página del Estado del Cron',
	'CRON_STATUS_DATE_FORMAT_EXPLAIN' => 'El formato de fecha es el mismo que la función <code>date</code> de PHP.',
	'CRON_STATUS_MAIN_NOTICE'         => 'Notice on the ACP index page',
	'CRON_STATUS_MAIN_NOTICE_EXPLAIN' => 'Mostrar Noticia del Estado del Cron en la página índice del PCA si el Cron esta bloqueado.',
	'CRON_TASK_NAME'                  => 'Nombre de la tarea',
	'CRON_TASK_DATE'                  => 'Última fecha de la tarea',
	'CRON_NEW_DATE'                   => 'Nueva fecha de la tarea',
	'CRON_TASK_NEVER_STARTED'         => 'Nunca realizado',
	'CRON_TASK_AUTO'                  => 'Auto',
	'CRON_TASK_DATE_TIME'             => 'Fecha y hora actual',
	'CRON_STATUS_ERROR'               => 'Error de refresco',
	'CRON_STATUS_TIMEOUT'             => 'Tiempo de espera de refresco',
	'CRON_STATUS_ERROR_EXPLAIN'       => 'Se ha producido un error durante la actualización de la página.',
	'CRON_STATUS_DEVELOPERS'          => 'Desarrolladores',
	'CRON_TASK_RUN'                   => 'Ejecutar',
	'CRON_TASK_RUNNING'               => 'Ejecutando...',
));

// Description of Cron Status extension
$lang = array_merge($lang, array(
	'CRONSTATUS_DESCRIPTION_PAGE'            => 'Página del estado del Cron',
	'CRONSTATUS_DESCRIPTION_PAGE_OVERVIEW'   => 'Visión general de trabajos Cron (con clasificación)',
	'CRONSTATUS_DESCRIPTION_PAGE_STATUS'     => 'Muestra el estado de cada tarea Cron',
	'CRONSTATUS_DESCRIPTION_PAGE_ABILITY'    => 'Puede ejecutar cualquier tarea preparada manualmente',
	'CRONSTATUS_DESCRIPTION_NOTICE'          => 'Noticia de Estado Cron (opcional)',
	'CRONSTATUS_DESCRIPTION_NOTICE_OVERVIEW' => 'Se muestra en la página principal del PCA cuando el Cron está bloqueado',
	'CRONSTATUS_DESCRIPTION_NOTICE_SETTINGS' => 'Puede apagar esta opción en los Ajustes del Foro',
));
