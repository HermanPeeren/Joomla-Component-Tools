<?php
/*----------------------------------------------------------------------------------|  www.vdm.io  |----/
				TLWebdesign 
/-------------------------------------------------------------------------------------------------------/

	@version		1.0.2
	@build			11th June, 2024
	@created		23rd May, 2024
	@package		Event Schedule
	@subpackage		HtmlView.php
	@author			Tom van der Laan <https://tlwebdesign.nl>	
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
  ____  _____  _____  __  __  __      __       ___  _____  __  __  ____  _____  _  _  ____  _  _  ____ 
 (_  _)(  _  )(  _  )(  \/  )(  )    /__\     / __)(  _  )(  \/  )(  _ \(  _  )( \( )( ___)( \( )(_  _)
.-_)(   )(_)(  )(_)(  )    (  )(__  /(__)\   ( (__  )(_)(  )    (  )___/ )(_)(  )  (  )__)  )  (   )(  
\____) (_____)(_____)(_/\/\_)(____)(__)(__)   \___)(_____)(_/\/\_)(__)  (_____)(_)\_)(____)(_)\_) (__) 

/------------------------------------------------------------------------------------------------------*/
namespace TLWeb\Component\Eventschedule\Site\View\Schedule;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper as Html;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Document\Document;
use TLWeb\Component\Eventschedule\Site\Helper\HeaderCheck;
use TLWeb\Component\Eventschedule\Site\Helper\EventscheduleHelper;
use TLWeb\Component\Eventschedule\Site\Helper\RouteHelper;
use TLWeb\Joomla\Utilities\StringHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Eventschedule Html View class for the Schedule
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 * @since  1.6
	 */
	public function display($tpl = null)
	{
		// get combined params of both component and menu
		$this->app ??= Factory::getApplication();
		$this->params = $this->app->getParams();
		$this->menu = $this->app->getMenu()->getActive();
		$this->styles = $this->get('Styles');
		$this->scripts = $this->get('Scripts');
		// get the user object
		$this->user ??= $this->app->getIdentity();
		// [VDM\Joomla\Componentbuilder\Compiler\Helper\Interpretation 4960] Initialise variables.
		$this->items = $this->get('Items');
		$this->events = $this->get('Events');

		// [VDM\Joomla\Componentbuilder\Compiler\Helper\Interpretation 5009] Set the toolbar
		$this->addToolBar();

		// [VDM\Joomla\Componentbuilder\Compiler\Helper\Interpretation 5012] Set the html view document stuff
		$this->_prepareDocument();

		// [VDM\Joomla\Componentbuilder\Compiler\Helper\Interpretation 5039] Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode(PHP_EOL, $errors), 500);
		}

		parent::display($tpl);
	}

	/**
	 * Prepare some document related stuff.
	 *
	 * @return  void
	 * @since   1.6
	 */
	protected function _prepareDocument(): void
	{

		// [VDM\Joomla\Componentbuilder\Compiler\Helper\Interpretation 6130] Only load jQuery if needed. (default is true)
		if ($this->params->get('add_jquery_framework', 1) == 1)
		{
			Html::_('jquery.framework');
		}
		// [VDM\Joomla\Componentbuilder\Compiler\Helper\Interpretation 6136] Load the header checker class.
		// [VDM\Joomla\Componentbuilder\Compiler\Helper\Interpretation 6158] Initialize the header checker.
		$HeaderCheck = new HeaderCheck();
		// add styles
		foreach ($this->styles as $style)
		{
			Html::_('stylesheet', $style, ['version' => 'auto']);
		}
		// [VDM\Joomla\Componentbuilder\Compiler\Helper\Interpretation 5706] Set the Custom CSS script to view
		$this->document->addStyleDeclaration("
			.day-view, .header {
						                display: flex;
						                height: auto;
						                overflow-y: auto;
						            }
						            .time-column {
						                width: 100px;
						                border-right: 1px solid #ddd;
						            }
						            .time-slot {
						                height: 60px; /* 1 hour = 60px height */
						                display: flex;
						                align-items: start;
						                justify-content: center;
						            }
						            .time-slot div {
						                position: relative;
						                top: -12px;
						            }
						            .events-column {
						                flex-grow: 1;
						                position: relative;
						                background-image: linear-gradient(to bottom, #ddd 1px, transparent 1px);
						                background-size: 100% 60px; /* 1 hour = 60px height */
						            }
						            .event {
						                position: absolute;
						                left: 0;
						                right: 5px;
						                margin: 0;
						                padding: 10px;
						                background-color: #f8f9fa;
						                overflow: hidden;
						            }
		");
		// add scripts
		foreach ($this->scripts as $script)
		{
			Html::_('script', $script, ['version' => 'auto']);
		}
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 * @since   1.6
	 */
	protected function addToolbar(): void
	{

		// set help url for this view if found
		$this->help_url = EventscheduleHelper::getHelpUrl('schedule');
		if (StringHelper::check($this->help_url))
		{
			ToolbarHelper::help('COM_EVENTSCHEDULE_HELP_MANAGER', false, $this->help_url);
		}
		// now initiate the toolbar
		$this->toolbar = Toolbar::getInstance();
	}

	/**
	 * Escapes a value for output in a view script.
	 *
	 * @param   mixed  $var     The output to escape.
	 * @param   bool   $shorten The switch to shorten.
	 * @param   int    $length  The shorting length.
	 *
	 * @return  mixed  The escaped value.
	 * @since   1.6
	 */
	public function escape($var, bool $shorten = false, int $length = 40)
	{
		if (!is_string($var))
		{
			return $var;
		}

		return StringHelper::html($var, $this->_charset ?? 'UTF-8', $shorten, $length);
	}
}
