<?php
/**
 *
 * @package       cronstatus
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
	'CRON'                            => '<em>Cron</em>',
	'CRON_LOCKED'                     => '<em>Cron</em> (je) zaključan',
	'CRON_TIME_LOCKED'                => 'Vrijeme <em>crona</em>',
	'ACP_CRON_STATUS_TITLE'           => 'Status <em>crona</em>',
	'ACP_CRON_STATUS_CONFIG_TITLE'    => 'Provjera statusa <em>crona</em>',
	'ACP_CRON_STATUS_EXPLAIN'         => 'Status <em>crona</em> je stranica phpBBa na kojoj možeš provjeriti jesu li <em>cron</em> zadatci spremni za pokretanje/izvršavanje.<br />“Automatski” datum zadnjeg zadatka odnosi se na zadatke s opcijom specifične vremenske kontrole koju ekstenzija “Status <em>crona</em>” nije mogla/ne može prepoznati.<br />Crvenom bojom označeni zadatci nikada nisu pokrenuti odnosno imaju problema.<br />“Crveno zaključanje” odnosi se na zadatke zaključane od strane <em>cron upravitelja</em> (a)	posljedica čega je blokiranje ostalih zadataka.',
	'CRON_STATUS_REFRESH'             => 'Preostalo sekundi do osvježavanja',
	'CRON_TASK_LOCKED'                => 'Zadatak <em>crona</em> (je) zaključan',
	'CRON_STATUS_READY_TASKS'         => 'Zadatci spremni za pokretanje',
	'CRON_STATUS_NOT_READY_TASKS'     => 'Zadatci nespremni za pokretanje',
	'CRON_STATUS_NO_TASKS'            => 'Nema dostupnih zadataka <em>crona</em>',
	'CRON_STATUS_DATE_FORMAT'         => 'Format datuma na stranici statusa <em>crona</em>',
	'CRON_STATUS_DATE_FORMAT_EXPLAIN' => 'Format datuma jednak je PHP <code>date</code> funkciji.',
	'CRON_STATUS_MAIN_NOTICE'         => 'Obavijest na početnoj stranici AF-a',
	'CRON_STATUS_MAIN_NOTICE_EXPLAIN' => 'Ukoliko je <em>cron</em> zaključan, prikazuje obavijest o statusu <em>crona</em> na početnoj stranici <em>Administriranja foruma [AF]</em>.',
	'CRON_TASK_NAME'                  => 'Naziv zadatka',
	'CRON_TASK_DATE'                  => 'Datum zadnjeg zadatka',
	'CRON_NEW_DATE'                   => 'Datum novog zadatka',
	'CRON_TASK_NEVER_STARTED'         => 'Nikad pokrenuto',
	'CRON_TASK_AUTO'                  => 'Automatski',
	'CRON_TASK_DATE_TIME'             => 'Trenutni datum i vrijeme',
	'CRON_STATUS_ERROR'               => 'Greška osvježavanja',
	'CRON_STATUS_TIMEOUT'             => 'Vrijeme isteka izvršavanja osvježavanja',
	'CRON_STATUS_ERROR_EXPLAIN'       => 'Došlo je do greške prilikom osvježavanja stranice.',
	'CRON_STATUS_DEVELOPERS'          => 'Razvojni/a programer/ka',
	'CRON_TASK_RUN'                   => 'Pokreni',
	'CRON_TASK_RUNNING'               => 'Pokretanje [izvršavanje] u tijeku...',
));

// Description of Cron Status extension
$lang = array_merge($lang, array(
	'CRONSTATUS_DESCRIPTION_PAGE'            => 'Stranica statusa <em>crona</em>',
	'CRONSTATUS_DESCRIPTION_PAGE_OVERVIEW'   => 'Pregled poslova <em>crona</em> (s redanjem)',
	'CRONSTATUS_DESCRIPTION_PAGE_STATUS'     => 'Prikazuje status svakog zadatka <em>crona</em>',
	'CRONSTATUS_DESCRIPTION_PAGE_ABILITY'    => 'Možeš pokrenuti bilo koji spreman zadatak ručno',
	'CRONSTATUS_DESCRIPTION_NOTICE'          => 'Obavijest statusa <em>crona</em> (opcionalno)',
	'CRONSTATUS_DESCRIPTION_NOTICE_OVERVIEW' => 'Ukoliko je <em>cron</em> zaključan, bit će prikazana na početnoj stranici <em>Administriranja foruma [AF]</em>',
	'CRONSTATUS_DESCRIPTION_NOTICE_SETTINGS' => 'Može biti isključen(o) u postavkama foruma',
));
