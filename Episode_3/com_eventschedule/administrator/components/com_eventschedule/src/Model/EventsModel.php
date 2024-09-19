<?php
/**
 * @package    EventSchedule
 * @subpackage eventschedule
 * @version    1.0.0
 *
 * @copyright  Herman Peeren, Yepr
 * @license    GPL vs3+
 */

namespace Yepr\Component\EventSchedule\Administrator\Model;

defined('_JEXEC') or die;

use Exception;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\QueryInterface;

/**
 * Methods supporting a list of Events records.
 */
class EventsModel extends ListModel
{
    /**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 */
	public function __construct($config = [])
	{
		// Add filter fields
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = [
							'id','event.id'
			];
		}

		parent::__construct($config);
	}


	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  QueryInterface
	 */
	protected function getListQuery():QueryInterface
	{
		// Create a new query object.
		$db = $this->getDatabase();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select([
                    $db->quoteName('event.event_name'),
                    $db->quoteName('event.short_description'),
                    $db->quoteName('event.long_description'),
                    $db->quoteName('event.duration'),
                    $db->quoteName('event_type.event_type_name', 'event_type'),
                    $db->quoteName('event.locators'),// JSON-string; unpack if you want to display this...
                    $db->quoteName('event.id')
                    ])
                ->from($db->quoteName('#__eventschedule_events', 'event'))
                ->join(
                    'LEFT',
                             $db->quoteName('#__eventschedule_event_types', 'event_type'), 
                    $db->quoteName('event.event_type_id') . ' = ' . $db->quoteName('event_type.id')
                  );

		return $query;
	}
    
}