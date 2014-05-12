<?php
/**
 * @version   $Id: rokcssfixer.php 20114 2014-04-02 17:18:27Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

// no direct access
defined('_JEXEC') or die();

/**
 *
 */
class JFormFieldRokcssfixer extends JFormField
{

    /**
     * @var string
     */
    public $type = 'RokCssFixer';

    /**
     *
     */
    public function getInput()
    {

        $document = JFactory::getDocument();

        $document->addStyleSheet(JURI::Root(true) . "/modules/mod_rokminievents3/admin/rokminievents3-admin.css");
    }
}

?>