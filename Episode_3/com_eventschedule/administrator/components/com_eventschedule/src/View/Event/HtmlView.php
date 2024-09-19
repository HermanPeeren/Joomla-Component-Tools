<?php
/**
 * @package    EventSchedule
 * @subpackage eventschedule
 * @version    1.0.0
 *
 * @copyright  Herman Peeren, Yepr
 * @license    GPL vs3+
 */

namespace Yepr\Component\EventSchedule\Administrator\View\Event;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;
use Exception;

use Yepr\Component\EventSchedule\Administrator\Model\EventModel;

class HtmlView extends BaseHtmlView
{
	/**
	 * The Form object
	 *
	 * @var    Form
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var    object (this is actually a Table object)
	 */
	protected $item;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
     * @return  void
     * @throws  Exception
	 */
	public function display($tpl = null)
	{
		/** @var EventModel $model */
		$model       = $this->getModel();

		$this->item  = $model->getItem();
		$this->form  = $model->getForm();

		// todo: If we are forcing a language in modal (used for associations).

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Set up Joomla's toolbar.
	 *
	 * @return  void
	 * @throws  Exception
	 */
	protected function addToolbar(): void
	{
		Factory::getApplication()->input->set('hidemainmenu', true);

		$user = Factory::getUser();
		$userId = $user->id;

		$isNew = ($this->item->id == 0);

		ToolbarHelper::title($isNew ? Text::_('COM_EVENTSCHEDULE_MANAGER_EVENT_NEW') : Text::_('COM_EVENTSCHEDULE_MANAGER_EVENT_EDIT'), 'address event');
		// todo: choose icon in model

		// Since we don't track these assets at the item level, use the category id. todo: categories & access control
		// $canDo = ContentHelper::getActions('com_eventschedule', 'category', $this->item->catid);

		// ACCESS CONTROL for now is on the extension-level...

		// Build the actions for new and existing records.
		if ($isNew) {
			// For new records, check the create permission.
			//if ($isNew && (count($user->getAuthorisedCategories('com_eventschedule', 'core.create')) > 0)) {
			if ($isNew) {
				ToolbarHelper::apply('event.apply');
				ToolbarHelper::saveGroup(
					[
						['save', 'event.save'],
						['save2new', 'event.save2new']
					],
					'btn-success'
				);
			}

			ToolbarHelper::cancel('event.cancel');
		} else {
			// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
			//$itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId);
			$toolbarButtons = [];

			// Can't save the record if it's not editable
			//if ($itemEditable) {
				ToolbarHelper::apply('event.apply');
				$toolbarButtons[] = ['save', 'event.save'];

				// We can save this record, but check the create permission to see if we can return to make a new one.
				//if ($canDo->get('core.create')) {
					$toolbarButtons[] = ['save2new', 'event.save2new'];
				//}
			//}

			// If checked out, we can still save
			//if ($canDo->get('core.create')) {
				$toolbarButtons[] = ['save2copy', 'event.save2copy'];
			//}

			ToolbarHelper::saveGroup(
				$toolbarButtons,
				'btn-success'
			);

			if (Associations::isEnabled() && ComponentHelper::isEnabled('com_associations')) {
				ToolbarHelper::custom('event.editAssociations', 'contract', 'contract', 'JTOOLBAR_ASSOCIATIONS', false, false);
			}

			ToolbarHelper::cancel('event.cancel', 'JTOOLBAR_CLOSE');
		}

		// Todo: Help-pages
		//ToolbarHelper::divider();
		//ToolbarHelper::inlinehelp();
		//ToolbarHelper::help('', false, 'http://example.org');
	}

}