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
 * Section Table class.
 */
class SectionTable extends Table
{
    /**
     * @var null|array  of integers: the ids of the containers for this section
     * (only used when editing a section)
     */
    private $container_ids = null;
    
	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  Database connector object
	 */
	public function __construct(DatabaseDriver $db)
	{
		$this->typeAlias = 'com_eventschedule.section';

		parent::__construct('#__eventschedule_sections', 'id', $db);
	}

    /**
     * Method to bind the section and containers data.
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

        // Set this->container_ids
        if ($return && array_key_exists('container_ids', $array)) {
            $this->container_ids = $array['container_ids'];
        }

        return $return;
    }

    /**
     * Method to store a row in the database from the section Table instance properties.
     *
     * If a primary key value is set the row with that primary key value will be updated with the instance property values.
     * If no primary key value is set a new row will be inserted into the database with the properties from the Table instance.
     *
     * The containers for this section will be updated (via a junction table)
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
        // Store containerIds locally so as to not update directly.
        $containerIds = $this->container_ids;
        unset($this->container_ids);

        // Insert or update the object based on presence of a key value.
        if ($key) {
            // Already have a table key, update the row.
            $this->_db->updateObject($this->_tbl, $this, $this->_tbl_key, $updateNulls);
        } else {
            // Don't have a table key, insert the row.
            $this->_db->insertObject($this->_tbl, $this, $this->_tbl_key);
        }

        // Reset containerIds to the local object.
        $this->container_ids = $containerIds;

        $query = $this->_db->getQuery(true);

        // Store the containerId data if the section data was saved.
        if (\is_array($this->container_ids) && \count($this->container_ids)) {
            $sectionId = (int) $this->id;

            // Grab all containerIds for the section, as is stored in the junction table
            $query->clear()
                ->select($this->_db->quoteName('container_id'))
                ->from($this->_db->quoteName('#__eventschedule_container_section'))
                ->where($this->_db->quoteName('section_id') . ' = :sectionid')
                ->order($this->_db->quoteName('container_id') . ' ASC')
                ->bind(':sectionid', $sectionId, ParameterType::INTEGER);

            $this->_db->setQuery($query);
            $containerIdsInDb = $this->_db->loadColumn();

            // Loop through them and check if database contains something $this->containerIds does not
            if (\count($containerIdsInDb)) {
                $deleteContainerIds = [];

                foreach ($containerIdsInDb as $storedContainerId) {
                    if (\in_array($storedContainerId, $this->container_ids)) {
                        // It already exists, no action required, so remove it from $containerIds
                        $containerIds = array_diff($containerIds,[$storedContainerId]);
                    } else {
                        $deleteContainerIds[] = (int) $storedContainerId;
                    }
                }

                if (\count($deleteContainerIds)) {
                    $query->clear()
                        ->delete($this->_db->quoteName('#__eventschedule_container_section'))
                        ->where($this->_db->quoteName('section_id') . ' = :sectionId')
                        ->whereIn($this->_db->quoteName('container_id'), $deleteContainerIds)
                        ->bind(':sectionId', $sectionId, ParameterType::INTEGER);

                    $this->_db->setQuery($query);
                    $this->_db->execute();
                }

                unset($deleteContainerIds);
            }

            // If there is anything left in $containerIds it needs to be inserted
            if (\count($containerIds)) {
                // Set the new section containerIds in the db junction table.
                $query->clear()
                    ->insert($this->_db->quoteName('#__eventschedule_container_section'))
                    ->columns([$this->_db->quoteName('section_id'), $this->_db->quoteName('container_id')]);

                foreach ($containerIds as $containerId) {
                    $query->values(
                        implode(
                            ',',
                            $query->bindArray(
                                [$this->id , $containerId],
                                [ParameterType::INTEGER, ParameterType::INTEGER]
                            )
                        )
                    );
                }

                $this->_db->setQuery($query);
                $this->_db->execute();
            }

            unset($containerIds);
        }


        return true;
    }

    /**
     * Method to delete a section (and mappings of that section to containers) from the database.
     *
     * @param   integer  $sectionId  An optional section id.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @see     \Joomla\CMS\Table\User handling of user groups
     */
    public function delete($sectionId = null):bool
    {
        // Set the primary key to delete.
        $k = $this->_tbl_key;

        if ($sectionId) {

            $this->$k = (int) $sectionId;
        }

        $key = (int) $this->$k;

        // Delete the section from the container-section junction table.
        $query = $this->_db->getQuery(true)
            ->delete($this->_db->quoteName('#__eventschedule_container_section'))
            ->where($this->_db->quoteName('section_id') . ' = :key')
            ->bind(':key', $key, ParameterType::INTEGER);
        $this->_db->setQuery($query);
        $this->_db->execute();

        // Delete the section.
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