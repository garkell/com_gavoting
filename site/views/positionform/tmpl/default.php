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

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.tooltip');
HTMLHelper::_('behavior.formvalidation');
HTMLHelper::_('formbehavior.chosen', 'select');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gavoting', JPATH_ADMINISTRATOR);

$user    = Factory::getUser();
$canEdit = GavotingHelper::canUserEdit($this->item, $user);

?>

<div class="position-edit front-end-edit">
	<?php if (!$canEdit) : ?>
		<h3>
			<?php throw new Exception(Text::_('COM_GAVOTING_ERROR_MESSAGE_NOT_AUTHORISED'), 403); ?>
		</h3>
	<?php else : ?>
		<?php if (!empty($this->item->id)): ?>
			<h2><?php echo Text::sprintf('COM_GAVOTING_EDIT_ITEM_TITLE', $this->item->pos_name); ?></h2>
		<?php else: ?>
			<h2><?php echo Text::_('COM_GAVOTING_ADD_ITEM_TITLE'); ?></h2>
		<?php endif; ?>

		<form id="form-position"
			  action="<?php echo Route::_('index.php?option=com_gavoting&task=positionform.save'); ?>"
			  method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
			
			<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
			<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
			<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />
			<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
			<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />
			<input type="hidden" name="jform[created_by]" value="<?php echo $this->item->created_by; ?>" />
			<input type="hidden" name="jform[modified_by]" value="<?php echo $this->item->modified_by; ?>" />
			<input type="hidden" name="jform[created_date]" value="<?php echo $this->item->created_date; ?>" />
			<input type="hidden" name="jform[modified_date]" value="<?php echo $this->item->modified_date; ?>" />

			<?php echo $this->form->renderField('cat_id'); ?>
			<?php echo $this->form->renderField('pos_name'); ?>
			<?php echo $this->form->renderField('elect_date'); ?>
			<?php echo $this->form->renderField('elected'); ?>
			<?php echo $this->form->renderField('comment'); ?>

			<div class="control-group">
				<div class="controls">

					<?php if ($this->canSave): ?>
						<button type="submit" class="validate btn btn-primary">
							<?php echo Text::_('JSUBMIT'); ?>
						</button>
					<?php endif; ?>
					<a class="btn"
					   href="<?php echo Route::_('index.php?option=com_gavoting&task=positionform.cancel'); ?>"
					   title="<?php echo Text::_('JCANCEL'); ?>">
						<?php echo Text::_('JCANCEL'); ?>
					</a>
				</div>
			</div>

			<input type="hidden" name="option" value="com_gavoting"/>
			<input type="hidden" name="task"
				   value="positionform.save"/>
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	<?php endif; ?>
</div>
