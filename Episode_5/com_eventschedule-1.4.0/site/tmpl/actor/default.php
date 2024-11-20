<?php
/**
 * @version    CVS: 1.4.0
 * @package    Com_Eventschedule
 * @author     Herman Peeren <herman@yepr.nl>
 * @copyright  2024 Herman Peeren
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;


?>

<div class="item_fields">

	<table class="table">
		

		<tr>
			<th><?php echo Text::_('COM_EVENTSCHEDULE_FORM_LBL_ACTOR_ACTOR_NAME'); ?></th>
			<td><?php echo $this->item->actor_name; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_EVENTSCHEDULE_FORM_LBL_ACTOR_ACTOR_IMAGE'); ?></th>
			<td><?php echo $this->item->actor_image; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_EVENTSCHEDULE_FORM_LBL_ACTOR_BIOGRAPHY'); ?></th>
			<td><?php echo nl2br($this->item->biography); ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_EVENTSCHEDULE_FORM_LBL_ACTOR_WEBSITE'); ?></th>
			<td><?php echo $this->item->website; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_EVENTSCHEDULE_FORM_LBL_ACTOR_EVENTS'); ?></th>
			<td><?php echo $this->item->events; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_EVENTSCHEDULE_FORM_LBL_ACTOR_SCHEDULE'); ?></th>
			<td><?php echo $this->item->schedule; ?></td>
		</tr>

	</table>

</div>

