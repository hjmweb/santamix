<?php
/**
 * @version   $Id: renderer.php 20114 2014-04-02 17:18:27Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2013 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

// no direct access
defined('JPATH_PLATFORM') or die;

check_token('request') or jexit('Invalid Token');

class RTRenderer
{
	public function render()
	{
		$module_name = JFactory::getApplication()->input->getString('module');
		$module_id   = JFactory::getApplication()->input->getInt('moduleid');

		$db = JFactory::getDBO();
		if (isset($module_name) && $module_name != '') {
			$query = "SELECT * from #__modules where title=" . $db->quote($module_name);
		} else if (isset($module_id) && $module_id != '') {
			$query = "SELECT * from #__modules where id=" . $module_id;
		} else {
			die;
		}

		$db->setQuery($query);
		$result = $db->loadObject();


		if ($result) {
			$page     = JFactory::getApplication()->input->getInt('page', 2);
			$module         = JModuleHelper::getModule($result->module);
			$module->params = $result->params;
			$params         = new JRegistry($result->params);

			$rokminievents3 = new RokMiniEvents3();
			$events        = $rokminievents3->getEvents($params);

			$pages    = ceil(count($events) / $params->get('events_pane'));
			$per_pane = $params->get('events_pane');


			//$output = $renderer->render($module, $options);

			$output                  = new stdClass();
			$output->status          = 'success';
			$output->message         = '';
            $output->id              = $module_id;
			$output->page            = $page;
			$output->payload         = array();

			if (count($events)) {
                $start = (($page * $per_pane) - ($per_pane - 1)) - 1;
                $stop  = (count($events) < ($page * $per_pane)) ? count($events) - 1 : (($page * $per_pane) - 1);

                $output->payload['html'] = '';
				for ($i = $start; $i <= $stop; $i++) {
					$event    = $events[$i];
					ob_start();
					include(JModuleHelper::getLayoutPath('mod_rokminievents3', 'default_item'));
					$output->payload['html'][] = ob_get_clean();
				}
			}
			echo json_encode($output);
		}
	}
}
