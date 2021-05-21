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

$user       = Factory::getUser();
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$canCreate  = $user->authorise('core.create', 'com_gavoting');
$canEdit    = $user->authorise('core.edit', 'com_gavoting');
$canCheckin = $user->authorise('core.manage', 'com_gavoting');
$canChange  = $user->authorise('core.edit.state', 'com_gavoting');
$canDelete  = $user->authorise('core.delete', 'com_gavoting');

if (!$canEdit && Factory::getUser()->authorise('core.edit.own', 'com_gavoting')) {
	$canEdit = Factory::getUser()->id == $this->item->created_by;
}
?>

<h2><?php echo Text::_('COM_GAVOTING_POSITION_DETAILS'); ?></h2>

<div class="item_fields">

	<table class="table">

		<tr>
			<th><?php echo Text::_('COM_GAVOTING_FORM_LBL_POSITION_CAT_ID'); ?></th>
			<td><?php echo $this->item->cat_id_name; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GAVOTING_FORM_LBL_POSITION_POS_NAME'); ?></th>
			<td><?php echo $this->item->pos_name; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GAVOTING_FORM_LBL_POSITION_ELECT_DATE'); ?></th>
			<td><?php if ($this->item->elect_date != '0000-00-00 00:00:00') { echo HtmlHelper::date($this->item->elect_date, Text::_('COM_GAVOTING_DISPLAY_DATE')); } ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GAVOTING_FORM_LBL_POSITION_ELECTED'); ?></th>
			<td><?php echo $this->item->elected; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GAVOTING_FORM_LBL_POSITION_COMMENT'); ?></th>
			<td><?php echo nl2br($this->item->comment); ?></td>
		</tr>

	</table>

</div>

<a class="btn btn-secondary" href="<?php echo Route::_('index.php?option=com_gavoting&view=positions'); ?>">
	<i class="icon-undo"></i> <?php echo Text::_("COM_GAVOTING_RETURN"); ?>
</a>

<?php if($canEdit && $this->item->checked_out == 0): ?>

	<a class="btn btn-warning" href="<?php echo Route::_('index.php?option=com_gavoting&task=position.edit&id='.$this->item->id); ?>">
		<i class="icon-edit"></i> <?php echo Text::_("COM_GAVOTING_EDIT_ITEM"); ?>
	</a>

<?php endif; ?>

<?php if (Factory::getUser()->authorise('core.delete','com_gavoting.position.'.$this->item->id)) : ?>

	<a class="btn btn-danger pull-right" href="#deleteModal" role="button" data-toggle="modal">
		<?php echo Text::_("COM_GAVOTING_DELETE_ITEM"); ?>
	</a>

	<div id="deleteModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="deleteModal" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3><?php echo Text::_('COM_GAVOTING_DELETE_ITEM'); ?></h3>
		</div>
		<div class="modal-body">
			<p><?php echo Text::sprintf('COM_GAVOTING_DELETE_CONFIRM', $this->item->id); ?></p>
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal"><?php echo Text::_('GACLOSE'); ?></button>
			<a href="<?php echo Route::_('index.php?option=com_gavoting&task=position.remove&id=' . $this->item->id, false, 2); ?>" class="btn btn-danger">
				<?php echo Text::_('COM_GAVOTING_DELETE_ITEM'); ?>
			</a>
		</div>
	</div>

<?php endif; ?>

