<?php
/**
 *
 * @package       cronstatus
 * @copyright (c) 2014 - 2018 Igor Lavrov and John Peskens
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
	'ACP_CRON_STATUS_TITLE'        => 'Estado del Cron',
	'ACP_CRON_STATUS_CONFIG_TITLE' => 'Comprobar estado del Cron',
));
