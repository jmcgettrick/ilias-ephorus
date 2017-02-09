<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

include_once("./Services/Repository/classes/class.ilObjectPluginAccess.php");
include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilObjEphorus.php");

/**
* Access/Condition checking for Example object
*
* Please do not create instances of large application classes (like ilObjExample)
* Write small methods within this class to determin the status.
*
* @author 		Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilObjEphorusAccess extends ilObjectPluginAccess
{

	/**
	* Checks wether a user may invoke a command or not
	* (this method is called by ilAccessHandler::checkAccess)
	*
	* Please do not check any preconditions handled by
	* ilConditionHandler here. Also don't do usual RBAC checks.
	*
	* @param	string		$a_cmd			command (not permission!)
 	* @param	string		$a_permission	permission
	* @param	int			$a_ref_id		reference id
	* @param	int			$a_obj_id		object id
	* @param	int			$a_user_id		user id (if not provided, current user is taken)
	*
	* @return	boolean		true, if everything is ok
	*/
	function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
	{
		return true;
	}

    function _lookupRemainingWorkingTimeString($a_obj_id)
    {
        global $ilDB, $lng;

        $q = "SELECT MIN(deadline) mtime FROM rep_robj_xeph_assign WHERE eph_id = ".
            $ilDB->quote($a_obj_id, "integer").
            " AND deadline > ".$ilDB->quote(time(), "integer");
        $set = $ilDB->query($q);
        $rec = $ilDB->fetchAssoc($set);

        if ($rec["mtime"] > 0)
        {
            $time_str = ilObjEphorus::period2String(new ilDateTime($rec["mtime"], IL_CAL_UNIX));
        }
        return $time_str;
    }
}
?>
