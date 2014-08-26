<?php
/**
*
* @package cronstatus
* @copyright (c) 2014 John Peskens (http://ForumHulp.com) and Igor Lavrov (https://github.com/LavIgor)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace boardtools\cronstatus\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	protected $config;
	protected $helper;
	protected $user;
	protected $template;
	protected $db;
	protected $cron_manager;

	/**
	* Constructor
	*
	* @param \phpbb\controller\helper    $helper        Controller helper object
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\user $user, \phpbb\template\template $template, \phpbb\db\driver\driver_interface $db, \phpbb\cron\manager $cron_manager)
	{
		$this->config = $config;
		$this->helper = $helper;
		$this->user = $user;
		$this->template = $template;
		$this->db = $db;
		$this->cron_manager = $cron_manager;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'					=> 'load_language_on_setup',
			'core.acp_main_notice'				=> 'load_cronstatus',
			'core.acp_board_config_edit_add'	=> 'add_config',
		);
	}

	public function load_cronstatus($event)
	{
		$tasks = $this->cron_manager->get_tasks();

		if (empty($tasks) || !$this->config['cron_lock'] || !$this->config['cronstatus_main_notice'])
		{
			return;
		}

		$time = explode(' ', $this->config['cron_lock']);

		$sql = 'SELECT * FROM ' . CONFIG_TABLE . ' where config_name LIKE "%last_gc" ORDER BY config_value DESC LIMIT 1';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$task = str_replace('_last_gc', '',$row['config_name']);
		$task = str_replace('read_notification', 'prune_notification', $task);

		$task = $this->array_find($task, $tasks);

		$this->template->assign_vars(array(
			'CRON_TIME' => (sizeof($time) == 2) ? $this->user->format_date((int) $time[0], $this->config['cronstatus_dateformat']) : false,
			'CRON_NAME' => $task
		));
	}

	// array_search with partial matches
	public function array_find($needle, $haystack)
	{
		if(!is_array($haystack))
		{
			return false;
		}
		foreach ($haystack as $item)
		{
			$name = $item->get_name();
			if (strpos($name, $needle) !== false)
			{
				return $name;
			}
		}
		return false;
	}

	public function add_config($event)
	{
		if($event['mode'] == 'settings')
		{
			$display_vars = $event['display_vars'];
			/* We add a new legend, but we need to search for the last legend instead of hard-coding */
			$submit_key = array_search('ACP_SUBMIT_CHANGES', $display_vars['vars']);
			$submit_legend_number = substr($submit_key, 6);
			$display_vars['vars']['legend'.$submit_legend_number] = 'ACP_CRON_STATUS_TITLE';
			$new_vars = array(
				'cronstatus_dateformat'	=> array('lang' => 'CRON_STATUS_DATE_FORMAT',	'validate' => 'string',	'type' => 'custom', 'method' => 'dateformat_select', 'explain' => true),
				'cronstatus_main_notice'	=> array('lang' => 'CRON_STATUS_MAIN_NOTICE',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
				'legend'.($submit_legend_number + 1)	=> 'ACP_SUBMIT_CHANGES',
			);
			$display_vars['vars'] = phpbb_insert_config_array($display_vars['vars'], $new_vars, array('after' => $submit_key));
			$event['display_vars'] = $display_vars;
		}
	}

	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'boardtools/cronstatus',
			'lang_set' => 'cronstatus',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}
}
