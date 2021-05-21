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

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gavoting', JPATH_ADMINISTRATOR);

$user       = Factory::getUser();
$userId     = $user->get('id');
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$canCreate  = $user->authorise('core.create', 'com_gavoting');
$canEdit    = $user->authorise('core.edit', 'com_gavoting');
$canCheckin = $user->authorise('core.manage', 'com_gavoting');
$canChange  = $user->authorise('core.edit.state', 'com_gavoting');
$canDelete  = $user->authorise('core.delete', 'com_gavoting');

?>

<h2><?php echo Text::_('COM_GAVOTING_TITLE_POSITION'); ?></h2>

<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post"
      name="adminForm" id="adminForm">

	<?php echo LayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>

    <div class="table-responsive">
	<table class="table table-striped" id="positionList">
		<thead>
		<tr>
			<?php if (isset($this->items[0]->state)): ?>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
				</th>
			<?php endif; ?>

			<th class=''>
				<?php echo JHtml::_('grid.sort',  'COM_GAVOTING_POSITIONS_POS_NAME', 'a.pos_name', $listDirn, $listOrder); ?>
			</th>
			<th class='center'>
				<?php echo JHtml::_('grid.sort',  'COM_GAVOTING_POSITIONS_CAT_ID', 'a.cat_id_name', $listDirn, $listOrder); ?>
			</th>
			<th class='center'>
				<?php echo JHtml::_('grid.sort',  'COM_GAVOTING_POSITIONS_ELECT_DATE', 'a.elect_date', $listDirn, $listOrder); ?>
			</th>
			<th class='center'>
				<?php echo JHtml::_('grid.sort',  'COM_GAVOTING_POSITIONS_ELECTED', 'a.elected', $listDirn, $listOrder); ?>
			</th>

			<?php if ($canEdit || $canDelete): ?>
				<th class="center">
				<a href="#"><?php echo Text::_('COM_GAVOTING_ACTIONS'); ?></a>
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
			<?php $canEdit = $user->authorise('core.edit', 'com_gavoting'); ?>

			<?php if (!$canEdit && $user->authorise('core.edit.own', 'com_gavoting')): ?>
				<?php $canEdit = Factory::getUser()->id == $item->created_by; ?>
			<?php endif; ?>

			<tr class="row<?php echo $i % 2; ?>">

				<?php if (isset($this->items[0]->state)) : ?>
					<?php $class = ($canChange) ? 'active' : 'disabled'; ?>
					<td class="center">
						<?php if ($item->state == -2): ?>
						<a class="btn btn-micro <?php echo $class; ?>" href="<?php echo ($canChange) ? Route::_('index.php?option=com_gavoting&task=position.publish&id=' . $item->id . '&state=' . ((0) % 2), false, 2) : '#'; ?>">
						<?php else : ?>
						<a class="btn btn-micro <?php echo $class; ?>" href="<?php echo ($canChange) ? Route::_('index.php?option=com_gavoting&task=position.publish&id=' . $item->id . '&state=' . (($item->state + 1) % 2), false, 2) : '#'; ?>">
						<?php endif; ?>
						<?php if ($item->state == 1): ?>
							<i class="icon-publish" title="Active"></i>
						<?php elseif ($item->state == 0): ?>
							<i class="icon-unpublish" title="Not Active"></i>
						<?php elseif ($item->state == -2): ?>
							<i class="icon-trash" title="Trashed"></i>
						<?php elseif ($item->state == 2): ?>
							<i class="icon-archive" title="Archived"></i>
						<?php endif; ?>
						</a>
					</td>
				<?php endif; ?>

				<td>
					<?php if (isset($item->checked_out) && $item->checked_out) : ?>
						<?php echo JHtml::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'positions.', $canCheckin); ?>
					<?php endif; ?>
					<a style="color:#3c4d91;" href="<?php echo Route::_('index.php?option=com_gavoting&view=position&id='.(int) $item->id); ?>">
						<?php echo $item->pos_name; ?>
					</a>
				</td>
				<td>
					<?php echo $item->cat_id_name; ?>
				</td>
				<td>
					<?php if (!empty($item->elect_date) && $item->elect_date != '0000-00-00 00:00:00') {echo HtmlHelper::date($item->elect_date, Text::_('COM_GAVOTING_DISPLAY_DATE'));} ?>
				</td>
				<td>
					<?php echo $item->elected; ?>
				</td>

				<?php if ($canEdit || $canDelete): ?>
					<td class="center">
						<?php if ($canEdit): ?>
							<a href="<?php echo Route::_('index.php?option=com_gavoting&task=positionform.edit&id=' . $item->id, false, 2); ?>" class="btn btn-mini" type="button" title="Edit Record"><i class="icon-edit" ></i></a>
						<?php endif; ?>
						<?php if ($canDelete): ?>
							<a href="<?php echo Route::_('index.php?option=com_gavoting&task=positionform.remove&id=' . $item->id, false, 2); ?>" class="btn btn-mini delete-button" type="button" title="Delete Record"><i class="icon-trash" ></i></a>
							<a href="<?php echo Route::_('index.php?option=com_gavoting&task=positionform.archive&id=' . $item->id, false, 2); ?>" class="btn btn-mini" type="button" title="Archive Record"><i class="icon-archive" ></i></a>
						<?php endif; ?>
					</td>
				<?php endif; ?>

			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
    </div>
	<?php if ($canCreate) : ?>
		<a href="<?php echo Route::_('index.php?option=com_gavoting&task=positionform.edit&id=0', false, 0); ?>"
		   class="btn btn-success btn-small"><i class="icon-plus"></i> <?php echo Text::_('COM_GAVOTING_ADD_ITEM'); ?>
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
