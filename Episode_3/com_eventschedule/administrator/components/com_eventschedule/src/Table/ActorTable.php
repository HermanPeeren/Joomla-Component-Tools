<?php
/**
 * @package     EventSchedule
 * @subpackage  com_eventschedule
 * @version     1.0.0
 *
 * @copyright   Herman Peeren, Yepr
 * @license     GPL vs3+
 */

namespace Yepr\Component\EventSchedule\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;

/**
 * Actor Table class.
 */
class ActorTable extends Table
{
    /**
     * @var null|array  of integers: the ids of the events for this event
     * (only used when editing an event)
     */
    private $event_ids = null;

    /**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  Database connector object
	 */
	public function __construct(DatabaseDriver $db)
	{
		$this->typeAlias = 'com_eventschedule.actor';

		parent::__construct('#__eventschedule_actors', 'id', $db);
	}


    /**
     * Method to bind the actor and events data.
     *
     * @param   array  $array   The data to bind.
     * @param   mixed  $ignore  An array or space separated list of fields to ignore.
     *
     * @return  boolean  True on success, false on failure.
     */
    public function bind($array, $ignore = ''):bool
    {
        // Attempt to bind the data.
        $return = parent::bind($array, $ignore);

        // Set the eventIds from the comma separated string of event-ids
        if ($return && array_key_exists('event_ids', $array)) {
            $this->event_ids = $array['event_ids'];
        }

        return $return;
    }

    /**
     * Method to store a row in the database from the actor Table instance properties.
     *
     * If a primary key value is set the row with that primary key value will be updated with the instance property values.
     * If no primary key value is set a new row will be inserted into the database with the properties from the Table instance.
     *
     * The events for this actor will be updated (via a junction table)
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success.
     *
     * @see     \Joomla\CMS\Table\User handling of user groups
     */
    public function store($updateNulls = true):bool
    {
        // Get the table key and key value.
        $k   = $this->_tbl_key;
        $key = $this->$k;

        // Joomla core comment: @todo: This is a dumb way to handle the groups.
        // Store eventIds locally so as to not update directly.
        $eventIds = $this->event_ids;
        unset($this->event_ids);

        // Insert or update the object based on presence of a key value.
        if ($key) {
            // Already have a table key, update the row.
            $this->_db->updateObject($this->_tbl, $this, $this->_tbl_key, $updateNulls);
        } else {
            // Don't have a table key, insert the row.
            $this->_db->insertObject($this->_tbl, $this, $this->_tbl_key);
        }

        // Reset eventIds to the local object.
        $this->event_ids = $eventIds;

        $query = $this->_db->getQuery(true);

        // Store the eventId data if the actor data was saved.
        if (\is_array($this->event_ids) && \count($this->event_ids)) {
            $actorId = (int) $this->id;

            // Grab all eventIds for the actor, as is stored in the junction table
            $query->clear()
                ->select($this->_db->quoteName('event_id'))
                ->from($this->_db->quoteName('#__eventschedule_actor_event'))
                ->where($this->_db->quoteName('actor_id') . ' = :actorid')
                ->order($this->_db->quoteName('event_id') . ' ASC')
                ->bind(':actorid', $actorId, ParameterType::INTEGER);

            $this->_db->setQuery($query);
            $eventIdsInDb = $this->_db->loadColumn();

            // Loop through them and check if database contains something $this->eventIds does not
            if (\count($eventIdsInDb)) {
                $deleteEventIds = [];

                foreach ($eventIdsInDb as $storedEventId) {
                    if (\in_array($storedEventId, $this->event_ids)) {
                        // It already exists, no action required, so remove it from $eventIds
                        $eventIds = array_diff($eventIds,[$storedEventId]);
                    } else {
                        $deleteEventIds[] = (int) $storedEventId;
                    }
                }

                if (\count($deleteEventIds)) {
                    $query->clear()
                        ->delete($this->_db->quoteName('#__eventschedule_actor_event'))
                        ->where($this->_db->quoteName('actor_id') . ' = :actorId')
                        ->whereIn($this->_db->quoteName('event_id'), $deleteEventIds)
                        ->bind(':actorId', $actorId, ParameterType::INTEGER);

                    $this->_db->setQuery($query);
                    $this->_db->execute();
                }

                unset($deleteEventIds);
            }

            // If there is anything left in $eventIds it needs to be inserted
            if (\count($eventIds)) {
                // Set the new actor eventIds in the db junction table.
                $query->clear()
                    ->insert($this->_db->quoteName('#__eventschedule_actor_event'))
                    ->columns([$this->_db->quoteName('actor_id'), $this->_db->quoteName('event_id')]);

                foreach ($eventIds as $eventId) {
                    $query->values(
                        implode(
                            ',',
                            $query->bindArray(
                                [$this->id , $eventId],
                                [ParameterType::INTEGER, ParameterType::INTEGER]
                            )
                        )
                    );
                }

                $this->_db->setQuery($query);
                $this->_db->execute();
            }

            unset($eventIds);
        }

        return true;
    }

    /**
     * Method to delete an actor (and mappings of that actor to events) from the database.
     *
     * @param   integer  $actorId  An optional actor id.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @see     \Joomla\CMS\Table\User handling of user groups
     */
    public function delete($actorId = null):bool
    {
        // Set the primary key to delete.
        $k = $this->_tbl_key;

        if ($actorId) {

            $this->$k = (int) $actorId;
        }

        $key = (int) $this->$k;

        // Delete the actor from the event-actor junction table.
        $query = $this->_db->getQuery(true)
            ->delete($this->_db->quoteName('#__eventschedule_actor_event'))
            ->where($this->_db->quoteName('actor_id') . ' = :key')
            ->bind(':key', $key, ParameterType::INTEGER);
        $this->_db->setQuery($query);
        $this->_db->execute();

        // Delete the actor.
        $query->clear()
            ->delete($this->_db->quoteName($this->_tbl))
            ->where($this->_db->quoteName($this->_tbl_key) . ' = :key')
            ->bind(':key', $key, ParameterType::INTEGER);
        $this->_db->setQuery($query);
        $this->_db->execute();

        return true;
    }

	/**
	 * Get the type alias
	 *
	 * @return  string  The alias as described above
	 */
	public function getTypeAlias()
	{
		return $this->typeAlias;
	}
    
}