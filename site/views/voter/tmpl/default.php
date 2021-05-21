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

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');
HTMLHelper::_('bootstrap.modal');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gavoting', JPATH_ADMINISTRATOR);

$canEdit = Factory::getUser()->authorise('core.edit', 'com_gavoting');
$canDelete = Factory::getUser()->authorise('core.delete', 'com_gavoting');
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');

if (!$canEdit && Factory::getUser()->authorise('core.edit.own', 'com_gavoting'))
{
	$canEdit = Factory::getUser()->id == $this->item->created_by;
}
?>

<h2><?php echo Text::_('COM_GAVOTING_VOTER_RECORD'); ?></h2>

<div class="item_fields">

	<table class="table">

		<tr>
			<th><?php echo Text::_('COM_GAVOTING_FORM_LBL_VOTER_USER_ID'); ?></th>
			<td><?php echo $this->item->user_id_name; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GAVOTING_FORM_LBL_VOTER_CAT_ID'); ?></th>
			<td><?php echo $this->item->cat_id; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GAVOTING_FORM_LBL_VOTER_PROXY_VOTE'); ?></th>
			<td><?php if ($this->item->proxy_vote) { echo Text::_('JYES').' ('.$this->item->created_by_name.')'; } else { echo Text::_('JNO'); } ?></td>
		</tr>

	</table>

</div>

<a class="btn btn-secondary" href="<?php echo Route::_('index.php?option=com_gavoting&view=voters'); ?>">
	<i class="icon-undo"></i> <?php echo Text::_("COM_GAVOTING_RETURN"); ?>
</a>
