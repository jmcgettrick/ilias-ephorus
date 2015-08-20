<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignment.php");
include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilFSStorageEphorus.php");

/**
* Ephorus participant table
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesEphorus
*/
class ilEphParticipantTableGUI extends ilTable2GUI
{
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd, $a_eph, $a_part_id)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;

		$this->eph = $a_eph;
		$this->eph_id = $this->eph->getId();

		include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignment.php");

		$this->part_id = $a_part_id;
		$this->setId("eph_part_".$this->eph_id."_".$this->part_id);

		include_once("./Services/User/classes/class.ilObjUser.php");

		if ($this->part_id > 0)
		{
			$name = ilObjUser::_lookupName($this->part_id);
			$this->user = new ilObjUser($this->part_id);
		}

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$data = ilEphAssignment::getAssignmentDataOfEphorus($this->eph_id);
		$this->setData($data);

		if ($this->part_id > 0)
		{
			$this->setTitle($lng->txt("rep_robj_xeph_participant").": ".
				$name["lastname"].", ".$name["firstname"]." [".$name["login"]."]");
		}
		else
		{
			$this->setTitle($lng->txt("rep_robj_xeph_participant"));
		}
		
		$this->setTopCommands(true);

		$this->addColumn($this->lng->txt("rep_robj_xeph_assignment"), "order_val");
		$this->addColumn($this->lng->txt("rep_robj_xeph_submission"), "submission");
		$this->addColumn($this->lng->txt("rep_robj_xeph_reports"), "reports");
		$this->addColumn($this->lng->txt("rep_robj_xeph_grading"), "solved_time");
		$this->addColumn($this->lng->txt("feedback"), "feedback_time");

		$this->setDefaultOrderField("order_val");
		$this->setDefaultOrderDirection("asc");

		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.eph_participant_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus");
		$this->setEnableTitle(true);

		if ($this->part_id > 0)
		{
			$this->addCommandButton("saveStatusParticipant", $lng->txt("rep_robj_xeph_save"));
		}
	}

	/**
	 * Check whether field is numeric
	 */
	function numericOrdering($a_f)
	{
		if (in_array($a_f, array("order_val")))
		{
			return true;
		}
		return false;
	}

	/**
	* Fill table row
	*/
	protected function fillRow($d)
	{
		global $lng, $ilCtrl;

		$this->tpl->setVariable("TXT_ASS_TITLE", $d["title"]);
		$this->tpl->setVariable("VAL_CHKBOX",
			ilUtil::formCheckbox(0, "assid[".$d["id"]."]",1));
		$this->tpl->setVariable("VAL_ID",
			$d["id"]);
		// submission:
		// see if files have been resubmmited after solved
		$last_sub =
			ilEphAssignment::getLastSubmission($d["id"], $this->part_id);
		if ($last_sub)
		{
			$last_sub = ilDatePresentation::formatDate(new ilDateTime($last_sub,IL_CAL_DATETIME));
		}
		else
		{
			$last_sub = "---";
		}
		if (ilEphAssignment::lookupUpdatedSubmission($d["id"], $this->part_id) == 1) 
		{
			$last_sub = "<b>".$last_sub."</b>";
		}
		$this->tpl->setVariable("VAL_LAST_SUBMISSION", $last_sub);
		$this->tpl->setVariable("TXT_LAST_SUBMISSION", $lng->txt("rep_robj_xeph_last_submission"));

		// nr of submitted files
		$this->tpl->setVariable("TXT_SUBMITTED_FILES",
			$lng->txt("rep_robj_xeph_files_returned"));
		$sub_cnt = count(ilEphAssignment::getDeliveredFiles($this->eph_id, $d["id"], $this->part_id));
		$new = ilEphAssignment::lookupNewFiles($d["id"], $this->part_id);
		if (count($new) > 0)
		{
			$sub_cnt.= " ".sprintf($lng->txt("cnt_new"),count($new));
		}
		$this->tpl->setVariable("VAL_SUBMITTED_FILES",
			$sub_cnt);
		
		// download command
		$ilCtrl->setParameter($this->parent_obj, "ass_id", $d["id"]);
		$ilCtrl->setParameter($this->parent_obj, "member_id", $this->part_id);
		if ($sub_cnt > 0)
		{
			$this->tpl->setCurrentBlock("download_link");
			$this->tpl->setVariable("LINK_DOWNLOAD",
				$ilCtrl->getLinkTarget($this->parent_obj, "downloadReturned"));
			if (count($new) <= 0)
			{
				$this->tpl->setVariable("TXT_DOWNLOAD",
					$lng->txt("rep_robj_xeph_download_files"));
			}
			else
			{
				$this->tpl->setVariable("TXT_DOWNLOAD",
					$lng->txt("rep_robj_xeph_download_all"));
			}
			$this->tpl->parseCurrentBlock();
			
			// download new files only
			if (count($new) > 0)
			{
				$this->tpl->setCurrentBlock("download_link");
				$this->tpl->setVariable("LINK_NEW_DOWNLOAD", $ilCtrl->getLinkTarget($this->parent_obj, "downloadNewReturned"));
				$this->tpl->setVariable("TXT_NEW_DOWNLOAD", $lng->txt("rep_robj_xeph_download_new"));
				$this->tpl->parseCurrentBlock();
			}
		}

		// note
		$this->tpl->setVariable("TXT_NOTE", $lng->txt("note"));
		$this->tpl->setVariable("NAME_NOTE", "notice[".$d["id"]."]");
		$this->tpl->setVariable("VAL_NOTE", ilUtil::prepareFormOutput(ilEphAssignment::lookupNoticeOfUser($d["id"], $this->part_id)));

		// comment for learner
		$this->tpl->setVariable("TXT_LCOMMENT", $lng->txt("rep_robj_xeph_comment_for_learner"));
		$this->tpl->setVariable("NAME_LCOMMENT", "lcomment[".$d["id"]."]");
		$lpcomment = ilEphAssignment::lookupCommentForUser($d["id"], $this->part_id);
		$this->tpl->setVariable("VAL_LCOMMENT", ilUtil::prepareFormOutput($lpcomment));

		// Reports
		$documents = ilEphAssignment::getDeliveredFiles($this->eph_id, $d["id"], $this->part_id);
		foreach ($documents as $document)
		{
			$ilCtrl->setParameter($this->parent_obj, "filename", $document["filename"]);
			$ilCtrl->setParameter($this->parent_obj, "filetitle", $document["filetitle"]);
			$this->tpl->setCurrentBlock("report");
			$this->tpl->setVariable("DOCUMENT_LINK", $ilCtrl->getLinkTarget($this->parent_obj, "downloadSubmittedFile"));
			$this->tpl->setVariable("DOCUMENT_TITLE", $document["filetitle"]);
			$ilCtrl->setParameter($this->parent_obj, "filename", "");
			$ilCtrl->setParameter($this->parent_obj, "filetitle", "");
			$ilCtrl->setParameter($this->parent_obj, "doc_id", $document["guid"]);

			$report_link = "";
			$visibility_link = "";

			switch($document["status"])
			{
				case 0:
					if(!$document["guid"])
					{
						$report_link = "<span title=\"".$lng->txt("rep_robj_xeph_wait_for_sending_msg")."\">".$lng->txt("rep_robj_xeph_wait_for_sending")."</span>";
					}
					else
					{
						$report_link = "<span title=\"".$lng->txt("rep_robj_xeph_processing_msg")."\">".$lng->txt("rep_robj_xeph_processing")."</span>";
						$visibility_link = "<img src=\"./templates/default/images/loader.gif"."\">";
					}
					break;

				case 1:
					$report_link = "<a href=\"".$ilCtrl->getLinkTarget($this->parent_obj, "viewReport")."\">"
						.$document["percentage"]."%</a>";
					$visibility_link = "<a href=\"".$ilCtrl->getLinkTarget($this->parent_obj, "changeVisibility")."\">"
						."<img src=\"./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/templates/images/"
						."eye_".(($document["visibility_index"] == 1)?"open": "closed").".png"."\"></a>";
					break;

				case 2:
					$report_link = "<a href=\"".$ilCtrl->getLinkTarget($this->parent_obj, "viewReport")."\""
						."title=\"".$lng->txt("rep_robj_xeph_duplicate_document_msg")."\">"
						.$lng->txt("rep_robj_xeph_duplicate_document")."</a>";
					break;

				case 3:
					$report_link = "<span title=\"".$lng->txt("rep_robj_xeph_document_protected_msg")."\">".$lng->txt("rep_robj_xeph_document_protected")."</span>";
					break;

				case 4:
					$report_link = "<span title=\"".$lng->txt("rep_robj_xeph_not_enough_text_msg")."\">".$lng->txt("rep_robj_xeph_not_enough_text")."</span>";
					break;

				case 5:
					$report_link = "<span title=\"".$lng->txt("rep_robj_xeph_no_text_msg")."\">".$lng->txt("rep_robj_xeph_no_text")."</span>";
					break;

				case 6:
					$report_link = "<span title=\"".$lng->txt("rep_robj_xeph_unknown_error_msg")."\">".$lng->txt("rep_robj_xeph_unknown_error")."</span>";
					break;

				case 99:
					$report_link = "<span title=\"".$lng->txt("rep_robj_xeph_".$document["error"]."_msg")."\">".$lng->txt("rep_robj_xeph_".$document["error"])."</span>";
                    break;
			}

			$this->tpl->setVariable("REPORT_LINK", $report_link);
			$this->tpl->setVariable("VISIBILITY_LINK", $visibility_link);

			$this->tpl->parseCurrentBlock();
		}

		// solved
		$status = ilEphAssignment::lookupStatusOfUser($d["id"], $this->part_id);
		$this->tpl->setVariable("SEL_".strtoupper($status), ' selected="selected" ');
		$this->tpl->setVariable("TXT_NOTGRADED", $lng->txt("rep_robj_xeph_notgraded"));
		$this->tpl->setVariable("TXT_PASSED", $lng->txt("rep_robj_xeph_passed"));
		$this->tpl->setVariable("TXT_FAILED", $lng->txt("rep_robj_xeph_failed"));
		if (($sd = ilEphAssignment::lookupStatusTimeOfUser($d["id"], $this->part_id)) > 0)
		{
			$this->tpl->setCurrentBlock("status_date");
			$this->tpl->setVariable("TXT_LAST_CHANGE", $lng->txt("last_change"));
			$this->tpl->setVariable('VAL_STATUS_DATE', ilDatePresentation::formatDate(new ilDateTime($sd,IL_CAL_DATETIME)));
			$this->tpl->parseCurrentBlock();
		}
		switch($status)
		{
			case "passed": $pic = "scorm/passed.png"; break;
			case "failed": $pic = "scorm/failed.png"; break;
			default:       $pic = "scorm/not_attempted.png"; break;
		}
		$this->tpl->setVariable("IMG_STATUS", ilUtil::getImagePath($pic));
		$this->tpl->setVariable("ALT_STATUS", $lng->txt("rep_robj_xeph_".$status));
		
		// mark
		$this->tpl->setVariable("TXT_MARK", $lng->txt("rep_robj_xeph_mark"));
		$this->tpl->setVariable("NAME_MARK", "mark[".$d["id"]."]");
		$mark = ilEphAssignment::lookupMarkOfUser($d["id"], $this->part_id);
		$this->tpl->setVariable("VAL_MARK", ilUtil::prepareFormOutput($mark));

		// feedback
		$ilCtrl->setParameter($this->parent_obj, "member_id", $this->part_id);
		if (($ft = ilEphAssignment::lookupFeedbackTimeOfUser($d["id"], $this->part_id)) > 0)
		{
			$this->tpl->setCurrentBlock("feedback_date");
			$this->tpl->setVariable("TXT_FEEDBACK_MAIL_SENT", sprintf($lng->txt("rep_robj_xeph_sent_at"),
				ilDatePresentation::formatDate(new ilDateTime($ft,IL_CAL_DATETIME))));
			$this->tpl->parseCurrentBlock();
		}
		$ilCtrl->setParameter($this, "rcp_to", $this->user->getLogin());
		$this->tpl->setVariable("LINK_FEEDBACK", $ilCtrl->getLinkTarget($this->parent_obj, "redirectFeedbackMail"));
		$this->tpl->setVariable("TXT_FEEDBACK", $lng->txt("rep_robj_xeph_send_mail"));
		$ilCtrl->setParameter($this->parent_obj, "rcp_to", "");
		
		$storage = new ilFSStorageEphorus($this->eph_id, $d["id"]);
		$cnt_files = $storage->countFeedbackFiles($this->part_id);
		$ilCtrl->setParameter($this->parent_obj, "fsmode", "feedbackpart");
		$this->tpl->setVariable("LINK_FILE_FEEDBACK", $ilCtrl->getLinkTargetByClass("ilfilesystemgui", "listFiles"));
		if ($cnt_files == 0)
		{
			$this->tpl->setVariable("TXT_FILE_FEEDBACK", $lng->txt("rep_robj_xeph_add_feedback_file"));
		}
		else
		{
			$this->tpl->setVariable("TXT_FILE_FEEDBACK", $lng->txt("rep_robj_xeph_fb_files")." (".$cnt_files.")");
		}

		$ilCtrl->setParameter($this->parent_obj, "ass_id", $_GET["ass_id"]);
	}
}
?>