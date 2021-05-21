<?php
/**
 * @version    1.4.04
 * @package    Com_Gavoting
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2020 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\MVC\Controller\BaseController;

JLoader::registerPrefix('Gavoting', JPATH_SITE . '/components/com_gavoting');
JLoader::register('GavotingController', JPATH_SITE . '/components/com_gavoting/controller.php');
JLoader::register('GavotingHelper', JPATH_ADMINISTRATOR . '/components/com_gavoting/helpers/gavoting.php');
JLoader::register('GaupdparamsHelper', JPATH_ADMINISTRATOR . '/components/com_gavoting/helpers/gaupdparams.php');


// Execute the task.
$controller = BaseController::getInstance('Gavoting');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
