<?php

/**
 * Demo: implementing event schedule with core additional fields.
 * Template override for category blog layout of com_content.
 * Sub-template to render the schedule.
 *
 * @copyright   Herman Peeren, Yepr, 2024
 * @license     GNU General Public License version 3 or later
 */

defined('_JEXEC') or die;

//use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Factory;

// Calculate width (percentage) of columns of the grid
$numberOfSections = count($this->sectionOptions);
$columnWidthPerc  = floor(100/($numberOfSections+0.25));
$timeColumnWidthPerc = floor(0.25 * $columnWidthPerc);

// Get Event Type tag-items
$parentId = 7; // Event Type
$table = Factory::getApplication()->bootComponent('com_tags')->getMVCFactory()->createTable('Tag', 'Administrator');
$eventTypeTree = $table->getTree($parentId);

$eventTypeTags = [];
if (!empty($eventTypeTree))
{
  // Get tag name and CSS class for event type tag children
  foreach ($eventTypeTree as $tag)
  {
    if ($tag->parent_id == $parentId)
    {
      $class = json_decode($tag->params)->tag_link_class;
      $eventTypeTags[] = ['name' => $tag->title, 'class' => $class];
    }
  }
}

?>
<div class="eventtype-legenda">
  <?php foreach ($eventTypeTags as $tag): ?>
      <span class="<?= $tag['class'] ?>"><?= $tag['name'] ?></span>
  <?php endforeach; ?>
</div>

<table class="schedule" style="width: 100%">
  <thead>
    <tr>  
       <th class="time-column" style="width: <?php echo $timeColumnWidthPerc; ?>%" /> 
      <?php foreach ($this->sectionOptions as $sectionOption) : ?>
        <th class="events-column" style="width: <?php echo $columnWidthPerc; ?>%;text-align:center">
            <small><strong><?php echo $sectionOption['value']; ?></strong></small>
        </th>
   
      <?php endforeach; ?>     
    </tr>
  </thead>
  
  <tbody>
    <tr>
      <td>
        <div class="time-column">
            <?php
            foreach ($this->timeSlots as $slot) : ?>
                <div class='time-slot' style="height:<?php echo $this->timeInterval * $this->category->height_px_per_minute; ?>px;">
                    <div><?php echo $slot; ?></div>
                </div>
            <?php endforeach; ?>
        </div>      
      </td>
      
      <?php foreach ($this->sectionOptions as $sectionOption) : ?>
      <?php $sectionLastTime = new \DateTime($this->category->schedule_start); ?>
      <td style="vertical-align:top">
            <div class="events-column">
                <?php if (!empty($this->events[$this->containerName][$sectionOption['value']])): ?>
                    <?php ksort($this->events[$this->containerName][$sectionOption['value']]); // sort in ascending time ?>
                    <?php foreach ($this->events[$this->containerName][$sectionOption['value']] as &$event) : ?>
                        <?php
                         // Fill the gap between endtime of last event and begintime of new event.
                         $starttime=  new \DateTime($event->starttime);
                         if ($starttime > $sectionLastTime)
                         {
                           $sinceLast = $sectionLastTime->diff($starttime);
                           $gap = ($sinceLast->i + $sinceLast->h * 60) * $this->category->height_px_per_minute;
                           echo '<div style="height:' . $gap . 'px;">&nbsp;</div>';
                         }
              
                         // Display the event.
                         if (!empty($event))
                         {
              		         $this->event = $event;
                             echo $this->loadTemplate('event');                      
                         } 
                         
                         // Set the endtime of last event as sectionLastTime.
                         $sectionLastTime = new \DateTime($event->endtime);
                         
                      ?> 
                    <?php endforeach; ?>
               <?php endif; ?>            
            </div>      
      </td>
      <?php endforeach; ?>
      
    </tr>
  </tbody>
</table>
