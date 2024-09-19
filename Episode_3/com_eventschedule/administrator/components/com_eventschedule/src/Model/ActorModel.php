<?php
/**
 * @package    EventSchedule
 * @subpackage com_eventschedule
 * @version    1.0.0
 *
 * @copyright  Herman Peeren, Yepr
 * @license    GPL vs3+
 */

namespace Yepr\Component\EventSchedule\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Form\Form;
use Joomla\Database\ParameterType;

/**
 * Item Model for an Actor.
 */
class ActorModel extends AdminModel
{
	 /**
	 * The type alias for this content type.
	 *
	 * @var    string
	 */
	public $typeAlias = 'com_eventschedule.actor';

    /**
     * @var null|array  of events for this event
     * (only used when displaying an event)
     */
    private $events = null;

	/**
	 * Method to get the Actor form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form|boolean        A Form object on success, false on failure
	 */
	public function getForm($data = [], $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm($this->typeAlias, 'actor', ['control' => 'jform', 'load_data' => $loadData]);

		if (empty($form))
		{
            return false;
		}

		return $form;
	}

    /**
     * Get the eventIds for this actor.
     * @param int|null $actorId
     * @return array
     */
    public function getEventIds(int $actorId = null):array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('event_id'))
            ->from($db->quoteName('#__eventschedule_actor_event', 'junction'))

            ->where($db->quoteName('actor_id') . ' = :thisId')
            ->order($db->quoteName('event_id') . ' ASC')
            ->bind(':thisId', $actorId, ParameterType::INTEGER);

        $eventIds = $db->setQuery($query)->loadColumn() ?: [];

        return $eventIds;
    }

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 */
	protected function loadFormData()
	{
		$app = Factory::getApplication();

		// Check the session for previously entered form data.
		$data = $app->getUserState('com_eventschedule.edit.actor.data', []);

		if (empty($data))
		{
			$data = $this->getItem();

            // Add foreign key ids (as an array)
            $data->events = $this->getEventIds($data->id);
		}

		return $data;
	}

    /**
     * Get the events for this actor. Only needed for details-display.
     * @param int|null $actorId
     * @return array
     */
    public function getEvents(int $actorId = null):array
    {
        if (is_null($this->events)) {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select($db->quoteName('event') . '.*')
                ->from($db->quoteName('#__eventschedule_actor_event', 'junction'))
                ->join('LEFT',
                    $db->quoteName('#__eventschedule_events', 'event'),
                    $db->quoteName('junction.event_id') . ' = ' . $db->quoteName('event.id'))
                ->where($db->quoteName('actor_id') . ' = :thisId')
                ->order($db->quoteName('id') . ' ASC')
                ->bind(':thisId', $actorId, ParameterType::INTEGER);

            $this->events = $db->setQuery($query)->loadObjectList() ?: [];
        }

        return $this->events;
    }

	// Override getTable to be sure the right table name is used (especially when page name is different from entity name).
	public function getTable($name = 'Actor', $prefix = '', $options = [])
	{
	    return parent::getTable($name, $prefix, $options);
	}

}