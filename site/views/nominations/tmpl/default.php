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
$showVotes = $this->params->get('show_votes',0);

// get restrictions
$restrict_nomees = $this->params->get('restrict_nomees',0);
$restrict_nomtors = $this->params->get('restrict_nomtors',0);
$restrict_voters = $this->params->get('restrict_voters',0);
$req_sec_nom = $this->params->get('req_sec_nom',1);

$nomees_user_gp = $this->params->get('nomees_user_gp',0);
$nomtors_user_gp = $this->params->get('nomtors_user_gp',0);
$voters_user_gp = $this->params->get('voters_user_gp',0);

$nomsClosed = GavotingHelper::nominationsClosed();
$votingClosed = GavotingHelper::votingClosed();
$votingOpen = GavotingHelper::votingOpen();
$ndate = new Date($this->params->get('close_noms'));
$odate = new Date($this->params->get('open_votes'));
$cdate = new Date($this->params->get('close_votes'));
$adate = new Date($this->params->get('agm_date'));
$nowdate = new Date();

?>

<h2><?php echo Text::_('COM_GAVOTING_TITLE_NOMINATIONS'); ?></h2>
<p><?php echo Text::_('COM_GAVOTING_IMPORTANT_DATES').' '. HtmlHelper::date($adate, Text::_('COM_GAVOTING_DISPLAY_DATETIME')); ?>:</p>
<ul>
	<li><?php echo Text::_('COM_GAVOTING_CLOSE_NOMS_LABEL').' '.HtmlHelper::date($ndate, Text::_('COM_GAVOTING_DISPLAY_DATETIME')); ?></li>
	<li><?php echo Text::_('COM_GAVOTING_OPEN_VOTES_LABEL').' '.HtmlHelper::date($odate, Text::_('COM_GAVOTING_DISPLAY_DATETIME')); ?></li>
	<li><?php echo Text::_('COM_GAVOTING_CLOSE_VOTES_LABEL').' '.HtmlHelper::date($cdate, Text::_('COM_GAVOTING_DISPLAY_DATETIME')); ?></li>
	<li><?php echo Text::_('COM_GAVOTING_CURRENTDATE_LABEL').' '.HtmlHelper::date($nowdate, Text::_('COM_GAVOTING_DISPLAY_DATETIME')); ?></li>
</ul>

<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post"
      name="adminForm" id="adminForm">

	<?php echo LayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>

    <div class="table-responsive">
	<table class="table table-striped" id="nominationList">
		<thead>
		<tr>
			<th width="25%" class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GAVOTING_NOMINATIONS_POSITION_ID', 'pos.pos_name', $listDirn, $listOrder); ?>
			</th>
			<th width="25%">
				<?php echo HTMLHelper::_('grid.sort',  'COM_GAVOTING_NOMINATIONS_NOMINATION', 'nom.name', $listDirn, $listOrder); ?>
			</th>
			<th width="15%">
				<?php echo HTMLHelper::_('grid.sort',  'COM_GAVOTING_NOMINATIONS_NOM_ID', 'a.nom_name', $listDirn, $listOrder); ?>
			</th>
			<?php if ($req_sec_nom): ?>
				<th width="15%">
					<?php echo HTMLHelper::_('grid.sort',  'COM_GAVOTING_NOMINATIONS_SEC_ID', 'sec_name', $listDirn, $listOrder); ?>
				</th>
				<?php if ($canCheckin || $showVotes): ?>
					<th width="15%" class="center">
						<?php echo HTMLHelper::_('grid.sort',  'COM_GAVOTING_NOMINATIONS_VOTES', 'a.votes', $listDirn, $listOrder); ?>
					</th>
				<?php endif; ?>

				<?php if ($canCheckin || $canDelete): ?>
					<th width="15%" class="center">
					<?php echo Text::_('COM_GAVOTING_ACTIONS'); ?>
					</th>
				<?php endif; ?>
			<?php else : ?>
				<?php if ($canCheckin || $showVotes): ?>
					<th width="15%" class="center">
						<?php echo HTMLHelper::_('grid.sort',  'COM_GAVOTING_NOMINATIONS_VOTES', 'a.votes', $listDirn, $listOrder); ?>
					</th>
				<?php endif; ?>

				<?php if ($canCheckin || $canDelete): ?>
					<th width="30%" class="center">
					<?php echo Text::_('COM_GAVOTING_ACTIONS'); ?>
					</th>
				<?php endif; ?>
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
					$canEdit = Factory::getUser()->id == $item->nomination;
				}
				
				if (!$item->agreed) { $nomStyle = ' style="color:red;"'; } else { $nomStyle = ''; }
			?>


			<tr class="row<?php echo $i % 2; ?>">

				<td<?php echo $nomStyle; ?>>
					<?php if (isset($item->checked_out) && $item->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'nominations.', $canCheckin); ?>
					<?php endif; ?>
					<?php if ($canEdit): ?>
						<a href="<?php echo Route::_('index.php?option=com_gavoting&view=nomination&id='.(int) $item->id); ?>">
							<?php echo $this->escape($item->position_id_name); ?>
						</a>
					<?php else : ?>
						<?php echo $this->escape($item->position_id_name); ?>
					<?php endif; ?>
				</td>
				<td class="left"<?php echo $nomStyle; ?>>
					<?php echo $item->nom_name; ?>
					<?php if ($item->agreed == 0 && $item->nomination == $userId): ?>
						<a href="<?php echo Route::_('index.php?option=com_gavoting&task=nomination.agree&id='.(int) $item->id, false, 2); ?>" class="btn btn-mini" type="button" title="Agree">
							<i class="icon-publish" ></i>
						</a>
					<?php endif; ?>
				</td>
				<td class="left"<?php echo $nomStyle; ?>>
					<?php echo $item->nominator_name; ?>
				</td>
				<?php if ($req_sec_nom): ?>
					<td class="left"<?php echo $nomStyle; ?>>
						<?php echo $item->seconder_name; ?>
					</td>
				<?php endif; ?>
				<?php if ($canCheckin || $showVotes): ?>
					<td class="center">
						<?php echo $item->votes; ?>
					</td>
				<?php endif; ?>

				<?php if ($canCheckin || $canDelete): ?>
					<td class="center">
						<?php if ($canCheckin): ?>
							<a href="<?php echo Route::_('index.php?option=com_gavoting&task=nominationform.edit&id=' . $item->id, false, 2); ?>" class="btn btn-mini" type="button" title="Edit Record"><i class="icon-edit" ></i></a>
						<?php endif; ?>
						<?php if ($canDelete): ?>
							<a href="<?php echo Route::_('index.php?option=com_gavoting&task=nominationform.remove&id=' . $item->id, false, 2); ?>" class="btn btn-mini delete-button" type="button" title="Delete Record"><i class="icon-trash" ></i></a>
							<a href="<?php echo Route::_('index.php?option=com_gavoting&task=nominationform.archive&id=' . $item->id, false, 2); ?>" class="btn btn-mini" type="button" title="Archive Record"><i class="icon-archive" ></i></a>
						<?php endif; ?>
					</td>
				<?php endif; ?>

			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<p class="center small" style="color:red;"><?php echo Text::_('COM_GAVOTING_NOMS_NOT_AGREED_YET'); ?></p>
    </div>
	<?php if ($canNominate && !$nomsClosed) : ?>
		<a href="<?php echo Route::_('index.php?option=com_gavoting&task=nominationform.edit&id=0', false, 0); ?>"
		   class="btn btn-success btn-small"><i class="icon-plus"></i> <?php echo Text::_('COM_GAVOTING_ADD_NOMINATION'); ?>
		</a>
	<?php endif; ?>
	<?php if ($canVote && $votingOpen && !$votingClosed && $nomsClosed) : ?>
		<a href="<?php echo Route::_('index.php?option=com_gavoting&task=voterform.edit&id=0', false, 0); ?>"
		   class="btn btn-warning btn-small"><i class="icon-plus"></i> <?php echo Text::_('COM_GAVOTING_CAST_VOTE'); ?>
		</a>
	<?php endif; ?>
	<?php if ($votingClosed) : ?>
		<a href="<?php echo Route::_('index.php?option=com_gavoting&task=voterform.voteDecision', false, 0); ?>"
		   class="btn btn-danger btn-small"><i class="icon-redo"></i> <?php echo Text::_('COM_GAVOTING_DECLARE_WINNERS'); ?>
		</a>
	<?php endif; ?>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

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
