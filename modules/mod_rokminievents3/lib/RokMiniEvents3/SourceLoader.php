<?php
/**
 * @version   $Id: SourceLoader.php 20114 2014-04-02 17:18:27Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('ROKMINIEVENTS3') or die('Restricted access');

class RokMiniEvents3_SourceLoader implements RTCommon_Loader
{

	const CLASS_NAME_PREFIX = 'RokMiniEvents3Source';

	public function __construct()
	{
		$this->addSourcePath(dirname(__FILE__) . '/Source');
	}

	/**
	 * @var array
	 */
	private $_orderedPaths = array();
	private $_allPaths = array();

	public function addSourcePath($path, $priority = 10)
	{
		if (in_array($path, $this->_allPaths)) return;
		if (!file_exists($path) || !is_dir($path)) {
			throw new RokMiniEvents3_Exception($path . ' is not a valid directory.');
		}
		$this->_orderedPaths[$priority][$path] = $path;
		$this->_allPaths[]                     = $path;
	}

	/**
	 * @param  string $className the class name to look for and load
	 *
	 * @return bool True if the class was found and loaded.
	 */
	public function loadClass($className)
	{
		$fileName = strtolower(str_replace(self::CLASS_NAME_PREFIX, '', $className) . self::FILE_EXTENSION);
		foreach ($this->_orderedPaths as $priority => $priorityPaths) {
			foreach ($priorityPaths as $path) {
				$full_file_path = $path . DIRECTORY_SEPARATOR . $fileName;
				if (file_exists($full_file_path) && is_readable($full_file_path)) {
					require($full_file_path);
					return true;
				}

			}
		}
		return false;
	}

	public static function getAvailableSources($sourcedir)
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		$paths   = self::getFrontsideTemplates();
		$paths[] = $sourcedir;

		foreach ($paths as $path) {
			RokMiniEvents3::addSourcesPath($path);
		}

		$results = array();
		foreach ($paths as $source_path) {
			if (JFolder::exists($source_path)) {
				$source_files = JFolder::files($source_path);
				foreach ($source_files as $entry) {
					$source_name = basename($entry, ".php");
					$path        = $source_path . '/' . $source_name . '.php';
					if (JFile::exists($path) && !array_key_exists($source_name, $results)) {
						$sourceClass = self::CLASS_NAME_PREFIX . ucfirst($source_name);
						$source      = new $sourceClass();
						if ($source->available()) {
							$source_info             = new stdClass();
							$source_info->name       = $source_name;
							$source_info->source     = $source;
							$source_info->paramspath = $source_path . '/' . $source_name . '.xml';
							$results[$source_name]   = $source_info;
						}

					}

				}
			}
		}

		return $results;
	}

	private static function getFrontsideTemplates()
	{
		jimport('joomla.filesystem.folder');
		$templateDirs = JFolder::folders(JPATH_ROOT . '/templates');
		$rows         = array();
		foreach ($templateDirs as $templateDir) {
			$posible_sourcedir = $templateDir . "/html/mod_rokminievents3/sources";
			if (file_exists($posible_sourcedir) && is_dir($posible_sourcedir)) {
				$rows[] = $posible_sourcedir;
			}
		}
		return $rows;
	}
}
