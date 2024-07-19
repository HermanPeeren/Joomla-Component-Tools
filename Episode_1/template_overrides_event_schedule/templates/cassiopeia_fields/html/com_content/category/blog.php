<?php

/**
 * Demo: implementing event schedule with core additional fields.
 * Template override for category blog layout of com_content.
 *
 * @copyright   Herman Peeren, Yepr, 2024
 * @license     GNU General Public License version 3 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Layout\LayoutHelper;

$app = Factory::getApplication();

$this->category->text = $this->category->description;

$app->triggerEvent('onContentPrepare', [$this->category->extension . '.categories', &$this->category, &$this->params, 0]);
$this->category->description = $this->category->text;

 // Initiate getting variables from additional fields
 $mvcFactory = $app->bootComponent('com_fields')->getMVCFactory();
 $fieldModel = $mvcFactory->createModel('Field', 'Administrator', ['ignore_request' => true]);

 // Get the container options
 $container = $fieldModel->getItem(5); 
 $this->containerOptions=$container->fieldparams['options'];

 // initialise $this->events per containerOption
 $this->events = [];
 foreach ($this->containerOptions as $containerOption)
 {
   $this->events[$containerOption['value']] = [];
 }

 // Get the section options
 $section = $fieldModel->getItem(6); 
 $this->sectionOptions = $section->fieldparams['options'];

 
 // Get the additional fields for this category and put them in named variables
 foreach( $this->category->jcfields as $jcfield)
 {     
      $fieldname =  str_replace("-", "_", $jcfield->name);
      $this->category->$fieldname = $jcfield->value;
 }

 // Get the timeslots
 $startTime = new \DateTime($this->category->schedule_start);
 $endTime = new \DateTime($this->category->schedule_end);
 // Translate pixels per minute to time-interval on timeline
 $timeIntervalMap = [1 => 30, 2 =>15, 3 => 10, 4 => 5];
 $this->timeInterval = $timeIntervalMap[$this->category->height_px_per_minute];
 $this->timeSlots = [];
 // todo: round starttime and endtime to units of timeInterval

 while ($startTime <= $endTime) {
        $this->timeSlots[] = $startTime->format('H:i');
        $startTime->modify('+' . $this->timeInterval . ' minutes');
 }

 $this->unscheduled = [];
 // Get the events per containerOption and sectionOption; the events are in $this->items
 foreach ($this->items as $item)
 {   
   // Get the additional fields of this item and put them in named variables.
   foreach($item->jcfields as $jcfield)
     {
       $fieldname        =  str_replace("-", "_", $jcfield->name);
       $item->$fieldname = $jcfield->value; 
     }
   
   // If this event doesn't have a container, section or starttime, then it is unscheduled.
   if (empty($item->container) || empty($item->section) || empty($item->starttime))
   {
     $this->unscheduled[] = $item;
   }
   else
   {
     // Add it to the events creating the section under the container if not exists
     if (!array_key_exists($item->section, $this->events[$item->container]))
     {
       $this->events[$item->container][$item->section]=[];
     }
     
     // Put the item in the events-array
     $this->events[$item->container][$item->section][$item->starttime] = $item;    
   }     
 }

// In this main template the containers are rendered as tabs
HTMLHelper::_('bootstrap.tab', '#myTab', []);
?>

<ul class="nav nav-tabs" id="myTab" role="tablist">
    <?php
    $first = true; $i=0;
    foreach ($this->containerOptions as $containerOption) : ?>

        <li class="nav-item" role="presentation">
            <button class="nav-link <?php echo ($first) ? 'active': ''; ?>" id="containertab-<?php echo $i; ?>" data-bs-toggle="tab" data-bs-target="#containertab-<?php echo $i; ?>-pane" type="button" role="tab" aria-controls="containertab-<?php echo $i; ?>-pane" aria-selected="<?php echo ($first) ? 'true': ''; ?>"><?php echo $containerOption['value']; ?></button>
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
              $this->containerName = $containerOption['value'];
              echo $this->loadTemplate('schedule'); ?>
        </div>
    </div>
<?php 
$first = false; $i++; ?>
<?php endforeach; ?>



