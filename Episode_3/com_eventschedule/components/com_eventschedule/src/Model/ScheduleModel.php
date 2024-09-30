<?php
/**
 * @package    EventSchedule
 * @subpackage com_eventschedule
 * @version    1.0.0
 *
 * @copyright  Herman Peeren, Yepr
 * @license    GPL vs3+
 */

namespace Yepr\Component\EventSchedule\Site\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\QueryInterface;
use Joomla\Registry\Registry;

/**
 * Custom List Model for Schedule
 */
class ScheduleModel extends ListModel
{
    private $events           = null;
    private $containerOptions = null;
    private $sectionOptions   = null;
    private $params           = null;
    private $timeInterval     = null;
    private $timeSlots        = null;
    private $eventTypes        = null;
    private $unscheduled      = null;
    
	/**
	 * The application object.
	 *
	 * @var   CMSApplicationInterface  The application instance.
	 * @since 3.2.0
	 */
	protected CMSApplicationInterface $app;

	/**
	 * Constructor
	 *
	 * @param   array                $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
	 * @param   MVCFactoryInterface  $factory  The factory.
     *
	 * @throws  \Exception
	 */
	public function __construct($config = [], MVCFactoryInterface $factory = null)
	{
		parent::__construct($config, $factory);

		$this->app = Factory::getApplication();
	}

    /**
     * Get the events in a nested array
     * @return array
     *
     * @since version
     */
    public function getEvents():array
    {
        if (is_null($this->events)) {
            // Prepare the events-array
            $this->events = [];
            foreach ($this->getContainerOptions() as $containerOption)
            {
                $this->events[$containerOption->id] = [];
            }

            // Get the events to place them in the events-array
            $items = $this->getItems();

            $this->unscheduled = [];
            // Get the events per containerOption and sectionOption; the raw events are in $this->items
            foreach ($items as $item) {

                // Add an array of actors. Put the whole actor object in it, so we have all data available in a template
                $item->actors = $this->getActors($item->id);

                // If this event doesn't have a locator, then it is unscheduled.
                if (empty($item->locators)) {
                    $this->unscheduled[] = $item;
                } else {
                    // Expand the json-encoded locator-subfields
                    // The LOCATORS will be stored as an array of subforms
                    // Each subform will be stored as an object (with fields)
                    $item->locators = json_decode($item->locators, false);

                    // Add the event in the events array, including and indexed by locator-data.
                    // The item will be placed multiple times if there are multiple locators.
                    foreach ($item->locators as $locator) {
                        // Add the locator-data to the event
                        $event = clone $item;
                        $event->container_id = $locator->container_id;
                        $event->section_id = $locator->section_id;
                        $event->starttime = $locator->starttime;
                        $event->endtime = $locator->endtime;

                        // Add it to the events array, creating the section under the container if not exists.
                        if (!array_key_exists($event->section_id, $this->events[$event->container_id])) {
                            $this->events[$event->container_id][$event->section_id] = [];
                        }

                        // Put the event in the events-array.
                        $this->events[$event->container_id][$event->section_id][$event->starttime] = $event;
                    }

                }

            }

        }

        return $this->events;
    }

    /**
     * Get the containerOptions in an array
     * @return array
     */
    public function getContainerOptions():array
    {
        if (is_null($this->containerOptions)) {
            // Create a new query object.
            $db = $this->getDatabase();
            $query = $db->getQuery(true);

            // Select the required fields from the table.
            $query->select([
                $db->quoteName('container.container_name'),
                $db->quoteName('container.id')
            ]);
            $query->from($db->quoteName('#__eventschedule_containers', 'container'));

            $db->setQuery($query);
            $this->containerOptions = $db->loadObjectList() ?: [];
            // todo: set as an array of container-id => container_name
        }

        return $this->containerOptions;
    }

    /**
     * Get the sectionOptions in an array
     * @return array
     */
    public function getSectionOptions():array
    {
        if (is_null($this->sectionOptions)) {
            // Create a new query object.
            $db = $this->getDatabase();
            $query = $db->getQuery(true);

            // Select the required fields from the table.
            $query->select([
                $db->quoteName('section.section_name'),
                $db->quoteName('section.id')
            ]);
            $query->from($db->quoteName('#__eventschedule_sections', 'section'));

            $db->setQuery($query);
            $this->sectionOptions = $db->loadObjectList() ?: [];
            // todo: set as an array of section-id => section_name
        }


        return $this->sectionOptions;
    }

    /**
     * Get the time interval for the schedule.
     * @return integer
     */
    public function getTimeInterval():int
    {
        if (is_null($this->timeInterval)) {
            $params = $this->getParams();
            $timeIntervalMap = [1 => 30, 2 =>15, 3 => 10, 4 => 5];
            $this->timeInterval = $timeIntervalMap[(int) $params->get('height-px-per-minute')];
        }

        return $this->timeInterval;
    }

    /**
     * Get the component's params (for general display properties)
     * @return array
     */
    public function getTimeslots():array
    {
        if (is_null($this->timeSlots)) {
            // Get the timeslots
            $params = $this->getParams();
            $startTime = new \DateTime($params->get('schedule_start'));
            $endTime = new \DateTime($params->get('schedule_end'));
            // Translate pixels per minute to time-interval on timeline
            $timeInterval = $this->getTimeInterval();
            $this->timeSlots = [];
            // todo: round starttime and endtime to units of timeInterval

            while ($startTime <= $endTime) {
                $this->timeSlots[] = $startTime->format('H:i');
                $startTime->modify('+' . $timeInterval . ' minutes');
            }
        }

        return $this->timeSlots;
    }

    /**
     * Get the eventTypes in an array
     * @return array
     */
    public function getEventTypes():array
    {
        if (is_null($this->eventTypes)) {
            // Create a new query object.
            $db = $this->getDatabase();
            $query = $db->getQuery(true);

            // Select the required fields from the table.
            $query->select([
                $db->quoteName('event_type.event_type_name'),
                $db->quoteName('event_type.id'),
                $db->quoteName('event_type.css_class'),
                $db->quoteName('event_type.background_color')
            ]);
            $query->from($db->quoteName('#__eventschedule_event_types', 'event_type'));

            $db->setQuery($query);
            $this->eventTypes = $db->loadObjectList() ?: [];
            // todo: set as an array of event_type-id => event_type_name
        }


        return $this->eventTypes;
    }

    /**
     * Get the actors who do an event in an array
     *
     * @param  int    $event_id
     * @return array
     */
    public function getActors(int $event_id):array
    {
       
            // Create a new query object.
            $db = $this->getDatabase();
            $query = $db->getQuery(true);

            // Select the required fields from the table.
            $query->select([
                $db->quoteName('actor.actor_name'),
                $db->quoteName('actor.id'),
                $db->quoteName('actor.biography'),
                $db->quoteName('actor.actor_image')
            ]);
            $query->from($db->quoteName('#__eventschedule_actors', 'actor'));
            $query->join('INNER', $db->quoteName('#__eventschedule_actor_event', 'junction'),
                $db->quoteName('junction.actor_id') . ' = ' . $db->quoteName('actor.id'));
            $query->where($db->quoteName('junction.event_id') . ' = ' . $db->quote($event_id));

            $db->setQuery($query);
            $actors = $db->loadObjectList() ?: [];

        return $actors;
    }

    /**
     * Get the component's params (for general display properties)
     * @return Registry
     */
    public function getParams():Registry
    {
        if (is_null($this->params)) {
            $this->params = $this->app->getParams();
        }

        return $this->params;
    }

    /**
     * Build an SQL query to load the list data for the getItems()-method.
     * We basicly get the events and the joined event types.
     * In getEvents() we use these items to expand the locators and to add the actors.
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
            $db->quoteName('event_type.event_type_name'),
            $db->quoteName('event.event_type_id'),
            $db->quoteName('event_type.css_class'),
            $db->quoteName('event_type.background_color'),
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
