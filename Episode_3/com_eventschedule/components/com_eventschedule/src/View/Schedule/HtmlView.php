<?php
/**
 * @package    EventSchedule
 * @subpackage com_eventschedule
 * @version    1.0.0
 *
 * @copyright  Herman Peeren, Yepr
 * @license    GPL vs3+
 */

namespace Yepr\Component\EventSchedule\Site\View\Schedule;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * Html View class for the Schedule
 */
class HtmlView extends BaseHtmlView
{
    protected $events           = null;
    protected $containerOptions = null;
    protected $sectionOptions   = null;
    protected $params           = null;
    protected $timeInterval     = null;
    protected $timeSlots        = null;
    protected $eventTypes        = null;

    /**
     * Method to display the view.
     *
     * @param   string  $tpl  A template file to load. [optional]
     *
     * @return  void
     * @throws  Exception
     */
	public function display($tpl = null)
	{
		$this->events           = $this->get('Events');
		$this->containerOptions = $this->get('ContainerOptions');
		$this->sectionOptions   = $this->get('SectionOptions');
		$this->params           = $this->get('Params');
		$this->timeInterval     = $this->get('TimeInterval');
		$this->timeSlots        = $this->get('TimeSlots');
		$this->eventTypes       = $this->get('EventTypes');

		parent::display($tpl);
	}
}
