<?php
/**
 * @version    CVS: 1.4.0
 * @package    Com_Eventschedule
 * @author     Herman Peeren <herman@yepr.nl>
 * @copyright  2024 Herman Peeren
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Yepr\Component\Eventschedule\Administrator\Model;
// No direct access.
defined('_JEXEC') or die;

use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\Database\ParameterType;
use \Joomla\Utilities\ArrayHelper;
use Yepr\Component\Eventschedule\Administrator\Helper\EventscheduleHelper;

/**
 * Methods supporting a list of Events records.
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
	* @see        JController
	* @since      1.6
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
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// List state information.
		parent::populateState("a.id", "ASC");

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
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string A store id.
	 *
	 * @since   1.4.0
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');

		
		return parent::getStoreId($id);
		
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
		
		// Join over the users for the checked out user
		$query->select("uc.name AS uEditor");
		$query->join("LEFT", "#__users AS uc ON uc.id=a.checked_out");

		// Join over the user field 'created_by'
		$query->select('`created_by`.name AS `created_by`');
		$query->join('LEFT', '#__users AS `created_by` ON `created_by`.id = a.`created_by`');

		// Join over the user field 'modified_by'
		$query->select('`modified_by`.name AS `modified_by`');
		$query->join('LEFT', '#__users AS `modified_by` ON `modified_by`.id = a.`modified_by`');
		// Join over the foreign key 'event_type'
		$query->select('`#__eventschedule_event_types_4107522`.`event_type_name` AS eventtypes_fk_value_4107522');
		$query->join('LEFT', '#__eventschedule_event_types AS #__eventschedule_event_types_4107522 ON #__eventschedule_event_types_4107522.`id` = a.`event_type`');
		// Join over the foreign key 'actors'
		$query->select('`#__eventschedule_actors_4111410`.`actor_name` AS actors_fk_value_4111410');
		$query->join('LEFT', '#__eventschedule_actors AS #__eventschedule_actors_4111410 ON #__eventschedule_actors_4111410.`id` = a.`actors`');
		// Join over the foreign key 'schedule'
		$query->select('`#__eventschedule_eventschedules_4107541`.`title` AS eventschedules_fk_value_4107541');
		$query->join('LEFT', '#__eventschedule_eventschedules AS #__eventschedule_eventschedules_4107541 ON #__eventschedule_eventschedules_4107541.`id` = a.`schedule`');
		

		// Filter by published state
		$published = $this->getState('filter.state');

		if (is_numeric($published))
		{
			$query->where('a.state = ' . (int) $published);
		}
		elseif (empty($published))
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

		if ($filter_actors !== null && !empty($filter_actors))
		{
			$query->where("FIND_IN_SET('" . $db->escape($filter_actors) . "',a.actors)");
		}

		// Filtering schedule
		$filter_schedule = $this->state->get("filter.schedule");

		if ($filter_schedule !== null && !empty($filter_schedule))
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
	 * Get an array of data items
	 *
	 * @return mixed Array of data items on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();
		
		foreach ($items as $oneItem)
		{

			if (isset($oneItem->event_type))
			{
				$values    = explode(',', $oneItem->event_type);
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

				$oneItem->event_type = !empty($textValue) ? implode(', ', $textValue) : $oneItem->event_type;
			}

			if (isset($oneItem->actors))
			{
				$values    = explode(',', $oneItem->actors);
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

				$oneItem->actors = !empty($textValue) ? implode(', ', $textValue) : $oneItem->actors;
			}

			if (isset($oneItem->schedule))
			{
				$values    = explode(',', $oneItem->schedule);
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

				$oneItem->schedule = !empty($textValue) ? implode(', ', $textValue) : $oneItem->schedule;
			}
		}

		return $items;
	}
}
