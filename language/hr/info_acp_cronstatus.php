<?php
/**
 *
 * @package       Cron Status
 * @copyright (c) 2014 - 2018 Igor Lavrov and John Peskens
 * Croatian translation by Ančica Sečan (http://ancica.sunceko.net)
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
	'ACP_CRON_STATUS_TITLE'        => 'Status <em>crona</em>',
	'ACP_CRON_STATUS_CONFIG_TITLE' => 'Provjera statusa <em>crona</em>',
));
