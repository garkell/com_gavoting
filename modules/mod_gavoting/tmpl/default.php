<?php
/*
# ------------------------------------------------------------------------
# @version     1.4.04
# @copyright   Copyright (C) 2020. All rights reserved.
# @license     GNU General Public License version 2 or later; see LICENSE.txt
# Author:      Glenn Arkell
# Websites:    http://www.glennarkell.com.au
# ------------------------------------------------------------------------
*/
// no direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('bootstrap.framework');

$moduleclass_sfx	= $params->get('moduleclass_sfx', '');
$ht = $params->get('header_tag');
$hc = $params->get('header_class');

$today = GavotingHelper::getTodaysDate();
$user = Factory::getUser();
$prev_year = '';

/*
echo '<pre><br />';
print_r($items);
echo '</pre>';
*/

?>

<div class="mod_gavoting<?php echo $moduleclass_sfx; ?>" >

    <div id="mentor_agreements<?php echo $module->id; ?>">
        <table class="table-bordered table-striped" style="margin: 0 auto;" width="100%">
			<tr><th width="50%">Position</th><th width="50%">Elected</th></tr>
			<?php foreach ($items as $item) : ?>
				<?php if ($item->elect_year != $prev_year) : $prev_year = $item->elect_year; ?>
					<?php echo '<tr><td colspan="2"><h3 class="center">'.$item->elect_year.'</h3></td></tr>'; ?>
					<?php echo '<tr><td>'.$item->pos_name.'</td><td>'.$item->elected.'</td></tr>'; ?>
				<?php else : ?>
					<?php echo '<tr><td>'.$item->pos_name.'</td><td>'.$item->elected.'</td></tr>'; ?>
				<?php endif; ?>
	        <?php endforeach; ?>
        </table>
	</div>
</div>
