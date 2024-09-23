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

// Calculate width (percentage) of columns of the grid
$numberOfSections = count($this->sectionOptions);
$columnWidthPerc  = floor(100/($numberOfSections+0.25));
$timeColumnWidthPerc = floor(0.25 * $columnWidthPerc);

?>
<div class="eventtype-legenda">
  <?php foreach ($this->eventTypes as $eventType): ?>
      <span class="<?= $eventType->css_class ?>" style="background-color: #<?= $eventType->background_color; ?>;"><?= $eventType->event_type_name ?></span>
  <?php endforeach; ?>
</div>

<table class="schedule" style="width: 100%">
  <thead>
    <tr>  
       <th class="time-column" style="width: <?php echo $timeColumnWidthPerc; ?>%"></th> 
      <?php foreach ($this->sectionOptions as $sectionOption) : ?>
        <th class="events-column" style="width: <?php echo $columnWidthPerc; ?>%;text-align:center">
            <small><strong><?php echo $sectionOption->section_name; ?></strong></small>
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
                <div class='time-slot' style="height:<?php echo $this->timeInterval * $this->params->get('height-px-per-minute'); ?>px;">
                    <div><?php echo $slot; ?></div>
                </div>
            <?php endforeach; ?>
        </div>      
      </td>
      
      <?php foreach ($this->sectionOptions as $sectionOption) : ?>
      <?php $sectionLastTime = new \DateTime($this->params->get('schedule_start')); ?>
      <td style="vertical-align:top">
            <div class="events-column">
                <?php if (!empty($this->events[$this->containerId][$sectionOption->id])): ?>
                    <?php ksort($this->events[$this->containerId][$sectionOption->id]); // sort in ascending time ?>
                    <?php foreach ($this->events[$this->containerId][$sectionOption->id] as &$event) : ?>
                        <?php
                         // Fill the gap between endtime of last event and begintime of new event.
                         $starttime=  new \DateTime($event->starttime);
                         if ($starttime > $sectionLastTime)
                         {
                           $sinceLast = $sectionLastTime->diff($starttime);
                           $gap = ($sinceLast->i + $sinceLast->h * 60) * $this->params->get('height-px-per-minute');
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
