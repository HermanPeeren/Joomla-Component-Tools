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
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;

/**
 * Methods supporting a list of Sections records.
 */
class SectionsModel extends ListModel
{
    /**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     \JControllerLegacy
	 */
	public function __construct($config = [])
	{
		// Add filter fields
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = [
							'id','section.id'
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

		// Select the required fields from the sectionstable.
		$query->select([
			$db->quoteName('section.section_name'),
			$db->quoteName('section.id')
		]);
		$query->from($db->quoteName('#__eventschedule_sections', 'section'));

		// Add the presentation fields from the joined tables (N.B.: is now MySql-specific)
		$query->select('GROUP_CONCAT('. $db->quoteName('container.container_name') . " SEPARATOR ', ') AS 'containers'");
        // Join with the junction table
		$query->join(
			'LEFT',
			$db->quoteName('#__eventschedule_container_section', 'junction'), $db->quoteName('section.id') . ' = ' . $db->quoteName('junction.section_id')
		);
        // And with the containers table
		$query->join(
			'LEFT',
			$db->quoteName('#__eventschedule_containers', 'container'), $db->quoteName('container.id') . ' = ' . $db->quoteName('junction.container_id')
		);
        // Group per section
        $query->group($db->quoteName('section.id'));

		return $query;
	}
    
}