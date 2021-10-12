<?php
/*
 *      Joomla Empresa TPVV Common Component
 *      @package Joomla Empresa TPVV Common Component
 *      @subpackage Content
 *      @author José António Cidre Bardelás
 *      @copyright Copyright (C) 2011-2015 José António Cidre Bardelás and Joomla Empresa. All rights reserved
 *      @license GNU/GPL v3 or later
 *      
 *      Contact us at info@joomlaempresa.com (http://www.joomlaempresa.es)
 *      
 *      This file is part of Joomla Empresa TPVV Common Component.
 *      
 *          Joomla Empresa TPVV Common Component is free software: you can redistribute it and/or modify
 *          it under the terms of the GNU General Public License as published by
 *          the Free Software Foundation, either version 3 of the License, or
 *          (at your option) any later version.
 *      
 *          Joomla Empresa TPVV Common Component is distributed in the hope that it will be useful,
 *          but WITHOUT ANY WARRANTY; without even the implied warranty of
 *          MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *          GNU General Public License for more details.
 *      
 *          You should have received a copy of the GNU General Public License
 *          along with Joomla Empresa TPVV Common Component.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_JEXEC') or die();

class com_JETPVvCommonInstallerScript
{
	public function postflight($type, $parent)
	{
		$file = JPATH_ROOT . '/administrator/components/com_jetpvvcommon/versom.php';

		if (JFile::exists($file))
		{
			JFile::delete($file);
		}

		$lang = JFactory::getLanguage();
		$lang->load('com_jetpvvcommon', JPATH_ADMINISTRATOR, null, true);

		?>
		<h1><?php echo JText::_('COM_JETPVVCOMMON_JETPVVCOMMON_VIEW_DEFAULT_TITLE'); ?></h1>

		<div><?php echo JText::_('JETPVV_COMMON_JE'); ?></div>

		<?php
	}
}
