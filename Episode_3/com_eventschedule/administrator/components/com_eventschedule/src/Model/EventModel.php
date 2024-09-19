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

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Form\Form;
use Joomla\Database\ParameterType;

/**
 * Item Model for a Event.
 */
class EventModel extends AdminModel
{
	 /**
	 * The type alias for this content type.
	 *
	 * @var    string
	 */
	public $typeAlias = 'com_eventschedule.event';

    /**
     * @var null|array of actors for this event
     * (only used when displaying an event)
     *  = null if not fetched from db
     */
    private $actors = null;

	/**
	 * Method to get the Event form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form|boolean         A Form object on success, false on failure
	 */
	public function getForm($data = [], $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm($this->typeAlias, 'event', ['control' => 'jform', 'load_data' => $loadData]);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

    /**
     * Get the actor_ids for this event.
     * @param int|null $eventId
     * @return array
     */
    public function getActorIds(int $eventId = null):array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('actor_id'))
            ->from($db->quoteName('#__eventschedule_actor_event', 'junction'))
            ->where($db->quoteName('event_id') . ' = :thisId')
            ->order($db->quoteName('actor_id') . ' ASC')
            ->bind(':thisId', $eventId, ParameterType::INTEGER);

        $actor_ids = $db->setQuery($query)->loadColumn() ?: [];      

        return $actor_ids;
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
		$data = $app->getUserState('com_eventschedule.edit.event.data', []);

		if (empty($data))
		{
			$data = $this->getItem();

            // Add foreign key ids
            $data->actor_ids = $this->getActorIds($data->id);
		}

		return $data;
	}

    /**
     * Get the actors for this event. Only needed for detail-display.
     * @param int|null $eventId
     * @return array of Actor-objects
     */
    public function getActors(int $eventId = null):array
    {
        if (is_null($this->actors)) {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select($db->quoteName('actor') . '.*')
                ->from($db->quoteName('#__eventschedule_actor_event', 'junction'))
                ->join('LEFT',
                    $db->quoteName('#__eventschedule_actors', 'actor'),
                    $db->quoteName('junction.actor_id') . ' = ' . $db->quoteName('actor.id'))
                ->where($db->quoteName('event_id') . ' = :thisId')
                ->order($db->quoteName('actor_name') . ' ASC')
                ->bind(':thisId', $eventId, ParameterType::INTEGER);

            $this->actors = $db->setQuery($query)->loadObjectList() ?: [];
        }

        return $this->actors;
    }

	// Override getTable to be sure the right table name is used (especially when page name is different from entity name).
	public function getTable($name = 'Event', $prefix = '', $options = [])
	{
	    return parent::getTable($name, $prefix, $options);
	}

}