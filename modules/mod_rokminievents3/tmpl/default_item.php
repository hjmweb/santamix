<?php
/** @var RokMiniEvents3_Event $event*/
$start = $event->getStart()->getDay() . ' ' . $event->getStart()->getMonth() . ' ' . $event->getStart()->getYear();
$end = $event->getEnd()->getDay() . ' ' . $event->getEnd()->getMonth() . ' ' . $event->getEnd()->getYear();
$time = $event->getStart()->getTime();
if (!$event->isAllDay() && !$event->isNoEndTime()) {
	$time .= ' to ' . $event->getEnd()->getTime();
}
?>

<li class="rme-item">
	<?php if ($params->get('datedisplay') == 'badge' || $params->get('datedisplay') == 'both'): ?>
	<div class="rme-badge">
		<span class="rme-day"><?php echo $event->getStart()->getDay(); ?></span>
		<span class="rme-month"><?php echo $event->getStart()->getMonth(); ?></span>
		<?php if ($params->get('showyear')): ?>
			<span class="rme-year"><?php echo $event->getStart()->getYear(); ?></span>
		<?php endif; ?>
	</div>
	<?php endif; ?>
	<div class="rme-description">
		<?php if (!$event->getLink()): ?>
			<span class="rme-title"><?php echo $event->getTitle(); ?></span>
		<?php else: ?>
			<?php
			$values   = $event->getLink();
			$internal = $values['internal'];
			$link     = $values['link'];
			?>
			<a class="rme-title<?php echo $internal ? '' : ' rme-external-link'; ?>" href="<?php echo $link ?>"><?php echo $event->getTitle(); ?></a>
		<?php endif; ?>
		<?php if ($params->get('datedisplay') == 'inline' || $params->get('datedisplay') == 'both'): ?>
			<span class="rme-date"><?php echo $start ?></span>
		<?php endif; ?>
		<?php if (!$event->isAllDay()): ?>
			<span class="rme-time"><?php echo $time ?></span>
		<?php else: ?>
			<span class="rme-time"><?php echo 'All Day'; ?></span>
		<?php endif; ?>
		<?php if ($params->get('show_description')): ?>
			<p class="rme-details"><?php echo $event->getDescription(); ?></p>
		<?php endif; ?>
	</div>
</li>
