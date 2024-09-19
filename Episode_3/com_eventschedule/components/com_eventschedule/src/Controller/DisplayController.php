<?php
/*----------------------------------------------------------------------------------|  www.vdm.io  |----/
				TLWebdesign 
/-------------------------------------------------------------------------------------------------------/

	@version		1.0.2
	@build			11th June, 2024
	@created		23rd May, 2024
	@package		Event Schedule
	@subpackage		DisplayController.php
	@author			Tom van der Laan <https://tlwebdesign.nl>	
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
  ____  _____  _____  __  __  __      __       ___  _____  __  __  ____  _____  _  _  ____  _  _  ____ 
 (_  _)(  _  )(  _  )(  \/  )(  )    /__\     / __)(  _  )(  \/  )(  _ \(  _  )( \( )( ___)( \( )(_  _)
.-_)(   )(_)(  )(_)(  )    (  )(__  /(__)\   ( (__  )(_)(  )    (  )___/ )(_)(  )  (  )__)  )  (   )(  
\____) (_____)(_____)(_/\/\_)(____)(__)(__)   \___)(_____)(_/\/\_)(__)  (_____)(_)\_)(____)(_)\_) (__) 

/------------------------------------------------------------------------------------------------------*/
namespace TLWeb\Component\Eventschedule\Site\Controller;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use TLWeb\Joomla\Utilities\StringHelper;
use TLWeb\Joomla\Utilities\ArrayHelper as UtilitiesArrayHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Eventschedule master site display controller.
 *
 * @since   4.0
 */
class DisplayController extends BaseController
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached.
	 * @param   boolean  $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link InputFilter::clean()}.
	 *
	 * @return  DisplayController  This object to support chaining.
     * @since   1.5
	 */
	function display($cachable = false, $urlparams = false)
	{
		// set default view if not set
		$view          = $this->input->getCmd('view', 'schedule');
		$this->input->set('view', $view);
		$isEdit        = $this->checkEditView($view);
		$layout        = $this->input->get('layout', null, 'WORD');
		$id            = $this->input->getInt('id');
		$cachable      = true;

		// ensure that the view is not cashable if edit view or if user is logged in
		$user = $this->app->getIdentity();
		if ($user->get('id') || $this->input->getMethod() === 'POST' || $isEdit)
		{
			$cachable = false;
		}

		// Check for edit form.
		if ($isEdit && !$this->checkEditId('com_eventschedule.edit.'.$view, $id))
		{
			// check if item was opened from other than its own list view
			$ref    = $this->input->getCmd('ref', 0);
			$refid  = $this->input->getInt('refid', 0);

			// set redirect
			if ($refid > 0 && StringHelper::check($ref))
			{
				// redirect to item of ref
				$this->setRedirect(Route::_('index.php?option=com_eventschedule&view='.(string)$ref.'&layout=edit&id='.(int)$refid, false));
			}
			elseif (StringHelper::check($ref))
			{
				// redirect to ref
				 $this->setRedirect(Route::_('index.php?option=com_eventschedule&view='.(string)$ref, false));
			}
			else
			{
				// normal redirect back to the list default site view
				$this->setRedirect(Route::_('index.php?option=com_eventschedule&view=schedule', false));
			}

			// Somehow the person just went to the form - we don't allow that.
        	throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 403);
		}

		// we may need to make this more dynamic in the future. (TODO)
		$safeurlparams = array(
			'catid' => 'INT',
			'id' => 'INT',
			'cid' => 'ARRAY',
			'year' => 'INT',
			'month' => 'INT',
			'limit' => 'UINT',
			'limitstart' => 'UINT',
			'showall' => 'INT',
			'return' => 'BASE64',
			'filter' => 'STRING',
			'filter_order' => 'CMD',
			'filter_order_Dir' => 'CMD',
			'filter-search' => 'STRING',
			'print' => 'BOOLEAN',
			'lang' => 'CMD',
			'Itemid' => 'INT');

		// should these not merge?
		if (UtilitiesArrayHelper::check($urlparams))
		{
			$safeurlparams = UtilitiesArrayHelper::merge(array($urlparams, $safeurlparams));
		}

		parent::display($cachable, $safeurlparams);

		return $this;
	}

	protected function checkEditView($view)
	{
		if (StringHelper::check($view))
		{
			$views = [

				];
			// check if this is a edit view
			if (in_array($view,$views))
			{
				return true;
			}
		}
		return false;
	}
}
