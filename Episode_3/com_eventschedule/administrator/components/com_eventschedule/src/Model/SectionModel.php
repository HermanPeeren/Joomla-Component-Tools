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
 * Item Model for a Section.
 */
class SectionModel extends AdminModel
{
	 /**
	 * The type alias for this content type.
	 *
	 * @var    string
	 */
	public $typeAlias = 'com_eventschedule.section';

    /**
     * @var null|array  of containers for this section
     * (only used when displaying a section)
     */
    private $containers = null;

	/**
	 * Method to get the Section form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form|boolean        A Form object on success, false on failure
	 */
	public function getForm($data = [], $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm($this->typeAlias, 'section', ['control' => 'jform', 'load_data' => $loadData]);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

    /**
     * Get the containerIds for this section.
     * @param int|null $eventId
     * @return array
     */
    public function getContainerIds(int $eventId = null):array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('container_id'))
            ->from($db->quoteName('#__eventschedule_container_section', 'junction'))
            ->where($db->quoteName('section_id') . ' = :thisId')
            ->order($db->quoteName('container_id') . ' ASC')
            ->bind(':thisId', $eventId, ParameterType::INTEGER);

        $container_ids = $db->setQuery($query)->loadColumn() ?: [];

        return $container_ids;
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
		$data = $app->getUserState('com_eventschedule.edit.section.data', []);

		if (empty($data))
		{
			$data = $this->getItem();

            // Add foreign key ids (as an array)
            $data->container_ids = $this->getContainerIds($data->id);
		}

		return $data;
	}

    /**
     * Get the containers for this section.
     * @param int|null $eventId
     * @return array
     */
    public function getContainers(int $eventId = null):array
    {
        if (is_null($this->containers)) {
            $db    = $this->getDbo();
            $query = $db->getQuery(true)
                ->select($db->quoteName('container') . '.*')
                ->from($db->quoteName('#__eventschedule_container_section', 'junction'))
                ->join('LEFT',
                    $db->quoteName('#__eventschedule_containers', 'container'),
                    $db->quoteName('junction.container_id') . ' = ' . $db->quoteName('container.id'))
                ->where($db->quoteName('section_id') . ' = :thisId')
                ->order($db->quoteName('id') . ' ASC')
                ->bind(':thisId', $eventId, ParameterType::INTEGER);

            $this->containers = $db->setQuery($query)->loadObjectList() ?: [];
        }

        return $this->containers;
    }

	// Override getTable to be sure the right table name is used (especially when page name is different from entity name).
	public function getTable($name = 'Section', $prefix = '', $options = [])
	{
	    return parent::getTable($name, $prefix, $options);
	}

}