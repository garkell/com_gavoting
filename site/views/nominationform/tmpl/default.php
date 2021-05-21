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
$canCheckin = $user->authorise('core.manage', 'com_gavoting');
$close_noms = GavotingHelper::nominationsClosed();
$today = GavotingHelper::getTodaysDate();

?>

<div class="nomination-edit front-end-edit">
	<?php if ($close_noms) : ?>
		<h3>
			<?php echo Text::_('COM_GAVOTING_NOMINATIONS_CLOSED'); ?>
		</h3>
	<?php else : ?>
		<?php if (!$canEdit) : ?>
			<h3>
				<?php throw new Exception(Text::_('COM_GAVOTING_ERROR_MESSAGE_NOT_AUTHORISED'), 403); ?>
			</h3>
		<?php else : ?>
			<h2><?php echo Text::_('COM_GAVOTING_TITLE_NOMINATION'); ?></h2>
			<?php if ($this->params->get('req_sec_nom', 1)) : ?>
				<h3><?php echo Text::_('COM_GAVOTING_NOMINATION_SUBMIT_TEXT'); ?></h3>
			<?php else : ?>
				<h3><?php echo Text::_('COM_GAVOTING_NOMINATION_SUBMIT_TEXT_NOSEC'); ?></h3>
            <?php endif ?>

			<form id="form-nomination"
				  action="<?php echo Route::_('index.php?option=com_gavoting&task=nominationform.save'); ?>"
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
				<input type="hidden" name="jform[nom_date]" value="<?php echo $today; ?>" />
	
				<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'nomination')); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'nomination', Text::_('COM_GAVOTING_TAB_NOMINATION', true)); ?>
				<div class="row-fluid">
					<div class="span10 form-horizontal">
						<fieldset class="adminform">
							<legend><?php echo Text::_('COM_GAVOTING_FIELDSET_NOMINATION'); ?></legend>
							<?php echo $this->form->renderField('position_id'); ?>
							<?php echo $this->form->renderField('nomination'); ?>
							<?php if ($this->params->get('single_mship',0)) : ?>
								<?php echo $this->form->renderField('oth_mbr'); ?>
							<?php endif; ?>
							<?php if ($canCheckin) : ?>
								<?php echo $this->form->renderField('nom_id'); ?>
							<?php else : ?>
								<input type="hidden" name="jform[nom_id]" value="<?php echo $user->id; ?>" />
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('nominator'); ?></div>
									<div class="controls">
										<input type="text" name="jform[nominator]" class="readonly" readonly="true" value="<?php echo $user->name; ?>" />
									</div>
								</div>
							<?php endif; ?>
							<?php if ($this->params->get('req_sec_nom',1)) : ?>
								<?php echo $this->form->renderField('sec_id'); ?>
							<?php else : ?>
								<input type="hidden" name="jform[sec_id]" value="0" />
							<?php endif; ?>
						</fieldset>
					</div>
				</div>
				<?php echo JHtml::_('bootstrap.endTab'); ?>

				<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	
				<div class="control-group">
					<div class="controls">

						<?php if ($this->canSave): ?>
							<button type="submit" class="validate btn btn-primary">
								<?php echo Text::_('JSUBMIT'); ?>
							</button>
						<?php endif; ?>
						<a class="btn"
						   href="<?php echo Route::_('index.php?option=com_gavoting&task=nominationform.cancel'); ?>"
						   title="<?php echo Text::_('JCANCEL'); ?>">
							<?php echo Text::_('JCANCEL'); ?>
						</a>
					</div>
				</div>
	
				<input type="hidden" name="option" value="com_gavoting"/>
				<input type="hidden" name="task"
					   value="nominationform.save"/>
				<?php echo HTMLHelper::_('form.token'); ?>
			</form>
		<?php endif; ?>
	<?php endif; ?>
</div>
