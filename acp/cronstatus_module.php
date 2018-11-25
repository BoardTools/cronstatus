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
	public $u_action;

	function main($id, $mode)
	{
		/** @var \phpbb\config\config $config */
		/** @var \phpbb\request\request_interface $request */
		/** @var \phpbb\extension\manager $phpbb_extension_manager */
		global $config, $user, $template, $request, $phpbb_root_path, $phpEx, $phpbb_extension_manager, $phpbb_container, $phpbb_dispatcher;

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

		$action = $request->variable('action', '');
		switch ($action)
		{
			case 'details':

				$user->add_lang(array('install', 'acp/extensions', 'migrator'));
				$ext_name = 'boardtools/cronstatus';

				if (version_compare($config['version'], '3.2.0-dev', '>='))
				{
					/** @var \phpbb\extension\metadata_manager $md_manager */
					$md_manager = $phpbb_extension_manager->create_extension_metadata_manager($ext_name);

					if (version_compare($config['version'], '3.2.0', '>'))
					{
						$metadata = $md_manager->get_metadata('all');
						$this->output_metadata_to_template($metadata, $template);
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

				$tasks = $task_array = array();
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

						$task_date = -1;
						$find = strpos($task_name, 'tidy');
						if ($find !== false)
						{
							$name = substr($task_name, $find + 5);
							$name = ($name == 'sessions') ? 'session' : $name;
							$task_date = (int) $this->array_find($name . '_last_gc', $rows);
						}
						else if (strpos($task_name, 'prune_notifications'))
						{
							$task_date = (int) $this->array_find('read_notification_last_gc', $rows);
							$name = 'read_notification';
						}
						else if (strpos($task_name, 'queue'))
						{
							$task_date = (int) $this->array_find('last_queue_run', $rows);
							$name = 'queue_interval';
						}
						else
						{
							$name = (strrpos($task_name, ".") !== false) ? substr($task_name, strrpos($task_name, ".") + 1) : $task_name;
							$task_last_gc = $this->array_find($name . '_last_gc', $rows);
							$task_date = ($task_last_gc !== false) ? (int) $task_last_gc : -1;
						}

						$new_task_interval = ($task_date > 0) ? $this->array_find($name . (($name != 'queue_interval') ? '_gc' : ''), $rows) : 0;
						$new_task_date = ($new_task_interval > 0) ? $task_date + $new_task_interval : 0;

						/**
						 * Event to modify task variables before displaying cron information
						 *
						 * @event   boardtools.cronstatus.modify_cron_task
						 * @var    object    task            Task object
						 * @var    object    task_name        Task name ($task->get_name())
						 * @var    object    name            Task name for new task date
						 * @var    object    task_date        Last task date
						 * @var    object    new_task_date    Next task date
						 * @since   3.1.0-RC3
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

					$task_array = $this->array_sort($task_array, $sk, (($sd == 'a') ? SORT_ASC : SORT_DESC));

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
				$cron_url = append_sid($phpbb_root_path . 'cron.' . $phpEx, false, false); // This is used in JavaScript (no &amp;).
				addslashes($cron_url);
				$template->assign_vars(array(
					'U_ACTION'   => $this->u_action,
					'U_NAME'     => $sk,
					'U_SORT'     => $sd,
					'CRON_URL'   => $cron_url,
					'VIEW_TABLE' => $view_table,
				));
		}
	}

	/**
	 * Recursive array sorting based on the second level key
	 *
	 * @param array  $array Array to be sorted
	 * @param string $on    Second level key for sorting
	 * @param int    $order Sorting direction (SORT_ASC, SORT_DESC)
	 * @return array
	 */
	function array_sort($array, $on, $order = SORT_ASC)
	{
		$new_array = array();
		$sortable_array = array();

		if (sizeof($array) > 0)
		{
			foreach ($array as $k => $v)
			{
				if (is_array($v))
				{
					foreach ($v as $k2 => $v2)
					{
						if ($k2 == $on)
						{
							$sortable_array[$k] = $v2;
						}
					}
				}
				else
				{
					$sortable_array[$k] = $v;
				}
			}

			switch ($order)
			{
				case SORT_ASC:
					asort($sortable_array);
				break;
				case SORT_DESC:
					arsort($sortable_array);
				break;
			}

			foreach ($sortable_array as $k => $v)
			{
				$new_array[$k] = $array[$k];
			}
		}
		return $new_array;
	}

	/**
	 * Performs the search for a specific config_name and
	 * returns the corresponding config_value or false if nothing was found
	 * Works like array_search with partial matches
	 *
	 * @param string $needle   The config_name to search for
	 * @param array  $haystack The array to search in
	 * @return mixed
	 */
	public function array_find($needle, $haystack)
	{
		if (!is_array($haystack))
		{
			return false;
		}
		foreach ($haystack as $key => $item)
		{
			if (strpos($item['config_name'], $needle) !== false)
			{
				return $haystack[$key]['config_value'];
			}
		}
		return false;
	}

	/**
	 * Outputs extension metadata into the template
	 *
	 * @param array                    $metadata Array with all metadata for the extension
	 * @param \phpbb\template\template $template phpBB template object
	 */
	public function output_metadata_to_template($metadata, $template)
	{
		$template->assign_vars(array(
			'META_NAME'        => $metadata['name'],
			'META_TYPE'        => $metadata['type'],
			'META_DESCRIPTION' => (isset($metadata['description'])) ? $metadata['description'] : '',
			'META_HOMEPAGE'    => (isset($metadata['homepage'])) ? $metadata['homepage'] : '',
			'META_VERSION'     => $metadata['version'],
			'META_TIME'        => (isset($metadata['time'])) ? $metadata['time'] : '',
			'META_LICENSE'     => $metadata['license'],

			'META_REQUIRE_PHP'      => (isset($metadata['require']['php'])) ? $metadata['require']['php'] : '',
			'META_REQUIRE_PHP_FAIL' => (isset($metadata['require']['php'])) ? false : true,

			'META_REQUIRE_PHPBB'      => (isset($metadata['extra']['soft-require']['phpbb/phpbb'])) ? $metadata['extra']['soft-require']['phpbb/phpbb'] : '',
			'META_REQUIRE_PHPBB_FAIL' => (isset($metadata['extra']['soft-require']['phpbb/phpbb'])) ? false : true,

			'META_DISPLAY_NAME' => (isset($metadata['extra']['display-name'])) ? $metadata['extra']['display-name'] : '',
		));

		foreach ($metadata['authors'] as $author)
		{
			$template->assign_block_vars('meta_authors', array(
				'AUTHOR_NAME'     => $author['name'],
				'AUTHOR_EMAIL'    => (isset($author['email'])) ? $author['email'] : '',
				'AUTHOR_HOMEPAGE' => (isset($author['homepage'])) ? $author['homepage'] : '',
				'AUTHOR_ROLE'     => (isset($author['role'])) ? $author['role'] : '',
			));
		}
	}
}
