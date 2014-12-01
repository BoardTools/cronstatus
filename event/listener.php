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
	protected $phpbb_dispatcher;

	/**
	* Constructor
	*
	* @param \phpbb\controller\helper    $helper        Controller helper object
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\user $user, \phpbb\template\template $template, \phpbb\db\driver\driver_interface $db, \phpbb\cron\manager $cron_manager, \phpbb\event\dispatcher $phpbb_dispatcher)
	{
		$this->config = $config;
		$this->helper = $helper;
		$this->user = $user;
		$this->template = $template;
		$this->db = $db;
		$this->cron_manager = $cron_manager;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.acp_main_notice'				=> 'load_cronstatus',
			'core.acp_board_config_edit_add'	=> 'add_config',
		);
	}

	public function load_cronstatus($event)
	{
		$this->user->add_lang_ext('boardtools/cronstatus', 'cronstatus');
		$tasks = $this->cron_manager->get_tasks();

		if (empty($tasks) || !$this->config['cron_lock'] || !$this->config['cronstatus_main_notice'])
		{
			return;
		}

		$time = explode(' ', $this->config['cron_lock']);

		$cronlock = '';
		$this->get_cron_tasks($cronlock, true);

		if ($cronlock)
		{
			$this->template->assign_vars(array(
				'CRON_TIME' => (sizeof($time) == 2) ? $this->user->format_date((int) $time[0], $this->config['cronstatus_dateformat']) : false,
				'CRON_NAME' => $cronlock
			));
		}
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
			$this->user->add_lang_ext('boardtools/cronstatus', 'cronstatus');
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

	public function get_cron_tasks(&$cronlock, $get_last_task = false)
	{
		$sql = 'SELECT config_name, config_value FROM ' . CONFIG_TABLE . ' WHERE config_name LIKE ' . (($get_last_task) ? '"%_last_gc" OR config_name = "last_queue_run" ORDER BY config_value DESC' : '"%_gc" OR config_name = "last_queue_run" OR config_name = "queue_interval"');
		$result = ($get_last_task) ? $this->db->sql_query_limit($sql, 1) : $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		$sql = 'SELECT prune_next, prune_freq * 86400 AS prune_time FROM ' . FORUMS_TABLE . ' WHERE enable_prune = 1 ORDER BY prune_next';
		$result = $this->db->sql_query_limit($sql, 1);
		$prune = $this->db->sql_fetchrow($result);
		$rows[] = array(
			"config_name"	=> "prune_forum_last_gc", // This is the time of the last Cron Job, not the time of pruned forums.
			"config_value"	=> $prune['prune_next'] - $prune['prune_time']
		);
		$rows[] = array(
			"config_name"	=> "prune_forum_gc",
			"config_value"	=> $prune['prune_time']
		);
		$this->db->sql_freeresult($result);

		$sql = 'SELECT prune_shadow_next, prune_shadow_freq * 86400 AS prune_shadow_time FROM ' . FORUMS_TABLE . ' WHERE enable_shadow_prune = 1 ORDER BY prune_shadow_next';
		$result = $this->db->sql_query_limit($sql, 1);
		$prune_shadow = $this->db->sql_fetchrow($result);
		$rows[] = array(
			"config_name"	=> "prune_shadow_topics_last_gc", // This is the time of the last Cron Job, not the time of pruned shadow topics.
			"config_value"	=> $prune_shadow['prune_shadow_next'] - $prune_shadow['prune_shadow_time']
		);
		$rows[] = array(
			"config_name"	=> "prune_shadow_topics_gc",
			"config_value"	=> $prune_shadow['prune_shadow_time']
		);
		$this->db->sql_freeresult($result);

		$rows[] = array(
			"config_name"	=> "plupload_gc",
			"config_value"	=> 86400
		);

		if ($this->config['cron_lock'])
		{
			$cronlock = $this->maxValueInArray($rows, 'config_value');
			$cronlock = str_replace(array('_last_gc', 'prune_notifications', 'last_queue_run'), array('', 'read_notification', 'queue_interval'), $cronlock['config_name']);
		}

		/**
		* Event to modify cron configuration variables before displaying cron information
		*
		* @event boardtools.cronstatus.modify_cron_config
		* @var	array	rows		Configuration array
		* @var	string	cronlock	Name of task that released cron lock (in last task date format)
		* @since 3.1.0-RC3
		*/
		$vars = array('rows', 'cronlock');
		extract($this->phpbb_dispatcher->trigger_event('boardtools.cronstatus.modify_cron_config', compact($vars)));

		return (!$get_last_task) ? $rows : true;
	}

	public function maxValueInArray($array, $keyToSearch)
	{
		$currentMax = null;
		foreach($array as $arr)
		{
			foreach($arr as $key => $value)
			{
				if (($key == $keyToSearch) && ($value >= $currentMax) && ((strrpos($arr['config_name'], '_last_gc') === strlen($arr['config_name']) - 8) || $arr['config_name'] === 'last_queue_run'))
				{
					$currentMax = $value;
					$currentName = $arr['config_name'];
				}
			}
		}
		return array('config_name' => $currentName , 'config_value' => $currentMax);
	}
}
