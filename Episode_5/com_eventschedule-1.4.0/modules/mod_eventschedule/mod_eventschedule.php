<?php

/**
 * @version     CVS: 1.4.0
 * @package     com_eventschedule
 * @subpackage  mod_eventschedule
 * @author      Herman Peeren <herman@yepr.nl>
 * @copyright   2024 Herman Peeren
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Yepr\Module\Eventschedule\Site\Helper\EventscheduleHelper;

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wr = $wa->getRegistry();
$wr->addRegistryFile('media/mod_eventschedule/joomla.asset.json');
$wa->useStyle('mod_eventschedule.style')
    ->useScript('mod_eventschedule.script');

require ModuleHelper::getLayoutPath('mod_eventschedule', $params->get('content_type', 'blank'));
