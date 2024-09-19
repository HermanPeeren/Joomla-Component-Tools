<?php
/**
 * @package    EventSchedule
 * @subpackage com_eventschedule
 * @version    1.0.0
 *
 * @copyright  Herman Peeren, Yepr
 * @license    GPL vs3+
 */

defined('_JEXEC') or die;

/** @var \Yepr\Component\Eventschedule\Administrator\View\Section\HtmlView $this */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

$this->useCoreUI = true; // = ?

$app = Factory::getApplication();
$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');

$layout  = 'edit';
?>
<form action="<?php echo Route::_('index.php?option=com_eventschedule&view=section&layout=' . $layout . '&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="section-form" class="form-validate">

    <div>
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'details']); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', empty($this->item->id) ? Text::_('COM_EVENTSCHEDULE_NEW_SECTION') : Text::_('COM_EVENTSCHEDULE_EDIT_SECTION')); ?>
        <div class="row">
            <div class="col-md-9">
                <div class="row">
                    <div class="col-md-6">
                        <?php echo $this->getForm()->renderField('section_name'); ?>
                        <?php echo $this->getForm()->renderField('container_ids'); ?>
                    </div>
                </div>
            </div>
        </div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
    </div>
    <input type="hidden" name="task" value="">
    <input type="hidden" name="id" value="<?php echo $this->form->getValue('id'); ?>">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>