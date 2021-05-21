<?php
/**
 * @version    1.4.04
 * @package    Com_Gavoting
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2020 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\MVC\Controller\BaseController;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;

// Access check.
if (!Factory::getUser()->authorise('core.manage', 'com_gavoting'))
{
	throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'));
}

JLoader::registerPrefix('Gavoting', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_gavoting');
JLoader::register('GavotingHelper', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_gavoting' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'gavoting.php');
JLoader::register('GaupdparamsHelper', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_gavoting' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'gaupdparams.php');
JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_fields' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'fields.php');

$controller = BaseController::getInstance('Gavoting');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
