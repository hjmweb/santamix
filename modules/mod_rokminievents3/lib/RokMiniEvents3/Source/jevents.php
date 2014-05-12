<?php
/**
 * @version   $Id: jevents.php 20114 2014-04-02 17:18:27Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('_JEXEC') or die('Restricted access');

class RokMiniEvents3SourceJEvents extends RokMiniEvents3_SourceBase
{
    function getEvents(&$params)
    {
        // Reuse existing language file from JomSocial
        $language = JFactory::getLanguage();
        $language->load('com_jevents', JPATH_ROOT);


        $query_start_date = null;
        $query_end_date = null;

        if ($params->get('time_range') == 'time_span' || $params->get('rangespan') != 'all_events') {
            $query_start_date = $params->get('startmin');
            $startMax = $params->get('startmax', false);
            if ($startMax !== false) {
                $query_end_date = $startMax;
            }
        }


        // setup for all required function and classes
        $file = JPATH_SITE . '/components/com_jevents/mod.defines.php';
        if (file_exists($file)) {
            include_once($file);
            include_once(JEV_LIBS . "/modfunctions.php");

        } else {
            die ("JEvents Calendar\n<br />This module needs the JEvents component");
        }

        $eventHelper = @new JEVHelper();
        // load language constants
        @$eventHelper->loadLanguage('modlatest');


        $datamodel = @new JEventsDataModel();
        $showrepeats = ($params->get('jevents_norepeats', 0) == 0) ? true : false;
        $catids = $params->get('jevents_category', array());
        if (!empty($catids) && !in_array('0', $catids)) {
            $params->set('catidnew', $catids);
        }
        $myItemid = @$datamodel->setupModuleCatids($params);
        $catout = @$datamodel->getCatidsOutLink(true);

        $reg = @JevRegistry::getInstance("jevents");
        $reg->setReference("jevents.datamodel", $datamodel);


        if (!empty($query_start_date)) {
            $rstartdate = new RokMiniEvents3_Date($query_start_date);
            if ($params->get('jevents_past', 0) == 0 && $rstartdate->toUnix() < time()) {
                $rstartdate = new RokMiniEvents3_Date(time());
            }
            $dates_start = $rstartdate->toISO8601();
        } else if ($params->get('jevents_past', 0) == 0) {
            $rstartdate = new RokMiniEvents3_Date(time());
            $dates_start = $rstartdate->toISO8601();
        } else {
            $dates_start = date('Y-m-d\T23:59:59', strtotime("-1 month"));
        }

        if (empty($query_end_date)) {
            $dates_end = date('Y-m-d\T23:59:59', strtotime("+1 year"));
        } else {
            $dates_end = $query_end_date;
        }

        $rows = @$datamodel->queryModel->listIcalEventsByRange($dates_start, $dates_end, 0, 20, $showrepeats);

        $events = array();
        if(empty($rows)) return $events;
        $total_count = 1;
        $total_max = $params->get('jevents_total', 10);
        foreach ($rows as $row) {
            if ($params->get('jevents_links') != 'link_no') {
                $title = JFilterOutput::stringURLSafe($row->_title);
                $itemId = @$eventHelper->getItemid();

                if (($params->get('jevents_links') == 'link_internal_event') || ($params->get('jevents_links') == 'link_external_event')) {

                    $link = array(
                        'internal' => ($params->get('jevents_links') == 'link_internal_event') ? true : false,
                        'link' => JRoute::_('index.php?option=' . JEV_COM_COMPONENT . '&task=icalrepeat.detail&rp_id=' . $row->_eventid . '&rp_id=' . $row->_rp_id . '&Itemid=' . $itemId . '&year=' . $row->_yup . '&month=' . $row->_mup . '&day=' . $row->_dup . 'title=' . $title)

                    );
                } else {
                    $link = array(
                        'internal' => ($params->get('jevents_links') == 'link_internal') ? true : false,
                        'link' => JRoute::_('index.php?option=' . JEV_COM_COMPONENT . '&task=month.calendar&year' . $row->_yup . '&month=' . $row->_mup . '&day=' . $row->_dup . '&Itemid=' . $itemId)
                    );
                }

            } else {
                $link = false;
            }

            $event = new RokMiniEvents3_Event($row->_unixstarttime, $row->_unixendtime, $row->_title, $row->_content, $link);
            $event->setAllDay(($row->_alldayevent) ? true : false);
            if ($row->_noendtime == "1") $event->setNoEndTime(true);
            $events[] = $event;
            $total_count++;
            if ($total_count > $total_max) break;
        }
        return $events;
    }

    /**
     * Checks to see if the source is available to be used
     * @return bool
     */
    function available()
    {
        $db = JFactory::getDBO();
        $query = 'select count(*) from #__extensions where element = ' . $db->Quote('com_jevents') . ' AND enabled=1';
        $db->setQuery($query);
        $count = (int)$db->loadResult();
        if ($count > 0) {
            return true;
        }
        return false;
    }
}
