<?php
/*
# ------------------------------------------------------------------------
# @version     1.4.04
# @copyright   Copyright (C) 2020. All rights reserved.
# @license     GNU General Public License version 2 or later; see LICENSE.txt
# Author:      Glenn Arkell
# Websites:    http://www.glennarkell.com.au
# ------------------------------------------------------------------------
*/
// no direct access
defined('_JEXEC') or die( 'Restricted access' );

use \Joomla\CMS\Factory;
use \Joomla\CMS\Date\Date;

class modGavotingHelper
{
	var $items;

    /**
     * Retrieves records to display
     * @param array $params An object containing the module parameters
     * @access public
     */
    public static function getPositions( $params )
    {
        JHtml::_('stylesheet','mod_gavoting/default.css', false, true);
		$agm_date = new Date($params->get('agm_date'));
		$agm_date = $agm_date->modify('-400 days');
		$lastElectDate = $agm_date->format('Y-m-d h:i:s');

    	// Query the articles table to get the articles in the selected category
   		$db = Factory::getDbo();
   		$query = $db->getQuery(true);
		$query->select(' a.*, CONCAT(a.pos_name, " - ",c.title) AS position, c.title AS elect_year ');
   		$query->from('#__gavoting_positions AS a ');
   		$query->join('LEFT','#__categories AS c ON c.id = a.cat_id ');
   		//$query->join('LEFT','#__categories AS c ON c.id = a.cat_id  AND c.title = substr(a.elect_date,1,4)');
		$query->where($db->quoteName('a.state') . ' = 2 ');
		//$query->where($db->quoteName('a.elect_date') . ' >= '.$db->Quote($lastElectDate));
        $query->order($db->quoteName('c.title') . ' desc ');

   		$db->setQuery((string)$query);
	    try {
	        $items = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
	        return false;
	    }

    	return $items;
    }

}
?>
