<?php

/**
 * @version    0.0.01
 * @package    Com_Gaupdparams
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2020 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\Data\DataObject;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\CMS\MVC\Model\BaseDatabaseModel;
use \Joomla\CMS\Installer\Installer;
use \Joomla\Filesystem\Path;
use \Joomla\CMS\Toolbar\Toolbar;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\User\UserHelper;
use \Joomla\CMS\Categories\Categories;
use \Joomla\CMS\Table\Table;

/**
 * Gaupdparams helper.
 *
 * @since  1.6
 */
class GaupdparamsHelper
{
    /**
     * Gets todays date based on global timezone settings
     */
    public static function getTodaysDate()
	{

		$tz = Factory::getConfig()->get('offset');
		$date = Factory::getDate('now', $tz);
		$today = date_format($date,'Y-m-d H:i:s');
		
		return $today;
	}

	/**
	 * Create new category record
	 * @return boolean true on success
	 */
	public static function rolloverElectionYear()
	{
		$lastYear = GavotingHelper::getElectionYear();
		$nxtYear = $lastYear->title + 1;
        $extension = 'com_gavoting';
        
        // now update election year record (category)
		$lastYear->published = 0;
        $result = Factory::getDbo()->updateObject('#__categories', $currYear, 'id');


        $category = Table::getInstance('Category');
        $category->extension = $extension;
        $category->title = $nxtYear;
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
        // Build the path for our category
        $category->rebuildPath($category->id);
	
	    // now reset params with new election year
	    GaupdparamsHelper::updateExtensionParams($nxtYear);

	    // update position records
	    GaupdparamsHelper::updatePositions($category->id);
	    
	    // tidy up the old nominations 
		GaupdparamsHelper::archiveNominations();

		return true;
	}

	/**
	 * Update records
	 * @params integer id reference for the new election year (category)
	 * @return boolean true on success
	 */
	public static function updatePositions($id = 0)
	{
	    $today = GaupdparamsHelper::getTodaysDate();
		$db		= Factory::getDbo();
		$db->setQuery((string)'SELECT * FROM #__gavoting_positions WHERE state = 1 ' );
	    try {
            $objects = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
	        return false;
	    }

		// cycle through each record to duplicate for new election year
		foreach ($objects AS $obj) {
			$winner = GaupdparamsHelper::getNominationMostVotes($obj->id);
			$obj->state = 2;
			$obj->modified_date = $today;
			$obj->elect_date = $today;
			$obj->elected = $winner->nom_name;
			Factory::getDbo()->updateObject('#__gavoting_positions', $obj, 'id');

			$newObj = new stdClass();
			$newObj->id = 0;
			$newObj->state = 1;
			$newObj->pos_name = $obj->pos_name;
			$newObj->created_date = $today;
			$newObj->cat_id = $id;
			Factory::getDbo()->insertObject('#__gavoting_positions', $newObj);
		}

		return true;
	}

	/**
	 * Get winning votes for position records
	 * @params integer id reference for the position
	 * @return string name of winning nomination
	 */
	public static function getNominationMostVotes($id = 0)
	{
		$db		= Factory::getDbo();
		$query	= $db->getQuery(true);
        $query->select(' nom_name, max(votes) AS vote_count ' );
		$query->from(' #__gavoting_nominations ');
        $query->where(' position_id = '.(int) $id );
        $query->where(' state = 1 ' );
        $query->group(' nom_name LIMIT 1' );
		$db->setQuery((string)$query);
	    try {
            return $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
	        return false;
	    }
	}

	/**
	 * Update records
	 * @return boolean true on success
	 */
	public static function archiveNominations()
	{
		$db		= Factory::getDbo();
		$db->setQuery((string)'UPDATE #__gavoting_nominations SET state = 2 WHERE state = 1' );
	    try {
            $db->execute();
            return true;
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
	        return false;
	    }
	}

    public static function updateExtensionParams($nxtYear = '2020')
	{
        // get the params
        $compname = 'com_gavoting';
        $comptype = 'component';

        // get the payment date value in the configuration data
        $db		= Factory::getDbo();
		$query	= $db->getQuery(true);
        $query->clear();
        $query->select(' * ' );
		$query->from(' #__extensions ');
        $query->where(' name = '.$db->Quote($compname) );
        $query->where(' type = '.$db->Quote($comptype) );
        $query->where(' element = '.$db->Quote($compname) );
		$db->setQuery((string)$query);
        
	    try {
            $object = $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
	    }

		///////////////////////////////////////////////////////////
		// dates are datetime stamp CCYY-MM-DD HH:MM:SS

        $params = json_decode($object->params);
        $nxtAGM = $nxtYear.substr($params->agm_date,4,15);
        $clNoms = $nxtYear.substr($params->close_noms,4,15);
        $opVote = $nxtYear.substr($params->open_votes,4,15);
        $clVote = $nxtYear.substr($params->close_votes,4,15);
        $params->agm_date = $nxtAGM;
        $params->close_noms = $clNoms;
        $params->open_votes = $opVote;
        $params->close_votes = $clVote;
        $params = json_encode($params);

		///////////////////////////////////////////////////////////

        // update the params
        $db		= Factory::getDbo();
		$query	= $db->getQuery(true);
        $query->clear();
		$query->update(' #__extensions ');
        $query->set(' params = '.$db->Quote($params) );
        $query->where(' name = '.$db->Quote($compname) );
        $query->where(' type = '.$db->Quote($comptype) );
        $query->where(' element = '.$db->Quote($compname) );
		$db->setQuery((string)$query);
	    try {
            $db->execute();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
	    }

	}
}

