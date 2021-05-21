<?php
/**
 * @version     1.4.04
 * @package     com_gavoting
 * @copyright   Copyright (C) 2020-2020. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */

// No direct access to this file
defined('_JEXEC') or die;

// import the list field type
use \Joomla\CMS\Factory;
use \Joomla\CMS\Form\FormField;
use \Joomla\CMS\Form\FormHelper;
use \Joomla\CMS\Language\Text;

FormHelper::loadFieldClass('list');

class JFormFieldRegions extends JFormFieldList
{
    /**
     * The field type.
     *
     * @var         string
     */
    protected $type = 'regions';

    /**
     * Method to get a list of options for a list input.
     *
     * @return      array           An array of JHtml options.
     */
    protected function getOptions()
    {
        $options = array();
        $table = '#__gavoting_regions';
        $label = Text::_('COM_GAVOTING_REGION_LABEL'); // label in the list options ie "- Select $label - "
        $field = 'title'; // field to display in the list options

        $options = GavotingHelper::getListOptions($table, $label, $field);

        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
