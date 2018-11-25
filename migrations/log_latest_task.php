<?php
/**
 *
 * @package       cronstatus
 * @copyright (c) 2014 John Peskens (http://ForumHulp.com) and Igor Lavrov (https://github.com/LavIgor)
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace boardtools\cronstatus\migrations;

class log_latest_task extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['cronstatus_version']) && version_compare($this->config['cronstatus_version'], '3.1.3', '>=');
	}

	static public function depends_on()
	{
		return array('\boardtools\cronstatus\migrations\cronstatus');
	}

	public function update_data()
	{
		return array(
			array('config.update', array('cronstatus_version', '3.1.3')),
			array('config.add', array('cronstatus_latest_task', '')),
		);
	}
}
