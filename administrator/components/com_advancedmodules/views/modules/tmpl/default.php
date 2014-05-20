<?php
/**
 * @package         Advanced Module Manager
 * @version         4.14.1
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2014 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

/**
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$clientid = (int) $this->state->get('stfilter.client_id');
$client = $clientid ? 'administrator' : 'site';
$user = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$trashed = $this->state->get('filter.published') == -2 ? true : false;
$canOrder = $user->authorise('core.edit.state', 'com_modules');
$saveOrder = $listOrder == 'ordering';
if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_advancedmodules&task=modules.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'articleList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
$showcolors = ($client == 'site' && $this->config->show_color);
if ($showcolors)
{
	require_once JPATH_LIBRARIES . '/joomla/form/fields/color.php';
	$colorfield = new JFormFieldColor;
	$script = "
		function setColor(id, el)
		{
			var f = document.getElementById('adminForm');
			f.setcolor.value = jQuery(el).val();
			listItemTask(id, 'modules.setcolor');
		}
	";
	JFactory::getDocument()->addScriptDeclaration($script);
}

JHtml::stylesheet('nnframework/style.min.css', false, true);

// Version check
require_once JPATH_PLUGINS . '/system/nnframework/helpers/versions.php';
if ($this->config->show_update_notification)
{
	echo NNVersions::getInstance()->getMessage('advancedmodules', '', '', 'component');
}
?>
	<form action="<?php echo JRoute::_('index.php?option=com_advancedmodules'); ?>" method="post" name="adminForm" id="adminForm">
		<div id="j-main-container">
			<?php
			// Search tools bar
			echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
			?>
			<div class="clearfix"></div>
			<?php $cols = 10; ?>
			<table class="table table-striped nn_tablelist" id="articleList">
				<thead>
					<tr>
						<th width="1%" class="nowrap center hidden-phone">
							<?php echo JHtml::_('searchtools.sort', '', 'ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
						</th>
						<th width="1%" class="hidden-phone">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th width="1%" class="nowrap center">
							<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
						</th>
						<?php if ($showcolors) : ?>
							<?php $cols++; ?>
							<th width="1%" class="nowrap center hidden-phone">
								<?php echo JHtml::_('searchtools.sort', '', 'color', $listDirn, $listOrder, null, 'asc', 'AMM_COLOR', 'icon-color'); ?>
							</th>
						<?php endif; ?>
						<th class="title">
							<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
						</th>
						<?php if ($this->config->show_note == 3) : ?>
							<?php $cols++; ?>
							<th class="title">
								<?php echo JHtml::_('searchtools.sort', 'JFIELD_NOTE_LABEL', 'a.note', $listDirn, $listOrder); ?>
							</th>
						<?php endif; ?>
						<th width="15%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'COM_MODULES_HEADING_POSITION', 'position', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'COM_MODULES_HEADING_MODULE', 'name', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'NN_MENU_ITEMS', 'pages', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
						</th>
						<th width="5%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap center hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="<?php echo $cols; ?>">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
					<?php foreach ($this->items as $i => $item) :
						$ordering = ($listOrder == 'ordering');
						$canCreate = $user->authorise('core.create', 'com_modules');
						$canEdit = $user->authorise('core.edit', 'com_modules.module.' . $item->id);
						$canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
						$canChange = $user->authorise('core.edit.state', 'com_modules.module.' . $item->id) && $canCheckin;
						?>
						<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->position; ?>">
							<td class="order nowrap center hidden-phone">
								<?php if ($canChange) :
									$disableClassName = '';
									$disabledLabel = '';
									if (!$saveOrder) :
										$disabledLabel = JText::_('JORDERINGDISABLED');
										$disableClassName = 'inactive tip-top';
									endif; ?>
									<span class="sortable-handler hasTooltip <?php echo $disableClassName; ?>" title="<?php echo $disabledLabel; ?>">
										<span class="icon-menu"></span>
									</span>
									<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order" />
								<?php else : ?>
									<span class="sortable-handler inactive">
										<span class="icon-menu"></span>
									</span>
								<?php endif; ?>
							</td>
							<td class="center hidden-phone">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>
							<td class="center">
								<div class="btn-group">
									<?php echo JHtml::_('jgrid.published', $item->published, $i, 'modules.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
									<?php
									// Create dropdown items
									JHtml::_('actionsdropdown.duplicate', 'cb' . $i, 'modules');

									$action = $trashed ? 'untrash' : 'trash';
									JHtml::_('actionsdropdown.' . $action, 'cb' . $i, 'modules');

									// Render dropdown list
									echo JHtml::_('actionsdropdown.render', $this->escape($item->title));
									?>
								</div>
							</td>
							<?php if ($showcolors) : ?>
								<td class="center inlist">
									<?php
									$color = (isset($item->params->color) && $item->params->color) ? $color = str_replace('##', '#', $item->params->color) : 'none';
									$element = new SimpleXMLElement(
										'<field
											name="color_' . $i . '"
											type="color"
											control="simple"
											default=""
											colors="' . (isset($this->config->main_colors) ? $this->config->main_colors : '') . '"
											split="4"
											onchange="setColor(\'cb' . $i . '\', this)"
											/>'
									);
									$element->value = $color;
									$colorfield->setup($element, $color);
									echo $colorfield->__get('input');
									?>
								</td>
							<?php endif; ?>
							<td class="has-context">
								<div class="pull-left">
									<?php if ($item->checked_out) : ?>
										<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'modules.', $canCheckin); ?>
									<?php endif; ?>
									<?php
									$title = $this->escape($item->title);
									$tooltip = '<strong>' . JText::_('AMM_EDIT_MODULE') . '</strong><br />' . htmlspecialchars($title);
									if (!empty($item->note) && $this->config->show_note == 1)
									{
										$tooltip .= '<br /><em>' . htmlspecialchars(JText::sprintf('JGLOBAL_LIST_NOTE', $this->escape($item->note))) . '</em>';
									}
									$title = '<span rel="tooltip" title="' . $tooltip . '">' . $title . '</span>';
									?>
									<?php if ($canEdit) : ?>
										<a href="<?php echo JRoute::_('index.php?option=com_advancedmodules&task=module.edit&id=' . (int) $item->id); ?>">
											<?php echo $title; ?></a>
									<?php else : ?>
										<?php echo $title; ?>
									<?php endif; ?>
									<?php if (!empty($item->note) && $this->config->show_note == 2) : ?>
										<div class="small">
											<?php echo JText::sprintf('JGLOBAL_LIST_NOTE', $this->escape($item->note)); ?>
										</div>
									<?php endif; ?>
								</div>
							</td>
							<?php if ($this->config->show_note == 3) : ?>
								<td class="has-context">
									<?php echo $this->escape($item->note); ?>
								</td>
							<?php endif; ?>
							<td class="small hidden-phone">
								<?php if ($item->position) : ?>
									<span class="label label-info">
										<?php echo $item->position; ?>
									</span>
								<?php else : ?>
									<span class="label">
										<?php echo JText::_('JNONE'); ?>
									</span>
								<?php endif; ?>
							</td>
							<td class="small hidden-phone">
								<?php echo $item->name; ?>
							</td>
							<td class="small hidden-phone">
								<?php echo $item->pages; ?>
							</td>

							<td class="small hidden-phone">
								<?php echo $this->escape($item->access_level); ?>
							</td>
							<td class="small hidden-phone">
								<?php if ($item->language == '') : ?>
									<?php echo JText::_('JDEFAULT'); ?>
								<?php elseif ($item->language == '*') : ?>
									<?php echo JText::alt('JALL', 'language'); ?>
								<?php else : ?>
									<?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
								<?php endif; ?>
							</td>
							<td class="center hidden-phone">
								<?php echo (int) $item->id; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php //Load the batch processing form. ?>
			<?php echo $this->loadTemplate('batch'); ?>

			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="setcolor" value="" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>

<?php if ($this->config->show_switch) : ?>
	<div style="text-align:right">
		<a href="<?php echo JRoute::_('index.php?option=com_modules&force=1'); ?>"><?php echo JText::_('AMM_SWITCH_TO_CORE'); ?></a>
	</div>
<?php endif; ?>
<?php
// PRO Check
require_once JPATH_PLUGINS . '/system/nnframework/helpers/licenses.php';
echo NNLicenses::getInstance()->getMessage('ADVANCED_MODULE_MANAGER', 0);

// Copyright
echo NNVersions::getInstance()->getCopyright('ADVANCED_MODULE_MANAGER', '', 10307, 'advancedmodules', 'component');
