<?php // no direct access
/**
* @package RokMicroEvents
* @copyright Copyright (C) 2009 RocketTheme. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/
defined('_JEXEC') or die('Restricted access');

if (!count($events)) echo JText::_("ROKMINIEVENTS3_NOEVENTSFROUND");
if (isset($events['error'])) echo $events;

if (!count($events) || isset($events['error'])) return;


$offset_x = $params->get('offset_x', 0);

$pages = ceil(count($events) / $params->get('events_pane'));
$per_pane = $params->get('events_pane');
$timeline = $params->get('timeline', 'both');
$timelineDates = RokMiniEvents3::getTimelineDates($events, $params);
$json_options  = htmlentities(json_encode(array("id" => $module->id, "pages" => (int)$pages)));

/** @var RokMiniEvents3_Event[] $events */
?>

<div class="rokminievents3" data-rokminievents3="<?php echo $json_options; ?>" data-rokminievents3-id="<?php echo $module->id; ?>">
    <ul class="rme-items cols-<?php echo $params->get('events_pane', 3); ?>">
        <?php for($i = 0; ($i < count($events) && $i<$per_pane); $i++):
            $event = $events[$i];
            include(JModuleHelper::getLayoutPath('mod_rokminievents3','default_item'));
        endfor; ?>
    </ul>
    <div class="rme-timeline<?php echo ($timeline == 'arrows' || $timeline == 'both') ? ' arrows-on' : '';?>">
        <?php if ($timeline == 'timeline' || $timeline == 'both'): ?>
        <div class="rme-timeline-bar">
            <ul class="rme-timeline-points cols-<?php echo $pages; ?>">
                <?php for($i = 0; $i < $pages; $i++): ?>
                    <li class="rme-timeline-point<?php echo !$i ? ' active' : '';?>" data-rokminievents3-page="<?php echo ($i+1); ?>">
                        <span></span>
                        <div class="rme-timeline-date"><?php echo $timelineDates[$i]['start']; ?> - <?php echo $timelineDates[$i]['end']; ?></div>
                    </li>
                <?php endfor; ?>
            </ul>
        </div>
        <?php endif; ?>
        <?php if ($timeline == 'arrows' || $timeline == 'both'): ?>
            <span data-rokminievents3-previous class="rme-arrow left">&#x2039;</span>
            <span data-rokminievents3-next class="rme-arrow right">&#x203A;</span>
        <?php endif;?>
    </div>
</div>
