<?php
/**
 * @package    EventSchedule
 * @subpackage EventSchedule
 * @version    1.0.0
 *
 * @copyright  Herman Peeren, Yepr
 * @license    GPL vs3+
 */

namespace Yepr\Component\EventSchedule\Administrator\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Categories\CategoryServiceInterface; // todo: only add when you want categories in this extension
use Joomla\CMS\Categories\CategoryServiceTrait;
use Joomla\CMS\Component\Router\RouterServiceInterface;  // todo: only add when you want router in this extension
use Joomla\CMS\Component\Router\RouterServiceTrait;

use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Joomla\CMS\Tag\TagServiceInterface; // todo: only add when you want tags in this extension
use Joomla\CMS\Tag\TagServiceTrait;
use Psr\Container\ContainerInterface;

/**
 * Component class for com_eventschedule
 * todo: optionally add FieldsServiceInterface, AssociationServiceInterface, WorkflowServiceInterface
 * todo: also make CategoryServiceInterface and TagServiceInterface (and RouterServiceInterface?) optional
 */
class EventscheduleComponent extends MVCComponent implements
	BootableExtensionInterface, CategoryServiceInterface, TagServiceInterface, RouterServiceInterface
{
	use HTMLRegistryAwareTrait;
	use RouterServiceTrait;
	use CategoryServiceTrait
	{
		CategoryServiceTrait::getTableNameForSection insteadof TagServiceTrait;
		CategoryServiceTrait::getStateColumnForSection insteadof TagServiceTrait;
	}
	use TagServiceTrait;
	//use AssociationServiceTrait;
	//use WorkflowServiceTrait;

	// todo: use this array to indicate what functionallity is supported
    /** @var array Supported functionality */
    protected $supportedFunctionality = [
		'core.featured' => false,
		'core.state'    => false,
	];

	/**
	 * Booting the extension. This is the function to set up the environment of the extension like
	 * registering new class loaders, etc.
	 *
	 * If required, some initial set up can be done from services of the container, eg.
	 * registering HTML services.
	 *
	 * @param   ContainerInterface  $container  The container
	 *
	 * @return  void
	 */
	public function boot(ContainerInterface $container)
	{
		// In ATS the db is injected into the ATS-Html-object (=???)
		//$db = $container->get('DatabaseDriver');
		//$this->getRegistry()->register('ats', new ATS($db));

		//$this->getRegistry()->register('eventscheduleadministrator', new AdministratorService());
		// todo: shouldn't that be an icon per entity or per page/view?
		//$this->getRegistry()->register('eventscheduleicon', new Icon($container->get(SiteApplication::class)));
	}


	/**
	 * Adds Count Items for Category Manager.
	 * todo: can we leave this out if no categories are used?
	 *
	 * @param   \stdClass[]  $items    The category objects
	 * @param   string       $section  The section
	 *
	 * @return  void
	 */
	public function countItems(array $items, string $section)
	{
		try {
			$config = (object) [
				'related_tbl'   => $this->getTableNameForSection($section),
				'state_col'     => 'published',
				'group_col'     => 'catid',
				'relation_type' => 'category_or_group',
			];

			ContentHelper::countRelations($items, $config);// ContentHelper is copied from com_content...
		} catch (\Exception $e) {
			// Ignore it
		}
	}

		/**
		 * Returns the table for the count items functions for the given section.
		 * todo: can we leave this out if no categories are used?
		 *
		 * @param   string  $section  The section
		 *
		 * @return  string|null
		 */
		protected function getTableNameForSection(string $section = null)
	{
		// todo: this eventschedule_details is probably wrong: should be some entity?
		return ($section === 'category' ? 'categories' : 'eventschedule_details');
	}

		/**
		 * Returns the state column for the count items functions for the given section.
		 * todo: only need this if there is a state column
		 *
		 * @param   string  $section  The section
		 *
		 * @return  string|null
		 */
		protected function getStateColumnForSection(string $section = null)
	{
		return 'published';
	}

}