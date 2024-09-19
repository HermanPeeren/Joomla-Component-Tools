<?php
/**
 * @package     EventSchedule
 * @subpackage  com_eventschedule
 * @version     1.0.0
 *
 * @copyright   Herman Peeren, Yepr
 * @license     GPL vs3+
 */

namespace Yepr\Component\EventSchedule\Administrator\Controller;

defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Factory;

/**
 * Event controller class.
 */
class EventController extends FormController
{
	/**
	  * Override constructor to indicate the corresponding list-view
	  * (especially with different names for views than standard entity-names)
	  *
	  * Alternative: Add $this->applyReturnUrl(); See Nic's PostController + ReturnURLAware mixin
	  */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		$this->view_list = 'events';
		parent::__construct($config, $factory, $app, $input);
	}


    /**
     * Override the reload() method to get the options for the section-dropdown with a chosen container.
     * @param $key
     * @param $urlVar
     * @return void
     * @throws Exception
     */
    public function reload($key = null, $urlVar = null)
    {
        $this->checkToken();

        $app   = Factory::getApplication();

        $data  = $this->input->post->get('jform', array(), 'array');

        // This is the usual call to set the state for preserving the form data entered by the user
        $app->setUserState('com_eventschedule.edit.event.data', $data);

        // In documentation container_id is explicitly put into user state, but: it is already in the event-data...
        //$container_id = filter_var($data['container_id'], FILTER_SANITIZE_NUMBER_INT);

        // This is the call you need to make to pass the sql_filter ids to the SQL field
        // The first parameter must be `'<context>.filter'` where `context` is what you set
        // as the context= ... attribute of the SQL field
        //$app->setUserState('eventschedule.filter', array('container_id' => $container_id));

        // Then re-present the form
        $model = $this->getModel('event');
        $view = $this->getView('event', 'html');
        $view->setLayout('edit');
        $view->setModel($model, true);

        $view->display();
    }

}