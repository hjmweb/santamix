<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('joomla.filesystem.folder');

class DPCalendarControllerSend extends JControllerLegacy
{

	public function translation ()
	{
		$files = array();
		$db = JFactory::getDbo();
		$db->setQuery("SELECT *  FROM `#__extensions` WHERE  `element` LIKE  '%dpcalendar%'");
		$dpcalendarExtensions = $db->loadObjectList();
		foreach ($dpcalendarExtensions as $ext)
		{
			if ($ext->type == 'module')
			{
				$files = array_merge($files, $this->addLanguage(JPATH_ROOT . '/modules/' . $ext->element . '/language/', $ext->element));
			}
			if ($ext->type == 'plugin')
			{
				$files = array_merge($files,
						$this->addLanguage(JPATH_ROOT . '/plugins/' . $ext->folder . '/' . $ext->element . '/language/', $ext->name));
			}
			if ($ext->type == 'component')
			{
				$files = array_merge($files, $this->addLanguage(JPATH_ROOT . '/components/' . $ext->element . '/language/', 'com_dpcalendar/site/'));
				$files = array_merge($files,
						$this->addLanguage(JPATH_ADMINISTRATOR . '/components/' . $ext->element . '/language/', 'com_dpcalendar/admin/'));
			}
		}

		$zipFileName = JPATH_ROOT . '/tmp/dpcalendar-language.zip';
		JFile::delete($zipFileName);
		$zip = JArchive::getAdapter('zip');
		if ($zip->create($zipFileName, $files))
		{
			$path = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_dpcalendar' . DS . 'dpcalendar.xml';
			if (file_exists($path))
			{
				$mailer = JFactory::getMailer();
				$mailer->setSubject('New translations');
				$mailer->setBody('Hi Digital Peak<p>The user ' . JFactory::getUser()->name . ' sent you his translations.</p>Integrate it!');
				$mailer->IsHTML(true);
				$mailer->addAttachment($zipFileName);
				$mailer->addRecipient(JFactory::getUser()->email);
				$manifest = simplexml_load_file($path);
				$mailer->addRecipient((string) $manifest->authorEmail);
				$mailer->Send();
				$this->setMessage('Sent your translation to Digital Peak. It will be included in the next release. Thank you!');
			}
			else
			{
				$this->setMessage('Could not send your translation to Digital Peak. You need to send the file ' . $zipFileName . ' manually!',
						'warning');
			}
		}
		else
		{
			$this->setMessage('Could not create the zip file!', 'error');
		}
		$this->setRedirect('index.php?option=com_dpcalendar');
		return true;
	}

	private function addLanguage ($folder, $name)
	{
		$files = array();
		foreach (JFolder::files($folder, 'ini', true, true) as $file)
		{
			if (strpos($file, 'en-GB') !== false)
			{
				continue;
			}
			$files[] = array(
					'name' => JPath::clean($name . '/' . substr($file, strpos($file, 'language/'))),
					'data' => JFile::read($file)
			);
		}
		return $files;
	}
}
