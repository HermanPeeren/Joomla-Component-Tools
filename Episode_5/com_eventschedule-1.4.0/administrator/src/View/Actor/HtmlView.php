<?php
/**
 * @version    CVS: 1.4.0
 * @package    Com_Eventschedule
 * @author     Herman Peeren <herman@yepr.nl>
 * @copyright  2024 Herman Peeren
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Yepr\Component\Eventschedule\Administrator\View\Actor;
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use \Joomla\CMS\Factory;
use \Yepr\Component\Eventschedule\Administrator\Helper\EventscheduleHelper;
use \Joomla\CMS\Language\Text;

/**
 * View class for a single Actor.
 *
 * @since  1.4.0
 */
class HtmlView extends BaseHtmlView
{
	protected $state;

	protected $item;

	protected $form;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode("\n", $errors));
		}
				$this->addToolbar();
		
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);

		$user  = Factory::getApplication()->getIdentity();
		$isNew = ($this->item->id == 0);

		if (isset($this->item->checked_out))
		{
			$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		}
		else
		{
			$checkedOut = false;
		}

		$canDo = EventscheduleHelper::getActions();

		ToolbarHelper::title(Text::_('COM_EVENTSCHEDULE_TITLE_ACTOR'), "jcc fa-person");

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit') || ($canDo->get('core.create'))))
		{
			ToolbarHelper::apply('actor.apply', 'JTOOLBAR_APPLY');
			ToolbarHelper::save('actor.save', 'JTOOLBAR_SAVE');
		}

		if (!$checkedOut && ($canDo->get('core.create')))
		{
			ToolbarHelper::custom('actor.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}

		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create'))
		{
			ToolbarHelper::custom('actor.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		}

		// Button for version control
		if ($this->state->params->get('save_history', 1) && $user->authorise('core.edit')) {
			ToolbarHelper::versions('com_eventschedule.actor', $this->item->id);
		}

		if (empty($this->item->id))
		{
			ToolbarHelper::cancel('actor.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			ToolbarHelper::cancel('actor.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
