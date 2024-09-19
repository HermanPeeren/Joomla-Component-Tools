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

 // Initiate getting actors from com_content
 $contentMvcFactory = $app->bootComponent('com_content')->getMVCFactory();
 $articleModel = $contentMvcFactory->createModel('Article', 'Administrator', ['ignore_request' => true]);

 $this->unscheduled = [];
 // Get the events per containerOption and sectionOption; the events are in $this->items
 foreach ($this->items as $item)
 {   
   // Get the additional fields of this item and put them in named variables.
   foreach($item->jcfields as $jcfield)
     {
       $fieldname        =  str_replace("-", "_", $jcfield->name);
       $item->$fieldname = $jcfield->value; 

       // The LOCATORS will be stored as an array of subforms
       // Each subform will be stored as an array of subform fields
       // Each subform field is an object; in this template we will use title and value properties
       if ($fieldname=='locators')
       {         
         // Make a new array for locators (overriding the standard value).
         $item->locators = [];

         // Get the subform rows: an array of rows, consisting of an array of fields (with title and value we will use)
         if (!empty($jcfield->rawvalue))
           {
             $item->locators = $jcfield->subform_rows;
             //echo '<b>' . $item->locators[0]['container']->value . '</b><br />';
           }
         

         //echo '<pre>';
         //print_r($item);
          //echo '</pre><br />';
       }

       // Get the ACTOR(s), so we can use all fields in our custom template
       if ($fieldname=='actors')
       {
         // Make a new array for actors (overriding the standard layout of ACF Articles field).
         $item->actors = [];
         
         // Get the IDs of the actor(s) form the ACF Articles field
         $item->actorIds = $jcfield->rawvalue;
         // Empty array
         if (empty($item->actorIds)) 
           {
             $item->actorIds = [];
           }
         else
           {
             // One value, no array: make it an array with one element
             if (!is_array($item->actorIds)) $item->actorIds = [$item->actorIds];

             foreach ($item->actorIds as $actorId)
               {
                 $item->actors[$actorId] = $articleModel->getItem($actorId);
               }             
           }         
       }
     }
   
   // If this event doesn't have a locator, then it is unscheduled.
   if (empty($item->locators))
   {
     $this->unscheduled[] = $item;
   }
   else
   {
     // Add the event in the events array, including and indexed by locator-data.
     // The item will be placed multiple times if there are multiple locators.
     foreach ($item->locators as $locator)
       {
         // Add the locator-data to the event
         $event = clone $item;
         $event->container = $locator['container']->value;
         $event->section = $locator['section']->value;
         $event->starttime = $locator['starttime']->value;
         $event->endtime = $locator['endtime']->value;
         
         // Add it to the events array, creating the section under the container if not exists.
         if (!array_key_exists($event->section, $this->events[$event->container]))
           {
             $this->events[$event->container][$event->section]=[];
           }         
     
         // Put the event in the events-array.
         $this->events[$event->container][$event->section][$event->starttime] = $event;  

       }
     
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



