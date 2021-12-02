<?php
/**
 *
 * @package       Cron Status
 * @copyright (c) 2014 - 2018 Igor Lavrov and John Peskens
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
	 * @param \phpbb\config\config              $config           Config object
	 * @param \phpbb\controller\helper          $helper           Controller helper object
	 * @param \phpbb\user                       $user             User object
	 * @param \phpbb\template\template          $template         Template object
	 * @param \phpbb\db\driver\driver_interface $db               Database driver object
	 * @param \phpbb\cron\manager               $cron_manager     Cron manager object
	 * @param \phpbb\event\dispatcher_interface $phpbb_dispatcher Event dispatcher object
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\user $user, \phpbb\template\template $template, \phpbb\db\driver\driver_interface $db, \phpbb\cron\manager $cron_manager, \phpbb\event\dispatcher_interface $phpbb_dispatcher)
	{
		$this->config = $config;
		$this->helper = $helper;
		$this->user = $user;
		$this->template = $template;
		$this->db = $db;
		$this->cron_manager = $cron_manager;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
	}

	/**
	 * Assign functions defined in this class to event listeners in the core
	 *
	 * @return array
	 */
	static public function getSubscribedEvents()
	{
		return array(
			'core.acp_main_notice'           => 'load_cronstatus',
			'core.acp_board_config_edit_add' => 'add_config',
			'core.cron_run_before'           => 'log_latest_task',
		);
	}

	/**
	 * Displays Cron Status Notice if needed
	 *
	 * @param object $event The event object
	 */
	public function load_cronstatus($event)
	{
		$this->user->add_lang_ext('boardtools/cronstatus', 'cronstatus');
		$tasks = $this->cron_manager->get_tasks();

		if (empty($tasks) || !$this->config['cron_lock'] || !$this->config['cronstatus_main_notice'])
		{
			return;
		}

		$time = explode(' ', $this->config['cron_lock']);

		$cronlock = $this->config['cronstatus_latest_task'];

		// Fall back on the previous method for phpBB <3.1.9
		if (!$cronlock)
		{
			$this->get_cron_tasks($cronlock, true);
		}

		if ($cronlock)
		{
			$this->template->assign_vars(array(
				'CRON_TIME' => (sizeof($time) == 2) ? $this->user->format_date((int) $time[0], $this->config['cronstatus_dateformat']) : false,
				'CRON_NAME' => $cronlock,
			));
		}
	}

	/**
	 * Adds configuration strings for current extension to the ACP
	 *
	 * @param object $event The event object
	 */
	public function add_config($event)
	{
		if ($event['mode'] == 'settings')
		{
			$this->user->add_lang_ext('boardtools/cronstatus', 'cronstatus');
			$display_vars = $event['display_vars'];
			/* We add a new legend, but we need to search for the last legend instead of hard-coding */
			$submit_key = array_search('ACP_SUBMIT_CHANGES', $display_vars['vars']);
			$submit_legend_number = substr($submit_key, 6);
			$display_vars['vars']['legend' . $submit_legend_number] = 'ACP_CRON_STATUS_TITLE';
			$new_vars = array(
				'cronstatus_dateformat'                => array('lang' => 'CRON_STATUS_DATE_FORMAT', 'validate' => 'string', 'type' => 'custom', 'method' => 'dateformat_select', 'explain' => true),
				'cronstatus_main_notice'               => array('lang' => 'CRON_STATUS_MAIN_NOTICE', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true),
				'legend' . ($submit_legend_number + 1) => 'ACP_SUBMIT_CHANGES',
			);
			$display_vars['vars'] = phpbb_insert_config_array($display_vars['vars'], $new_vars, array('after' => $submit_key));
			$event['display_vars'] = $display_vars;
		}
	}

	/**
	 * Returns configuration parameters for all available Cron tasks
	 *
	 * @param string $cronlock      The name of the latest executed Cron task
	 * @param bool   $get_last_task Calculate the latest executed task only
	 * @return array|true If $get_last_task is true, true is always returned instead of parameters
	 */
	public function get_cron_tasks(&$cronlock, $get_last_task = false)
	{
		$sql = "SELECT config_name, config_value FROM " . CONFIG_TABLE . " WHERE config_name LIKE " . (($get_last_task) ? "'%_last_gc' OR config_name = 'last_queue_run' ORDER BY config_value DESC" : "'%_gc' OR config_name = 'last_queue_run' OR config_name = 'queue_interval'");
		$result = ($get_last_task) ? $this->db->sql_query_limit($sql, 1) : $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		$sql = 'SELECT prune_next, prune_freq * 86400 AS prune_time FROM ' . FORUMS_TABLE . ' WHERE enable_prune = 1 ORDER BY prune_next';
		$result = $this->db->sql_query_limit($sql, 1);
		$prune = $this->db->sql_fetchrow($result);
		$rows[] = array(
			"config_name"  => "prune_forum_last_gc", // This is the time of the last Cron Job, not the time of pruned forums.
			"config_value" => ($prune['prune_next'] ?? 0) - ($prune['prune_time'] ?? 0),
		);
		$rows[] = array(
			"config_name"  => "prune_forum_gc",
			"config_value" => $prune['prune_time'] ?? 0,
		);
		$this->db->sql_freeresult($result);

		$sql = 'SELECT prune_shadow_next, prune_shadow_freq * 86400 AS prune_shadow_time FROM ' . FORUMS_TABLE . ' WHERE enable_shadow_prune = 1 ORDER BY prune_shadow_next';
		$result = $this->db->sql_query_limit($sql, 1);
		$prune_shadow = $this->db->sql_fetchrow($result);
		$rows[] = array(
			"config_name"  => "prune_shadow_topics_last_gc", // This is the time of the last Cron Job, not the time of pruned shadow topics.
			"config_value" => ($prune_shadow['prune_shadow_next'] ?? 0) - ($prune_shadow['prune_shadow_time'] ?? 0),
		);
		$rows[] = array(
			"config_name"  => "prune_shadow_topics_gc",
			"config_value" => $prune_shadow['prune_shadow_time'] ?? 0,
		);
		$this->db->sql_freeresult($result);

		$rows[] = array(
			"config_name"  => "plupload_gc",
			"config_value" => 86400,
		);

		$last_task_date = 0;
		if ($this->config['cron_lock'])
		{
			$cronlock = $this->maxValueInArray($rows, 'config_value');
			$last_task_date = $cronlock['config_value'];
			$cronlock = str_replace(array('_last_gc', 'prune_notifications', 'last_queue_run'), array('', 'read_notification', 'queue_interval'), $cronlock['config_name']);
		}

		/**
		 * Event to modify cron configuration variables before displaying cron information
		 *
		 * @event boardtools.cronstatus.modify_cron_config
		 * @var array  rows           Configuration array
		 * @var string cronlock       Name of the task that released cron lock (in last task date format)
		 * @var string last_task_date Last task date of the task that released cron lock
		 * @since 3.1.0-RC3
		 * @changed 3.1.2-RC Added last_task_date variable
		 */
		$vars = array('rows', 'cronlock', 'last_task_date');
		extract($this->phpbb_dispatcher->trigger_event('boardtools.cronstatus.modify_cron_config', compact($vars)));

		return (!$get_last_task) ? $rows : true;
	}

	/**
	 * Calculates the maximum value for last date of the cron task execution
	 *
	 * @param array  $array       Array of Cron configuration parameters
	 * @param string $keyToSearch Array key containing last date value
	 * @return array
	 */
	public function maxValueInArray($array, $keyToSearch)
	{
		$currentName = $currentMax = null;
		foreach ($array as $arr)
		{
			foreach ($arr as $key => $value)
			{
				if (($key == $keyToSearch) && ($value >= $currentMax) && ((strrpos($arr['config_name'], '_last_gc') === strlen($arr['config_name']) - 8) || $arr['config_name'] === 'last_queue_run'))
				{
					$currentMax = $value;
					$currentName = $arr['config_name'];
				}
			}
		}
		return array('config_name' => $currentName, 'config_value' => $currentMax);
	}

	/**
	 * Logs the name of the latest executed Cron task to the database
	 *
	 * @param object $event The event object
	 */
	public function log_latest_task($event)
	{
		/** @var \phpbb\cron\task\task $task */
		$task = $event['task'];
		$this->config->set('cronstatus_latest_task', $task->get_name());
	}
}
