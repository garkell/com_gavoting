<?php
/**
 * @version    1.2.08
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
use \Joomla\CMS\Date\Date;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.tooltip');
HTMLHelper::_('behavior.formvalidation');
//HTMLHelper::_('formbehavior.chosen', 'select');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gavoting', JPATH_ADMINISTRATOR);

$user    = Factory::getUser();
$canEdit = GavotingHelper::canUserEdit($this->item, $user);

$nomsClosed = GavotingHelper::nominationsClosed();
$votingClosed = GavotingHelper::votingClosed();
$votingOpen = GavotingHelper::votingOpen();


if ($this->params->get('multi_users', 0)) {
	$linked_field = $this->params->get('linkcust_field', 0);
	$linkedUser = GavotingHelper::getCustomField($user->id, $linked_field);
} else {
    $linkedUser = 0;
}
$hasVoted = GavotingHelper::hasVoted($user->id, 0, $linkedUser);
$nominations = GavotingHelper::getNomination(0);
$allowProxy = $this->params->get('allow_proxy', 0);
$extra_rules = $this->params->get('extra_rules', 0);
$specific_rules = $this->params->get('specific_rules', 0);

$prevPos='';
$cntr=0;

?>

<div class="voter-edit front-end-edit">
<?php if ($hasVoted): ?>
	<h2><?php echo Text::_('COM_GAVOTING_NOMINATIONS_ALREADY_VOTED'); ?></h2>
<?php else: ?>
<?php if (!$nomsClosed): ?>
	<h2><?php echo Text::_('COM_GAVOTING_NOMINATIONS_STILL_OPEN'); ?></h2>
<?php else: ?>
	<?php if (!$canEdit) : ?>
		<h3>
			<?php throw new Exception(Text::_('COM_GAVOTING_ERROR_MESSAGE_NOT_AUTHORISED'), 403); ?>
		</h3>
	<?php else : ?>
		<?php if ($votingClosed): ?>
			<h2><?php echo Text::_('COM_GAVOTING_VOTING_CLOSED'); ?></h2>
		<?php else: ?>
			<?php if (!$votingOpen): ?>
				<h2><?php echo Text::_('COM_GAVOTING_VOTING_NOT_OPEN'); ?></h2>
			<?php else : ?>
				<h2><?php echo Text::_('COM_GAVOTING_VOTER_SUBMIT_TEXT'); ?></h2>
				<?php if ($extra_rules) : ?>
					<?php echo $specific_rules; ?>
				<?php endif; ?>
				<form id="form-voter"
					  action="<?php echo Route::_('index.php?option=com_gavoting&task=voterform.save'); ?>"
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
					<input type="hidden" name="jform[user_id]" value="<?php echo $user->id; ?>" />
					<input type="hidden" name="jform[cat_id]" value="<?php echo $this->item->cat_id; ?>" />

					<?php if ($allowProxy) : ?>
						<div>
							<span style="color:red;"><?php echo Text::_('COM_GAVOTING_PROXY_MESSAGE'); ?><br />
								<?php if ($this->params->get('proxy_type') == 0) : ?>
									<?php if ($this->params->get('dupl_vote')) : ?>
										<?php echo Text::_('COM_GAVOTING_PROXY_GENERAL_DUPYES'); ?>
									<?php else : ?>
										<?php echo Text::_('COM_GAVOTING_PROXY_GENERAL_DUPNO'); ?>
									<?php endif; ?>
								<?php elseif ($this->params->get('proxy_type') == 1) : ?>
									<?php echo Text::_('COM_GAVOTING_PROXY_SPECIFIC'); ?>
								<?php elseif ($this->params->get('proxy_type') == 2) : ?>
									<?php echo Text::_('COM_GAVOTING_PROXY_HYBRID'); ?>
								<?php endif; ?>
							</span>
						</div>
						<div class="center"><?php echo Text::_('COM_GAVOTING_FORM_LBL_VOTER_SPACER'); ?></div>
	
						<?php echo $this->form->renderField('proxy_vote'); ?>
						<?php echo $this->form->renderField('proxy_for'); ?>

					<?php else : ?>
						<div style="margin-bottom: 30px;"></div>
					<?php endif; ?>

					<?php foreach ($nominations AS $nom) : $cntr++; ?>

						<?php if ($cntr == 1): $prevPos = $nom->pos_name; ?>
							<div class="control-group">
								<div class="control-label spacerheader">
									<label id="jform_nom_ids_lbl" for="jform_nom_ids_<?php echo $nom->position_id; ?>">
										<?php echo $nom->pos_name; ?>
									</label>
								</div>
								<div class="controls extrawide">
								<select id="jform_nom_ids_nom_id<?php echo $nom->position_id; ?>" name="jform[nom_ids][nom_id<?php echo $nom->position_id; ?>]">
									<option value="0"> - <?php echo Text::_('COM_GAVOTING_SELECT_NOMINATION'); ?> - </option>
									<option value="<?php echo $nom->id; ?>"><?php echo $nom->nomination_name; ?></option>
						<?php else : ?>
							<?php if ($nom->pos_name != $prevPos): $prevPos = $nom->pos_name; ?>
									</select>
									</div>
								</div>
								<div class="control-group">
									<div class="control-label spacerheader">
										<label id="jform_nom_ids_lbl" for="jform_nom_ids_<?php echo $nom->position_id; ?>">
											<?php echo $nom->pos_name; ?>
										</label>
									</div>
									<div class="controls extrawide">
									<select id="jform_nom_ids_nom_id<?php echo $nom->position_id; ?>" name="jform[nom_ids][nom_id<?php echo $nom->position_id; ?>]">
									<option value="0"> - <?php echo Text::_('COM_GAVOTING_SELECT_NOMINATION'); ?> - </option>
									<option value="<?php echo $nom->id; ?>"><?php echo $nom->nomination_name; ?></option>
							<?php else : ?>
									<option value="<?php echo $nom->id; ?>"><?php echo $nom->nomination_name; ?></option>
							<?php endif; ?>
						<?php endif; ?>
					<?php endforeach; ?>
						</select>
						</div>
					</div>

					<div class="control-group">
						<div class="controls">

							<?php if ($this->canSave): ?>
								<button type="submit" class="validate btn btn-primary">
									<?php echo Text::_('JSUBMIT'); ?>
								</button>
							<?php endif; ?>
							<a class="btn"
							   href="<?php echo Route::_('index.php?option=com_gavoting&task=voterform.cancel'); ?>"
							   title="<?php echo Text::_('JCANCEL'); ?>">
								<?php echo Text::_('JCANCEL'); ?>
							</a>
						</div>
					</div>

					<input type="hidden" name="option" value="com_gavoting"/>
					<input type="hidden" name="task"
						   value="voterform.save"/>
					<?php echo HTMLHelper::_('form.token'); ?>
				</form>
			<?php endif; ?>
		<?php endif; ?>
	<?php endif; ?>
<?php endif; ?>
<?php endif; ?>
</div>
<script type="text/javascript">
	jQuery(document).ready(function () {
		jQuery('select').on('change', function(event) {
			var prevValue = (this).data('previous');
            jQuery('select').not(this).children("option").filter(function() {return (this).html() == prevValue;}).attr('disabled', null).show();
            var value = (this).children("option").filter(":selected").text();
            jQuery(this).data('previous', value);
            jQuery('select').not(this).children("option").filter(function() {return (this).html() == value;}).attr('disabled', 'disabled').hide();
        });
	});


<?php
// 	(function($) {
// 	    $(document).ready(function() {
// 	        $('select').on('change', function(event) {
// 	            var prevValue = $(this).data('previous');
// 	            $('select').not(this).children("option").filter(function() {return $(this).html() == prevValue;}).attr('disabled', null).show();
// 	            var value = $(this).children("option").filter(":selected").text();
// 	            $(this).data('previous', value);
// 	            $('select').not(this).children("option").filter(function() {return $(this).html() == value;}).attr('disabled', 'disabled').hide();
// 	        });
// 	    });
// 	})(jQuery);
?>
</script>
