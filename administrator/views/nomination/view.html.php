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
use \Joomla\CMS\MVC\View\HtmlView;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use \Joomla\CMS\Helper\ContentHelper;

/**
 * View to edit
 *
 * @since  1.6
 */
class GavotingViewNomination extends HtmlView
{
	protected $state;

	protected $item;

	protected $form;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			Factory::getApplication()->enqueueMessage($errors, 'danger');
		}

		$this->addToolbar();

        // Import CSS - best practice way to get css via media API
        JHtml::_('stylesheet','com_gavoting/gavoting.css', false, true);

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 * @return void
	 * @throws Exception
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);

		$user  = Factory::getUser();
		$isNew = ($this->item->id == 0);

		if (isset($this->item->checked_out)) {
			$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		} else {
			$checkedOut = false;
		}

		$canDo = ContentHelper::getActions('com_gavoting','component',$this->item->id);

		//ToolbarHelper::title(Text::_('COM_GAVOTING_TITLE_NOMINATION'), 'nominations.png');
		ToolbarHelper::title($isNew ? Text::_('COM_GAVOTING_TITLE_NOMINATION_NEW') : Text::_('COM_GAVOTING_TITLE_NOMINATION_EDIT'), 'nominations.png');

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit') || $canDo->get('core.create'))) {
			ToolbarHelper::apply('nomination.apply', 'JTOOLBAR_APPLY');
			ToolbarHelper::save('nomination.save', 'JTOOLBAR_SAVE');
		}

		if (!$checkedOut && ($canDo->get('core.create'))) {
			ToolbarHelper::save2new('nomination.save2new', 'JTOOLBAR_SAVE_AND_NEW');
		}

		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create')) {
			ToolbarHelper::save2copy('nomination.save2copy', 'JTOOLBAR_SAVE_AS_COPY');
		}

		// Button for version control
		if ($this->state->params->get('save_history', 1) && $user->authorise('core.edit')) {
			ToolbarHelper::versions('com_gavoting.nomination', $this->item->id);
		}

		if (empty($this->item->id)) {
			ToolbarHelper::cancel('nomination.cancel', 'JTOOLBAR_CANCEL');
		} else {
			ToolbarHelper::cancel('nomination.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
