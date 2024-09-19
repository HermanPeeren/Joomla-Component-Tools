<?php
/*----------------------------------------------------------------------------------|  www.vdm.io  |----/
				TLWebdesign 
/-------------------------------------------------------------------------------------------------------/

	@version		1.0.2
	@build			11th June, 2024
	@created		23rd May, 2024
	@package		Event Schedule
	@subpackage		default_tabs.php
	@author			Tom van der Laan <https://tlwebdesign.nl>	
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
  ____  _____  _____  __  __  __      __       ___  _____  __  __  ____  _____  _  _  ____  _  _  ____ 
 (_  _)(  _  )(  _  )(  \/  )(  )    /__\     / __)(  _  )(  \/  )(  _ \(  _  )( \( )( ___)( \( )(_  _)
.-_)(   )(_)(  )(_)(  )    (  )(__  /(__)\   ( (__  )(_)(  )    (  )___/ )(_)(  )  (  )__)  )  (   )(  
\____) (_____)(_____)(_/\/\_)(____)(__)(__)   \___)(_____)(_/\/\_)(__)  (_____)(_)\_)(____)(_)\_) (__) 

/------------------------------------------------------------------------------------------------------*/

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper as Html;
use Joomla\CMS\Layout\LayoutHelper;

// No direct access to this file
defined('_JEXEC') or die;

?>

<!--[JCBGUI.template.template.1.$$$$]-->
<ul class="nav nav-tabs" id="myTab" role="tablist">
    <?php
    $first = true;
    foreach ($this->dataArray as $item) : ?>

        <li class="nav-item" role="presentation">
            <button class="nav-link <?php echo ($first) ? 'active': ''; ?>" id="containertab-<?php echo $item['id']; ?>" data-bs-toggle="tab" data-bs-target="#containertab-<?php echo $item['id']; ?>-pane" type="button" role="tab" aria-controls="containertab-<?php echo $item['id']; ?>-pane" aria-selected="<?php echo ($first) ? 'true': ''; ?>"><?php echo $item['name']; ?></button>
        </li>
    <?php 
    $first = false;
    endforeach; 
    ?>
</ul>

<?php
$first = true;
foreach ($this->dataArray as $item) : ?>
    <div class="tab-content" id="containertab-<?php echo $item['id']; ?>-content">
        <div class="tab-pane fade <?php echo ($first) ? 'show active': ''; ?>" id="containertab-<?php echo $item['id']; ?>-pane" role="tabpanel" aria-labelledby="containertab-<?php echo $item['id']; ?>" tabindex="0">
          <?php echo \Joomla\CMS\Layout\LayoutHelper::render('container', $item); ?>
        </div>
    </div>
<?php 
$first = false;
endforeach; 
?><!--[/JCBGUI$$$$]-->

