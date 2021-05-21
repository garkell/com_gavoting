<?php
/**
 * @version     1.4.04
 * @package     com_gavoting
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com.au
 */

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Installer\Installer;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\Filesystem\Path;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\MVC\Model\AdminModel;

class com_gavotingInstallerScript
{

	/**
	 * method to install the component
	 * @return void
	 */
	function install($parent)
	{
		// $parent is the class calling this method
		echo '<p>' . Text::_('COM_GAVOTING_INSTALL_TEXT') . '</p>';

		/* --------------------------------  Action Log  ------------------------------ */
		// New action logging system
		$extension = 'com_gavoting';
        $ActLog = $this->checkIfActionLog($extension);
        if (!$ActLog) {
			$this->loadToActionLog($extension);
			echo '<p>' . Text::_('Action Logging Setup') . '</p>';
		}
        $ALConf = $this->checkIfActionLogConfig($extension);
        if (!$ALConf) {
			$this->loadToActionLogConfig($extension, 'user_id', 'id', 'position_id', '#__gavoting_nominations','COM_GAVOTING_ACTION');
			echo '<p>' . Text::_('Action Log Configuration Setup') . '</p>';
		}
		/* -------------------------------------------------------------- */

		$name = 'gavoting';
		$type = 'search';
        $this->installPlugin($parent, $type, $name);
        $this->installModule($parent, $name);

	}

	/**
	 * method to uninstall the component
	 * @return void
	 */
	function uninstall($parent)
	{
		// $parent is the class calling this method
		echo '<p>' . Text::_('COM_GAVOTING_UNINSTALL_TEXT') . '</p>';
		
		//$this->_removeMenu();
        $this->uninstallModPlug($parent, 'module', 'gavoting');
        $this->uninstallModPlug($parent, 'plugin', 'gavoting');

	}

	/**
	 * method to update the component
	 * @return void
	 */
	function update($parent)
	{
		// $parent is the class calling this method
		echo '<p>' . Text::_('COM_GAVOTING_UPDATE_TEXT') . '</p>';

		/* --------------------------------  Action Log  ------------------------------ */
		// New action logging system
		$extension = 'com_gavoting';
        $ActLog = $this->checkIfActionLog($extension);
        if (!$ActLog) {
			$this->loadToActionLog($extension);
			echo '<p>' . Text::_('Action Logging Setup') . '</p>';
		}
        $ALConf = $this->checkIfActionLogConfig($extension);
        if (!$ALConf) {
			$this->loadToActionLogConfig($extension, 'user_id', 'id', 'position_id', '#__gavoting_nominations','COM_GAVOTING_ACTION');
			echo '<p>' . Text::_('Action Log Configuration Setup') . '</p>';
		}
		/* -------------------------------------------------------------- */

		$name = 'gavoting';
		$type = 'search';
        $this->installPlugin($parent, $type, $name);
        $this->installModule($parent, $name);

	}
 
	/**
	 * method to run before an install/update/uninstall method
	 * @return void
	 */
	function preflight($type, $parent)
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
		// the variable $type was returning lowercase values so have inserted STRTOUPPER
		$type = STRTOUPPER($type);
		echo '<p>' . Text::_('COM_GAVOTING_PREFLIGHT_' . $type . '_TEXT') . '</p>';

	}

	/**
	 * method to run after an install/update/uninstall method
	 * @return void
	 */
	function postflight($type, $parent)
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
		// the variable $type was returning lowercase values so have inserted STRTOUPPER
		$type = STRTOUPPER($type);
		echo '<p>' . Text::_('COM_GAVOTING_POSTFLIGHT_' . $type . '_TEXT') . '</p>';

		// setup data for first time install
        if ($type == "INSTALL") {
            
			$extension = 'com_gavoting';
            // Load categories
			$cattitles = array(
					"2020"
					);
            $cat_id = $this->createCategory($extension, $cattitles);
			$this->updatePositions($cat_id);
		}

	}

	/**
	 * Insert extension to the Action Logs register
	 * @param   string $extension   Extension name
	 * @return boolean
	 */
	function updatePositions($cat_id = 0)
	{
        if ($cat_id) {
			$db = Factory::getDbo();
	        $db->setQuery(' UPDATE #__gavoting_positions SET cat_id = '.(int) $cat_id );
		    try {
		        $db->execute();
		    } catch (RuntimeException $e) {
		        Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
		        return false;
		    }
	    }

		return true;
	}

    /**
    * Function to create category records
    * @param array category titles
    * @param string category group or type
    * @return void
    */
    function createCategory($extension, $cat_titles)
    {
		foreach ($cat_titles as $cat) {
            $category = Table::getInstance('Category');
            $category->extension = $extension;
            $category->title = $cat;
            $category->description = '';
            $category->published = 1;
            $category->access = 1;
            $category->params = '{"category_layout":"","image":"","image_alt":""}';
            $category->metadata = '{"page_title":"","author":"","robots":""}';
            $category->language = '*';
            // Set the location in the tree
            $category->setLocation(1, 'last-child');
            // Check to make sure our data is valid
            if (!$category->check()) {
                throw new Exception(500, $category->getError());
                return false;
            }
            // Now store the category
            if (!$category->store(true)) {
                throw new Exception(500, $category->getError());
                return false;
            }
	 	}
        // Build the path for our category
        $category->rebuildPath($category->id);
        echo '<p>' . Text::_('Categories created') . '</p>';
        return $category->id;
	}

	/**
	 * Install module and/or plugin for this component
	 * @param   mixed $parent Object who called the install/update method
	 * @return void
	 */
	public function installPlugin($parent, $type = '', $name = '')
	{
		$app = Factory::getApplication();
		$installer = new Installer;
		$installation_folder = $parent->getParent()->getPath('source');
		$path = $installation_folder . '/plugins/'.$type.'/';

		if (!$this->isAlreadyInstalled('plugin', $name, $type)) {
			$result = $installer->install($path);
		} else {
			$result = $installer->update($path);
		}

		if ($result) {
			$app->enqueueMessage('Installation of '.$type.' Plugin was successful.', 'message');
			return true;
		} else {
			$app->enqueueMessage('There was an issue installing the '.$type.' Plugin', 'error');
			return false;
		}

	}

	/**
	 * Install module and/or plugin for this component
	 * @param   mixed $parent Object who called the install/update method
	 * @return void
	 */
	public function installModule($parent, $name = '')
	{
		$app = Factory::getApplication();
		$installer = new Installer;
		$installation_folder = $parent->getParent()->getPath('source');
		$path = $installation_folder . '/modules/';

		if (!$this->isAlreadyInstalled('module', 'mod_'.$name, null)) {
			$result = $installer->install($path);
		} else {
			$result = $installer->update($path);
		}

		if ($result) {
			$app->enqueueMessage('Installation of Module was successful.', 'message');
			return true;
		} else {
			$app->enqueueMessage('There was an issue installing the Module', 'error');
			return false;
		}

	}

	/**
	 * @param   string  $parent  parent
	 * @return void
	 */
	public function discover_install($parent)
	{
		return self::install($parent);
	}

	/**
	 * @param   string  $group    group
	 * @param   string  $element  element
	 * @return boolean
	 */
	public function enablePlugin($group, $element)
	{
		$plugin = Table::getInstance('extension');
		if (!$plugin->load(array('type' => 'plugin', 'folder' => $group, 'element' => $element)))
		{
			return false;
		}
		$plugin->enabled = 1;
		return $plugin->store();
	}


	/**
	 * Uninstalls modules
	 * @param   mixed $parent Object who called the uninstall method
	 * @return void
	 */
	function uninstallModPlug($parent, $modplug = 'module', $mpName = '')
	{
		$app = Factory::getApplication();
		$modplugs = $modplug.'s';

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->clear();
		$query->select('extension_id');
		$query->from('#__extensions');
		if ($modplug == 'plugin') {
			$query->where(
					array (
						'type LIKE ' . $db->quote($modplug),
						'element LIKE ' . $db->quote($mpName),
						'folder LIKE ' . $db->quote('search')
					)
				);
		} else {
			$query->where(
					array (
						'type LIKE ' . $db->quote($modplug),
						'element LIKE ' . $db->quote('mod_'.$mpName)
					)
				);
		}
		$db->setQuery($query);
		$extension = $db->loadResult();

		if (!empty($extension)) {
			$installer = new Installer;
			$result    = $installer->uninstall($modplug, $extension);

			if ($result) {
				$app->enqueueMessage($modplug . ' ' . $mpName . ' was uninstalled successfully');
			} else {
				$app->enqueueMessage('There was an issue uninstalling the ' . $modplug . ' ' . $mpName, 'error');
			}
		} else {
			$app->enqueueMessage('Empty extension for ' . $modplug . ' ' . $mpName, 'warning');
		}
	}


	/**
	 * Check if an extension is already installed in the system
	 *
	 * @param   string $type   Extension type
	 * @param   string $name   Extension name
	 * @param   mixed  $folder Extension folder(for plugins)
	 *
	 * @return boolean
	 */
	function isAlreadyInstalled($type, $name, $folder = null)
	{
		$result = false;

		switch ($type)
		{
			case 'plugin':
				$result = file_exists(JPATH_PLUGINS . '/' . $folder . '/' . $name);
				break;
			case 'module':
				$result = file_exists(JPATH_SITE . '/modules/' . $name);
				break;
		}

		return $result;
	}

	/**
	 * Check if extension is set in Action Logs register
	 * @param   string $extension   Extension name
	 * @return boolean
	 */
	function checkIfActionLog($extension)
	{
		$result = false;

		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__action_logs_extensions'))
			->where($db->quoteName('extension') .' = '. $db->Quote($extension));
		$db->setQuery($query);
	    try {
	        // If it fails, it will throw a RuntimeException
	        $result = $db->loadResult();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	    }

		return $result;
	}

	/**
	 * Insert extension to the Action Logs register
	 * @param   string $extension   Extension name
	 * @return boolean
	 */
	function loadToActionLog($extension)
	{
		$result = false;
        $db = Factory::getDbo();
        $db->setQuery(' INSERT into #__action_logs_extensions (extension) VALUES ('.$db->Quote($extension).') ' );
	    try {
	        // If it fails, it will throw a RuntimeException
	        $result = $db->execute();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }

		return $result;
	}

	/**
	 * Check if extension is set in Action Logs register
	 * @param   string $extension   Extension name
	 * @return boolean
	 */
	function checkIfActionLogConfig($extension)
	{
		$result = false;

		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__action_log_config'))
			->where($db->quoteName('type_alias') .' = '. $db->Quote($extension));
		$db->setQuery($query);
	    try {
	        // If it fails, it will throw a RuntimeException
	        $result = $db->loadResult();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	    }

		return $result;
	}

	/**
	 * Insert extension to the Action Log Configuration record
	 * @param   string $extension   Extension name
	 * @return boolean
	 */
	function loadToActionLogConfig($extension, $type, $key = 'id', $title, $tablename, $txtpref)
	{
		// Create and populate an object.
		$logConf = new stdClass();
		$logConf->id = 0;
		$logConf->type_title = $type;
		$logConf->type_alias = $extension;
		$logConf->id_holder = $key;
		$logConf->title_holder = $title;
		$logConf->table_name = $tablename;
		$logConf->text_prefix = $txtpref;

	    try {
	        // If it fails, it will throw a RuntimeException
			// Insert the object into the user profile table.
			$result = Factory::getDbo()->insertObject('#__action_log_config', $logConf);
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }

		return $result;
	}

}
