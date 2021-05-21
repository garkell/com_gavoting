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
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\Controller\BaseController;
use \Joomla\CMS\Session\Session;

/**
 * Motions controller class.
 *
 * @since  1.6
 */
class GavotingControllerMotion extends BaseController
{
	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 * @return void
	 * @since    1.6
     * @throws Exception
	 */
	public function edit()
	{
		$app = Factory::getApplication();

		// Get the previous edit id (if any) and the current edit id.
		$previousId = (int) $app->getUserState('com_gavoting.edit.motion.id');
		$editId     = $app->input->getInt('id', 0);

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_gavoting.edit.motion.id', $editId);

		// Get the model.
		$model = $this->getModel('Motion', 'GavotingModel');

		// Check out the item
		if ($editId)
		{
			$model->checkout($editId);
		}

		// Check in the previous user.
		if ($previousId && $previousId !== $editId)
		{
			$model->checkin($previousId);
		}

		// Redirect to the edit screen.
		$this->setRedirect(Route::_('index.php?option=com_gavoting&view=motionform&layout=edit', false));
	}

	/**
	 * Method to save a user's profile data.
	 * @return    void
	 * @throws Exception
	 * @since    1.6
	 */
	public function publish()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Checking if the user can remove object
		$user = Factory::getUser();

		if ($user->authorise('core.edit', 'com_gavoting') || $user->authorise('core.edit.state', 'com_gavoting'))
		{
			$model = $this->getModel('Motion', 'GavotingModel');

			// Get the user data.
			$id    = $app->input->getInt('id');
			$state = $app->input->getInt('state');

			// Attempt to save the data.
			$return = $model->publish($id, $state);

			// Check for errors.
			if ($return === false)
			{
				$this->setMessage(Text::sprintf('COM_GAVOTING_SAVE_FAILED', $model->getError()), 'warning');
			}

			// Clear the profile id from the session.
			$app->setUserState('com_gavoting.edit.motion.id', null);

			// Flush the data from the session.
			$app->setUserState('com_gavoting.edit.motion.data', null);

			// Redirect to the list screen.
			$this->setMessage(Text::_('COM_GAVOTING_ITEM_SAVED_SUCCESSFULLY'), 'success');
			$menu = Factory::getApplication()->getMenu();
			$item = $menu->getActive();

			if (!$item)
			{
				// If there isn't any menu item active, redirect to list view
				$this->setRedirect(Route::_('index.php?option=com_gavoting&view=motions', false));
			}
			else
			{
                $this->setRedirect(Route::_('index.php?Itemid='. $item->id, false));
			}
		}
		else
		{
			throw new Exception(500);
		}
	}

	/**
	 * Remove data
	 * @return void
	 * @throws Exception
	 */
	public function remove()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Checking if the user can remove object
		$user = Factory::getUser();

		if ($user->authorise('core.delete', 'com_gavoting'))
		{
			$model = $this->getModel('Motion', 'GavotingModel');

			// Get the user data.
			$id = $app->input->getInt('id', 0);

			// Attempt to save the data.
			$return = $model->delete($id);

			// Check for errors.
			if ($return === false)
			{
				$this->setMessage(Text::sprintf('COM_GAVOTING_DELETE_FAILED', $model->getError()), 'warning');
			}
			else
			{
				// Check in the profile.
				if ($return)
				{
					$model->checkin($return);
				}

                $app->setUserState('com_gavoting.edit.motion.id', null);
                $app->setUserState('com_gavoting.edit.motion.data', null);

                $app->enqueueMessage(Text::_('COM_GAVOTING_ITEM_DELETED_SUCCESSFULLY'), 'success');
                $app->redirect(Route::_('index.php?option=com_gavoting&view=motions', false));
			}

			// Redirect to the list screen.
			$menu = Factory::getApplication()->getMenu();
			$item = $menu->getActive();
			$this->setRedirect(Route::_($item->link, false));
		}
		else
		{
			throw new Exception(500);
		}
	}

	/**
	 * Accept Motion
	 * @return void
	 * @throws Exception
	 */
	public function agree()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Checking if the user can remove object
		$user = Factory::getUser();

		if ($user->authorise('core.nominate', 'com_gavoting')) {
			$id = $app->input->getInt('id', 0);
			$app->setUserState('com_gavoting.edit.motion.id', $id);

			$model = $this->getModel('Motion', 'GavotingModel');

			// Attempt to update the data.
			$return = $model->agree($id, $user->id);

			// Check for errors.
			if ($return === false) {
				$this->setMessage(Text::sprintf('COM_GAVOTING_AGREE_FAILED', $model->getError()), 'warning');
			} else {
				// Check in the profile.
				if ($return) {
					$model->checkin($return);
				}

                $app->setUserState('com_gavoting.edit.motion.id', null);

                $app->enqueueMessage(Text::_('COM_GAVOTING_AGREED_MOTION'), 'message');
                $app->redirect(Route::_('index.php?option=com_gavoting&view=motions', false));
			}
		} else {
			$app->enqueueMessage(Text::_('COM_GAVOTING_DECLINED_MOTION'), 'danger');
		}

	}

}
