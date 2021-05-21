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
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\User\UserHelper;
use \Joomla\CMS\Date\Date;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gavoting', JPATH_ADMINISTRATOR);

$user       = Factory::getUser();
$userId     = $user->id;
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$canCreate  = $user->authorise('core.create', 'com_gavoting');
$canEdit    = $user->authorise('core.edit', 'com_gavoting');
$canCheckin = $user->authorise('core.manage', 'com_gavoting');
$canChange  = $user->authorise('core.edit.state', 'com_gavoting');
$canDelete  = $user->authorise('core.delete', 'com_gavoting');
$canNominate  = $user->authorise('core.nominate', 'com_gavoting');
$canVote  = $user->authorise('core.vote', 'com_gavoting');
$canMotion  = $user->authorise('core.motion', 'com_gavoting');
$showVotes = $this->params->get('show_votes',0);
$notag_colour = $this->params->get('notag_colour','#a51f18');
$req_sec_mot = $this->params->get('req_sec_mot',1);
$cols = 5;
$padSpace = '&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;';

if ($this->params->get('multi_users', 0)) {
	$linked_field = $this->params->get('linkcust_field', 0);
	$linkedUser = GavotingHelper::getCustomField($user->id, $linked_field);
} else {
    $linkedUser = 0;
}
$votingClosed = GavotingHelper::votingClosed();

?>

<h2><?php echo Text::_('COM_GAVOTING_TITLE_MOTIONS'); ?></h2>

<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post"
      name="adminForm" id="adminForm">

	<?php echo LayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>

    <div class="table-responsive">
	<table class="table table-striped" id="motionList">
		<thead>
		<tr>
			<th width="12%" class='center'>
				<?php echo JHtml::_('grid.sort',  'COM_GAVOTING_MOTIONS_MOV_DATE', 'a.mov_date', $listDirn, $listOrder); ?>
			</th>
			<th width="20%" class="left">
				<?php echo JHtml::_('grid.sort',  'COM_GAVOTING_MOTIONS_MOV_ID', 'mov_id_name', $listDirn, $listOrder); ?>
			</th>
			<th width="20%" class="left">
				<?php echo JHtml::_('grid.sort',  'COM_GAVOTING_MOTIONS_SEC_ID', 'sec_id_name', $listDirn, $listOrder); ?>
			</th>
			<?php if ($canCheckin || $showVotes): ?>
				<th width="8%" class="center">
					<?php echo JHtml::_('grid.sort',  'COM_GAVOTING_MOTIONS_VOTES_FOR', 'a.votes_for', $listDirn, $listOrder); ?>
				</th>
				<th width="8%" class="center">
					<?php echo JHtml::_('grid.sort',  'COM_GAVOTING_MOTIONS_VOTES_AGAINST', 'a.votes_against', $listDirn, $listOrder); ?>
				</th>
			<?php endif; ?>
			<th width="5%" class="center">
				<?php echo JHtml::_('grid.sort',  'COM_GAVOTING_MOTIONS_AGREED', 'a.agreed', $listDirn, $listOrder); ?>
			</th>
			<th width="12%" class="center">
				<?php echo JHtml::_('grid.sort',  'COM_GAVOTING_MOTIONS_AGREED_DATE', 'a.agreed_date', $listDirn, $listOrder); ?>
			</th>

			<?php if ($canCheckin || $canDelete || $canVote): ?>
				<th width="15%" class="center">
					<?php echo Text::_('COM_GAVOTING_ACTIONS'); ?>
				</th>
			<?php endif; ?>

		</tr>
		</thead>
		<tfoot>
		<tr>
			<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => $item) : ?>
			<?php
				$canEdit = $user->authorise('core.edit', 'com_gavoting');
				if (!$canEdit && $user->authorise('core.edit.own', 'com_gavoting')) {
					$canEdit = Factory::getUser()->id == $item->motion;
				}
				
				if (!$item->agreed && $item->agreed != null) {
					$nomStyle = ' style="color:'.$notag_colour.';"'; 
				} else { 
					$nomStyle = ''; 
				}
				
				// check if already voted for motion
				if ($canVote) {
					$hasVoted = GavotingHelper::hasVoted($userId, $item->id, $linkedUser);
				}

			?>


			<tr class="row<?php echo $i % 2; ?>">
				<td<?php echo $nomStyle; ?>>
					<?php if (isset($item->checked_out) && $item->checked_out) : ?>
						<?php echo JHtml::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'motions.', $canCheckin); ?>
					<?php endif; ?>
					<?php if ($canEdit): ?>
						<a href="<?php echo Route::_('index.php?option=com_gavoting&view=motion&id='.(int) $item->id); ?>">
							<?php echo HtmlHelper::date($item->mov_date, Text::_('COM_GAVOTING_DISPLAY_DATE')); ?>
						</a>
					<?php else : ?>
						<?php echo HtmlHelper::date($item->mov_date, Text::_('COM_GAVOTING_DISPLAY_DATE')); ?>
					<?php endif; ?>
				</td>
				<td class="left"<?php echo $nomStyle; ?>>
					<?php echo $item->mov_id_name; ?>
				</td>
				<td class="left"<?php echo $nomStyle; ?>>
					<?php if ($req_sec_mot): ?>
						<?php echo $item->sec_id_name; ?>
					<?php else : ?>
						<?php echo Text::_('COM_GAVOTING_NO_SECONDER_REQ'); ?>
					<?php endif; ?>
				</td>
				<?php if ($canCheckin || $showVotes): ?>
	                <?php $cols = 7; ?>
					<td class="center">
						<?php echo $item->votes_for; ?>
					</td>
					<td class="center">
						<?php echo $item->votes_against; ?>
					</td>
				<?php endif; ?>

				<td class="center"<?php echo $nomStyle; ?>>
					<?php if ($item->agreed != null) { if ($item->agreed) { echo '<i class="icon-publish"></i>'; } else { echo '<i class="icon-unpublish"></i>'; } }?>
				</td>
				<td class="center"<?php echo $nomStyle; ?>>
					<?php if ($item->agreed_date == '0000-00-00 00:00:00' || $item->agreed_date == null) { /* do nothing */ } else {echo HtmlHelper::date($item->agreed_date, Text::_('COM_GAVOTING_DISPLAY_DATE')); } ?>
				</td>

				<?php if ($canCheckin || $canDelete || $canVote): ?>
	                <?php $cols = $cols == 7 ? 8 : 6; ?>
					<td class="center">
						<?php if (!$hasVoted): ?>
							<?php if ($item->agreed_date == '0000-00-00 00:00:00' || $item->agreed_date == null): ?>
								<?php if (!$votingClosed): ?>
									<a href="<?php echo Route::_('index.php?option=com_gavoting&task=motionform.voteFor&id=' . $item->id, false, 2); ?>" class="btn btn-mini" type="button" title="<?php echo Text::_('COM_GAVOTING_VOTE_FOR'); ?>"><i class="icon-publish" ></i></a>
									<a href="<?php echo Route::_('index.php?option=com_gavoting&task=motionform.voteAgainst&id=' . $item->id, false, 2); ?>" class="btn btn-mini" type="button" title="<?php echo Text::_('COM_GAVOTING_VOTE_AGAINST'); ?>"><i class="icon-unpublish" ></i></a>
								<?php else : ?>
		                            <?php echo Text::_('COM_GAVOTING_VOTING_CLOSED'); ?>
								<?php endif; ?>
							<?php else : ?>
								<?php echo Text::_('COM_GAVOTING_MOTION_ALREADY_VOTED'); ?>
							<?php endif; ?>
						<?php else : ?>
                            <?php echo Text::_('COM_GAVOTING_MOTION_ALREADY_VOTED'); ?>
						<?php endif; ?>
						<?php if ($canCheckin): ?>
							<a href="<?php echo Route::_('index.php?option=com_gavoting&task=motionform.edit&id=' . $item->id, false, 2); ?>" class="btn btn-mini" type="button" title="<?php echo Text::_('COM_GAVOTING_EDIT_RECORD'); ?>"><i class="icon-edit" ></i></a>
							<?php if ($item->agreed_date == '0000-00-00 00:00:00' || $item->agreed_date == null): ?>
								<a href="<?php echo Route::_('index.php?option=com_gavoting&task=motionform.voteDecision&id=' . $item->id, false, 2); ?>" class="btn btn-mini" type="button" title="<?php echo Text::_('COM_GAVOTING_VOTE_DECISION'); ?>"><i class="icon-goldstar" ></i></a>
							<?php endif; ?>
						<?php endif; ?>
						<?php if ($canDelete): ?>
							<a href="<?php echo Route::_('index.php?option=com_gavoting&task=motionform.remove&id=' . $item->id, false, 2); ?>" class="btn btn-mini delete-button" type="button" title="<?php echo Text::_('COM_GAVOTING_DELETE_RECORD'); ?>"><i class="icon-trash" ></i></a>
							<a href="<?php echo Route::_('index.php?option=com_gavoting&task=motionform.archive&id=' . $item->id, false, 2); ?>" class="btn btn-mini" type="button" title="<?php echo Text::_('COM_GAVOTING_ARCHIVE_RECORD'); ?>"><i class="icon-archive" ></i></a>
						<?php endif; ?>
					</td>
				<?php endif; ?>

			</tr>
			<tr>
				<td<?php echo $nomStyle; ?> colspan="<?php echo $cols; ?>">
					<?php echo $item->motion; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
    </div>

	<?php if ($canMotion) : ?>
		<a href="<?php echo Route::_('index.php?option=com_gavoting&task=motionform.edit&id=0', false, 0); ?>"
		   class="btn btn-success btn-small"><i class="icon-plus"></i> <?php echo Text::_('COM_GAVOTING_ADD_ITEM'); ?>
		</a>
	<?php endif; ?>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

<p class="small center"><?php echo Text::_('COM_GAVOTING_MOTION_LIST_LEGEND'); ?></p>
<p class="small" style="margin-left: 60px;">
	<a href="#" class="btn btn-mini" type="button"><i class="icon-publish" ></i></a> <?php echo Text::_('COM_GAVOTING_VOTE_FOR').$padSpace; ?>
	<a href="#" class="btn btn-mini" type="button"><i class="icon-unpublish" ></i></a> <?php echo Text::_('COM_GAVOTING_VOTE_AGAINST').$padSpace; ?>
	<?php if ($canCheckin || $canDelete): ?>
		<a href="#" class="btn btn-mini" type="button"><i class="icon-goldstar" ></i></a> <?php echo Text::_('COM_GAVOTING_VOTE_DECISION'); ?><br /><br />
		<a href="#" class="btn btn-mini" type="button"><i class="icon-edit" ></i></a> <?php echo Text::_('COM_GAVOTING_EDIT_RECORD').$padSpace; ?>
		<a href="#" class="btn btn-mini" type="button"><i class="icon-trash" ></i></a> <?php echo Text::_('COM_GAVOTING_DELETE_RECORD').$padSpace; ?>
		<a href="#" class="btn btn-mini" type="button"><i class="icon-archive" ></i></a> <?php echo Text::_('COM_GAVOTING_ARCHIVE_RECORD'); ?>
	<?php endif; ?>
</p>

<?php if($canDelete) : ?>
<script type="text/javascript">

	jQuery(document).ready(function () {
		jQuery('.delete-button').click(deleteItem);
	});

	function deleteItem() {

		if (!confirm("<?php echo Text::_('COM_GAVOTING_DELETE_MESSAGE'); ?>")) {
			return false;
		}
	}
</script>
<?php endif; ?>
