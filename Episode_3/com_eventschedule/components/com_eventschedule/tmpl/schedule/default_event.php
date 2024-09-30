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

use Joomla\CMS\Router\Route;

// Make a link to the event page todo: use an alias/slug
$link = Route::_('/index.php?option=com_eventschedule&view=event&id=' . $this->event->id);


// Create a string with the actors.
$actorNames=[];
foreach ($this->event->actors as $actor)
  {
     $actorNames[] = $actor->actor_name; // todo: link to actor-page with a modal. Link to article with id.
  }
$actors = implode (', ',$actorNames); 

// todo: modal window with the link.
?>

<div class="event-content <?= $this->event->css_class; ?>"
      style="height: <?= $this->event->duration * $this->params->get('height-px-per-minute'); ?>px;
              background-color: #<?= $this->event->background_color; ?>;">
  
    <a href="<?= $link; ?>">
      <div style="position: relative;width:100%;height:100%">
        <strong><?= $this->event->starttime; ?> - <?= $this->event->endtime; ?></strong>   
        <?= $this->event->event_name; ?>
  
        <?php if (!empty($this->event->short_description)): ?>
          - <?= $this->event->short_description; ?>
        <?php endif; ?> 
  
        <?php if (!empty($actors)): ?>        
        <p style="position: absolute;bottom: 0">(<?= $actors; ?>)</p>
        <?php endif; ?>
      </div>
    </a>
  
</div>
