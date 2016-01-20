<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignment.php");

/**
 * Ephorus member table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesEphorus
 */
class ilEphorusMemberTableGUI extends ilTable2GUI
{

    /**
     * Constructor
     */
    function ilEphorusMemberTableGUI($a_parent_obj, $a_parent_cmd, $a_eph, $a_ass_id)
    {
        global $ilCtrl, $lng, $ilAccess, $lng;

        $this->eph = $a_eph;
        $this->eph_id = $this->eph->getId();
        $this->setId("eph_mem_".$a_ass_id);


        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilFSStorageEphorus.php");
        $this->storage = new ilFSStorageEphorus($this->eph_id, $a_ass_id);
        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignment.php");

        $this->ass_id = $a_ass_id;
//var_dump(ilEphAssignment::getMemberListData($this->eph_id, $this->ass_id));
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setData(ilEphAssignment::getMemberListData($this->eph_id, $this->ass_id));
        $this->setTitle($lng->txt("rep_robj_xeph_assignment").": ".ilEphAssignment::lookupTitle($a_ass_id));
        $this->setTopCommands(true);
        //$this->setLimit(9999);

        $this->addColumn("", "", "1", true);
        $this->addColumn($this->lng->txt("image"), "", "1");
        $this->addColumn($this->lng->txt("name"), "name");
        $this->addColumn($this->lng->txt("login"), "login");
        $this->sent_col = ilEphAssignment::lookupAnyEphorusSent($this->eph->getId(), $this->ass_id);
        if ($this->sent_col)
        {
            $this->addColumn($this->lng->txt("rep_robj_xeph_exercise_sent"), "sent_time");
        }
        $this->addColumn($this->lng->txt("rep_robj_xeph_submission"), "submission");
        $this->addColumn($this->lng->txt("rep_robj_xeph_reports"), "reports");
        $this->addColumn($this->lng->txt("rep_robj_xeph_grading"), "solved_time");
        $this->addColumn($this->lng->txt("feedback"), "feedback_time");

        $this->setDefaultOrderField("name");
        $this->setDefaultOrderDirection("asc");

        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));

        $this->setRowTemplate("tpl.eph_members_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus");
        //$this->disable("footer");
        $this->setEnableTitle(true);
        $this->setSelectAllCheckbox("member");

        $this->addMultiCommand("saveStatus", $lng->txt("rep_robj_xeph_save"));
        $this->addMultiCommand("redirectFeedbackMail", $lng->txt("rep_robj_xeph_send_mail"));
        $this->addMultiCommand("sendMembers", $lng->txt("rep_robj_xeph_send_assignment"));
        $this->addMultiCommand("confirmDeassignMembers", $lng->txt("rep_robj_xeph_deassign_members"));

        //if(count($this->eph->members_obj->getAllDeliveredFiles()))
        if (count(ilEphAssignment::getAllDeliveredFiles($this->eph_id, $this->ass_id)))
        {
            $this->addCommandButton("downloadAllDeliveredFiles", $lng->txt("download_all_returned_files"));
        }
    }

    /**
     * Fill table row
     */
    protected function fillRow($member)
    {
        global $lng, $ilCtrl;

        include_once "./Services/Object/classes/class.ilObjectFactory.php";
        $member_id = $member["user_id"];

        if(!($mem_obj = ilObjectFactory::getInstanceByObjId($member_id,false)))
        {
            return;
        }

        // mail sent
        if ($this->sent_col)
        {
            if (ilEphAssignment::lookupStatusSentOfUser($this->ass_id, $member_id))
            {
                $this->tpl->setCurrentBlock("mail_sent");
                if (($st = ilEphAssignment::lookupSentTimeOfUser($this->ass_id,
                    $member_id)) > 0)
                {
                    $this->tpl->setVariable("TXT_MAIL_SENT", sprintf($lng->txt("rep_robj_xeph_sent_at"), ilDatePresentation::formatDate(new ilDateTime($st,IL_CAL_DATE))));
                }
                else
                {
                    $this->tpl->setVariable("TXT_MAIL_SENT", $lng->txt("sent"));
                }
                $this->tpl->parseCurrentBlock();
            }
            else
            {
                $this->tpl->setCurrentBlock("mail_sent");
                $this->tpl->setVariable("TXT_MAIL_SENT", "&nbsp;");
                $this->tpl->parseCurrentBlock();
            }
        }

        // checkbox
        $this->tpl->setVariable("VAL_CHKBOX", ilUtil::formCheckbox(0,"member[$member_id]",1));
        $this->tpl->setVariable("VAL_ID", $member_id);

        // name and login
        $this->tpl->setVariable("TXT_NAME", $member["name"]);
        $this->tpl->setVariable("TXT_LOGIN", "[".$member["login"]."]");

        // image
        $this->tpl->setVariable("USR_IMAGE", $mem_obj->getPersonalPicturePath("xxsmall"));
        $this->tpl->setVariable("USR_ALT", $lng->txt("personal_picture"));

        // submission:
        // see if files have been resubmmited after solved
        $last_sub = ilEphAssignment::getLastSubmission($this->ass_id, $member_id);

        if ($last_sub)
        {
            $last_sub = ilDatePresentation::formatDate(new ilDateTime($last_sub,IL_CAL_DATETIME));
        }
        else
        {
            $last_sub = "---";
        }
        if (ilEphAssignment::lookupUpdatedSubmission($this->ass_id, $member_id) == 1)
        {
            $last_sub = "<b>".$last_sub."</b>";
        }
        $this->tpl->setVariable("VAL_LAST_SUBMISSION", $last_sub);
        $this->tpl->setVariable("TXT_LAST_SUBMISSION", $lng->txt("rep_robj_xeph_last_submission"));

        // nr of submitted files
        $this->tpl->setVariable("TXT_SUBMITTED_FILES", $lng->txt("rep_robj_xeph_files_returned"));

        $sub_cnt = count(ilEphAssignment::getDeliveredFiles($this->eph_id, $this->ass_id, $member_id));

        $new = ilEphAssignment::lookupNewFiles($this->ass_id, $member_id);
        if (count($new) > 0)
        {
            $sub_cnt.= " ".sprintf($lng->txt("cnt_new"),count($new));
        }
        $this->tpl->setVariable("VAL_SUBMITTED_FILES", $sub_cnt);

        // download command
        $ilCtrl->setParameter($this->parent_obj, "member_id", $member_id);
        if ($sub_cnt > 0)
        {
            $this->tpl->setCurrentBlock("download_link");
            $this->tpl->setVariable("LINK_DOWNLOAD", $ilCtrl->getLinkTarget($this->parent_obj, "downloadReturned"));
            if (count($new) <= 0)
            {
                $this->tpl->setVariable("TXT_DOWNLOAD", $lng->txt("rep_robj_xeph_download_files"));
            }
            else
            {
                $this->tpl->setVariable("TXT_DOWNLOAD", $lng->txt("rep_robj_xeph_download_all"));
            }
            $this->tpl->parseCurrentBlock();

            // download new files only
            if (count($new) > 0)
            {
                $this->tpl->setCurrentBlock("download_link");
                $this->tpl->setVariable("LINK_NEW_DOWNLOAD",
                    $ilCtrl->getLinkTarget($this->parent_obj, "downloadNewReturned"));
                $this->tpl->setVariable("TXT_NEW_DOWNLOAD",
                    $lng->txt("rep_robj_xeph_download_new"));
                $this->tpl->parseCurrentBlock();
            }
        }

        // note
        $this->tpl->setVariable("TXT_NOTE", $lng->txt("note"));
        $this->tpl->setVariable("NAME_NOTE", "notice[$member_id]");
        $this->tpl->setVariable("VAL_NOTE", ilUtil::prepareFormOutput(ilEphAssignment::lookupNoticeOfUser($this->ass_id, $member_id)));

        // comment for learner
        $this->tpl->setVariable("TXT_LCOMMENT", $lng->txt("rep_robj_xeph_comment_for_learner"));
        $this->tpl->setVariable("NAME_LCOMMENT", "lcomment[$member_id]");
        $lpcomment = ilEphAssignment::lookupCommentForUser($this->ass_id, $member_id);
        $this->tpl->setVariable("VAL_LCOMMENT", ilUtil::prepareFormOutput($lpcomment));

        // Reports
        $documents = ilEphAssignment::getDeliveredFiles($this->eph_id, $this->ass_id, $member_id);
        foreach ($documents as $document)
        {
            $ilCtrl->setParameter($this->parent_obj, "filename", $document["filename"]);
            $ilCtrl->setParameter($this->parent_obj, "filetitle", $document["filetitle"]);
            $ilCtrl->setParameter($this->parent_obj, "part_id", $member_id);

            $this->tpl->setCurrentBlock("report");
            $this->tpl->setVariable("DOCUMENT_LINK", $ilCtrl->getLinkTarget($this->parent_obj, "downloadSubmittedFile"));
            $this->tpl->setVariable("DOCUMENT_TITLE", $document["filetitle"]);

            $ilCtrl->setParameter($this->parent_obj, "filename", "");
            $ilCtrl->setParameter($this->parent_obj, "filetitle", "");
            $ilCtrl->setParameter($this->parent_obj, "part_id", "");

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
                        .$document["percentage"]."%"
                        ."</a>";
                    $visibility_link = "<a href=\"".$ilCtrl->getLinkTarget($this->parent_obj, "changeVisibility")."\">"
                            ."<img src=\"./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/templates/images/"
                                ."eye_".(($document["visibility_index"] == 1)?"open": "closed").".png"."\">"
                        ."</a>";
                    break;

                case 2:
                    $report_link = "<a href=\"".$ilCtrl->getLinkTarget($this->parent_obj, "viewReport")."\""
                        ."title=\"".$lng->txt("rep_robj_xeph_duplicate_document_msg")."\">"
                        .$lng->txt("rep_robj_xeph_duplicate_document")
                        ."</a>";
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
        //$this->tpl->setVariable("CHKBOX_SOLVED",
        //ilUtil::formCheckbox($this->eph->members_obj->getStatusByMember($member_id),"solved[$member_id]",1));
        $status = ilEphAssignment::lookupStatusOfUser($this->ass_id, $member_id);
        $this->tpl->setVariable("SEL_".strtoupper($status), ' selected="selected" ');
        $this->tpl->setVariable("TXT_NOTGRADED", $lng->txt("rep_robj_xeph_notgraded"));
        $this->tpl->setVariable("TXT_PASSED", $lng->txt("rep_robj_xeph_passed"));
        $this->tpl->setVariable("TXT_FAILED", $lng->txt("rep_robj_xeph_failed"));
        if (($sd = ilEphAssignment::lookupStatusTimeOfUser($this->ass_id, $member_id)) > 0)
        {
            $this->tpl->setCurrentBlock("status_date");
            $this->tpl->setVariable("TXT_LAST_CHANGE", $lng->txt("last_change"));
            $this->tpl->setVariable('VAL_STATUS_DATE',
                ilDatePresentation::formatDate(new ilDateTime($sd,IL_CAL_DATETIME)));
            $this->tpl->parseCurrentBlock();
        }
        switch($status)
        {
            case "passed":	$pic = "scorm/passed.png"; break;
            case "failed":	$pic = "scorm/failed.png"; break;
            default:		$pic = "scorm/not_attempted.png"; break;
        }
        $this->tpl->setVariable("IMG_STATUS", ilUtil::getImagePath($pic));
        $this->tpl->setVariable("ALT_STATUS", $lng->txt("rep_robj_xeph_".$status));

        // mark
        $this->tpl->setVariable("TXT_MARK", $lng->txt("rep_robj_xeph_mark"));
        $this->tpl->setVariable("NAME_MARK", "mark[$member_id]");
        $mark = ilEphAssignment::lookupMarkOfUser($this->ass_id, $member_id);
        $this->tpl->setVariable("VAL_MARK", ilUtil::prepareFormOutput($mark));

        // feedback
        $ilCtrl->setParameter($this->parent_obj, "member_id", $member_id);
        if (($ft = ilEphAssignment::lookupFeedbackTimeOfUser($this->ass_id, $member_id)) > 0)
        {
            $this->tpl->setCurrentBlock("feedback_date");
            $this->tpl->setVariable("TXT_FEEDBACK_MAIL_SENT",
                sprintf($lng->txt("rep_robj_xeph_sent_at"),
                    ilDatePresentation::formatDate(new ilDateTime($ft,IL_CAL_DATETIME))));
            $this->tpl->parseCurrentBlock();
        }

        // feedback mail
        $ilCtrl->setParameter($this, "rcp_to", $mem_obj->getLogin());
        $this->tpl->setVariable("LINK_FEEDBACK",
            $ilCtrl->getLinkTarget($this->parent_obj, "redirectFeedbackMail"));
        $this->tpl->setVariable("TXT_FEEDBACK",
            $lng->txt("rep_robj_xeph_send_mail"));
        $ilCtrl->setParameter($this->parent_obj, "rcp_to", "");

        // file feedback
        $cnt_files = $this->storage->countFeedbackFiles($member_id);
        $ilCtrl->setParameter($this->parent_obj, "fsmode", "feedback");
        $this->tpl->setVariable("LINK_FILE_FEEDBACK", $ilCtrl->getLinkTargetByClass("ilfilesystemgui", "listFiles"));
        if ($cnt_files == 0)
        {
            $this->tpl->setVariable("TXT_FILE_FEEDBACK", $lng->txt("rep_robj_xeph_add_feedback_file"));
        }
        else
        {
            $this->tpl->setVariable("TXT_FILE_FEEDBACK",$lng->txt("rep_robj_xeph_fb_files")." (".$cnt_files.")");
        }

        $this->tpl->parseCurrentBlock();
    }

}
?>