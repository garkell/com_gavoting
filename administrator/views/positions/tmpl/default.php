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
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Language\Text;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

$user      = Factory::getUser();
$userId    = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$canOrder  = $user->authorise('core.edit.state', 'com_gavoting');
$saveOrder = $listOrder == 'a.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_gavoting&task=positions.saveOrderAjax&tmpl=component';
    HTMLHelper::_('sortablelist.sortable', 'positionList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
$version = GavotingHelper::getComponentVersion();
$messText = Text::_('COM_GAVOTING_ROLLOVER_CONFIRMATION_TEXT');

$sortFields = $this->getSortFields();
// $document = Factory::getDocument();
// $document->addScriptDeclaration('
//   Joomla.submitbutton = function(task) {
//     if (task == "positions.rolloverElection") {
//       if (confirm(Joomla.Text._("COM_GAVOTING_ROLLOVER_CONFIRMATION_TEXT"))) {
//         Joomla.submitform(task);
//       } else {
//         return false;
//       }
//     }
//   }
// ');
// Text::script('COM_GAVOTING_ROLLOVER_CONFIRMATION_TEXT');
//            if (confirm(Joomla.Text._('COM_GAVOTING_ROLLOVER_CONFIRMATION_TEXT'))) {

?>
<script type="text/javascript">
    Joomla.submitbutton = function(task)
    {
        if (task == 'positions.rolloverElection')
        {
            if (confirm('<?php echo Text::_("COM_GAVOTING_ROLLOVER_CONFIRMATION_TEXT"); ?>')) {
                Joomla.submitform(task);
            } else {
                return false;
            }
        }
    }
</script>

<form action="<?php echo Route::_('index.php?option=com_gavoting&view=positions'); ?>" method="post"
	  name="adminForm" id="adminForm">
	<?php if (!empty($this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?><br /><br />
		<span class="small" style="margin-left:20px;">Version: <?php echo $version; ?></span>
	</div>
	<div id="j-main-container" class="span10">
		<?php else : ?>
		<div id="j-main-container">
			<?php endif; ?>

            <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

			<div class="clearfix"></div>
			<table class="table table-striped" id="positionList">
				<thead>
				<tr>
					<?php if (isset($this->items[0]->ordering)): ?>
						<th width="1%" class="nowrap center hidden-phone">
                            <?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
                        </th>
					<?php endif; ?>
					<th width="1%" class="hidden-phone">
						<input type="checkbox" name="checkall-toggle" value=""
							   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
					</th>
					<?php if (isset($this->items[0]->state)): ?>
						<th width="1%" class="nowrap center">
								<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
						</th>
					<?php endif; ?>

					<th class='left'>
						<?php echo JHtml::_('searchtools.sort',  'COM_GAVOTING_POSITIONS_POS_NAME', 'a.pos_name', $listDirn, $listOrder); ?>
					</th>
					<th class='left'>
						<?php echo JHtml::_('searchtools.sort',  'COM_GAVOTING_POSITIONS_CAT_ID', 'a.cat_id', $listDirn, $listOrder); ?>
					</th>
					<th class='left'>
						<?php echo JHtml::_('searchtools.sort',  'COM_GAVOTING_POSITIONS_ELECT_DATE', 'a.elect_date', $listDirn, $listOrder); ?>
					</th>
					<th class='left'>
						<?php echo JHtml::_('searchtools.sort',  'COM_GAVOTING_POSITIONS_ELECTED', 'a.elected', $listDirn, $listOrder); ?>
					</th>
					<th class='center'>
						<?php echo JHtml::_('searchtools.sort',  'COM_GAVOTING_POSITIONS_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
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
				<?php foreach ($this->items as $i => $item) :
					$ordering   = ($listOrder == 'a.ordering');
					$canCreate  = $user->authorise('core.create', 'com_gavoting');
					$canEdit    = $user->authorise('core.edit', 'com_gavoting');
					$canCheckin = $user->authorise('core.manage', 'com_gavoting');
					$canChange  = $user->authorise('core.edit.state', 'com_gavoting');
					?>
					<tr class="row<?php echo $i % 2; ?>">

						<?php if (isset($this->items[0]->ordering)) : ?>
							<td class="order nowrap center hidden-phone">
								<?php if ($canChange) :
									$disableClassName = '';
									$disabledLabel    = '';

									if (!$saveOrder) :
										$disabledLabel    = Text::_('JORDERINGDISABLED');
										$disableClassName = 'inactive tip-top';
									endif; ?>
									<span class="sortable-handler hasTooltip <?php echo $disableClassName ?>"
										  title="<?php echo $disabledLabel ?>"><i class="icon-menu"></i>
									</span>
									<input type="text" style="display:none" name="order[]" size="5"
										   value="<?php echo $item->ordering; ?>" class="width-20 text-area-order "/>
								<?php else : ?>
									<span class="sortable-handler inactive"><i class="icon-menu"></i></span>
								<?php endif; ?>
							</td>
						<?php endif; ?>
						<td class="hidden-phone">
							<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
						</td>
						<?php if (isset($this->items[0]->state)): ?>
							<td class="center">
								<?php echo JHtml::_('jgrid.published', $item->state, $i, 'positions.', $canChange, 'cb'); ?>
							</td>
						<?php endif; ?>

						<td>
							<?php if (isset($item->checked_out) && $item->checked_out && ($canEdit || $canChange)) : ?>
								<?php echo JHtml::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'positions.', $canCheckin); ?>
							<?php endif; ?>
							<?php if ($canEdit) : ?>
								<a href="<?php echo Route::_('index.php?option=com_gavoting&task=position.edit&id='.(int) $item->id); ?>">
								<?php echo $item->pos_name; ?></a>
							<?php else : ?>
								<?php echo $item->pos_name; ?>
							<?php endif; ?>
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
						<td class="center">
							<?php echo $item->id; ?>
						</td>

					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="boxchecked" value="0"/>
            <input type="hidden" name="list[fullorder]" value="<?php echo $listOrder; ?> <?php echo $listDirn; ?>"/>
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
</form>
<script>
    window.toggleField = function (id, task, field) {

        var f = document.adminForm, i = 0, cbx, cb = f[ id ];

        if (!cb) return false;

        while (true) {
            cbx = f[ 'cb' + i ];

            if (!cbx) break;

            cbx.checked = false;
            i++;
        }

        var inputField   = document.createElement('input');

        inputField.type  = 'hidden';
        inputField.name  = 'field';
        inputField.value = field;
        f.appendChild(inputField);

        cb.checked = true;
        f.boxchecked.value = 1;
        window.submitform(task);

        return false;
    };
</script>
