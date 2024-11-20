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

$elements = EventscheduleHelper::getList($params);

$tableField = explode(':', $params->get('field'));
$table_name = !empty($tableField[0]) ? $tableField[0] : '';
$field_name = !empty($tableField[1]) ? $tableField[1] : '';
?>

<?php if (!empty($elements)) : ?>
	<table class="jcc-table">
		<?php foreach ($elements as $element) : ?>
			<tr>
				<th><?php echo EventscheduleHelper::renderTranslatableHeader($table_name, $field_name); ?></th>
				<td><?php echo EventscheduleHelper::renderElement(
						$table_name, $params->get('field'), $element->{$field_name}
					); ?></td>
			</tr>
		<?php endforeach; ?>
	</table>
<?php endif;
