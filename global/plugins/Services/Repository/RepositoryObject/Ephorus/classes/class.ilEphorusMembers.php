<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilEphorusMembers
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id: class.ilEphorusMembers.php 29906 2011-07-16 21:10:20Z akill $
*
* @ingroup ModulesEphorus
*/
class ilEphorusMembers
{
	var $ref_id;
	var $obj_id;
	var $members;
	var $status;
//	var $status_feedback;
//	var $status_sent;
//	var $status_returned;
//	var $notice;

	function ilEphorusMembers($a_eph)
	{
		$this->eph = $a_eph;
		$this->obj_id = $a_eph->getId();
		$this->ref_id = $a_eph->getRefId();
		$this->read();
	}

	/**
	 * Get ephorus ref id
	 */
	function getRefId()
	{
		return $this->ref_id;
	}

	/**
	 * Get ephorus id
	 */
	function getObjId()
	{
		return $this->obj_id;
	}
	
	/**
	 * Set ephorus id
	 */
	function setObjId($a_obj_id)
	{
		$this->obj_id = $a_obj_id;
	}

	/**
	 * Get members array
	 */
	function getMembers()
	{
		return $this->members ? $this->members : array();
	}
	
	/**
	 * Set members array
	 */
	function setMembers($a_members)
	{
		$this->members = $a_members;
	}

	/**
	* Assign a user to the ephorus
	*
	* @param	int		$a_user_id		user id
	*/
	function assignMember($a_user_id)
	{
		global $ilDB;

		$tmp_user = ilObjectFactory::getInstanceByObjId($a_user_id);
		$tmp_user->addDesktopItem($this->getRefId(),"eph");

		$ilDB->manipulate("DELETE FROM rep_robj_xeph_members ".
			"WHERE obj_id = ".$ilDB->quote($this->getObjId(), "integer")." ".
			"AND user_id = ".$ilDB->quote($a_user_id, "integer")." ");

// @todo: some of this fields may not be needed anymore
		$ilDB->manipulateF("INSERT INTO rep_robj_xeph_members (obj_id, user_id, status, sent, feedback) ".
			" VALUES (%s,%s,%s,%s,%s)",
			array("integer", "integer", "text", "integer", "integer"),
			array($this->getObjId(), $a_user_id, 'notgraded', 0, 0));

		include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignment.php");
		ilEphAssignment::createNewUserRecords($a_user_id, $this->getObjId());
		
		$this->read();
		
		//include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
		//ilLPStatusWrapper::_updateStatus($this->getObjId(), $a_user_id);

		return true;
	}
	
	/**
	 * Is user assigned to ephorus?
	 */
	function isAssigned($a_id)
	{
		return in_array($a_id,$this->getMembers());
	}

	/**
	 * Assign members to ephorus
	 */
	function assignMembers($a_members)
	{
		$assigned = 0;
		if(is_array($a_members))
		{
			foreach($a_members as $member)
			{
				if(!$this->isAssigned($member))
				{
					$this->assignMember($member);
				}
				else
				{
					++$assigned;
				}
			}
		}
		if($assigned == count($a_members))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Detaches a user from an ephorus
	 *
	 * @param	int		$a_user_id		user id
	 */
	function deassignMember($a_user_id)
	{
		global $ilDB;

		$tmp_user = ilObjectFactory::getInstanceByObjId($a_user_id);
		$tmp_user->dropDesktopItem($this->getRefId(),"eph");

		$query = "DELETE FROM rep_robj_xeph_members ".
			"WHERE obj_id = ".$ilDB->quote($this->getObjId(), "integer")." ".
			"AND user_id = ".$ilDB->quote($a_user_id, "integer")." ";

		$ilDB->manipulate($query);
		
		$this->read();
		
		//include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
		//ilLPStatusWrapper::_updateStatus($this->getObjId(), $a_user_id);
		
		// delete all delivered files of the member
		$this->eph->deleteAllDeliveredFilesOfUser($a_user_id);

// @todo: delete all assignment associations (and their files)
		
		return false;
	}

	/**
	 * Deassign members
	 */
	function deassignMembers($a_members)
	{
		if(is_array($a_members))
		{
			foreach($a_members as $member)
			{
				$this->deassignMember($member);
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Read all members
	 */
	function read()
	{
		global $ilDB;

		$tmp_arr_members = array();

		$query = "SELECT * FROM rep_robj_xeph_members ".
			"WHERE obj_id = ".$ilDB->quote($this->getObjId(), "integer");

		$res = $ilDB->query($query);
		while($row = $ilDB->fetchObject($res))
		{
			$tmp_arr_members[] = $row->user_id;
		}
		$this->setMembers($tmp_arr_members);

		return true;
	}

// @todo: clone also assignments
	function ilClone($a_new_id)
	{
		global $ilDB;

		$data = array();

		$query = "SELECT * FROM rep_robj_xeph_members ".
			"WHERE obj_id = ".$ilDB->quote($this->getObjId(), "integer");

		$res = $ilDB->query($query);
		while($row = $ilDB->fetchObject($res))
		{
			$data[] = array("user_id" => $row->user_id,
							"notice" => $row->notice,
							"returned" => $row->returned,
							"status" => $row->status,
							"sent"	 => $row->sent,
							"feedback"	 => $row->feedback
							);
		}
		foreach($data as $row)
		{
			$ilDB->manipulateF("INSERT INTO rep_robj_xeph_members ".
				" (obj_id, user_id, notice, returned, status, feedback, sent) VALUES ".
				" (%s,%s,%s,%s,%s,%s,%s)",
				array ("integer", "integer", "text", "integer", "text", "integer", "integer"),
				array ($a_new_id, $row["user_id"], $row["notice"], (int) $row["returned"],
					$row["status"], (int) $row["feedback"], (int) $row["sent"])
					);
			
			//include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
			//ilLPStatusWrapper::_updateStatus($a_new_id, $row["user_id"]);
		}
		return true;
	}

// @todo: delete also assignments
	function delete()
	{
		global $ilDB;

		$query = "DELETE FROM rep_robj_xeph_members WHERE obj_id = ".
			$ilDB->quote($this->getObjId(), "integer");
		$ilDB->manipulate($query);
		
		//include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
		//ilLPStatusWrapper::_refreshStatus($this->getObjId());

		return true;
	}

	function _getMembers($a_obj_id)
	{
		global $ilDB;

		$query = "SELECT DISTINCT(user_id) as ud FROM rep_robj_xeph_members ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id, "integer");

		$res = $ilDB->query($query);
		while($row = $ilDB->fetchObject($res))
		{
			$user_ids[] = $row->ud;
		}

		return $user_ids ? $user_ids : array();
	}


	/**
	 * Get returned status for all members (if they have anything returned for
	 * any assignment)
	 */
	function _getReturned($a_obj_id)
	{
		global $ilDB;

		$query = "SELECT DISTINCT(user_id) as ud FROM rep_robj_xeph_members ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id, "integer")." ".
			"AND returned = 1";

		$res = $ilDB->query($query);
		while($row = $ilDB->fetchObject($res))
		{
			$user_ids[] = $row->ud;
		}

		return $user_ids ? $user_ids : array();
	}

	/**
	 * Has user returned anything in any assignment?
	 *
	 * @param		integer		object id
	 * @param		integer		user id
	 * @return		boolean		true/false
	 */
	function _hasReturned($a_obj_id, $a_user_id)
	{
		global $ilDB;
	
		$set = $ilDB->query("SELECT DISTINCT(user_id) FROM rep_robj_xeph_members WHERE ".
			" obj_id = ".$ilDB->quote($a_obj_id, "integer")." AND ".
			" returned = ".$ilDB->quote(1, "integer")." AND ".
			" user_id = ".$ilDB->quote($a_user_id, "integer")
			);
		if ($rec = $ilDB->fetchAssoc($set))
		{
			return true;
		}
		return false;
	}

	/**
	 * Get all users that passed the ephorus
	 */
	function _getPassedUsers($a_obj_id)
	{
		global $ilDB;

		$query = "SELECT DISTINCT(user_id) FROM rep_robj_xeph_members ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id, "integer")." ".
			"AND status = ".$ilDB->quote("passed", "text");
		$res = $ilDB->query($query);
		while($row = $ilDB->fetchObject($res))
		{
			$user_ids[] = $row->user_id;
		}
		return $user_ids ? $user_ids : array();
	}

	/**
	 * Get all users that failed the ephorus
	 */
	function _getFailedUsers($a_obj_id)
	{
		global $ilDB;

		$query = "SELECT DISTINCT(user_id) FROM rep_robj_xeph_members ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id, "integer")." ".
			"AND status = ".$ilDB->quote("failed", "text");
		$res = $ilDB->query($query);
		while($row = $ilDB->fetchObject($res))
		{
			$user_ids[] = $row->user_id;
		}
		return $user_ids ? $user_ids : array();
	}

	/**
	 * Lookup current status (notgraded|passed|failed)
	 *
	 * This information is determined by the assignment status and saved
	 * redundtantly in this table for performance reasons.
	 *
	 * @param	int		$a_obj_id	ephorus id
	 * @param	int		$a_user_id	member id
	 * @return	mixed	false (if user is no member) or notgraded|passed|failed
	 */
	function _lookupStatus($a_obj_id, $a_user_id)
	{
		global $ilDB;

		$query = "SELECT status FROM rep_robj_xeph_members ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id, "integer").
			" AND user_id = ".$ilDB->quote($a_user_id, "integer");

		$res = $ilDB->query($query);
		if($row = $ilDB->fetchAssoc($res))
		{
			return $row["status"];
		}

		return false;
	}

	/**
	 * Write user status
	 *
	 * This information is determined by the assignment status and saved
	 * redundtantly in this table for performance reasons.
	 * See ilObjEphorus->updateUserStatus().
	 *
	 * @param	int		ephorus id
	 * @param	int		user id
	 * @param	text	status
	 */
	function _writeStatus($a_obj_id, $a_user_id, $a_status)
	{
		global $ilDB;

		$ilDB->manipulate("UPDATE rep_robj_xeph_members SET ".
			" status = ".$ilDB->quote($a_status, "text").
			" WHERE obj_id = ".$ilDB->quote($a_obj_id, "integer").
			" AND user_id = ".$ilDB->quote($a_user_id, "integer")
		);
		
		//include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
		//ilLPStatusWrapper::_updateStatus($a_obj_id, $a_user_id);
	}
	
	/**
	 * Write returned status
	 *
	 * The returned status is initially 0. If the first file is returned
	 * by a user for any assignment of the ephorus, the returned status
	 * is set to 1 and it will stay that way, even if this file is deleted again.
	 * -> learning progress uses this to determine "in progress" status
	 *
	 * @param	int		ephorus id
	 * @param	int		user id
	 * @param	text	status
	 */
	function _writeReturned($a_obj_id, $a_user_id, $a_status)
	{
		global $ilDB;
		
		$ilDB->manipulate("UPDATE rep_robj_xeph_members SET ".
			" returned = ".$ilDB->quote($a_status, "text").
			" WHERE obj_id = ".$ilDB->quote($a_obj_id, "integer").
			" AND user_id = ".$ilDB->quote($a_user_id, "integer")
			);
		
		//include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
		//ilLPStatusWrapper::_updateStatus($a_obj_id, $a_user_id);
	}

} //END class.ilObjEphorus
?>
