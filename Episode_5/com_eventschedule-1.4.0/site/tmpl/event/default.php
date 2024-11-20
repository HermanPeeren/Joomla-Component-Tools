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
			<th><?php echo Text::_('COM_EVENTSCHEDULE_FORM_LBL_EVENT_EVENT_NAME'); ?></th>
			<td><?php echo $this->item->event_name; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_EVENTSCHEDULE_FORM_LBL_EVENT_SHORT_DESCRIPTION'); ?></th>
			<td><?php echo nl2br($this->item->short_description); ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_EVENTSCHEDULE_FORM_LBL_EVENT_LONG_DESCRIPTION'); ?></th>
			<td><?php echo nl2br($this->item->long_description); ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_EVENTSCHEDULE_FORM_LBL_EVENT_DURATION'); ?></th>
			<td><?php echo $this->item->duration; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_EVENTSCHEDULE_FORM_LBL_EVENT_EVENT_TYPE'); ?></th>
			<td><?php echo $this->item->event_type; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_EVENTSCHEDULE_FORM_LBL_EVENT_LOCATORS'); ?></th>
			<td><?php echo $this->item->locators; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_EVENTSCHEDULE_FORM_LBL_EVENT_ACTORS'); ?></th>
			<td><?php echo $this->item->actors; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_EVENTSCHEDULE_FORM_LBL_EVENT_STATE'); ?></th>
			<td>
			<i class="icon-<?php echo ($this->item->state == 1) ? 'publish' : 'unpublish'; ?>"></i></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_EVENTSCHEDULE_FORM_LBL_EVENT_SCHEDULE'); ?></th>
			<td><?php echo $this->item->schedule; ?></td>
		</tr>

	</table>

</div>

