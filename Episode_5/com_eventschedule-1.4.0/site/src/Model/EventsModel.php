<?php
/**
 * @version    CVS: 1.4.0
 * @package    Com_Eventschedule
 * @author     Herman Peeren <herman@yepr.nl>
 * @copyright  2024 Herman Peeren
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Yepr\Component\Eventschedule\Site\Model;
// No direct access.
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\CMS\Layout\FileLayout;
use \Joomla\Database\ParameterType;
use \Joomla\Utilities\ArrayHelper;
use \Yepr\Component\Eventschedule\Site\Helper\EventscheduleHelper;


/**
 * Methods supporting a list of Eventschedule records.
 *
 * @since  1.4.0
 */
class EventsModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see    JController
	 * @since  1.4.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'created_by', 'a.created_by',
				'modified_by', 'a.modified_by',
				'event_name', 'a.event_name',
				'short_description', 'a.short_description',
				'long_description', 'a.long_description',
				'duration', 'a.duration',
				'event_type', 'a.event_type',
				'locators', 'a.locators',
				'actors', 'a.actors',
				'state', 'a.state',
				'schedule', 'a.schedule',
			);
		}

		parent::__construct($config);
	}

	

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// List state information.
		parent::populateState("a.id", "ASC");

		$app = Factory::getApplication();
		$list = $app->getUserState($this->context . '.list');

		$value = $app->getUserState($this->context . '.list.limit', $app->get('list_limit', 25));
		$list['limit'] = $value;
		
		$this->setState('list.limit', $value);

		$value = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.start', $value);

		$ordering  = $this->getUserStateFromRequest($this->context .'.filter_order', 'filter_order', "a.id");
		$direction = strtoupper($this->getUserStateFromRequest($this->context .'.filter_order_Dir', 'filter_order_Dir', "ASC"));
		
		if(!empty($ordering) || !empty($direction))
		{
			$list['fullordering'] = $ordering . ' ' . $direction;
		}

		$app->setUserState($this->context . '.list', $list);

		

		$context = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $context);

		// Split context into component and optional section
		if (!empty($context))
		{
			$parts = FieldsHelper::extract($context);

			if ($parts)
			{
				$this->setState('filter.component', $parts[0]);
				$this->setState('filter.section', $parts[1]);
			}
		}
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  DatabaseQuery
	 *
	 * @since   1.4.0
	 */
	protected function getListQuery()
	{
			// Create a new query object.
			$db    = $this->getDbo();
			$query = $db->getQuery(true);

			// Select the required fields from the table.
			$query->select(
						$this->getState(
								'list.select', 'DISTINCT a.*'
						)
				);

			$query->from('`#__eventschedule_events` AS a');
			
		// Join over the users for the checked out user.
		$query->select('uc.name AS uEditor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the created by field 'created_by'
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

		// Join over the created by field 'modified_by'
		$query->join('LEFT', '#__users AS modified_by ON modified_by.id = a.modified_by');
		// Join over the foreign key 'event_type'
		$query->select('`#__eventschedule_event_types_4107522`.`event_type_name` AS eventtypes_fk_value_4107522');
		$query->join('LEFT', '#__eventschedule_event_types AS #__eventschedule_event_types_4107522 ON #__eventschedule_event_types_4107522.`id` = a.`event_type`');
		// Join over the foreign key 'actors'
		$query->select('`#__eventschedule_actors_4111410`.`actor_name` AS actors_fk_value_4111410');
		$query->join('LEFT', '#__eventschedule_actors AS #__eventschedule_actors_4111410 ON #__eventschedule_actors_4111410.`id` = a.`actors`');
		// Join over the foreign key 'schedule'
		$query->select('`#__eventschedule_eventschedules_4107541`.`title` AS eventschedules_fk_value_4107541');
		$query->join('LEFT', '#__eventschedule_eventschedules AS #__eventschedule_eventschedules_4107541 ON #__eventschedule_eventschedules_4107541.`id` = a.`schedule`');
			
		if (!Factory::getApplication()->getIdentity()->authorise('core.edit', 'com_eventschedule'))
		{
			$query->where('a.state = 1');
		}
		else
		{
			$query->where('(a.state IN (0, 1))');
		}

			// Filter by search in title
			$search = $this->getState('filter.search');

			if (!empty($search))
			{
				if (stripos($search, 'id:') === 0)
				{
					$query->where('a.id = ' . (int) substr($search, 3));
				}
				else
				{
					$search = $db->Quote('%' . $db->escape($search, true) . '%');
					$query->where('( a.long_description LIKE ' . $search . ' )');
				}
			}
			

		// Filtering actors
		$filter_actors = $this->state->get("filter.actors");

		if ($filter_actors)
		{
			$query->where("FIND_IN_SET('" . $db->escape($filter_actors) . "',a.actors)");
		}

		// Filtering schedule
		$filter_schedule = $this->state->get("filter.schedule");

		if ($filter_schedule)
		{
			$query->where("a.`schedule` = '".$db->escape($filter_schedule)."'");
		}

			
			
			// Add the list ordering clause.
			$orderCol  = $this->state->get('list.ordering', "a.id");
			$orderDirn = $this->state->get('list.direction', "ASC");

			if ($orderCol && $orderDirn)
			{
				$query->order($db->escape($orderCol . ' ' . $orderDirn));
			}

			return $query;
	}

	/**
	 * Method to get an array of data items
	 *
	 * @return  mixed An array of data on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();
		
		foreach ($items as $item)
		{

			if (isset($item->event_type))
			{

				$values    = explode(',', $item->event_type);
				$textValue = array();

				foreach ($values as $value)
				{
					$db    = $this->getDbo();
					$query = $db->getQuery(true);
					$query
						->select('`#__eventschedule_event_types_4107522`.`event_type_name`')
						->from($db->quoteName('#__eventschedule_event_types', '#__eventschedule_event_types_4107522'))
						->where($db->quoteName('#__eventschedule_event_types_4107522.id') . ' = '. $db->quote($db->escape($value)));

					$db->setQuery($query);
					$results = $db->loadObject();

					if ($results)
					{
						$textValue[] = $results->event_type_name;
					}
				}

				$item->event_type = !empty($textValue) ? implode(', ', $textValue) : $item->event_type;
			}


			if (isset($item->actors))
			{

				$values    = explode(',', $item->actors);
				$textValue = array();

				foreach ($values as $value)
				{
					$db    = $this->getDbo();
					$query = $db->getQuery(true);
					$query
						->select('`#__eventschedule_actors_4111410`.`actor_name`')
						->from($db->quoteName('#__eventschedule_actors', '#__eventschedule_actors_4111410'))
						->where($db->quoteName('#__eventschedule_actors_4111410.id') . ' = '. $db->quote($db->escape($value)));

					$db->setQuery($query);
					$results = $db->loadObject();

					if ($results)
					{
						$textValue[] = $results->actor_name;
					}
				}

				$item->actors = !empty($textValue) ? implode(', ', $textValue) : $item->actors;
			}


			if (isset($item->schedule))
			{

				$values    = explode(',', $item->schedule);
				$textValue = array();

				foreach ($values as $value)
				{
					$db    = $this->getDbo();
					$query = $db->getQuery(true);
					$query
						->select('`#__eventschedule_eventschedules_4107541`.`title`')
						->from($db->quoteName('#__eventschedule_eventschedules', '#__eventschedule_eventschedules_4107541'))
						->where($db->quoteName('#__eventschedule_eventschedules_4107541.id') . ' = '. $db->quote($db->escape($value)));

					$db->setQuery($query);
					$results = $db->loadObject();

					if ($results)
					{
						$textValue[] = $results->title;
					}
				}

				$item->schedule = !empty($textValue) ? implode(', ', $textValue) : $item->schedule;
			}

		}

		return $items;
	}

	/**
	 * Overrides the default function to check Date fields format, identified by
	 * "_dateformat" suffix, and erases the field if it's not correct.
	 *
	 * @return void
	 */
	protected function loadFormData()
	{
		$app              = Factory::getApplication();
		$filters          = $app->getUserState($this->context . '.filter', array());
		$error_dateformat = false;

		foreach ($filters as $key => $value)
		{
			if (strpos($key, '_dateformat') && !empty($value) && $this->isValidDate($value) == null)
			{
				$filters[$key]    = '';
				$error_dateformat = true;
			}
		}

		if ($error_dateformat)
		{
			$app->enqueueMessage(Text::_("COM_EVENTSCHEDULE_SEARCH_FILTER_DATE_FORMAT"), "warning");
			$app->setUserState($this->context . '.filter', $filters);
		}

		return parent::loadFormData();
	}

	/**
	 * Checks if a given date is valid and in a specified format (YYYY-MM-DD)
	 *
	 * @param   string  $date  Date to be checked
	 *
	 * @return bool
	 */
	private function isValidDate($date)
	{
		$date = str_replace('/', '-', $date);
		return (date_create($date)) ? Factory::getDate($date)->format("Y-m-d") : null;
	}
}
