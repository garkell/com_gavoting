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

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gavoting', JPATH_ADMINISTRATOR);

$user = Factory::getUser();

$canEdit = $user->authorise('core.edit', 'com_gavoting');
$canDelete = $user->authorise('core.delete', 'com_gavoting');
$canManage = $user->authorise('core.manage', 'com_gavoting');

if (!$canEdit && $user->authorise('core.edit.own', 'com_gavoting')) {
	$canEdit = $user->id == $this->item->motion;
}

?>

<h2><?php echo Text::_('COM_GAVOTING_TITLE_MOTION'); ?></h2>

<div class="item_fields">

	<div class="row-fluid span12">
		<table class="table" style="margin-bottom: 30px;">
			<tbody>
			<tr><th><?php echo Text::_('COM_GAVOTING_FORM_LBL_MOTION_MOV_DATE'); ?></th>
				<td><?php echo substr($this->item->mov_date,0,10); ?></td>
			<tr>
			<tr><th><?php echo Text::_('COM_GAVOTING_FORM_LBL_MOTION_MOV_ID'); ?></th>
				<td><?php echo $this->item->mov_id_name; ?></td>
			<tr>
			<tr><th><?php echo Text::_('COM_GAVOTING_FORM_LBL_MOTION_SEC_ID'); ?></th>
				<td><?php echo $this->item->sec_id_name; ?></td>
			<tr>
			<tr><th><?php echo Text::_('COM_GAVOTING_FORM_LBL_MOTION_AGREED'); ?></th>
				<td><?php if ($this->item->agreed) { echo '<i class="icon-publish"></i>';} else { echo '<i class="icon-unpublish"></i>';} ?></td>
			<tr>
			<tr><th><?php echo Text::_('COM_GAVOTING_FORM_LBL_MOTION_AGREED_DATE'); ?></th>
				<td><?php echo substr($this->item->agreed_date,0,10); ?></td>
			<tr>
			<tr><th><?php echo Text::_('COM_GAVOTING_FORM_LBL_MOTION_VOTES_FOR'); ?></th>
				<td><?php echo $this->item->votes_for; ?></td>
			<tr>
			<tr><th><?php echo Text::_('COM_GAVOTING_FORM_LBL_MOTION_VOTES_AGAINST'); ?></th>
				<td><?php echo $this->item->votes_against; ?></td>
			<tr>
			</tbody>
		</table>

		<a class="btn btn-secondary" href="<?php echo Route::_('index.php?option=com_gavoting&task=motionform.cancel'); ?>" title="<?php echo Text::_('COM_GAVOTING_RETURN_DESC'); ?>">
		<i class="icon-undo"></i> <?php echo Text::_("COM_GAVOTING_RETURN"); ?>
		</a>

		<?php if($canEdit && $this->item->checked_out == 0): ?>

			<a class="btn btn-warning" href="<?php echo Route::_('index.php?option=com_gavoting&task=motion.edit&id='.$this->item->id); ?>" title="<?php echo Text::_('COM_GAVOTING_EDIT_DESC'); ?>">
			<i class="icon-edit"></i> <?php echo Text::_("COM_GAVOTING_EDIT_ITEM"); ?>
			</a>

		<?php endif; ?>

		<?php if ($canDelete) : ?>
			<a class="btn btn-danger pull-right" href="#deleteModal" role="button" data-toggle="modal"  title="<?php echo Text::_('COM_GAVOTING_DELETE_DESC'); ?>">
				<i class="icon-trash"></i> <?php echo Text::_("COM_GAVOTING_DELETE_ITEM"); ?>
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
					<a href="<?php echo Route::_('index.php?option=com_gavoting&task=motion.remove&id=' . $this->item->id, false, 2); ?>" class="btn btn-danger">
						<?php echo Text::_('COM_GAVOTING_DELETE_ITEM'); ?>
					</a>
				</div>
			</div>

		<?php endif; ?>

	</div>

</div>

