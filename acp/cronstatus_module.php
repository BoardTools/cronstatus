<?php
/**
 *
 * @package       Cron Status
 * @copyright (c) 2014 - 2018 Igor Lavrov and John Peskens
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace boardtools\cronstatus\acp;

class cronstatus_module
{
	/** @var cronstatus_helper */
	public $helper;
	public $u_action;
	public $page_title;
	public $tpl_name;
	public $metadata;

	public function main($id, $mode)
	{
		/** @var \phpbb\config\config $config */
		/** @var \phpbb\request\request_interface $request */
		/** @var \phpbb\extension\manager $phpbb_extension_manager */
		global $config, $user, $template, $request, $phpbb_root_path, $phpEx, $phpbb_extension_manager, $phpbb_container, $phpbb_dispatcher;

		$this->helper = new cronstatus_helper();
		$this->page_title = $user->lang['ACP_CRON_STATUS_TITLE'];
		$this->tpl_name = 'acp_cronstatus';
		$user->add_lang_ext('boardtools/cronstatus', 'cronstatus');

		list($sk_config, $sd_config) = explode("|", $config['cronstatus_default_sort']);

		$sk = $request->variable('sk', $sk_config);
		$sd = $request->variable('sd', $sd_config);

		if ($sk != $sk_config || $sd != $sd_config)
		{
			$config->set("cronstatus_default_sort", $sk . "|" . $sd);
		}

		$template->assign_var('FONTAWESOME_NEEDED', version_compare($config['version'], '3.2.0', '<'));

		$action = $request->variable('action', '');
		switch ($action)
		{
			case 'details':
				$this->show_details($user, $request, $config, $phpbb_extension_manager, $template, $phpbb_root_path);

				if ($request->is_ajax())
				{
					$template->assign_vars(array(
						'IS_AJAX' => true,
					));
				}
				else
				{
					$template->assign_vars(array(
						'U_BACK' => $this->u_action,
					));
				}

				$this->tpl_name = 'acp_ext_details';
			break;

			default:
				$view_table = $request->variable('table', false);
				$cron_type = $request->variable('cron_type', '');

				if (!($request->is_ajax()) && $cron_type)
				{
					$url = append_sid($phpbb_root_path . 'cron.' . $phpEx, 'cron_type=' . $cron_type);
					$template->assign_var('RUN_CRON_TASK', '<img src="' . $url . '" width="1" height="1" alt="" />');
					meta_refresh(60, $this->u_action . '&amp;sk=' . $sk . '&amp;sd=' . $sd);
				}

				$task_array = array();
				$tasks = $phpbb_container->get('cron.manager')->get_tasks();

				// Fall back on the previous method for phpBB <3.1.9
				$cronlock = '';
				$rows = $phpbb_container->get('boardtools.cronstatus.listener')->get_cron_tasks($cronlock);

				if ($config['cronstatus_latest_task'])
				{
					$cronlock = $config['cronstatus_latest_task'];
				}

				if (sizeof($tasks) && is_array($rows))
				{
					/** @var \phpbb\cron\task\task $task */
					foreach ($tasks as $task)
					{
						$task_name = $task->get_name();
						if (empty($task_name))
						{
							continue;
						}

						$find = strpos($task_name, 'tidy');
						if ($find !== false)
						{
							$name = substr($task_name, $find + 5);
							$name = ($name == 'sessions') ? 'session' : $name;
							$task_date = (int) $this->helper->array_find($name . '_last_gc', $rows);
						}
						else if (strpos($task_name, 'prune_notifications'))
						{
							$task_date = (int) $this->helper->array_find('read_notification_last_gc', $rows);
							$name = 'read_notification';
						}
						else if (strpos($task_name, 'queue'))
						{
							$task_date = (int) $this->helper->array_find('last_queue_run', $rows);
							$name = 'queue_interval';
						}
						else
						{
							$name = (strrpos($task_name, ".") !== false) ? substr($task_name, strrpos($task_name, ".") + 1) : $task_name;
							$task_last_gc = $this->helper->array_find($name . '_last_gc', $rows);
							$task_date = ($task_last_gc !== false) ? (int) $task_last_gc : -1;
						}

						$new_task_interval = ($task_date > 0) ? $this->helper->array_find($name . (($name != 'queue_interval') ? '_gc' : ''), $rows) : 0;
						$new_task_date = ($new_task_interval > 0) ? $task_date + $new_task_interval : 0;

						/**
						 * Event to modify task variables before displaying cron information
						 *
						 * @event boardtools.cronstatus.modify_cron_task
						 * @var object task          Task object
						 * @var object task_name     Task name ($task->get_name())
						 * @var object name          Task name for new task date
						 * @var object task_date     Last task date
						 * @var object new_task_date Next task date
						 * @since 3.1.0-RC3
						 * @changed 3.1.1 Added new_task_date variable
						 */
						$vars = array('task', 'task_name', 'name', 'task_date', 'new_task_date');
						extract($phpbb_dispatcher->trigger_event('boardtools.cronstatus.modify_cron_task', compact($vars)));

						$task_array[] = array(
							'task_sort'       => ($task->is_ready()) ? 'ready' : 'not_ready',
							'display_name'    => $task_name,
							'task_date'       => $task_date,
							'task_date_print' => ($task_date == -1) ? $user->lang['CRON_TASK_AUTO'] : (($task_date) ? $user->format_date($task_date, $config['cronstatus_dateformat']) : $user->lang['CRON_TASK_NEVER_STARTED']),
							'new_date'        => $new_task_date,
							'new_date_print'  => ($new_task_date > 0) ? $user->format_date($new_task_date, $config['cronstatus_dateformat']) : '-',
							'task_ok'         => ($task_date > 0 && ($new_task_date > time())) ? false : true,
							'locked'          => ($config['cron_lock'] && $cronlock == $name) ? true : false,
						);
					}
					unset($tasks, $rows);

					$task_array = $this->helper->array_sort($task_array, $sk, (($sd == 'a') ? SORT_ASC : SORT_DESC));

					foreach ($task_array as $row)
					{
						$template->assign_block_vars($row['task_sort'], array(
							'DISPLAY_NAME'  => $row['display_name'],
							'TASK_DATE'     => $row['task_date_print'],
							'NEW_DATE'      => $row['new_date_print'],
							'TASK_OK'       => $row['task_ok'],
							'LOCKED'        => $row['locked'],
							'CRON_TASK_RUN' => ($request->is_ajax()) ? '' : (($row['display_name'] != $cron_type) ? '<a href="' . $this->u_action . '&amp;cron_type=' . $row['display_name'] . '&amp;sk=' . $sk . '&amp;sd=' . $sd . '" class="cron_run_link">' . $user->lang['CRON_TASK_RUN'] . '</a>' : '<span class="cron_running_update">' . $user->lang['CRON_TASK_RUNNING'] . '</span>'),
						));
					}
				}
				$template->assign_vars(array(
					'U_ACTION'   => $this->u_action,
					'U_NAME'     => $sk,
					'U_SORT'     => $sd,
					'CRON_URL'   => addslashes(append_sid($phpbb_root_path . 'cron.' . $phpEx, false, false)), // This is used in JavaScript (no &amp;)
					'VIEW_TABLE' => $view_table,
				));
		}
	}

	/**
	 * Assigns template parameters for details page
	 *
	 * @param \phpbb\user                      $user                    User object
	 * @param \phpbb\request\request_interface $request                 Request object
	 * @param \phpbb\config\config             $config                  Config object
	 * @param \phpbb\extension\manager         $phpbb_extension_manager Extension manager object
	 * @param \phpbb\template\template         $template                Template object
	 * @param string                           $phpbb_root_path         Path to phpBB root directory
	 */
	public function show_details(\phpbb\user $user, \phpbb\request\request_interface $request, \phpbb\config\config $config, \phpbb\extension\manager $phpbb_extension_manager, \phpbb\template\template $template, $phpbb_root_path)
	{
		$user->add_lang(array('install', 'acp/extensions', 'migrator'));
		$ext_name = 'boardtools/cronstatus';

		if (version_compare($config['version'], '3.2.0-dev', '>='))
		{
			/** @var \phpbb\extension\metadata_manager $md_manager */
			$md_manager = $phpbb_extension_manager->create_extension_metadata_manager($ext_name);

			if (version_compare($config['version'], '3.2.0', '>'))
			{
				$metadata = $md_manager->get_metadata('all');
				$this->helper->output_metadata_to_template($metadata, $template);
			}
			else
			{
				$md_manager->output_template_data($template);
			}
		}
		else
		{
			$md_manager = new \phpbb\extension\metadata_manager($ext_name, $config, $phpbb_extension_manager, $template, $user, $phpbb_root_path);
			try
			{
				$this->metadata = $md_manager->get_metadata('all');
			}
			catch (\phpbb\extension\exception $e)
			{
				trigger_error($e, E_USER_WARNING);
			}

			$md_manager->output_template_data();
		}

		try
		{
			$updates_available = $phpbb_extension_manager->version_check($md_manager, $request->variable('versioncheck_force', false), false, $config['extension_force_unstable'] ? 'unstable' : null);

			$template->assign_vars(array(
				'S_UP_TO_DATE'   => empty($updates_available),
				'S_VERSIONCHECK' => true,
				'UP_TO_DATE_MSG' => $user->lang(empty($updates_available) ? 'UP_TO_DATE' : 'NOT_UP_TO_DATE', $md_manager->get_metadata('display-name')),
			));

			foreach ($updates_available as $branch => $version_data)
			{
				$template->assign_block_vars('updates_available', $version_data);
			}
		}
		catch (\RuntimeException $e)
		{
			$template->assign_vars(array(
				'S_VERSIONCHECK_STATUS'    => $e->getCode(),
				'VERSIONCHECK_FAIL_REASON' => ($e->getMessage() !== $user->lang('VERSIONCHECK_FAIL')) ? $e->getMessage() : '',
			));
		}
	}
}
