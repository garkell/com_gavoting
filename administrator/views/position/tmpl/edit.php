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

HTMLHelper::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_gavoting/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');

?>

<script type="text/javascript">
	js = jQuery.noConflict();
	js(document).ready(function () {
		
	});

	Joomla.submitbutton = function (task) {
		if (task == 'position.cancel') {
			Joomla.submitform(task, document.getElementById('position-form'));
		} else {
			if (task != 'position.cancel' && document.formvalidator.isValid(document.id('position-form'))) {
				Joomla.submitform(task, document.getElementById('position-form'));
			} else {
				alert('<?php echo $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}
</script>

<form
	action="<?php echo Route::_('index.php?option=com_gavoting&view=position&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="position-form" class="form-validate form-horizontal">

	
	<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
	<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
	<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />

	<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'position')); ?>
	<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'position', empty($this->item->id) ? Text::_('COM_GAVOTING_TAB_POSITION') : Text::_('COM_GAVOTING_TAB_POSITION')); ?>
	<div class="row-fluid">
		<div class="span10 form-horizontal">
			<fieldset class="adminform">
				<legend><?php echo Text::_('COM_GAVOTING_FIELDSET_POSITION'); ?></legend>
				<?php echo $this->form->renderField('cat_id'); ?>
				<?php echo $this->form->renderField('pos_name'); ?>
				<?php echo $this->form->renderField('elect_date'); ?>
				<?php echo $this->form->renderField('elected'); ?>
			</fieldset>
		</div>
	</div>
	<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

	<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'sysinfo', Text::_('COM_GAVOTING_TAB_SYSINFO', true)); ?>
	<div class="row-fluid">
		<div class="span10 form-horizontal">
			<fieldset class="adminform">
				<?php echo $this->form->renderField('comment'); ?>
				<?php echo $this->form->renderField('created_by'); ?>
				<?php echo $this->form->renderField('created_date'); ?>
				<?php echo $this->form->renderField('modified_by'); ?>
				<?php echo $this->form->renderField('modified_date'); ?>
				<?php echo $this->form->renderField('id'); ?>
				<?php echo $this->form->renderField('state'); ?>
				<?php if ($this->state->params->get('save_history', 1)) : ?>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('version_note'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('version_note'); ?></div>
					</div>
				<?php endif; ?>
			</fieldset>
		</div>
	</div>
	<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

	<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>

	<input type="hidden" name="task" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>

</form>
