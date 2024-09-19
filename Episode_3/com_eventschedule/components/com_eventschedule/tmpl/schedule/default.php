<?php
/*----------------------------------------------------------------------------------|  www.vdm.io  |----/
				TLWebdesign 
/-------------------------------------------------------------------------------------------------------/

	@version		1.0.2
	@build			11th June, 2024
	@created		23rd May, 2024
	@package		Event Schedule
	@subpackage		default.php
	@author			Tom van der Laan <https://tlwebdesign.nl>	
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
  ____  _____  _____  __  __  __      __       ___  _____  __  __  ____  _____  _  _  ____  _  _  ____ 
 (_  _)(  _  )(  _  )(  \/  )(  )    /__\     / __)(  _  )(  \/  )(  _ \(  _  )( \( )( ___)( \( )(_  _)
.-_)(   )(_)(  )(_)(  )    (  )(__  /(__)\   ( (__  )(_)(  )    (  )___/ )(_)(  )  (  )__)  )  (   )(  
\____) (_____)(_____)(_/\/\_)(____)(__)(__)   \___)(_____)(_/\/\_)(__)  (_____)(_)\_)(____)(_)\_) (__) 

/------------------------------------------------------------------------------------------------------*/

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper as Html;
use TLWeb\Component\Eventschedule\Site\Helper\EventscheduleHelper;

// No direct access to this file
defined('_JEXEC') or die;


/***[JCBGUI.site_view.php_view.26.$$$$]***/
Html::_('bootstrap.tab', '#myTab', []);

$this->dataArray= array();
$eventsArray = array();
foreach ($this->events as $event) {
    $eventArray['id'] = $event->id; 
    $eventArray['starttime'] = $event->starttime; 
    $eventArray['endtime'] = $event->endtime; 
    $eventArray['tags'] = $event->tags; 
    $eventArray['article'] = $event->article; 
    $eventArray['description'] = $event->description; 
    $eventArray['name'] = $event->name; 
    $sections = json_decode($event->section,true);
    $eventArray['section'] = $sections;
    foreach ($sections as $section) {
        $eventsArray[$section][$event->id]= $eventArray;
    }
}

foreach ($this->items as $item) {
    $this->dataArray[$item->id]['id'] = $item->id;
    $this->dataArray[$item->id]['name'] = $item->name;
    foreach ($item->idContainerSectionB as $section) {
        $this->dataArray[$section->container]['sections'][$section->id]['id'] = $section->id;
        $this->dataArray[$section->container]['sections'][$section->id]['container'] = $section->container;
        $this->dataArray[$section->container]['sections'][$section->id]['name'] = $section->name;
        $this->dataArray[$section->container]['sections'][$section->id]['events'] = $eventsArray[$section->id];
    }
}/***[/JCBGUI$$$$]***/


?>
<?php echo $this->toolbar->render(); ?>

<!--[JCBGUI.site_view.default.26.$$$$]-->
<?php echo $this->loadTemplate('tabs'); ?><!--[/JCBGUI$$$$]-->

