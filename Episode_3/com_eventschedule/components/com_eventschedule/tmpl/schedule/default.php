<?php
/**
 * @package    EventSchedule
 * @subpackage com_eventschedule
 * @version    1.0.0
 *
 * @copyright  Herman Peeren, Yepr
 * @license    GPL vs3+
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\HTML\HTMLHelper;

// In this main template the containers are rendered as tabs
HTMLHelper::_('bootstrap.tab', '#myTab', []);
?>

<ul class="nav nav-tabs" id="myTab" role="tablist">
    <?php
    $first = true; $i=0;
    foreach ($this->containerOptions as $containerOption) : ?>

        <li class="nav-item" role="presentation">
            <button class="nav-link <?php echo ($first) ? 'active': ''; ?>" id="containertab-<?php echo $i; ?>" data-bs-toggle="tab" data-bs-target="#containertab-<?php echo $i; ?>-pane" type="button" role="tab" aria-controls="containertab-<?php echo $i; ?>-pane" aria-selected="<?php echo ($first) ? 'true': ''; ?>">
                <?php echo $containerOption->container_name; ?></button>
        </li>
    <?php 
        $first = false;
        $i++;
    endforeach; 
    ?>
</ul>

<?php
$first = true; $i=0; ?>
<?php foreach ($this->containerOptions as $containerOption): ?>
    <div class="tab-content" id="containertab-<?php echo $i; ?>-content">
        <div class="tab-pane fade <?php echo ($first) ? 'show active': ''; ?>" id="containertab-<?php echo $i; ?>-pane" role="tabpanel" aria-labelledby="containertab-<?php echo $i; ?>" tabindex="0">
          <?php 
              $this->containerId = $containerOption->id;
              echo $this->loadTemplate('schedule'); ?>
        </div>
    </div>
<?php 
$first = false; $i++; ?>
<?php endforeach; ?>



