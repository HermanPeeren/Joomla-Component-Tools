<?php
/*----------------------------------------------------------------------------------|  www.vdm.io  |----/
				TLWebdesign 
/-------------------------------------------------------------------------------------------------------/

	@version		1.0.2
	@build			11th June, 2024
	@created		23rd May, 2024
	@package		Event Schedule
	@subpackage		ScheduleModel.php
	@author			Tom van der Laan <https://tlwebdesign.nl>	
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
  ____  _____  _____  __  __  __      __       ___  _____  __  __  ____  _____  _  _  ____  _  _  ____ 
 (_  _)(  _  )(  _  )(  \/  )(  )    /__\     / __)(  _  )(  \/  )(  _ \(  _  )( \( )( ___)( \( )(_  _)
.-_)(   )(_)(  )(_)(  )    (  )(__  /(__)\   ( (__  )(_)(  )    (  )___/ )(_)(  )  (  )__)  )  (   )(  
\____) (_____)(_____)(_/\/\_)(____)(__)(__)   \___)(_____)(_/\/\_)(__)  (_____)(_)\_)(____)(_)\_) (__) 

/------------------------------------------------------------------------------------------------------*/
namespace TLWeb\Component\Eventschedule\Site\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\User\User;
use Joomla\Utilities\ArrayHelper;
use Joomla\Input\Input;
use TLWeb\Component\Eventschedule\Site\Helper\EventscheduleHelper;
use TLWeb\Component\Eventschedule\Site\Helper\RouteHelper;
use Joomla\CMS\Helper\TagsHelper;
use TLWeb\Joomla\Utilities\ArrayHelper as UtilitiesArrayHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Eventschedule List Model for Schedule
 *
 * @since  1.6
 */
class ScheduleModel extends ListModel
{
	/**
	 * Represents the current user object.
	 *
	 * @var   User  The user object representing the current user.
	 * @since 3.2.0
	 */
	protected User $user;

	/**
	 * The unique identifier of the current user.
	 *
	 * @var   int|null  The ID of the current user.
	 * @since 3.2.0
	 */
	protected ?int $userId;

	/**
	 * Flag indicating whether the current user is a guest.
	 *
	 * @var   int  1 if the user is a guest, 0 otherwise.
	 * @since 3.2.0
	 */
	protected int $guest;

	/**
	 * An array of groups that the current user belongs to.
	 *
	 * @var   array|null  An array of user group IDs.
	 * @since 3.2.0
	 */
	protected ?array $groups;

	/**
	 * An array of view access levels for the current user.
	 *
	 * @var   array|null  An array of access level IDs.
	 * @since 3.2.0
	 */
	protected ?array $levels;

	/**
	 * The application object.
	 *
	 * @var   CMSApplicationInterface  The application instance.
	 * @since 3.2.0
	 */
	protected CMSApplicationInterface $app;

	/**
	 * The input object, providing access to the request data.
	 *
	 * @var   Input  The input object.
	 * @since 3.2.0
	 */
	protected Input $input;

	/**
	 * The styles array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $styles = [
		'components/com_eventschedule/assets/css/site.css',
		'components/com_eventschedule/assets/css/schedule.css'
 	];

	/**
	 * The scripts array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $scripts = [
		'components/com_eventschedule/assets/js/site.js'
 	];

	/**
	 * A custom property for UIKit components. (not used unless you load v2)
	 */
	protected $uikitComp;

	/**
	 * Constructor
	 *
	 * @param   array                 $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
	 * @param   ?MVCFactoryInterface  $factory  The factory.
	 *
	 * @since   1.6
	 * @throws  \Exception
	 */
	public function __construct($config = [], MVCFactoryInterface $factory = null)
	{
		parent::__construct($config, $factory);

		$this->app ??= Factory::getApplication();
		$this->input ??= $this->app->getInput();

		// Set the current user for authorisation checks (for those calling this model directly)
		$this->user ??= $this->getCurrentUser();
		$this->userId = $this->user->get('id');
		$this->guest = $this->user->get('guest');
		$this->groups = $this->user->get('groups');
		$this->authorisedGroups = $this->user->getAuthorisedGroups();
		$this->levels = $this->user->getAuthorisedViewLevels();

		// will be removed
		$this->initSet = true;
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return   string  An SQL query
	 * @since    1.6
	 */
	protected function getListQuery()
	{
		// [VDM\Joomla\Componentbuilder\Compiler\Helper\Interpretation 4644] Make sure all records load, since no pagination allowed.
		$this->setState('list.limit', 0);
		// [VDM\Joomla\Componentbuilder\Compiler\Helper\Interpretation 4650] Get a db connection.
		$db = $this->getDatabase();

		// [VDM\Joomla\Componentbuilder\Compiler\Helper\Interpretation 4662] Create a new query object.
		$query = $db->getQuery(true);

		// [VDM\Joomla\Componentbuilder\Compiler\Helper\Interpretation 2437] Get from #__eventschedule_container as a
		$query->select($db->quoteName(
			array('a.id','a.asset_id','a.name','a.published','a.created_by','a.modified_by','a.created','a.modified','a.version','a.hits','a.ordering'),
			array('id','asset_id','name','published','created_by','modified_by','created','modified','version','hits','ordering')));
		$query->from($db->quoteName('#__eventschedule_container', 'a'));

		// [VDM\Joomla\Componentbuilder\Compiler\Helper\Interpretation 4698] return the query object
		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 * @since   1.6
	 */
	public function getItems()
	{
		$user = $this->user;
		// [VDM\Joomla\Componentbuilder\Compiler\Helper\Interpretation 3540] check if this user has permission to access item
		if (!$user->authorise('site.schedule.access', 'com_eventschedule'))
		{
			$app = Factory::getApplication();
			$app->enqueueMessage(Text::_('COM_EVENTSCHEDULE_NOT_AUTHORISED_TO_VIEW_SCHEDULE'), 'error');
			// [VDM\Joomla\Componentbuilder\Compiler\Helper\Interpretation 3535] redirect away to the home page if no access allowed.
			$app->redirect(Uri::root());
			return false;
		}
		// load parent items
		$items = parent::getItems();

		// Get the global params
		$globalParams = ComponentHelper::getParams('com_eventschedule', true);

		// [VDM\Joomla\Componentbuilder\Compiler\Helper\Interpretation 4729] Insure all item fields are adapted where needed.
		if (UtilitiesArrayHelper::check($items))
		{
			foreach ($items as $nr => &$item)
			{
				// [VDM\Joomla\Componentbuilder\Compiler\Helper\Interpretation 4738] Always create a slug for sef URL's
				$item->slug = ($item->id ?? '0') . (isset($item->alias) ? ':' . $item->alias : '');
				// [VDM\Joomla\Componentbuilder\Compiler\Helper\Interpretation 3029] set idContainerSectionB to the $item object.
				$item->idContainerSectionB = $this->getIdContainerSectionFfae_B($item->id);
			}
		}

		// return items
		return $items;
	}

	/**
	 * Method to get the styles that have to be included on the view
	 *
	 * @return  array    styles files
	 * @since   4.3
	 */
	public function getStyles(): array
	{
		return $this->styles;
	}

	/**
	 * Method to set the styles that have to be included on the view
	 *
	 * @return  void
	 * @since   4.3
	 */
	public function setStyles(string $path): void
	{
		$this->styles[] = $path;
	}

	/**
	 * Method to get the script that have to be included on the view
	 *
	 * @return  array    script files
	 * @since   4.3
	 */
	public function getScripts(): array
	{
		return $this->scripts;
	}

	/**
	 * Method to set the script that have to be included on the view
	 *
	 * @return  void
	 * @since   4.3
	 */
	public function setScript(string $path): void
	{
		$this->scripts[] = $path;
	}

	/**
	 * Method to get an array of Section Objects.
	 *
	 * @return mixed  An array of Section Objects on success, false on failure.
	 *
	 */
	public function getIdContainerSectionFfae_B($id)
	{
		// [VDM\Joomla\Componentbuilder\Compiler\Helper\Interpretation 4285] Get a db connection.
		$db = $this->getDatabase();

		// [VDM\Joomla\Componentbuilder\Compiler\Helper\Interpretation 4298] Create a new query object.
		$query = $db->getQuery(true);

		// [VDM\Joomla\Componentbuilder\Compiler\Helper\Interpretation 4303] Get from #__eventschedule_section as b
		$query->select($db->quoteName(
			array('b.id','b.asset_id','b.container','b.name','b.published','b.created_by','b.modified_by','b.created','b.modified','b.version','b.hits','b.ordering'),
			array('id','asset_id','container','name','published','created_by','modified_by','created','modified','version','hits','ordering')));
		$query->from($db->quoteName('#__eventschedule_section', 'b'));
		$query->where('b.container = ' . $db->quote($id));

		// [VDM\Joomla\Componentbuilder\Compiler\Helper\Interpretation 4382] Reset the query using our newly populated query object.
		$db->setQuery($query);
		$db->execute();

		// [VDM\Joomla\Componentbuilder\Compiler\Helper\Interpretation 4388] check if there was data returned
		if ($db->getNumRows())
		{
			return $db->loadObjectList();
		}
		return false;
	}


	/**
	 * Custom Method
	 *
	 * @return mixed  An array of objects on success, false on failure.
	 *
	 */
	public function getEvents()
	{

		// [VDM\Joomla\Componentbuilder\Compiler\Helper\Interpretation 3986] Get the global params
		$globalParams = ComponentHelper::getParams('com_eventschedule', true);
		// [VDM\Joomla\Componentbuilder\Compiler\Helper\Interpretation 4638] Get a db connection.
		$db = $this->getDatabase();

		// [VDM\Joomla\Componentbuilder\Compiler\Helper\Interpretation 4662] Create a new query object.
		$query = $db->getQuery(true);

		// [VDM\Joomla\Componentbuilder\Compiler\Helper\Interpretation 2437] Get from #__eventschedule_event as a
		$query->select('a.*');
		$query->from($db->quoteName('#__eventschedule_event', 'a'));

		// [VDM\Joomla\Componentbuilder\Compiler\Helper\Interpretation 4020] Reset the query using our newly populated query object.
		$db->setQuery($query);
		$items = $db->loadObjectList();

		if (empty($items))
		{
			return false;
		}

		// [VDM\Joomla\Componentbuilder\Compiler\Helper\Interpretation 4729] Insure all item fields are adapted where needed.
		if (UtilitiesArrayHelper::check($items))
		{
			foreach ($items as $nr => &$item)
			{
				// [VDM\Joomla\Componentbuilder\Compiler\Helper\Interpretation 4738] Always create a slug for sef URL's
				$item->slug = ($item->id ?? '0') . (isset($item->alias) ? ':' . $item->alias : '');
			}
		}
		// [VDM\Joomla\Componentbuilder\Compiler\Helper\Interpretation 4046] return items
		return $items;
	}
}
