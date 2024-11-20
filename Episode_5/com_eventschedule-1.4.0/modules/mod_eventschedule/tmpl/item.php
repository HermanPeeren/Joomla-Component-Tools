<?php
/**
 * @version     CVS: 1.4.0
 * @package     com_eventschedule
 * @subpackage  mod_eventschedule
 * @author      Herman Peeren <herman@yepr.nl>
 * @copyright   2024 Herman Peeren
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Yepr\Module\Eventschedule\Site\Helper\EventscheduleHelper;

$element = EventscheduleHelper::getItem($params);
?>

<?php if (!empty($element)) : ?>
	<div>
		<?php $fields = get_object_vars($element); ?>
		<?php foreach ($fields as $field_name => $field_value) : ?>
			<?php if (EventscheduleHelper::shouldAppear($field_name)): ?>
				<div class="row">
					<div class="span4">
						<strong><?php echo EventscheduleHelper::renderTranslatableHeader($params->get('item_table'), $field_name); ?></strong>
					</div>
					<div
						class="span8"><?php echo EventscheduleHelper::renderElement($params->get('item_table'), $field_name, $field_value); ?></div>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
<?php endif;
