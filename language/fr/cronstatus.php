<?php
/**
 *
 * Cron Status extension for the phpBB Forum Software package.
 * French translation by Galixte (http://www.galixte.com)
 *
 * @copyright (c) 2014 - 2018 Igor Lavrov and John Peskens
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/**
 * DO NOT CHANGE
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ « » “ ” …
//

$lang = array_merge($lang, array(
	'CRON'                            => 'Cron',
	'CRON_LOCKED'                     => 'Cron arrêté',
	'CRON_TIME_LOCKED'                => 'Temps d’arrêt du Cron',
	'ACP_CRON_STATUS_TITLE'           => 'Statut du Cron',
	'ACP_CRON_STATUS_CONFIG_TITLE'    => 'Vérifier le statut des tâches du Cron',
	'ACP_CRON_STATUS_EXPLAIN'         => 'Voici la page de votre forum phpBB où vous pouvez vérifier le statut des tâches du Cron. L’indication « Automatique » concernant la dernière date d’une tâche signifie que la tâche a une option dédiée à la gestion de son temps qui ne peut être reconnue par l’extension « Cron Status ». Une tâche indiquée en rouge signifie qu’elle n’a jamais été exécutée ou qu’il y a un problème. Un verrou rouge signifie que cette tâche est verrouillée par le gestionnaire du Cron et bloque d’autres tâches.',
	'CRON_STATUS_REFRESH'             => 'Nombre de secondes avant l’actualisation',
	'CRON_TASK_LOCKED'                => 'Tâche du Cron arrêtée',
	'CRON_STATUS_READY_TASKS'         => 'Tâches disponibles',
	'CRON_STATUS_NOT_READY_TASKS'     => 'Tâches non disponibles',
	'CRON_STATUS_NO_TASKS'            => 'Aucune tâche du Cron disponible',
	'CRON_STATUS_DATE_FORMAT'         => 'Format de la date du statut des tâches du Cron',
	'CRON_STATUS_DATE_FORMAT_EXPLAIN' => 'Le format de la date est le même que la fonction <code>date</code> de PHP.',
	'CRON_STATUS_MAIN_NOTICE'         => 'Notification sur la page de l’index de l’administration',
	'CRON_STATUS_MAIN_NOTICE_EXPLAIN' => 'Si le Cron est arrêté une notification est affichée sur le statut du Cron sur la page de l’index de l’administration.',
	'CRON_TASK_NAME'                  => 'Nom de la tâche',
	'CRON_TASK_DATE'                  => 'Date de la dernière exécution',
	'CRON_NEW_DATE'                   => 'Date de la prochaine exécution',
	'CRON_TASK_NEVER_STARTED'         => 'N’a jamais été exécutée',
	'CRON_TASK_AUTO'                  => 'Automatique',
	'CRON_TASK_DATE_TIME'             => 'Date et heure actuelles',
	'CRON_STATUS_ERROR'               => 'Erreur d’actualisation',
	'CRON_STATUS_TIMEOUT'             => 'Temps d’actualisation dépassé',
	'CRON_STATUS_ERROR_EXPLAIN'       => 'Une erreur s’est produite lors de l’actualisation de la page.',
	'CRON_STATUS_DEVELOPERS'          => 'Développeurs',
	'CRON_TASK_RUN'                   => 'Exécuter',
	'CRON_TASK_RUNNING'               => 'En cours d’exécution…',
));

// Description of Cron Status extension
$lang = array_merge($lang, array(
	'CRONSTATUS_DESCRIPTION_PAGE'            => 'Page du statut du Cron',
	'CRONSTATUS_DESCRIPTION_PAGE_OVERVIEW'   => 'Vue d’ensemble des tâches du Cron (avec tri).',
	'CRONSTATUS_DESCRIPTION_PAGE_STATUS'     => 'Affiche le statut de chaque tâche du Cron.',
	'CRONSTATUS_DESCRIPTION_PAGE_ABILITY'    => 'Vous pouvez exécuter manuellement n’importe quelle tâche disponible.',
	'CRONSTATUS_DESCRIPTION_NOTICE'          => 'Notification sur le statut du Cron (optionnelle)',
	'CRONSTATUS_DESCRIPTION_NOTICE_OVERVIEW' => 'Est affichée sur la page de l’index de l’administration lorsque le Cron est arrêté.',
	'CRONSTATUS_DESCRIPTION_NOTICE_SETTINGS' => 'Peut être désactivée dans la page « Configuration du forum ».',
));
