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

/**
 * Modules manager master display controller.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 * @since       1.6
 */
class AdvancedModulesController extends JControllerLegacy
{
	/**
	 * @var      string    The default view.
	 * @since    1.6
	 */
	protected $default_view = 'modules';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean         $cachable       If true, the view output will be cached
	 * @param   array|boolean   $urlparams      An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}
	.
	 *
	 * @return  JController        This object to support chaining.
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		require_once JPATH_COMPONENT.'/helpers/modules.php';

		// Load the submenu.
		ModulesHelper::addSubmenu($this->input->get('view', 'modules'));

		$view   = $this->input->get('view', 'modules');
		$layout = $this->input->get('layout', 'default');
		$id     = $this->input->getInt('id');

		return parent::display();
	}
}
