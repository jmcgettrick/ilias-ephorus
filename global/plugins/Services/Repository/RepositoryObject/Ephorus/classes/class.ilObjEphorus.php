<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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

include_once("./Services/Repository/classes/class.ilObjectPlugin.php");
include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphorusMembers.php");

/**
 * Application class for example repository object.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 *
 * $Id$
 */
class ilObjEphorus extends ilObjectPlugin
{
    var $file_obj;
    var $members_obj;
    var $files;

    var $timestamp;
    var $hour;
    var $minutes;
    var $day;
    var $month;
    var $year;
    var $instruction;
    var $certificate_visibility;

    /**
     *
     * Indicates whether completion by submission is enabled or not
     *
     * @var boolean
     * @access protected
     *
     */
    protected $completion_by_submission = false;

    /**
     * Constructor
     *
     * @access	public
     */
    function __construct($a_ref_id = 0)
    {
        parent::__construct($a_ref_id);
    }


    /**
     * Get type.
     */
    final function initType()
    {
        $this->setType("xeph");
    }

    /**
     * Create object
     */
    function doCreate()
    {
        global $ilDB;

        $sql = $ilDB->query($up = "SELECT value FROM settings ".
                " WHERE module = ".$ilDB->quote("rep_robj_xeph", "text").
                " AND keyword = ".$ilDB->quote("processtype", "text")
        );
        $default_processtype = $ilDB->fetchAssoc($sql);

        $ilDB->manipulate("INSERT INTO rep_robj_xeph_data ".
            "(obj_id, processtype) VALUES (".
            $ilDB->quote($this->getId(), "integer").",".
            $ilDB->quote($default_processtype["value"], "integer").
            ")");
    }

    /**
     * Read data from db
     */
    function doRead()
    {
        global $ilDB;

        $sql = $ilDB->query("SELECT * FROM rep_robj_xeph_data ".
                " WHERE obj_id = ".$ilDB->quote($this->getId(), "integer")
        );
        while ($rec = $ilDB->fetchAssoc($sql))
        {
            $this->setShowSubmissions($rec["show_submissions"]);
            $this->setPassMode($rec["pass_mode"]);
            $this->setMinNumber($rec["min_number"]);
            $this->setNotification($rec["notification"]);
            $this->setCompletionBySubmission($rec["copletion_by_submission"]);
            $this->setProcesstype($rec["processtype"]);
        }

        $this->members_obj = new ilEphorusMembers($this);
    }

    /**
     * Update data
     */
    function doUpdate()
    {
        global $ilDB;

        $ilDB->manipulate("UPDATE rep_robj_xeph_data SET ".
                " show_submissions = ".$ilDB->quote($this->getShowSubmissions(), "integer").",".
                " pass_mode = ".$ilDB->quote($this->getPassMode(), "text").",".
                " min_number = ".$ilDB->quote($this->getMinNumber(), "integer").",".
                " notification = ".$ilDB->quote($this->getNotification(), "integer").",".
                " copletion_by_submission = ".$ilDB->quote($this->getCompletionBySubmission(), "integer").",".
                " processtype = ".$ilDB->quote($this->getProcesstype(), "integer").
                " WHERE obj_id = ".$ilDB->quote($this->getId(), "integer")
        );
    }

    /**
     * Delete data from db
     */
    function doDelete()
    {
        global $ilDB;

        $ilDB->manipulate("DELETE FROM rep_robj_xeph_data WHERE ".
                "obj_ id = ".$ilDB->quote($this->getId(), "integer")
        );

    }

    /**
     * Do Cloning
     */
    function doClone($a_target_id,$a_copy_id,$new_obj)
    {
        global $ilDB;

        $new_obj->setShowSubmissions($this->getShowSubmissions());
        $new_obj->setPassMode($this->getPassMode());
        $new_obj->setMinNumber($this->getMinNumber());
        $new_obj->setNotification($this->getNotification());
        $new_obj->setCompletionBySubmission($this->getCompletionBySubmission());
        $new_obj->setProcesstype($this->getProcesstype());
        $new_obj->update();
    }

//
// Set/Get Methods for our example properties
//

    /**
     * Set online
     *
     * @param	boolean		online
     */
    function setShowSubmissions($a_val)
    {
        $this->show_submissions = $a_val;
    }

    /**
     * Get online
     *
     * @return	boolean		online
     */
    function getShowSubmissions()
    {
        return $this->show_submissions;
    }

    /**
     * Set online
     *
     * @param	boolean		online
     */
    function setTimestamp($a_val)
    {
        $this->time_stamp = $a_val;
    }

    /**
     * Get online
     *
     * @return	boolean		online
     */
    function getTimestamp()
    {
        return $this->time_stamp;
    }

    /**
     * Set option one
     *
     * @param	string		option one
     */
    function setPassMode($a_val)
    {
        $this->pass_mode = $a_val;
    }

    /**
     * Get option one
     *
     * @return	string		option one
     */
    function getPassMode()
    {
        return $this->pass_mode;
    }

    /**
     * Set option two
     *
     * @param	string		option two
     */
    function setMinNumber($a_val)
    {
        $this->min_number = $a_val;
    }

    /**
     * Get option two
     *
     * @return	string		option two
     */
    function getMinNumber()
    {
        return $this->min_number;
    }

    /**
     * Set option two
     *
     * @param	string		option two
     */
    function setNotification($a_val)
    {
        $this->notification = $a_val;
    }

    /**
     * Get option two
     *
     * @return	string		option two
     */
    function getNotification()
    {
        return $this->notification;
    }

    /**
     * Set option two
     *
     * @param	string		option two
     */
    function setCompletionBySubmission($a_val)
    {
        $this->completion_by_submission = $a_val;
    }

    /**
     * Get option two
     *
     * @return	string		option two
     */
    public function getCompletionBySubmission()
    {
        return $this->completion_by_submission;
    }

    /**
     * Set option two
     *
     * @param	string		option two
     */
    function setProcesstype($a_val)
    {
        $this->processtype = $a_val;
    }

    /**
     * Get option two
     *
     * @return	string		option two
     */
    function getProcesstype()
    {
        return $this->processtype;
    }

    /**
    * Return a string of time period. This is duplicated from ilUtil as in Ilias 5.1 it replaced 2 functions;
    * int2array and timearray2string that were used by the
    *
    * @param      ilDateTime $a_from
    * @param      ilDateTime $a_to
    * @return    string
    * @static
    *
    */
    public static function period2String(ilDateTime $a_from, $a_to = null)
    {
        global $lng;

        if (!$a_to)
        {
            $a_to = new ilDateTime(time(), IL_CAL_UNIX);
        }

        $from = new DateTime($a_from->get(IL_CAL_DATETIME));
        $to = new DateTime($a_to->get(IL_CAL_DATETIME));
        $diff = $to->diff($from);

        $periods = array();
        $periods["years"] = $diff->format("%y");
        $periods["months"] = $diff->format("%m");
        $periods["days"] = $diff->format("%d");
        $periods["hours"] = $diff->format("%h");
        $periods["minutes"] = $diff->format("%i");
        $periods["seconds"] = $diff->format("%s");

        if (!array_sum($periods))
        {
            return;
        }

        foreach ($periods as $key => $value)
        {
            if($value)
            {
                $segment_name = ($value > 1)
                    ? $key
                    : substr($key, 0, -1);
                $array[] = $value . ' ' . $lng->txt($segment_name);
            }
        }

        $len = sizeof($array);
        if ($len > 3)
        {
            $array = array_slice($array, 0, (3-$len));
        }

        return implode(', ', $array);
    }

    /**
     * Upload assigment files
     */
    function addUploadedFile($a_http_post_files, $unzipUploadedFile = false)
    {
        global $lng;
        if ($unzipUploadedFile && preg_match("/zip/",	$a_http_post_files["type"]) == 1)
        {

            $this->processUploadedFile($a_http_post_files["tmp_name"], "storeUploadedFile", true);
            return true;
        }
        else
        {
            $this->file_obj->storeUploadedFile($a_http_post_files, true);
            return true;
        }
    }


    /**
     * Save submitted file of user
     */
    function deliverFile($a_http_post_files, $a_ass_id, $user_id, $unzip = false)
    {
        global $ilDB;

        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilFSStorageEphorus.php");
        $storage = new ilFSStorageEphorus($this->getId(), $a_ass_id);
        $deliver_result = $storage->deliverFile($a_http_post_files, $user_id, $unzip);
        if ($deliver_result)
        {
            $next_id = $ilDB->nextId("rep_robj_xeph_subm");

            $processtype = $this->getProcesstype();

            $visibility_index = ($processtype == 3)? 2 : 1;
            $query = sprintf("INSERT INTO rep_robj_xeph_subm ".
                    "(id, obj_id, user_id, filename, filetitle, mimetype, date_created, ass_id, processtype, visibility_index, status) ".
                    "VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                $ilDB->quote($next_id, "integer"),
                $ilDB->quote($this->getId(), "integer"),
                $ilDB->quote($user_id, "integer"),
                $ilDB->quote($deliver_result["fullname"], "text"),
                $ilDB->quote($a_http_post_files["name"], "text"),
                $ilDB->quote($deliver_result["mimetype"], "text"),
                $ilDB->quote(ilUtil::now(), "timestamp"),
                $ilDB->quote($a_ass_id, "integer"),
                $ilDB->quote($processtype, "integer"),
                $ilDB->quote($visibility_index, "integer"),
                $ilDB->quote(0, "integer")
            );
            $ilDB->manipulate($query);
            if (!$this->members_obj->isAssigned($user_id))
            {
                $this->members_obj->assignMember($user_id);
            }
            ilEphAssignment::updateStatusReturnedForUser($a_ass_id, $user_id, 1);
            ilEphorusMembers::_writeReturned($this->getId(), $user_id, 1);
        }
        return true;
    }

    /**
     * processes errorhandling etc for uploaded archive
     * @param string $tmpFile path and filename to uploaded file
     * @param string $storageMethod deliverFile or storeUploadedFile
     * @param boolean $persistentErrorMessage Defines whether sendInfo will be persistent or not
     */
    function processUploadedFile ($fileTmp, $storageMethod, $persistentErrorMessage, $a_ass_id)
    {
        global $lng, $ilUser;

        // Create unzip-directory
        $newDir = ilUtil::ilTempnam();
        ilUtil::makeDir($newDir);

        include_once ("Services/Utilities/classes/class.ilFileUtils.php");

        try
        {
            $processDone = ilFileUtils::processZipFile($newDir,$fileTmp, false);
            ilFileUtils::recursive_dirscan($newDir, $filearray);

            foreach ($filearray["file"] as $key => $filename)
            {
                $a_http_post_files["name"] = ilFileUtils::utf8_encode($filename);
                $a_http_post_files["type"] = "other";
                $a_http_post_files["tmp_name"] = $filearray["path"][$key]."/".$filename;
                $a_http_post_files["error"] = 0;
                $a_http_post_files["size"] = filesize($filearray["path"][$key]."/".$filename);

                if ($storageMethod == "deliverFile")
                {
                    $this->$storageMethod($a_http_post_files, $a_ass_id, $ilUser->id, true);
                }
                else if ($storageMethod == "storeUploadedFile")
                {
                    $this->file_obj->$storageMethod($a_http_post_files, true, true);
                }
            }
            ilEphorusMembers::_writeReturned($this->getId(), $ilUser->id, 1);
            ilUtil::sendSuccess($this->lng->txt("file_added"),$persistentErrorMessage);

        }
        catch (ilFileUtilsException $e)
        {
	        ilUtil::sendFailure($e->getMessage(), true);
        }


        ilUtil::delDir($newDir);
        return $processDone;

    }

    /**
     *
     * This method is called after an user submitted one or more files.
     * It should handle the setting "Completion by Submission" and, if enabled, set the status of
     * the current user to either 'passed' or 'notgraded'.
     *
     * @param	integer
     * @access	public
     *
     */
    public function handleSubmission($ass_id)
    {
        global $ilUser, $ilDB;

        if($this->getCompletionBySubmission())
        {
            include_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignment.php';

            $res = $ilDB->queryF(
                'SELECT id FROM rep_robj_xeph_subm WHERE obj_id = %s AND user_id = %s AND ass_id = %s',
                array('integer', 'integer', 'integer'),
                array($this->getId(), $ilUser->getId(), (int)$ass_id)
            );

            if($num = $ilDB->numRows($res))
            {
                ilEphAssignment::updateStatusOfUser($ass_id, $ilUser->getId(), 'passed');
            }
            else
            {
                ilEphAssignment::updateStatusOfUser($ass_id, $ilUser->getId(), 'notgraded');
            }
        }
    }

    /**
     * Deletes already delivered files
     * @param array $file_id_array An array containing database ids of the delivered files
     * @param numeric $user_id The database id of the user
     * @access	public
     */
    function deleteDeliveredFiles($a_eph_id, $a_ass_id, $file_id_array, $user_id)
    {
        ilEphAssignment::deleteDeliveredFiles($a_eph_id, $a_ass_id, $file_id_array, $user_id);

        // Finally update status 'returned' of member if no file exists
        if(!count(ilEphAssignment::getDeliveredFiles($a_eph_id, $a_ass_id, $user_id)))
        {
            ilEphAssignment::updateStatusReturnedForUser($a_ass_id, $user_id, 0);
        }
    }

    /**
     * Determine status of user
     */
    function determineStatusOfUser($a_user_id = 0)
    {
        global $ilUser;

        if ($a_user_id == 0)
        {
            $a_user_id = $ilUser->getId();
        }

        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignment.php");
        $ass = ilEphAssignment::getAssignmentDataOfEphorus($this->getId());

        $passed_all_mandatory = true;
        $failed_a_mandatory = false;
        $cnt_passed = 0;
        $cnt_notgraded = 0;
        $passed_at_least_one = false;

        foreach ($ass as $a)
        {
            $stat = ilEphAssignment::lookupStatusOfUser($a["id"], $a_user_id);

            if ($a["mandatory"] && ($stat == "failed" || $stat == "notgraded"))
            {
                $passed_all_mandatory = false;
            }
            if ($a["mandatory"] && ($stat == "failed"))
            {
                $failed_a_mandatory = true;
            }
            if ($stat == "passed")
            {
                $cnt_passed++;
            }
            if ($stat == "notgraded")
            {
                $cnt_notgraded++;
            }
        }

        if (count($ass) == 0)
        {
            $passed_all_mandatory = false;
        }
        if ($this->getPassMode() != "nr")
        {
            $overall_stat = "notgraded";
            if ($failed_a_mandatory)
            {
                $overall_stat = "failed";
            }
            else if ($passed_all_mandatory && $cnt_passed > 0)
            {
                $overall_stat = "passed";
            }
        }
        else
        {
            $min_nr = $this->getPassNr();
            $overall_stat = "notgraded";
//echo "*".$cnt_passed."*".$cnt_notgraded."*".$min_nr."*";
            if ($failed_a_mandatory || ($cnt_passed + $cnt_notgraded < $min_nr))
            {
                $overall_stat = "failed";
            }
            else if ($passed_all_mandatory && $cnt_passed >= $min_nr)
            {
                $overall_stat = "passed";
            }
        }

        $ret =  array(
            "overall_status" => $overall_stat,
            "failed_a_mandatory" => $failed_a_mandatory);
//echo "<br>p:".$cnt_passed.":ng:".$cnt_notgraded;
//var_dump($ret);
        return $ret;
    }

    /**
     * Update ephorus status of user
     */
    function updateUserStatus($a_user_id = 0)
    {
        global $ilUser;

        if ($a_user_id == 0)
        {
            $a_user_id = $ilUser->getId();
        }
        $st = $this->determineStatusOfUser($a_user_id);

        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphorusMembers.php");
        ilEphorusMembers::_writeStatus($this->getId(), $a_user_id, $st["overall_status"]);
    }

    /**
     * Update status of all users
     */
    function updateAllUsersStatus()
    {
        if (!is_object($this->members_obj));
        {
            $this->members_obj = new ilEphorusMembers($this);
        }

        $mems = $this->members_obj->getMembers();
        foreach ($mems as $mem)
        {
            $this->updateUserStatus($mem);
        }
    }

    /**
     * send ephorus per mail to members
     */
    function sendAssignment($a_eph_id, $a_ass_id, $a_members)
    {
        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignment.php");
        $ass_title = ilEphAssignment::lookupTitle($a_ass_id);

        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilFSStorageEphorus.php");
        $storage = new ilFSStorageEphorus($a_eph_id, $a_ass_id);
        $files = $storage->getFiles();

        if(count($files))
        {
            include_once "./Services/Mail/classes/class.ilFileDataMail.php";

            $mfile_obj = new ilFileDataMail($_SESSION["AccountId"]);
            foreach($files as $file)
            {
                $mfile_obj->copyAttachmentFile($file["fullpath"], $file["name"]);
                $file_names[] = $file["name"];
            }
        }

        include_once "Services/Mail/classes/class.ilMail.php";

        $tmp_mail_obj = new ilMail($_SESSION["AccountId"]);
        $message = $tmp_mail_obj->sendMail(
            $this->__formatRecipients($a_members),"","",
            $this->__formatSubject($ass_title), $this->__formatBody($a_ass_id),
            count($file_names) ? $file_names : array(),array("normal"));

        unset($tmp_mail_obj);

        if(count($file_names))
        {
            $mfile_obj->unlinkFiles($file_names);
            unset($mfile_obj);
        }


        // SET STATUS SENT FOR ALL RECIPIENTS
        foreach($a_members as $member_id => $value)
        {
            ilEphAssignment::updateStatusSentForUser($a_ass_id, $member_id, 1);
        }

        return true;
    }

    // PRIVATE METHODS
    function __formatBody($a_ass_id)
    {
        global $lng;

        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignment.php");
        $ass = new ilEphAssignment($a_ass_id);

        $body = $ass->getInstruction();
        $body .= "\n\n";
        $body .= $lng->txt("eph_edit_until") . ": ".
            ilFormat::formatDate(date("Y-m-d H:i:s",$ass->getDeadline()), "datetime", true);
        $body .= "\n\n";
        $body .= ILIAS_HTTP_PATH.
            "/goto.php?target=".
            $this->getType().
            "_".$this->getRefId()."&client_id=".CLIENT_ID;

        return $body;
    }

    function __formatSubject($a_ass_title = "")
    {
        $subject = $this->getTitle();

        if ($a_ass_title != "")
        {
            $subject.= ": ".$a_ass_title;
        }

        return $subject;
    }

    function __formatRecipients($a_members)
    {
        foreach($a_members as $member_id => $value)
        {
            $tmp_obj = ilObjectFactory::getInstanceByObjId($member_id);
            $tmp_members[] = $tmp_obj->getLogin();

            unset($tmp_obj);
        }

        return implode(',',$tmp_members ? $tmp_members : array());
    }

    /**
     * Delete all delivered files of user
     *
     * @param int $a_user_id user id
     */
    function deleteAllDeliveredFilesOfUser($a_user_id)
    {
        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignment.php");
        ilEphAssignment::deleteAllDeliveredFilesOfUser($this->getId(), $a_user_id);
    }

    /**
     * Exports grades as ephel
     */
    function exportGradesExcel()
    {
        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignment.php");
        $ass_data = ilEphAssignment::getAssignmentDataOfEphorus($this->getId());

        include_once "./Services/Excel/classes/class.ilExcelWriterAdapter.php";
        $ephelfile = ilUtil::ilTempnam();
        $adapter = new ilExcelWriterAdapter($ephelfile, FALSE);
        $workbook = $adapter->getWorkbook();
        $workbook->setVersion(8); // Use Excel97/2000 Format
        include_once ("./Services/Excel/classes/class.ilExcelUtils.php");

        //
        // status
        //
        $mainworksheet = $workbook->addWorksheet();

        // header row
        $mainworksheet->writeString(0, 0, ilExcelUtils::_convert_text($this->lng->txt("name")));
        $cnt = 1;
        foreach ($ass_data as $ass)
        {
            $mainworksheet->writeString(0, $cnt, $cnt);
            $cnt++;
        }
        $mainworksheet->writeString(0, $cnt, ilExcelUtils::_convert_text($this->lng->txt("rep_robj_xeph_total_exc")));

        // data rows
        $this->mem_obj = new ilEphorusMembers($this);
        $getmems = $this->mem_obj->getMembers();
        $mems = array();
        foreach ($getmems as $user_id)
        {
            $mems[$user_id] = ilObjUser::_lookupName($user_id);
        }
        $mems = ilUtil::sortArray($mems, "lastname", "asc", false, true);

        $data = array();
        $row_cnt = 1;
        foreach ($mems as $user_id => $d)
        {
            $col_cnt = 1;

            // name
            $mainworksheet->writeString($row_cnt, 0,
                ilExcelUtils::_convert_text($d["lastname"].", ".$d["firstname"]." [".$d["login"]."]"));

            reset($ass_data);

            foreach ($ass_data as $ass)
            {
                $status = ilEphAssignment::lookupStatusOfUser($ass["id"], $user_id);
                $mainworksheet->writeString($row_cnt, $col_cnt, ilExcelUtils::_convert_text($this->lng->txt("rep_robj_xeph_".$status)));
                $col_cnt++;
            }

            // total status
            $status = ilEphorusMembers::_lookupStatus($this->getId(), $user_id);
            $mainworksheet->writeString($row_cnt, $col_cnt, ilExcelUtils::_convert_text($this->lng->txt("rep_robj_xeph_".$status)));

            $row_cnt++;
        }

        //
        // mark
        //
        $worksheet2 = $workbook->addWorksheet();

        // header row
        $worksheet2->writeString(0, 0, ilExcelUtils::_convert_text($this->lng->txt("name")));
        $cnt = 1;
        foreach ($ass_data as $ass)
        {
            $worksheet2->writeString(0, $cnt, $cnt);
            $cnt++;
        }
        $worksheet2->writeString(0, $cnt, ilExcelUtils::_convert_text($this->lng->txt("rep_robj_xeph_total_exc")));

        // data rows
        $data = array();
        $row_cnt = 1;
        reset($mems);
        foreach ($mems as $user_id => $d)
        {
            $col_cnt = 1;
            $d = ilObjUser::_lookupName($user_id);

            // name
            $worksheet2->writeString($row_cnt, 0,
                ilExcelUtils::_convert_text($d["lastname"].", ".$d["firstname"]." [".$d["login"]."]"));

            reset($ass_data);

            foreach ($ass_data as $ass)
            {
                $worksheet2->writeString($row_cnt, $col_cnt,
                    ilExcelUtils::_convert_text(ilEphAssignment::lookupMarkOfUser($ass["id"], $user_id)));
                $col_cnt++;
            }

            // total mark
            include_once 'Services/Tracking/classes/class.ilLPMarks.php';
            $worksheet2->writeString($row_cnt, $col_cnt,
                ilExcelUtils::_convert_text(ilLPMarks::_lookupMark($user_id, $this->getId())));

            $row_cnt++;
        }

        $workbook->close();
        $eph_name = ilUtil::getASCIIFilename(preg_replace("/\s/", "_", $this->getTitle()));
        ilUtil::deliverFile($ephelfile, $eph_name.".xls", "application/vnd.ms-ephel");
    }
}
?>