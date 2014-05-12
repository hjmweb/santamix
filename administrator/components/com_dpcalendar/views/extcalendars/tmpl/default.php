<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
DPCalendarHelper::loadLibrary(array('bootstrap' => true));
JFactory::getDocument()->addScript(JURI::base() . 'components/com_dpcalendar/libraries/iframe-resizer/iframeResizer.contentWindow.min.js');

$input = JFactory::getApplication()->input;
$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));

if ($input->getCmd('tmpl') == 'component')
{
	$bar = JToolbar::getInstance('toolbar');
	echo $bar->render();
	?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'plugin.action') {
			jQuery('#extcalendar-action').val('import');
		}
		Joomla.submitform(task, document.getElementById('adminForm'));
	}
</script>
<?php
}
?>
<form action="<?php echo JRoute::_('index.php?option=com_dpcalendar&view=extcalendars&plugin=' . $input->getWord('plugin')) . '&tmpl=' . $input->getWord('tmpl'); ?>" method="post" name="adminForm" id="adminForm" class="dp-container">
		<table class="table table-striped" id="extcalendarsList">
			<thead>
				<tr>
					<th width="1%" class="hidden-phone">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
					</th>
					<th width="1%" class="nowrap center">
						<?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
					</th>
					<th class="title">
						<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
					</th>
					<th width="5%" class="nowrap">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'a.language', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="nowrap center">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="10">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php foreach ($this->items as $i => $item)
			{
				$ordering   = ($listOrder == 'a.ordering');
				$canCreate  = $user->authorise('core.create',     'com_dpcalendar');
				$canEdit    = $user->authorise('core.edit',       'com_dpcalendar');
				$canChange  = $user->authorise('core.edit.state', 'com_dpcalendar');
				?>
				<tr class="row<?php echo $i % 2; ?>" sortable-group-id="">
					<td class="center">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>
					<td class="center">
						<?php echo JHtml::_('jgrid.published', $item->state, $i, 'extcalendars.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
					</td>
					<td class="nowrap">
						<?php if ($canEdit)
						{ ?>
							<a href="<?php echo JRoute::_('index.php?option=com_dpcalendar&task=extcalendar.edit&id=' . (int) $item->id . '&tmpl=' . $input->getWord('tmpl') . '&plugin=' . $input->getWord('plugin')); ?>">
								<?php echo $this->escape($item->title); ?></a>
						<?php
						}
						else
						{ ?>
								<?php echo $this->escape($item->title); ?>
						<?php
						} ?>
					</td>
					<td class="small nowrap">
						<?php if ($item->language == '*')
						{?>
							<?php echo JText::alt('JALL', 'language'); ?>
						<?php
						}
						else
						{?>
							<?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
						<?php
						}?>
					</td>
					<td class="center">
						<?php echo (int) $item->id; ?>
					</td>
				</tr>
				<?php
				} ?>
			</tbody>
		</table>

		<input type="hidden" name="action" value="" id="extcalendar-action"/>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
</form>
