<?php

/**
 * @version    1.4.04
 * @package    Com_Gavoting
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2020 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\User\UserHelper;

/**
 * Methods supporting a list of Gavoting records.
 *
 * @since  1.6
 */
class GavotingModelNominations extends ListModel
{
	/**
	 * Constructor.
	 * @param   array  $config  An optional associative array of configuration settings.
	 * @see        JController
	 * @since      1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'ordering', 'a.ordering',
				'state', 'a.state',
				'created_by', 'a.created_by',
				'modified_by', 'a.modified_by',
				'created_date', 'a.created_date',
				'modified_date', 'a.modified_date',
				'position_id', 'a.position_id',
				'nomination', 'a.nomination',
				'nom_name', 'a.nom_name',
				'nom_date', 'a.nom_date',
				'nom_id', 'a.nom_id',
				'sec_id', 'a.sec_id',
				'agreed', 'a.agreed',
				'agreed_date', 'a.agreed_date',
				'votes', 'a.votes',
				'comment', 'a.comment',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 * Note. Calling getState in this method will result in recursion.
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 * @return void
	 * @throws Exception
	 * @since    1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
        $app  = Factory::getApplication();
		$list = $app->getUserState($this->context . '.list');

		$ordering  = isset($list['filter_order'])     ? $list['filter_order']     : 'a.position_id';
		$direction = isset($list['filter_order_Dir']) ? $list['filter_order_Dir'] : 'ASC';

		$list['limit']     = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit', $app->get('list_limit'), 'uint');
		$list['start']     = $app->input->getInt('start', 0);
		$list['ordering']  = $ordering;
		$list['direction'] = $direction;

		$app->setUserState($this->context . '.list', $list);
		$app->input->set('list', null);

		//$this->setState('list.limit', $list['limit']);
        //$this->setState('list.start', $list['start']);

        // List state information.

        parent::populateState("a.id", "ASC");

        $context = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $context);
        $status = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state');
        $this->setState('filter.state', $status);

        // Split context into component and optional section
        $parts = FieldsHelper::extract($context);

        if ($parts)
        {
            $this->setState('filter.component', $parts[0]);
            $this->setState('filter.section', $parts[1]);
        }
	}

	/**
	 * Build an SQL query to load the list data.
	 * @return   JDatabaseQuery
	 * @since    1.6
	 */
	protected function getListQuery()
	{
		$params = ComponentHelper::getParams('com_gavoting');
		// Create a new query object.
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select( $this->getState( 'list.select', 'DISTINCT a.* ' ) );
		$query->from('#__gavoting_nominations AS a');

		// Join over the users for the checked out user
		$query->select("uc.name AS uEditor");
		$query->join("LEFT", "#__users AS uc ON uc.id=a.checked_out");

		// Join over the user field 'created_by'
		$query->select('created_by.name AS created_by');
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

		// Join over the user field 'modified_by'
		$query->select('modified_by.name AS modified_by');
		$query->join('LEFT', '#__users AS modified_by ON modified_by.id = a.modified_by');

		// Join over the user field 'nomination'
		$query->select('a.nom_name AS nomination_name');
		$query->join('LEFT', '#__users AS n ON n.id = a.nomination');
		// Join over the user field 'nom & sec'
		$query->select('nom.name AS nominator_name, s.name AS seconder_name');
		$query->join('LEFT', '#__users AS nom ON nom.id = a.nom_id');
		$query->join('LEFT', '#__users AS s ON s.id = a.sec_id');

		// Join over the position field 'position_id'
		$query->select('pos.id as pos_id, pos.pos_name AS position_id_name');
		$query->join('LEFT', '#__gavoting_positions AS pos ON pos.id = a.position_id');

        $status = $this->getState('filter.state');
		if (isset($status) && ($status == 0 || $status == 1 || $status == 2 || $status == -2 )) {
			$query->where('a.state = '.(int) $status);
		} elseif ($status == 9)  {
			$query->where('a.state IN (0,1,2,-2)');
		} else {
            $query->where('a.state IN (1)');
		}
		
		// Only show agreed nominations
		if ($params->get('disp_unaccept',0)) {
			$query->where('a.agreed IN (0,1)');
		} else {
            $query->where(' a.agreed = 1 ');
		}

        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( a.nom_name LIKE ' . $search . ' OR nom.name LIKE ' . $search . ')');
            }
        }

        // Add the list ordering clause.
        $orderCol  = $this->state->get('list.ordering', 'a.position_id');
        $orderDirn = $this->state->get('list.direction', 'ASC');

        if ($orderCol && $orderDirn) {
            $query->order($db->escape('a.position_id ASC, a.ordering ASC'));
        }

        return $query;
	}

	/**
	 * Method to get an array of data items
	 * @return  mixed An array of data on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();
		

		return $items;
	}

	/**
	 * Overrides the default function to check Date fields format, identified by
	 * "_dateformat" suffix, and erases the field if it's not correct.
	 * @return void
	 */
	protected function loadFormData()
	{
		$app              = Factory::getApplication();
		$filters          = $app->getUserState($this->context . '.filter', array());
		$error_dateformat = false;

		foreach ($filters as $key => $value)
		{
			if (strpos($key, '_dateformat') && !empty($value) && $this->isValidDate($value) == null)
			{
				$filters[$key]    = '';
				$error_dateformat = true;
			}
		}

		if ($error_dateformat)
		{
			$app->enqueueMessage(Text::_('COM_GAVOTING_SEARCH_FILTER_DATE_FORMAT'), 'warning');
			$app->setUserState($this->context . '.filter', $filters);
		}

		return parent::loadFormData();
	}

	/**
	 * Checks if a given date is valid and in a specified format (YYYY-MM-DD)
	 * @param   string  $date  Date to be checked
	 * @return bool
	 */
	private function isValidDate($date)
	{
		$date = str_replace('/', '-', $date);
		return (date_create($date)) ? Factory::getDate($date)->format("Y-m-d") : null;
	}
}
