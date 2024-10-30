<?php
/**
 * @package    CForms
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-04-18
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

class CformsApi
{
	/**
	 * Task to execute
	 *
	 * @var    string
	 *
	 * @since  1.0.0
	 */
	protected $task;

	/**
	 * The request
	 *
	 * @var    string
	 *
	 * @since  1.0.0
	 */
	protected $request;

	/**
	 * Database con
	 *
	 * @var    wpdb
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $db;

	/**
	 * The default task (can be overwritten in sub class)
	 *
	 * @var    string
	 *
	 * @since  1.0.0
	 */
	protected $defaultTask = '_';

	/**
	 * The output, defaults to JSON
	 *
	 * @var     string
	 *
	 * @since   1.0.0
	 */
	protected $output = 'json';

	/**
	 * CformsApi constructor.
	 *
	 * @param   array  $config  Optional Configuration array
	 *
	 * @since   1.0.0
	 */
	public function __construct($config = array())
	{
		global $wpdb;

		$this->db = $wpdb;

		$this->request = CformsInput::getCmd('request');
	}

	/**
	 * Execute the given task in the class
	 *
	 * @param   string  $task  The task
	 *
	 * @since   1.0.0
	 *
	 * @return  void
	 */
	public function execute($task)
	{
		$this->task = $task;

		if (empty($task))
		{
			$this->task = $this->defaultTask;
		}

		$execute = strtolower($this->task);

		$result = $this->$execute();

		// TODO move to presentation logic
		if ($this->output == 'json')
		{
			echo json_encode($result);
		}
		else
		{
			echo $result;
		}
	}

	/**
	 * Default fallback function
	 *
	 * @return  mixed
	 *
	 * @since   1.0.0
	 */
	public function _()
	{
		return array('error' => 'No Request');
	}
}
