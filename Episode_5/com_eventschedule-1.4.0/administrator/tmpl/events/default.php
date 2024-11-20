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
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

// Import CSS
$wa =  $this->document->getWebAssetManager();
$wa->useStyle('com_eventschedule.admin')
    ->useScript('com_eventschedule.admin');

$user      = Factory::getApplication()->getIdentity();
$userId    = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$canOrder  = $user->authorise('core.edit.state', 'com_eventschedule');



if (!empty($saveOrder))
{
	$saveOrderingUrl = 'index.php?option=com_eventschedule&task=events.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}

?>

<form action="<?php echo Route::_('index.php?option=com_eventschedule&view=events'); ?>" method="post"
	  name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
			<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

				<div class="clearfix"></div>
				<table class="table table-striped" id="eventList">
					<thead>
					<tr>
						<th class="w-1 text-center">
							<input type="checkbox" autocomplete="off" class="form-check-input" name="checkall-toggle" value=""
								   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
						</th>
						
						
					<th  scope="col" class="w-1 text-center">
						<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
					</th>
						
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_EVENTSCHEDULE_EVENTS_EVENT_NAME', 'a.event_name', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_EVENTSCHEDULE_EVENTS_SHORT_DESCRIPTION', 'a.short_description', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_EVENTSCHEDULE_EVENTS_EVENT_TYPE', 'a.event_type', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_EVENTSCHEDULE_EVENTS_LONG_DESCRIPTION', 'a.long_description', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_EVENTSCHEDULE_EVENTS_ACTORS', 'a.actors', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_EVENTSCHEDULE_EVENTS_LOCATORS', 'a.locators', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_EVENTSCHEDULE_EVENTS_SCHEDULE', 'a.schedule', $listDirn, $listOrder); ?>
						</th>
						
					<th scope="col" class="w-3 d-none d-lg-table-cell" >

						<?php echo HTMLHelper::_('searchtools.sort',  'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>					</th>
					</tr>
					</thead>
					<tfoot>
					<tr>
						<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
					</tfoot>
					<tbody <?php if (!empty($saveOrder)) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" <?php endif; ?>>
					<?php foreach ($this->items as $i => $item) :
						$ordering   = ($listOrder == 'a.ordering');
						$canCreate  = $user->authorise('core.create', 'com_eventschedule');
						$canEdit    = $user->authorise('core.edit', 'com_eventschedule');
						$canCheckin = $user->authorise('core.manage', 'com_eventschedule');
						$canChange  = $user->authorise('core.edit.state', 'com_eventschedule');
						?>
						<tr class="row<?php echo $i % 2; ?>" data-draggable-group='1' data-transition>
							<td class="text-center">
								<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
							</td>
							
							
							<td class="text-center">
								<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'events.', $canChange, 'cb'); ?>
							</td>
							
							<td>
								<?php if (isset($item->checked_out) && $item->checked_out && ($canEdit || $canChange)) : ?>
									<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'events.', $canCheckin); ?>
								<?php endif; ?>
								<?php if ($canEdit) : ?>
									<a href="<?php echo Route::_('index.php?option=com_eventschedule&task=event.edit&id='.(int) $item->id); ?>">
									<?php echo $this->escape($item->event_name); ?>
									</a>
								<?php else : ?>
												<?php echo $this->escape($item->event_name); ?>
								<?php endif; ?>
							</td>
							<td>
								<?php echo $item->short_description; ?>
							</td>
							<td>
								<?php echo $item->event_type; ?>
							</td>
							<td>
								<?php echo $item->long_description; ?>
							</td>
							<td>
								<?php echo $item->actors; ?>
							</td>
							<td>
								<?php if (!empty($item->locators)) :
								$subform_elements = json_decode($item->locators);
						foreach($subform_elements as $element)
						{
							foreach($element as $key =>$value)
							{
								echo '</br>'; 
								if(is_array($value) || is_object($value))
								{
								echo $key.':&nbsp';
								foreach($value as $key => $val)
								{
									if(is_array($val) || is_object($val))
									{
										echo $key.':&nbsp';
										foreach($val as $key => $val)
										{
											echo $key.'&nbsp'.$val.'&nbsp&nbsp&nbsp';
										}
									}else
									{
										echo $key .':&nbsp'.$value.'&nbsp&nbsp&nbsp';
									}
									}
								}else
								{
								echo $key .':&nbsp'.$value.'&nbsp&nbsp&nbsp';
								}
							}
						} 
								endif; ?>
							</td>
							<td>
								<?php echo $item->schedule; ?>
							</td>
							
							<td class="d-none d-lg-table-cell">
							<?php echo $item->id; ?>

							</td>


						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>

				<input type="hidden" name="task" value=""/>
				<input type="hidden" name="boxchecked" value="0"/>
				<input type="hidden" name="list[fullorder]" value="<?php echo $listOrder; ?> <?php echo $listDirn; ?>"/>
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>