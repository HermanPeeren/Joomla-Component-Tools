<?php
/**
 * @package    EventSchedule
 * @subpackage com_eventschedule
 * @version    1.0.0
 *
 * @copyright  Herman Peeren, Yepr
 * @license    GPL vs3+
 */

namespace Yepr\Component\EventSchedule\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Container controller class.
 */
class ContainerController extends FormController
{
	/**
	  * Override constructor to indicate the corresponding list-view
	  * (especially with different names for views than standard entity-names)
	  *
	  * Alternative: Add $this->applyReturnUrl(); See Nic's PostController + ReturnURLAware mixin
	  */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		$this->view_list = 'containers';
		parent::__construct($config, $factory, $app, $input);
	}

}