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
 * Event Table class.
 */
class EventTable extends Table
{
    /**
     * @var null|array of integers: the ids of the actors for this event
     * (only used when editing an event)
     * = null if not fetched from db
     */
    private $actor_ids = null;

    /**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  Database connector object
	 */
	public function __construct(DatabaseDriver $db)
	{
		$this->typeAlias = 'com_eventschedule.event';

		parent::__construct('#__eventschedule_events', 'id', $db);
	}

    /**
     * Method to bind the event and actors data.
     *
     * @param   array  $array   The data to bind.
     * @param   mixed  $ignore  An array or space separated list of fields to ignore.
     *
     * @return  boolean  True on success, false on failure.
     */
    public function bind($array, $ignore = ''):bool
    {
        // Put multiple subform field-name in $this->_jsonEncode array
        if ( ( !empty( $array[ 'locators' ] ) && ( is_array( $array[ 'locators' ] ) ) ) ) {
            $this->_jsonEncode[] = "locators";
        }

        // Attempt to bind the data.
        $return = parent::bind($array, $ignore);

        // Set the actor_ids from form-input
        if ($return && array_key_exists('actor_ids', $array)) {
            $this->actor_ids = $array['actor_ids'];
        }

        return $return;
    }

    /**
     * Method to store a row in the database from the event Table instance properties.
     *
     * If a primary key value is set the row with that primary key value will be updated with the instance property values.
     * If no primary key value is set a new row will be inserted into the database with the properties from the Table instance.
     * 
     * The actors for this event will be updated (via a junction table)
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
        // Store actorIds locally so as to not update directly.
        $actorIds = $this->actor_ids;
        unset($this->actor_ids);

        // Insert or update the object based on presence of a key value.
        if ($key) {
            // Already have a table key, update the row.
            $this->_db->updateObject($this->_tbl, $this, $this->_tbl_key, $updateNulls);
        } else {
            // Don't have a table key, insert the row.
            $this->_db->insertObject($this->_tbl, $this, $this->_tbl_key);
        }

        // Reset actorIds to the local object.
        $this->actor_ids = $actorIds;

        $query = $this->_db->getQuery(true);

        // Store the actorId data if the event data was saved.
        if (\is_array($this->actor_ids) && \count($this->actor_ids)) {
            $eventId = (int) $this->id;

            // Grab all actorIds for the event, as is stored in the junction table
            $query->clear()
                ->select($this->_db->quoteName('actor_id'))
                ->from($this->_db->quoteName('#__eventschedule_actor_event'))
                ->where($this->_db->quoteName('event_id') . ' = :eventid')
                ->order($this->_db->quoteName('actor_id') . ' ASC')
                ->bind(':eventid', $eventId, ParameterType::INTEGER);

            $this->_db->setQuery($query);
            $actorIdsInDb = $this->_db->loadColumn();

            // Loop through them and check if database contains something $this->actorIds does not
            if (\count($actorIdsInDb)) {
                $deleteActorIds = [];

                foreach ($actorIdsInDb as $storedActorId) {
                    if (\in_array($storedActorId, $this->actor_ids)) {
                        // It already exists, no action required, so remove it from $actorIds
                        $actorIds = array_diff($actorIds,[$storedActorId]);
                    } else {
                        $deleteActorIds[] = (int) $storedActorId;
                    }
                }

                if (\count($deleteActorIds)) {
                    $query->clear()
                        ->delete($this->_db->quoteName('#__eventschedule_actor_event'))
                        ->where($this->_db->quoteName('event_id') . ' = :eventId')
                        ->whereIn($this->_db->quoteName('actor_id'), $deleteActorIds)
                        ->bind(':eventId', $eventId, ParameterType::INTEGER);

                    $this->_db->setQuery($query);
                    $this->_db->execute();
                }
                
                unset($deleteActorIds);
            }

            // If there is anything left in $actorIds it needs to be inserted
            if (\count($actorIds)) {
                // Set the new event actorIds in the db junction table.
                $query->clear()
                    ->insert($this->_db->quoteName('#__eventschedule_actor_event'))
                    ->columns([$this->_db->quoteName('event_id'), $this->_db->quoteName('actor_id')]);

                foreach ($actorIds as $actorId) {
                    $query->values(
                        implode(
                            ',',
                            $query->bindArray(
                                [$this->id , $actorId],
                                [ParameterType::INTEGER, ParameterType::INTEGER]
                            )
                        )
                    );
                }

                $this->_db->setQuery($query);
                $this->_db->execute();
            }

            unset($actorIds);
        }

        return true;
    }

    /**
     * Method to delete an event (and mappings of that event to actors) from the database.
     *
     * @param   integer  $eventId  An optional event id.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @see     \Joomla\CMS\Table\User handling of user groups
     */
    public function delete($eventId = null):bool
    {
        // Set the primary key to delete.
        $k = $this->_tbl_key;

        if ($eventId) {
            
            $this->$k = (int) $eventId;
        }

        $key = (int) $this->$k;

        // Delete the event from the actor-event junction table.
        $query = $this->_db->getQuery(true)
            ->delete($this->_db->quoteName('#__eventschedule_actor_event'))
            ->where($this->_db->quoteName('event_id') . ' = :key')
            ->bind(':key', $key, ParameterType::INTEGER);
        $this->_db->setQuery($query);
        $this->_db->execute();

        // Delete the event.
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