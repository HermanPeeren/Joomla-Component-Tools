<?php

/**
 * Demo: implementing event schedule with core additional fields.
 * Template override for category blog layout of com_content.
 * Sub-template to render an event.
 *
 * @copyright   Herman Peeren, Yepr, 2024
 * @license     GNU General Public License version 3 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\CMS\Helper\TagsHelper;

// Get the CSS class to set the background colour.
$tags = $this->event->tags;
$tagParams = json_decode($tags->itemTags[0]->params);
$tagStyle = $tagParams->tag_link_class;

// Create a shortcut for params.
$params = $this->event->params;

// Make a link to the event page
$link="";
if ($params->get('access-view'))
{
     $link = Route::_(RouteHelper::getArticleRoute($this->event->slug, $this->event->catid, $this->event->language));
}
else
{
    $menu = Factory::getApplication()->getMenu();
    $active = $menu->getActive();
    $itemId = $active->id;
    $link = new Uri(Route::_('index.php?option=com_users&view=login&Itemid=' . $itemId, false));
    $link->setVar('return', base64_encode(RouteHelper::getArticleRoute($this->event->slug, $this->event->catid, $this->event->language)));
}

//todo: modal window with the link.
?>

<div class="event-content <?= $tagStyle; ?>" 
      style="height: <?= $this->event->duration * $this->category->height_px_per_minute; ?>px"> 
  
    <a href="<?= $link; ?>">
      <div style="width:100%;height:100%">
        <strong><?= $this->event->starttime; ?> - <?= $this->event->endtime; ?></strong>   
        <?= $this->event->title; ?>
  
        <?php if (!empty($this->event->introtext)): ?>       
          - <?= $this->event->introtext; ?>
        <?php endif; ?> 
  
        <?php if (!empty($this->event->actors)): ?>        
        <p>(<?= $this->event->actors; ?>)</p>
        <?php endif; ?>
      </div>
    </a>
  
</div>
