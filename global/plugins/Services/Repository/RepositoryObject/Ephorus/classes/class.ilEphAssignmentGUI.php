<?php
/**
 * GUI clas for ephorus assignments
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup
 *
 * @ilCtrl_Calls ilEphAssignmentGUI: ilShopPurchaseGUI
 */
class ilEphAssignmentGUI
{

    /**
     * Constructor
     */
    function __construct($a_eph)
    {
        $this->eph = $a_eph;
    }


    /**
     * Get assignment header for overview
     */
    function getOverviewHeader($a_data)
    {
        global $lng, $ilUser;

        $tpl = new ilTemplate("tpl.assignment_head.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus");

        if ($a_data["deadline"] - time() <= 0)
        {
            $tpl->setCurrentBlock("prop");
            $tpl->setVariable("PROP", $lng->txt("rep_robj_xeph_ended_on"));
            $tpl->setVariable("PROP_VAL",
                ilDatePresentation::formatDate(new ilDateTime($a_data["deadline"],IL_CAL_UNIX)));
            $tpl->parseCurrentBlock();
        }
        else if ($a_data["start_time"] > 0 && time() - $a_data["start_time"] <= 0)
        {
            $tpl->setCurrentBlock("prop");
            $tpl->setVariable("PROP", $lng->txt("rep_robj_xeph_starting_on"));
            $tpl->setVariable("PROP_VAL",
                ilDatePresentation::formatDate(new ilDateTime($a_data["start_time"],IL_CAL_UNIX)));
            $tpl->parseCurrentBlock();
        }
        else
        {
            $time_str = $this->getTimeString($a_data["deadline"]);
            $tpl->setCurrentBlock("prop");
            $tpl->setVariable("PROP", $lng->txt("rep_robj_xeph_time_to_send"));
            $tpl->setVariable("PROP_VAL", $time_str);
            $tpl->parseCurrentBlock();
            $tpl->setCurrentBlock("prop");
            $tpl->setVariable("PROP", $lng->txt("rep_robj_xeph_edit_until"));
            $tpl->setVariable("PROP_VAL",
                ilDatePresentation::formatDate(new ilDateTime($a_data["deadline"],IL_CAL_UNIX)));
            $tpl->parseCurrentBlock();

        }

        $mand = "";
        if ($a_data["mandatory"])
        {
            $mand = " (".$lng->txt("rep_robj_xeph_mandatory").")";
        }
        $tpl->setVariable("TITLE", $a_data["title"].$mand);
//		$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("accordion_arrow.png"));

        // status icon
        $stat = ilEphAssignment::lookupStatusOfUser($a_data["id"], $ilUser->getId());
        switch ($stat)
        {
            case "passed": 	$pic = "scorm/passed.png"; break;
            case "failed":	$pic = "scorm/failed.png"; break;
            default: 		$pic = "scorm/not_attempted.png"; break;
        }
        $tpl->setVariable("IMG_STATUS", ilUtil::getImagePath($pic));
        $tpl->setVariable("ALT_STATUS", $lng->txt("rep_robj_xeph_".$stat));

        return $tpl->get();
    }

    /**
     * Get assignment body for overview
     */
    function getOverviewBody($a_data)
    {
        global $lng, $ilCtrl, $ilUser;

        $tpl = new ilTemplate("tpl.assignment_body.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus");

        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");


        $info = new ilInfoScreenGUI(null);
        $info->setTableClass("");

        $not_started_yet = false;
        if ($a_data["start_time"] > 0 && time() - $a_data["start_time"] <= 0)
        {
            $not_started_yet = true;
        }

        if (!$not_started_yet)
        {
            // instructions
            $info->addSection($lng->txt("rep_robj_xeph_instruction"));
            $info->addProperty("", nl2br(ilUtil::makeClickable($a_data["instruction"], true)));
        }

        // schedule
        $info->addSection($lng->txt("rep_robj_xeph_schedule"));
        if ($a_data["start_time"] > 0)
        {
            $info->addProperty($lng->txt("rep_robj_xeph_start_time"),
                ilDatePresentation::formatDate(new ilDateTime($a_data["start_time"],IL_CAL_UNIX)));
        }
        $info->addProperty($lng->txt("rep_robj_xeph_edit_until"),
            ilDatePresentation::formatDate(new ilDateTime($a_data["deadline"],IL_CAL_UNIX)));
        $time_str = $this->getTimeString($a_data["deadline"]);
        if (!$not_started_yet)
        {
            $info->addProperty($lng->txt("rep_robj_xeph_time_to_send"),
                "<b>".$time_str."</b>");
        }

        // public submissions
        if ($this->eph->getShowSubmissions())
        {
            $ilCtrl->setParameterByClass("ilobjephorusgui", "ass_id", $a_data["id"]);
            if ($a_data["deadline"] - time() <= 0)
            {
                $link = '<a class="submit" href="'.
                    $ilCtrl->getLinkTargetByClass("ilobjephorusgui", "listPublicSubmissions").'">'.
                    $lng->txt("rep_robj_xeph_list_submission").'</a>';
                $info->addProperty($lng->txt("rep_robj_xeph_public_submission"), $link);
            }
            else
            {
                $info->addProperty($lng->txt("rep_robj_xeph_public_submission"),
                    $lng->txt("rep_robj_xeph_msg_public_submission"));
            }
            $ilCtrl->setParameterByClass("ilobjephorusgui", "ass_id", $_GET["ass_id"]);
        }

        $ilCtrl->setParameterByClass("ilobjephorusgui", "ass_id", $a_data["id"]);

        if (!$not_started_yet)
        {
            // download files
            $files = ilEphAssignment::getFiles($a_data["eph_id"], $a_data["id"]);
            if (count($files) > 0)
            {
                $info->addSection($lng->txt("rep_robj_xeph_files"));
                foreach($files as $file)
                {
                    $ilCtrl->setParameterByClass("ilobjephorusgui", "file", urlencode($file["name"]));
                    $info->addProperty($file["name"], $lng->txt("rep_robj_xeph_download"),
                        $ilCtrl->getLinkTargetByClass("ilobjephorusgui", "downloadFile"));
                        $ilCtrl->setParameterByClass("ilobjephorusgui", "file", "");
                }
            }

            // submission
            $info->addSection($lng->txt("rep_robj_xeph_your_submission"));

            $delivered_files = ilEphAssignment::getDeliveredFiles($a_data["eph_id"], $a_data["id"], $ilUser->getId());

            $times_up = false;
            if($a_data["deadline"] - time() < 0)
            {
                $times_up = true;
            }

            $titles = array();
            foreach($delivered_files as $file)
            {
                $titles[] = $file["filetitle"];
            }
            $files_str = implode($titles, ", ");
            if ($files_str == "")
            {
                $files_str = $lng->txt("rep_robj_xeph_message_no_delivered_files");
            }

            $ilCtrl->setParameterByClass("ilobjephorusgui", "ass_id", $a_data["id"]);

            if (!$times_up)
            {
                $files_str.= ' <a class="submit" href="'.
                    $ilCtrl->getLinkTargetByClass("ilobjephorusgui", "submissionScreen").'">'.
                    (count($titles) == 0
                        ? $lng->txt("rep_robj_xeph_hand_in")
                        : $lng->txt("rep_robj_xeph_edit_submission")).'</a>';
            }
            else
            {
                if (count($titles) > 0)
                {
                    $files_str.= ' <a class="submit" href="'.
                        $ilCtrl->getLinkTargetByClass("ilobjephorusgui", "submissionScreen").'">'.
                        $lng->txt("rep_robj_xeph_already_delivered_files").'</a>';
                }
            }

            $info->addProperty($lng->txt("rep_robj_xeph_files_returned"), $files_str);

            $last_sub = ilEphAssignment::getLastSubmission($a_data["id"], $ilUser->getId());
            if ($last_sub)
            {
                $last_sub = ilDatePresentation::formatDate(new ilDateTime($last_sub,IL_CAL_DATETIME));
            }
            else
            {
                $last_sub = "---";
            }

            if ($last_sub != "---")
            {
                $info->addProperty($lng->txt("rep_robj_xeph_last_submission"),
                    $last_sub);
            }

            // feedback from tutor
            $storage = new ilFSStorageEphorus($a_data["eph_id"], $a_data["id"]);
            $cnt_files = $storage->countFeedbackFiles($ilUser->getId());
            $lpcomment = ilEphAssignment::lookupCommentForUser($a_data["id"], $ilUser->getId());
            $mark = ilEphAssignment::lookupMarkOfUser($a_data["id"], $ilUser->getId());
            $status = ilEphAssignment::lookupStatusOfUser($a_data["id"], $ilUser->getId());
            if ($lpcomment != "" || $mark != "" || $status != "notgraded" || $cnt_files > 0)
            {
                $info->addSection($lng->txt("rep_robj_xeph_feedback_from_tutor"));
                if ($lpcomment != "")
                {
                    $info->addProperty($lng->txt("rep_robj_xeph_comment"),
                        $lpcomment);
                }
                if ($mark != "")
                {
                    $info->addProperty($lng->txt("rep_robj_xeph_mark"), $mark);
                }

                if ($status == "")
                {
                    $info->addProperty($lng->txt("rep_robj_xeph_status"), $lng->txt("rep_robj_xeph_message_no_delivered_files"));
                }
                else if ($status != "notgraded")
                {
                    $img = '<img border="0" src="'.ilUtil::getImagePath("scorm/".$status.".png").'" '.
                        ' alt="'.$lng->txt("rep_robj_xeph_".$status).'" title="'.$lng->txt("rep_robj_xeph_".$status).
                        '" style="vertical-align:middle;"/>';
                    $info->addProperty($lng->txt("rep_robj_xeph_status"), $img." ".$lng->txt("rep_robj_xeph_".$status));
                }

                if ($cnt_files > 0)
                {
                    $info->addSection($lng->txt("fb_files"));
                    $files = $storage->getFeedbackFiles($ilUser->getId());
                    foreach($files as $file)
                    {
                        $ilCtrl->setParameterByClass("ilobjephorusgui", "file", urlencode($file));
                        $info->addProperty($file,
                            $lng->txt("rep_robj_xeph_download"),
                            $ilCtrl->getLinkTargetByClass("ilobjephorusgui", "downloadFeedbackFile"));
                        $ilCtrl->setParameter($this, "file", "");
                    }
                }
            }
        }

        $tpl->setVariable("CONTENT", $info->getHTML());

        return $tpl->get();
    }

    /**
     * Get time string for deadline
     */
    function getTimeString($a_deadline)
    {
        global $lng;

        if ($a_deadline - time() <= 0)
        {
            $time_str = $lng->txt("rep_robj_xeph_time_over_short");
        }
        else
        {
            $time_diff = ilUtil::int2array($a_deadline - time(),null);
            unset($time_diff['seconds']);
            if (isset($time_diff['days']))
            {
                unset($time_diff['minutes']);
            }
            if (isset($time_diff['months']))
            {
                unset($time_diff['hours']);
            }
            $time_str = ilUtil::timearray2string($time_diff);
        }

        return $time_str;
    }
}