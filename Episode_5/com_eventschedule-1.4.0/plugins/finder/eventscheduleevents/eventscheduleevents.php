<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Finder.event
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Table\Table;
use \Joomla\Component\Finder\Administrator\Indexer\Adapter;
use \Joomla\Component\Finder\Administrator\Indexer\Helper;
use \Joomla\Component\Finder\Administrator\Indexer\Indexer;
use \Joomla\Component\Finder\Administrator\Indexer\Result;
use \Joomla\Database\DatabaseQuery;
use \Joomla\Registry\Registry;
use \Joomla\CMS\Router\Route;

/**
 * event finder plugin.
 *
 * @package  Joomla.Plugin
 * @since    1.4.0
 */
class PlgFinderEventscheduleevents extends Adapter
{
	/**
	 * The plugin identifier.
	 *
	 * @var    string
	 * @since  1.4.0
	 */
	protected $context = 'Event';

	/**
	 * The extension name.
	 *
	 * @var    string
	 * @since  1.4.0
	 */
	protected $extension = 'com_eventschedule';

	/**
	 * The sublayout to use when rendering the results.
	 *
	 * @var    string
	 * @since  1.4.0
	 */
	protected $layout = 'event';

	/**
	 * The type of event that the adapter indexes.
	 *
	 * @var    string
	 * @since  1.4.0
	 */
	protected $type_title = 'Event';

	/**
	 * The table name.
	 *
	 * @var    string
	 * @since  1.4.0
	 */
	protected $table = '#__eventschedule_events';

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  1.4.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Method to setup the indexer to be run.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 */
	protected function setup()
	{
		return true;
	}

	/**
	 * Smart Search after save event method.
	 * Reindexes the link information for an event that has been saved.
	 * It also makes adjustments if the access level of an item or the
	 * category to which it belongs has changed.
	 *
	 * @param   string   $context  The context of the event passed to the plugin.
	 * @param   Table    $row      A Table object.
	 * @param   boolean  $isNew    True if the event has just been created.
	 *
	 * @return  void
	 *
	 * @since   1.4.0
	 * @throws  Exception on database error.
	 */
	public function onFinderAfterSave($context, $row, $isNew): void
	{
		// We only want to handle event here.
		if ($context === 'com_eventschedule.event')
		{
			// Reindex the item.
			$this->reindex($row->id);
		}
	}

	/**
	 * Method to update the link information for items that have been changed
	 * from outside the edit screen. This is fired when the item is published,
	 * unpublished, archived, or unarchived from the list view.
	 *
	 * @param   string   $context  The context for the event passed to the plugin.
	 * @param   array    $pks      An array of primary key ids of the event that has changed state.
	 * @param   integer  $value    The value of the state that the event has been changed to.
	 *
	 * @return  void
	 *
	 * @since   1.4.0
	 */
	public function onFinderChangeState($context, $pks, $value)
	{
		// We only want to handle event here.
		if ($context === 'com_eventschedule.event')
		{
			$this->itemStateChange($pks, $value);
		}
	}

	/**
	 * Method to get a SQL query to load the published and access states for
	 * an event and category.
	 *
	 * @return  QueryInterface  A database object.
	 *
	 * @since   1.4.0
	 */
	protected function getStateQuery()
	{
		$query = $this->db->getQuery(true);

		// Item ID
		$query->select('a.id');
		$query->select('1 AS access');
		$query->from($this->table . ' AS a');

		return $query;
	}

	/**
	 * Method to index an item. The item must be a Result object.
	 *
	 * @param   Result  $item  The item to index as a Result object.
	 *
	 * @return  void
	 *
	 * @since   1.4.0
	 * @throws  Exception on database error.
	 */
	protected function index(Result $item)
	{
		$item->setLanguage();

		// Check if the extension is enabled.
		if (ComponentHelper::isEnabled($this->extension) === false)
		{
			return;
		}

		$item->url = $this->getUrl($item->id, $this->extension, $this->layout);
		$item->route = $item->url;


		$item->context = 'com_eventschedule.event';

		$this->indexer->index($item);
	}

	/**
	 * Method to get the SQL query used to retrieve the list of event items.
	 *
	 * @param   mixed  $query  A DatabaseQuery object or null.
	 *
	 * @return  DatabaseQuery  A database object.
	 *
	 * @since   1.4.0
	 */
	protected function getListQuery($query = null)
	{
		$db = $this->db;

		// Check if we can use the supplied SQL query.
		$query = $query instanceof DatabaseQuery ? $query : $db->getQuery(true)
		->select('a.id, a.event_name AS title, a.event_name AS summary, a.state AS state, 1 AS access');


		$query->from($this->table . ' as a');
		return $query;
	}
}
