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
 * Container Table class.
 */
class ContainerTable extends Table
{
    /**
     * @var null|array of sections for this section
     * (only used when displaying a section)
     */
    private $sections = null;

	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  Database connector object
	 */
	public function __construct(DatabaseDriver $db)
	{
		$this->typeAlias = 'com_eventschedule.container';

		parent::__construct('#__eventschedule_containers', 'id', $db);
	}

    /**
     * Get the sections for this container.
     * @return array
     */
    public function getSections():array
    {
        if (is_null($this->sections)) {
            $db    = $this->getDbo();
            $query = $db->getQuery(true)
                ->select($db->quoteName('section') . '.*')
                ->from($db->quoteName('#__eventschedule_container_section', 'junction'))
                ->join('LEFT',
                    $db->quoteName('#__eventschedule_sections', 'section'),
                    $db->quoteName('junction.section_id') . ' = ' . $db->quoteName('section.id'))
                ->where($db->quoteName('container_id') . ' = :thisId')
                ->order($db->quoteName('id') . ' ASC')
                ->bind(':thisId', $this->id, ParameterType::INTEGER);

            $this->sections = $db->setQuery($query)->loadObjectList() ?: [];
        }

        return $this->sections;
    }

    /**
     * Method to delete a container (and mappings of that container to sections) from the database.
     *
     * @param   integer  $containerId  An optional container id.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @see     \Joomla\CMS\Table\User handling of user groups
     */
    public function delete($containerId = null):bool
    {
        // Set the primary key to delete.
        $k = $this->_tbl_key;

        if ($containerId) {

            $this->$k = (int) $containerId;
        }

        $key = (int) $this->$k;

        // Delete the container from the container_section junction table.
        $query = $this->_db->getQuery(true)
            ->delete($this->_db->quoteName('#__eventschedule_container_section'))
            ->where($this->_db->quoteName('container_id') . ' = :key')
            ->bind(':key', $key, ParameterType::INTEGER);
        $this->_db->setQuery($query);
        $this->_db->execute();

        // Delete the container.
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