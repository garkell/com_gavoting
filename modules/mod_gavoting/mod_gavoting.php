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

require_once( dirname(__FILE__).'/helper.php' );

use \Joomla\CMS\Helper\ModuleHelper;

JLoader::register('GavotingHelper', JPATH_ADMINISTRATOR . '/components/com_gavoting/helpers/gavoting.php');

$items = modGavotingHelper::getPositions( $params );
require( ModuleHelper::getLayoutPath( 'mod_gavoting' ) );
?>
