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


include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");

/**
 * User Interface class for example repository object.
 *
 * User interface classes process GET and POST parameter and call
 * application classes to fulfill certain tasks.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 *
 * $Id$
 *
 * Integration into control structure:
 * - The GUI class is called by ilRepositoryGUI
 * - GUI classes used by this class are ilPermissionGUI (provides the rbac
 *   screens) and ilInfoScreenGUI (handles the info screen).
 *
 * @ilCtrl_isCalledBy ilObjEphorusGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjEphorusGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilFileSystemGUI
 * @ilCtrl_Calls ilObjEphorusGUI: ilRepositorySearchGUI, ilCommonActionDispatcherGUI
 *
 */
class ilObjEphorusGUI extends ilObjectPluginGUI
{
    /**
     * Initialisation
     */
    protected function afterConstructor()
    {
        global $ilCtrl;
        // anything needed after object has been constructed
        // - example: append my_id GET parameter to each request
        $ilCtrl->saveParameter($this, array("ass_id"));
        $ilCtrl->saveParameter($this, array("part_id"));
        $ilCtrl->saveParameter($this, array("doc_id"));
        $ilCtrl->saveParameter($this, array("mode"));

        if (isset($_GET["cmdClass"]) && $_GET["cmdClass"] == "ilfilesystemgui")
        {
            $ilCtrl->saveParameter($this, array("member_id"));
        }

        if ($_GET["ass_id"] > 0)
        {
            include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignment.php");
            $this->ass = new ilEphAssignment((int) $_GET["ass_id"]);
        }
    }

    /**
     * Get type.
     */
    final function getType()
    {
        return "xeph";
    }

    /**
     * Handles all commmands of this class, centralizes permission checks
     */
    function performCommand($cmd)
    {
        global $tpl;

        $tpl->setDescription($this->object->getDescription());

        switch ($cmd)
        {                                      // list all commands that need write permission here
            /* Assignments */
            case "listAssignments":            // Overview of assignments
            case "addAssignment":              // Add assignment form
            case "saveAssignment":             // Save new assignment
            case "editAssignment":             // Edit assignment form
            case "updateAssignment":           // Save edited assignment
            case "confirmDeleteAssignment":    // Confirm for deletion of selected assignment(s)
            case "deleteAssignment":           // Delete assignment(s) for real
            case "orderAssignmentsByDeadline": // Order assignments by deadline
            case "saveAssignmentsOrder":       // Order Assignments as set

            /* Settings */
            case "settings":                   // Settings form
            case "updateSettings":             // Save settings

            /* Submissions and Grades */
            case "showSubmissions":            // Submissions per assignment
            case "saveStatus":                 // Update submission users
            case "selectAssignment":           // Select an assignment
            case "addUserFromAutoComplete":    // Add user from dropdown
            case "addParticipant":             // Add user from search
            case "showParticipants":           // Submissions per user
            case "saveStatusParticipant":      // Update user submissions
            case "selectParticipant":          // Select a user
            case "redirectFeedbackMail":       //
            case "sendMembers":                //
            case "confirmDeassignMembers":     //
            case "deassignMembers":            //
            case "downloadSubmittedFile":      //
            case "showGradesOverview":         // Grades overview
            case "exportExcel":                // Export grades as xls
            case "saveGrades":                 // Update grades

            /* Ephorus stuff */
            case "viewReport":                 // View the report of the selected document
            case "changeVisibility":                // Change the index of the selected document
                $this->checkPermission("write");
                $this->$cmd();
                break;
                                               // list all commands that need read permission here
            /* Assignments */
            case "showOverview":               // Assignment overview
            case "downloadFile":               // Download selected assignment file
            case "submissionScreen":           // Add submission
            case "deliverFile":                // Save file to server
            case "deliverUnzip":               // Save zip
            case "download":                   // Download own submitted document(s)
            case "confirmDeleteDelivered":     // Confirm for deletion of document(s)
            case "deleteDelivered":            // Delete document(s) for real
            case "listPublicSubmissions":      // Show all Submissions
            case "downloadReturned":           // Download all Submissions
            case "downloadAllDeliveredFiles":
                $this->checkPermission("read");
                $this->$cmd();
                break;

            /* ilFileSystemGUI */
            case "listFiles":                  // Edit assignment files
            case "uploadFile":                 // Edit assignment files
            case "extCommand_0":               // Assignment overview
            case "extCommand_1":               // Add submission
            case "extCommand_2":               // Save file to server
            case "extCommand_3":               // Save zip
            case "deleteFile":                 //
            case "renameFile":                 //
            case "cancelRename":               //
                $this->checkPermission("write");
                $this->fileSystemHandler($cmd);
                break;

            /* ilRepositorySearchGUI */
            case "doUserAutoComplete":          // Search for users auto-complete box
            case "showSearch":                  //
            case "performSearch":               //
            case "addUser":                     //
                $this->checkPermission("write");
                $this->repoSearchHandler($cmd);
                break;

            /* Cancel ilRepositorySearchGUI */
            case "cancel":                      // Cancel search for users
                $this->checkPermission("write");
                $this->showSubmissions();
                break;

            /* Fix for edit timings */
            case "edit":
            case "update":
            case "listConditions":
            case "selector":
            case "add":
            case "assign":
            case "saveObligatorySettings":
            case "askDelete":
            case "delete":
            include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;
        }
    }

    /**
     * After object has been created -> jump to this command
     */
    function getAfterCreationCmd()
    {
        return "settings";
    }

    /**
     * Get standard command
     */
    function getStandardCmd()
    {
        return "showOverview";
    }

//
// DISPLAY TABS
//
    /**
     * Set tabs
     */
    function setTabs()
    {
        global $ilTabs, $ilCtrl, $ilAccess;

        // tab for the "show content" command
        if ($ilAccess->checkAccess("read", "", $this->object->getRefId()))
        {
            $ilTabs->addTab("overview", $this->txt("assignments"), $ilCtrl->getLinkTarget($this, "showOverview"));
        }

        // standard info screen tab
        $ilTabs->addTab("info", $this->txt("info_short"), $this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary"));

        // a "settings" tab
        if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
        {
            $ilTabs->addTab("settings", $this->txt("settings"), $ilCtrl->getLinkTarget($this, "settings"));
        }

        // a "submissions" tab
        if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
        {
            $ilTabs->addTab("submissions", $this->txt("submissions_and_grades"), $ilCtrl->getLinkTarget($this, "showSubmissions"));
        }

        // standard permission tab
        $this->addPermissionTab();
    }


    //
    // Show content
    //

    /**
     * Add subtabs of content view
     *
     * @param	object		$tabs_gui		ilTabsGUI object
     */
    function addOverviewSubTabs($a_activate)
    {
        global $ilTabs, $lng, $ilCtrl, $ilAccess;

        $ilTabs->addSubTab("overview", $lng->txt("view"), $ilCtrl->getLinkTarget($this, "showOverview"));

        if ($ilAccess->checkAccess("write", "", $this->ref_id))
        {
            $ilTabs->addSubTab("list_assignments", $lng->txt("edit"), $ilCtrl->getLinkTarget($this, "listAssignments"));
        }
        $ilTabs->activateSubTab($a_activate);
    }

    /**
     * Show overview
     */
    function showOverview()
    {
        global $tpl, $ilTabs;

        $ilTabs->activateTab("overview");
        $this->addOverviewSubTabs("overview");

        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignment.php");
        include_once("./Services/Accordion/classes/class.ilAccordionGUI.php");
        $acc = new ilAccordionGUI();
        $acc->setBehaviour("OneOpenSession");
        $acc->setId("eph_ow_".$this->object->getId());
        $ass_data = ilEphAssignment::getAssignmentDataOfEphorus($this->object->getId());
        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignmentGUI.php");
        $ass_gui = new ilEphAssignmentGUI($this->object);

        foreach ($ass_data as $ass)
        {
            $acc->addItem($ass_gui->getOverviewHeader($ass),
                $ass_gui->getOverviewBody($ass));
        }

        $tpl->setContent($acc->getHTML());
    }

    /**
     * Show assignment overview
     */
    function listAssignments()
    {
        global $tpl, $ilTabs, $ilToolbar, $lng, $ilCtrl;

        $ilTabs->activateTab("overview");
        $this->addOverviewSubTabs("list_assignments");

        $ilToolbar->addButton($this->txt("add_assignment"), $ilCtrl->getLinkTarget($this, "addAssignment"));

        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignmentTableGUI.php");
        $t = new ilEphAssignmentTableGUI($this, "listAssignments", $this->object);
        $tpl->setContent($t->getHTML());
    }

    /**
     * Download assignment file
     */
    function downloadFile()
    {
        $file = ($_POST["file"]) ? $_POST["file"] : $_GET["file"];

        if (!isset($file))
        {
            ilUtil::sendFailure($this->lng->txt("rep_robj_xeph_select_one_file"),true);
            $this->ctrl->redirect($this, "view");
        }

        // check, whether file belongs to assignment
        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignment.php");
        $files = ilEphAssignment::getFiles($this->object->getId(), (int) $_GET["ass_id"]);
        $file_exist = false;
        foreach($files as $lfile)
        {
            if($lfile["name"] == urldecode($file))
            {
                $file_exist = true;
                break;
            }
        }
        if(!$file_exist)
        {
            echo "FILE DOES NOT EXIST";
            exit;
        }

        // check whether assignment as already started
        $ass = new ilEphAssignment((int) $_GET["ass_id"]);
        $not_started_yet = false;
        if ($ass->getStartTime() > 0 && time() - $ass->getStartTime() <= 0)
        {
            $not_started_yet = true;
        }

        // deliver file
        if (!$not_started_yet)
        {
            include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilFSStorageEphorus.php");
            $storage = new ilFSStorageEphorus($this->object->getId(), (int) $_GET["ass_id"]);
            $p = $storage->getAssignmentFilePath(urldecode($file));
            ilUtil::deliverFile($p, urldecode($file));
        }

        return true;
    }

    function addAssignment()
    {
        global $tpl, $ilTabs;

        $ilTabs->activateTab("overview");
        $this->addOverviewSubTabs("list_assignments");

        $this->initAssignmentForm();
        $tpl->setContent($this->form->getHTML());
    }

    function editAssignment()
    {
        global $ilTabs, $tpl;

        $this->setAssignmentHeader();
        $ilTabs->activateTab("ass_settings");

        $this->initAssignmentForm("edit");
        $this->getAssignmentValues();
        $tpl->setContent($this->form->getHTML());
    }

    /**
     * Get current values for assignment from
     *
     */
    public function getAssignmentValues()
    {
        $values = array();

        $ass = new ilEphAssignment($_GET["ass_id"]);
        $values["title"] = $ass->getTitle();
        if ($ass->getStartTime() > 0)
        {
            $values["start_time"] = true;
        }
        $values["mandatory"] = $ass->getMandatory();
        $values["instruction"] = $ass->getInstruction();

        $this->form->setValuesByArray($values);

        $edit_date = new ilDateTime($ass->getDeadline(), IL_CAL_UNIX);
        $ed_item = $this->form->getItemByPostVar("deadline");
        $ed_item->setDate($edit_date);

        if ($ass->getStartTime() > 0)
        {
            $edit_date = new ilDateTime($ass->getStartTime(), IL_CAL_UNIX);
            $ed_item = $this->form->getItemByPostVar("start_time");
            $ed_item->setDate($edit_date);
        }
    }

    function fileSystemHandler($command)
    {
        //0 download
        //1 delete
        //2 unzip
        //3 rename
        if (isset($_GET["member_id"]))
        {
            global $ilTabs, $lng, $ilCtrl, $tpl;

            include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignment.php");
            $tpl->setTitle(ilEphAssignment::lookupTitle($_GET["ass_id"]));

            $ilTabs->clearTargets();
            $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "showSubmissions"));

            include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilFSStorageEphorus.php");
            $storage = new ilFSStorageEphorus($this->object->getId(), $_GET["ass_id"]);

            include_once("./Services/FileSystem/classes/class.ilFileSystemGUI.php");
            $fs = new ilFileSystemGUI($storage->getFeedbackPath($_GET["member_id"]));
            $fs->setAllowDirectoryCreation(false);
            $fs->executeCommand($command);
        }
        else
        {
            global $ilTabs;

            $this->setAssignmentHeader();
            $ilTabs->activateTab("ass_files");

            include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilFSStorageEphorus.php");
            $storage = new ilFSStorageEphorus($this->object->getId(), $_GET["ass_id"]);

            include_once("./Services/FileSystem/classes/class.ilFileSystemGUI.php");

            $fs = new ilFileSystemGUI($storage->getPath());
            $fs->setAllowDirectoryCreation(false);
            $fs->executeCommand($command);
        }
    }

    function repoSearchHandler($command)
    {
        global $ilTabs;

        $ilTabs->activateTab("grades");

        include_once('./Services/Search/classes/class.ilRepositorySearchGUI.php');
        $rep_search = new ilRepositorySearchGUI();
        $rep_search->setTitle($this->lng->txt("rep_robj_xeph_add_participant"));
        $rep_search->setCallback($this,'addParticipant');
        $rep_search->executeCommand($command);

        // Set tabs
        $this->tabs_gui->setTabActive('members');
        $this->ctrl->setReturn($this,'showSubmissions');
    }

    /**
     * Update assignment
     *
     */
    public function updateAssignment()
    {
        global $tpl, $lng, $ilCtrl, $ilTabs;

        $ilTabs->activateTab("overview");
        $this->addOverviewSubTabs("list_assignments");

        $this->initAssignmentForm("edit");
        if ($this->form->checkInput())
        {
            include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignment.php");

            // additional checks
            if ($_POST["start_time_cb"])
            {
                // check whether start date is before end date
                $start_date =
                    $this->form->getItemByPostVar("start_time")->getDate();
                $end_date =
                    $this->form->getItemByPostVar("deadline")->getDate();
                if ($start_date->get(IL_CAL_UNIX) >=
                    $end_date->get(IL_CAL_UNIX))
                {
                    ilUtil::sendFailure($lng->txt("form_input_not_valid"), true);
                    $this->form->getItemByPostVar("start_time")->setAlert($lng->txt("rep_robj_xeph_start_date_should_be_before_end_date"));
                    $this->form->getItemByPostVar("deadline")->setAlert($lng->txt("rep_robj_xeph_start_date_should_be_before_end_date"));
                    $this->form->setValuesByPost();
                    $tpl->setContent($this->form->getHtml());
                    return;
                }
            }


            $ass = new ilEphAssignment($_GET["ass_id"]);
            $ass->setTitle($_POST["title"]);
            $ass->setInstruction($_POST["instruction"]);
            $ass->setEphorusId($this->object->getId());
            $ass->setMandatory($_POST["mandatory"]);

            if ($_POST["start_time_cb"])
            {
                $date =
                    $this->form->getItemByPostVar("start_time")->getDate();
                $ass->setStartTime($date->get(IL_CAL_UNIX));
            }
            else
            {
                $ass->setStartTime(null);
            }

            // deadline
            $date =
                $this->form->getItemByPostVar("deadline")->getDate();
            $ass->setDeadline($date->get(IL_CAL_UNIX));

            $ass->update($this->object);
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "listAssignments");
        }
        else
        {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHtml());
        }
    }

    public function initAssignmentForm($a_mode = "create")
    {
        global $lng, $ilCtrl, $ilSetting;

        // init form
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();
        $this->form->setTitle(($a_mode == "edit") ? $this->txt("edit_assignment") : $this->txt("new_assignment"));
        $this->form->setFormAction($ilCtrl->getFormAction($this));

        // title
        $title = new ilTextInputGUI($this->txt("title"), "title");
        $title->setMaxLength(200);
        $title->setRequired(true);
        $this->form->addItem($title);

        // start time y/n
        $check_start_time = new ilCheckboxInputGUI($this->txt("start_time"), "start_time_cb");
        $this->form->addItem($check_start_time);

        // start time
        $start_time = new ilDateTimeInputGUI("", "start_time");
        $start_time->setShowTime(true);
        $check_start_time->addSubItem($start_time);

        // Deadline
        $deadline = new ilDateTimeInputGUI($this->txt("deadline"), "deadline");
        $deadline->setShowTime(true);
        $deadline->setRequired(true);
        $this->form->addItem($deadline);

        // mandatory
        $mandatory = new ilCheckboxInputGUI($this->txt("mandatory"), "mandatory");
        $mandatory->setInfo($this->txt("mandatory_info"));
        $mandatory->setChecked(true);
        $this->form->addItem($mandatory);

        // Work Instructions
        $instruction = new ilTextAreaInputGUI($this->txt("instruction"), "instruction");
        $instruction->setCols(39);
        $instruction->setRows(4);
        $instruction->setRequired(true);
        $this->form->addItem($instruction);

        // files
        if ($a_mode == "create")
        {
            $files = new ilFileWizardInputGUI($this->txt('files'),'files');
            $files->setFilenames(array(0 => ''));
            $this->form->addItem($files);

            $this->form->addCommandButton("saveAssignment", $lng->txt("save"));
            $this->form->addCommandButton("listAssignments", $lng->txt("cancel"));
        }
        else
        {
            $this->form->addCommandButton("updateAssignment", $lng->txt("save"));
            $this->form->addCommandButton("listAssignments", $lng->txt("cancel"));
        }
    }

    /**
     * Save assignment
     *
     */
    public function saveAssignment()
    {
        global $tpl, $lng, $ilCtrl, $ilTabs;

        $ilTabs->activateTab("overview");
        $this->addOverviewSubTabs("list_assignments");

        $this->initAssignmentForm();
        if ($this->form->checkInput())
        {
            include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignment.php");

            // additional checks
            if ($_POST["start_time_cb"])
            {
                // check whether start date is before end date
                $start_date = $this->form->getItemByPostVar("start_time")->getDate();
                $end_date = $this->form->getItemByPostVar("deadline")->getDate();

                if ($start_date->get(IL_CAL_UNIX) >=
                    $end_date->get(IL_CAL_UNIX))
                {
                    ilUtil::sendFailure($lng->txt("form_input_not_valid"), true);
                    $this->form->getItemByPostVar("start_time")
                        ->setAlert($lng->txt("start_date_should_be_before_end_date"));
                    $this->form->getItemByPostVar("deadline")
                        ->setAlert($lng->txt("start_date_should_be_before_end_date"));
                    $this->form->setValuesByPost();
                    $tpl->setContent($this->form->getHtml());
                    return;
                }
            }

            $ass = new ilEphAssignment();
            $ass->setTitle($_POST["title"]);
            $ass->setInstruction($_POST["instruction"]);
            $ass->setEphorusId($this->object->getId());
            $ass->setMandatory($_POST["mandatory"]);

            if ($_POST["start_time_cb"])
            {
                $date = $this->form->getItemByPostVar("start_time")->getDate();
                $ass->setStartTime($date->get(IL_CAL_UNIX));
            }
            else
            {
                $ass->setStartTime(null);
            }

            // deadline
            $date = $this->form->getItemByPostVar("deadline")->getDate();
            $ass->setDeadline($date->get(IL_CAL_UNIX));

            $ass->save($this->object);

            // save files
            $ass->uploadAssignmentFiles($_FILES["files"]);


            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "listAssignments");
        }
        else
        {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHtml());
        }
    }

    /**
     * Set assignment header
     */
    function setAssignmentHeader()
    {
        global $ilTabs, $lng, $ilCtrl, $tpl;

        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignment.php");
        $tpl->setTitle(ilEphAssignment::lookupTitle($_GET["ass_id"]));
        $tpl->setDescription("");

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listAssignments"));
        $ilTabs->addTab("ass_settings", $lng->txt("settings"), $ilCtrl->getLinkTarget($this, "editAssignment"));
        $ilTabs->addTab("ass_files", $lng->txt("files"), $ilCtrl->getLinkTargetByClass("ilfilesystemgui", "listFiles"));
    }


    function confirmDeleteAssignment(){
        global $ilCtrl, $tpl, $lng, $ilTabs;

        $ilTabs->activateTab("overview");
        $this->addOverviewSubTabs("list_assignments");

        if (!is_array($_POST["id"]) || count($_POST["id"]) == 0)
        {
            ilUtil::sendInfo($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listAssignments");
        }
        else
        {
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("rep_robj_xeph_conf_del_assignments"));
            $cgui->setCancel($lng->txt("cancel"), "listAssignments");
            $cgui->setConfirm($lng->txt("delete"), "deleteAssignment");

            include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignment.php");

            foreach ($_POST["id"] as $i)
            {
                $cgui->addItem("id[]", $i, ilEphAssignment::lookupTitle($i));
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    function deleteAssignment(){
        global $ilDB, $ilCtrl, $lng;

        $delete = false;
        if (is_array($_POST["id"]))
        {
            include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignment.php");
            foreach($_POST["id"] as $id)
            {
                $ass = new ilEphAssignment(ilUtil::stripSlashes($id));
                $ass->delete($this->object);
                $delete = true;
            }
        }

        if ($delete)
        {
            ilUtil::sendSuccess($lng->txt("rep_robj_xeph_assignments_deleted"), true);
        }
        $ilCtrl->redirect($this, "listAssignments");
    }

    function orderAssignmentsByDeadline(){
        global $lng, $ilCtrl;

        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignment.php");
        ilEphAssignment::orderAssByDeadline($this->object->getId());

        ilUtil::sendSuccess($lng->txt("rep_robj_xeph_saved_order"), true);
        $ilCtrl->redirect($this, "listAssignments");
    }

    function saveAssignmentsOrder(){
        global $lng, $ilCtrl;

        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignment.php");
        ilEphAssignment::saveAssOrderOfEphorus($this->object->getId(), $_POST["order"]);

        ilUtil::sendSuccess($lng->txt("rep_robj_xeph_saved_order"), true);
        $ilCtrl->redirect($this, "listAssignments");
    }

    /**
     * List all submissions
     */
    function listPublicSubmissions()
    {
        global $tpl, $ilTabs;

        $ilTabs->activateTab("overview");
        $this->addOverviewSubTabs("overview");

        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphPublicSubmissionsTableGUI.php");
        $tab = new ilEphPublicSubmissionsTableGUI($this, "listPublicSubmissions", $this->object, (int) $_GET["ass_id"]);
        $tpl->setContent($tab->getHTML());
    }

    /**
     * Download submitted files of user.
     */
    function downloadReturned()
    {
        global $ilAccess;

        if ($ilAccess->checkAccess("read", "", $this->object->getRefId()) &&
            $this->object->getShowSubmissions() &&
            $this->object->getTimestamp() - time() <= 0)
        {
            // ok: read access + public submissions
        }
        else
        {
            $this->checkPermission("write");
        }

        if (!ilEphAssignment::deliverReturnedFiles($this->object->getId(), (int) $_GET["ass_id"], (int) $_GET["member_id"]))
        {
            $this->ctrl->redirect($this, "showSubmissions");
        }
        exit;
    }

    /**
     * Displays a form which allows members to deliver their solutions
     *
     * @access public
     */
    function submissionScreen()
    {
        global $ilUser;
        require_once "./Services/Utilities/classes/class.ilUtil.php";

        $this->tabs_gui->setTabActive("overview");
        $this->addOverviewSubTabs("overview");

        if (mktime() > $this->ass->getDeadline())
        {
            ilUtil::sendInfo($this->lng->txt("rep_robj_xeph_time_up"));
        }

        $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.eph_deliver_file.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus");

	    include_once(dirname(dirname(__FILE__)).'/include/class.DLEApi.php');

	    include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphDeliveredFilesTableGUI.php");
        $tab = new ilEphDeliveredFilesTableGUI($this, "deliver", $this->object, $_GET["ass_id"]);
        $this->tpl->setVariable("DELIVERED_FILES_TABLE", $tab->getHTML());

        if (mktime() < $this->ass->getDeadline())
        {
	        $disclosure = DLEApi::getSetting("disclosure");
	        if(!empty($disclosure)) {
	            $this->tpl->setVariable("DISCLOSURE", "<div class=\"framed\"><p class=\"small\">".$disclosure."</p></div><br />");
	        }
	        $this->initUploadForm();
            $this->tpl->setVariable("UPLOAD_SINGLE_FORM", $this->form->getHTML());

            $this->initZipUploadForm();
            $this->tpl->setVariable("UPLOAD_MULTI_FORM", $this->form->getHTML());
        }
    }

    /**
     * Init upload form form.
     */
    public function initUploadForm()
    {
        global $lng, $ilCtrl;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();

        // file input
        include_once("./Services/Form/classes/class.ilFileWizardInputGUI.php");
        $fi = new ilFileWizardInputGUI($lng->txt("file"), "deliver");
        $fi->setFilenames(array(0 => ''));
        //$fi->setInfo($lng->txt(""));
        $this->form->addItem($fi);

        $this->form->addCommandButton("deliverFile", $lng->txt("upload"));

        $this->form->setTitle($lng->txt("file_add"));
        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }

    /**
     * Init upload form form.
     */
    public function initZipUploadForm()
    {
        global $lng, $ilCtrl;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();

        // desc
        include_once("./Services/Form/classes/class.ilFileInputGUI.php");
        $fi = new ilFileInputGUI($lng->txt("file"), "deliver");
        $fi->setSuffixes(array("zip"));
        $this->form->addItem($fi);

        $this->form->addCommandButton("deliverUnzip", $lng->txt("upload"));

        $this->form->setTitle($lng->txt("header_zip"));
        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }

    /**
     * Upload files
     */
    function deliverFile()
    {
        global $ilUser, $lng, $ilCtrl;

        $success = false;
        foreach ($_FILES["deliver"]["name"] as $k => $v)
        {
            $file = array(
                "name" => $_FILES["deliver"]["name"][$k],
                "type" => $_FILES["deliver"]["type"][$k],
                "tmp_name" => $_FILES["deliver"]["tmp_name"][$k],
                "error" => $_FILES["deliver"]["error"][$k],
                "size" => $_FILES["deliver"]["size"][$k],
            );
            if(!$this->object->deliverFile($file, (int) $_GET["ass_id"], $ilUser->id))
            {
                ilUtil::sendFailure($this->lng->txt("upload_error"), true);
            }
            else
            {
                $success = true;
            }
        }

        if($success)
        {
            //$this->sendNotifications((int)$_GET["ass_id"]);
            $this->object->handleSubmission((int)$_GET['ass_id']);
        }
        $ilCtrl->redirect($this, "submissionScreen");
    }

    /**
     * Upload zip file
     */
    function deliverUnzip()
    {
        global $ilCtrl;

	    if (preg_match("/zip/",$_FILES["deliver"]["name"]) == 1)
        {
            if($this->object->processUploadedFile($_FILES["deliver"]["tmp_name"], "deliverFile", false, (int) $_GET["ass_id"]))
            {
                //$this->sendNotifications((int)$_GET["ass_id"]);
                $this->object->handleSubmission((int)$_GET['ass_id']);
            }
        }

        $ilCtrl->redirect($this, "submissionScreen");
    }

    /**
     * User downloads (own) submitted files
     *
     * @param
     * @return
     */
    function download()
    {
        global $ilUser, $ilCtrl;

        $this->checkPermission("read");
        if (count($_REQUEST["delivered"]))
        {
            if(!is_array($_REQUEST["delivered"]))
            {
                $_REQUEST["delivered"] = array($_REQUEST["delivered"]);
            }
            ilEphAssignment::downloadSelectedFiles($this->object->getId(), (int) $_GET["ass_id"],
                $ilUser->getId(), $_REQUEST["delivered"]);
            exit;
        }
        else
        {
            ilUtil::sendFailure($this->lng->txt("please_select_a_delivered_file_to_download"), true);
            $ilCtrl->redirect($this, "submissionScreen");
        }
    }

    /**
     * Confirm deletion of delivered files
     */
    function confirmDeleteDelivered()
    {
        global $ilCtrl, $tpl, $lng, $ilUser;

        $this->tabs_gui->setTabActive("overview");
        $this->addOverviewSubTabs("overview");

        if (mktime() > $this->ass->getDeadline())
        {
            ilUtil::sendFailure($this->lng->txt("rep_robj_xeph_time_up"), true);
            $ilCtrl->redirect($this, "submissionScreen");
        }

        if (!is_array($_POST["delivered"]) || count($_POST["delivered"]) == 0)
        {
            ilUtil::sendFailure($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "submissionScreen");
        }
        else
        {
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("info_delete_sure"));
            $cgui->setCancel($lng->txt("cancel"), "submissionScreen");
            $cgui->setConfirm($lng->txt("delete"), "deleteDelivered");

            $files = ilEphAssignment::getDeliveredFiles($this->object->getId(), (int) $_GET["ass_id"], $ilUser->getId());
            foreach ($_POST["delivered"] as $i)
            {
                reset ($files);
                $title = "";
                foreach ($files as $f)
                {
                    if ($f["id"] == $i)
                    {
                        $title = $f["filetitle"];
                    }
                }
                $cgui->addItem("delivered[]", $i, $title);
            }
            $tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Delete file(s) submitted by user
     *
     * @param
     * @return
     */
    function deleteDelivered()
    {
        global $ilUser, $ilCtrl;

        if (mktime() > $this->ass->getDeadline())
        {
            ilUtil::sendFailure($this->lng->txt("rep_robj_xeph_time_up"), true);
            $ilCtrl->redirect($this, "submissionScreen");
        }

        if (count($_POST["delivered"]) && mktime() < $this->ass->getDeadline())
        {
            $this->object->deleteDeliveredFiles($this->object->getId(), (int) $_GET["ass_id"],
                $_POST["delivered"], $ilUser->id);

            $this->object->handleSubmission((int)$_GET['ass_id']);

            ilUtil::sendSuccess($this->lng->txt("rep_robj_xeph_submitted_files_deleted"), true);
        }
        else
        {
            ilUtil::sendFailure($this->lng->txt("please_select_a_delivered_file_to_delete"), true);
        }
        $ilCtrl->redirect($this, "submissionScreen");
    }

    /**
     * show information screen
     */
    function infoScreen()
    {
        global $ilAccess, $ilUser, $ilTabs, $lng, $tpl;

        $ilTabs->activateTab("info");

        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
        $info = new ilInfoScreenGUI($this);
        $tpl->setDescription($this->object->getDescription());
        $info->enablePrivateNotes();

        $info->enableNews();
        if ($ilAccess->checkAccess("read", "", $this->object->getRefId()))
        {
            $info->enableNewsEditing();
            $info->setBlockProperty("news", "settings", true);
        }

        // standard meta data
        //$info->addMetaDataSections($this->object->getId(),0, $this->object->getType());

        // instructions
        $info->addSection($this->lng->txt("rep_robj_xeph_overview"));
        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignment.php");
        $ass = ilEphAssignment::getAssignmentDataOfEphorus($this->object->getId());
        $cnt = 0;
        $mcnt = 0;
        foreach ($ass as $a)
        {
            $cnt++;
            if ($a["mandatory"])
            {
                $mcnt++;
            }
        }
        $info->addProperty($lng->txt("rep_robj_xeph_assignments"), $cnt);
        $info->addProperty($lng->txt("rep_robj_xeph_mandatory"), $mcnt);
        if ($this->object->getPassMode() != "nr")
        {
            $info->addProperty($lng->txt("rep_robj_xeph_pass_mode"),
                $lng->txt("rep_robj_xeph_msg_all_mandatory_ass"));
        }
        else
        {
            $info->addProperty($lng->txt("rep_robj_xeph_pass_mode"),
                sprintf($lng->txt("rep_robj_xeph_msg_min_number_ass"), $this->object->getPassNr()));
        }

        // feedback from tutor
        include_once("Services/Tracking/classes/class.ilLPMarks.php");
        if ($ilAccess->checkAccess("read", "", $this->object->getRefId()))
        {
            $lpcomment = ilLPMarks::_lookupComment($ilUser->getId(), $this->object->getId());
            $mark = ilLPMarks::_lookupMark($ilUser->getId(), $this->object->getId());
            //$status = ilEphorusMembers::_lookupStatus($this->object->getId(), $ilUser->getId());
            $st = $this->object->determineStatusOfUser($ilUser->getId());
            $status = $st["overall_status"];
            if ($lpcomment != "" || $mark != "" || $status != "notgraded")
            {
                $info->addSection($this->lng->txt("rep_robj_xeph_feedback_from_tutor"));
                if ($lpcomment != "")
                {
                    $info->addProperty($this->lng->txt("rep_robj_xeph_comment"),
                        $lpcomment);
                }
                if ($mark != "")
                {
                    $info->addProperty($this->lng->txt("rep_robj_xeph_mark"),
                        $mark);
                }

                //if ($status == "")
                //{
                //  $info->addProperty($this->lng->txt("status"),
                //		$this->lng->txt("message_no_delivered_files"));
                //}
                //else
                if ($status != "notgraded")
                {
                    $img = '<img border="0" src="'.ilUtil::getImagePath("scorm/".$status.".png").'" '.
                        ' alt="'.$lng->txt("rep_robj_xeph_".$status).'" title="'.$lng->txt("rep_robj_xeph_".$status).
                        '" style="vertical-align:middle;"/>';

                    $add = "";
                    if ($st["failed_a_mandatory"])
                    {
                        $add = " (".$lng->txt("rep_robj_xeph_msg_failed_mandatory").")";
                    }
                    else if ($status == "failed")
                    {
                        $add = " (".$lng->txt("rep_robj_xeph_msg_missed_minimum_number").")";
                    }
                    $info->addProperty($this->lng->txt("status"),
                        $img." ".$this->lng->txt("rep_robj_xeph_".$status).$add);
                }
            }
        }
        // forward the command
        $this->ctrl->forwardCommand($info);
    }







//
// Edit properties form
//

    /**
     * Edit settings. This commands uses the form class to display an input form.
     */
    function settings()
    {
        global $tpl, $ilTabs;

        $ilTabs->activateTab("settings");
        $this->initSettingsForm();
        $this->getSettings();
        $tpl->setContent($this->form->getHTML());
        $a = 'a';
    }

    /**
     * Init  form.
     *
     * @param        int        $a_mode        Edit Mode
     */
    public function initSettingsForm()
    {
        global $ilCtrl;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();

        // title
        $title = new ilTextInputGUI($this->txt("title"), "title");
        $title->setRequired(true);
        $this->form->addItem($title);

        // description
        $description = new ilTextAreaInputGUI($this->txt("description"), "description");
        $description->setCols(39);
        $description->setRows(4);
        $this->form->addItem($description);

        // show submissions
        $show_submissions = new ilCheckboxInputGUI($this->txt("show_submissions"), "show_submissions");
        $show_submissions->setInfo($this->txt("show_submissions_info"));
        $this->form->addItem($show_submissions);

        // pass mode
        $pass_mode = new ilRadioGroupInputGUI($this->txt("pass_mode"), "pass_mode");

        $pass_all = new ilRadioOption($this->txt("pass_all"), "all",
            $this->txt("pass_all_info"));
        $pass_mode->addOption($pass_all);

        $pass_min = new ilRadioOption($this->txt("pass_minimum_nr"), "pass_min",
            $this->txt("pass_minimum_nr_info"));
        $pass_mode->addOption($pass_min);

        // minimum number of assignments to pass
        $min_number = new ilNumberInputGUI($this->txt("min_nr"), "min_number");
        $min_number->setSize(4);
        $min_number->setMaxLength(4);
        $min_number->setRequired(true);
        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignment.php");
        $mand = ilEphAssignment::countMandatory($this->object->getId());
        $min = max($mand, 1);
        $min_number->setMinValue($min);
        $pass_min->addSubItem($min_number);

        $this->form->addItem($pass_mode);

        $notification = new ilCheckboxInputGUI($this->txt("submission_notification"), "notification");
        $notification->setInfo($this->txt("submission_notification_info"));
        $this->form->addItem($notification);

        $copletion_by_submission = new ilCheckboxInputGUI($this->txt('completion_by_submission'), 'completion_by_submission');
        $copletion_by_submission->setInfo($this->txt('completion_by_submission_info'));
        $copletion_by_submission->setValue(1);
        $this->form->addItem($copletion_by_submission);

        $processtype = new ilSelectInputGUI($this->txt("processtype"), "processtype");
        $processtype->setOptions(array(1 => $this->txt("default"), 2 => $this->txt("reference"), 3 => $this->txt("private")));
        $processtype->setInfo($this->txt("processtype_description"));
        $this->form->addItem($processtype);

        $this->form->addCommandButton("updateSettings", $this->txt("save"));

        $this->form->setTitle($this->txt("edit_ephorus_exercise"));
        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }

    /**
     * Get values for edit properties form
     */
    function getSettings()
    {
        $values["title"] = $this->object->getTitle();
        $values["description"] = $this->object->getDescription();
        $values["show_submissions"] = $this->object->getShowSubmissions();
        $values["pass_mode"] = $this->object->getPassMode();
        $values["min_number"] = $this->object->getMinNumber();
        $values["notification"] = $this->object->getNotification();
        $values["completion_by_submission"] = $this->object->getCompletionBySubmission();
        $values["processtype"] = $this->object->getProcesstype();
        $this->form->setValuesByArray($values);
    }

    /**
     * Update settings
     */
    public function updateSettings()
    {
        global $tpl, $lng, $ilCtrl;

        $this->initSettingsForm();
        if ($this->form->checkInput())
        {
            $this->object->setTitle($this->form->getInput("title"));
            $this->object->setDescription($this->form->getInput("description"));
            $this->object->setShowSubmissions($this->form->getInput("show_submissions"));
            $this->object->setPassMode($this->form->getInput("pass_mode"));
            $this->object->setMinNumber($this->form->getInput("min_number"));
            $this->object->setNotification($this->form->getInput("notification"));
            $this->object->setCompletionBySubmission($this->form->getInput("completion_by_submission"));
            $this->object->setProcesstype($this->form->getInput("processtype"));
            $this->object->doUpdate();
            $this->object->update();

            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "settings");
        }

        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHtml());
    }

    /**
     * Show submissions
     */
    function showSubmissions()
    {
        global $tpl, $ilTabs, $tree, $ilToolbar, $ilCtrl, $lng;

        $ilTabs->activateTab("submissions");
        $this->addSubmissionSubTabs("submissions");

        include_once 'Services/Tracking/classes/class.ilLPMarks.php';

        // assignment selection
        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignment.php");
        $ass = ilEphAssignment::getAssignmentDataOfEphorus($this->object->getId());

        if ($_GET["ass_id"] == "")
        {
            $a = current($ass);
            $_GET["ass_id"] = $a["id"];
        }

        reset($ass);
        if (count($ass) > 1)
        {
            $options = array();
            foreach ($ass as $a)
            {
                $options[$a["id"]] = $a["title"];
            }
            include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
            $si = new ilSelectInputGUI($this->lng->txt(""), "ass_id");
            $si->setOptions($options);
            $si->setValue($_GET["ass_id"]);
            $ilToolbar->addInputItem($si);

            $ilToolbar->setFormAction($ilCtrl->getFormAction($this));
            $ilToolbar->addFormButton($this->lng->txt("rep_robj_xeph_select_ass"), "selectAssignment");
            $ilToolbar->addSeparator();
        }

        // add member
        include_once './Services/Search/classes/class.ilRepositorySearchGUI.php';
        ilRepositorySearchGUI::fillAutoCompleteToolbar(
            $this,
            $ilToolbar,
            array(
                'auto_complete_name'	=> $lng->txt('user'),
                'submit_name'			=> $lng->txt('add')
            )
        );

        $ilToolbar->addSpacer();

        $ilToolbar->addButton(
            $lng->txt("rep_robj_xeph_search_users"),
            $this->ctrl->getLinkTargetByClass('ilRepositorySearchGUI','showSearch'));
        $ilToolbar->setFormAction($ilCtrl->getFormAction($this));

        // add course members button, in case the ephorus is inside a course
        $parent_id = $tree->getParentId($_GET["ref_id"]);
        $parent_obj_id = ilObject::_lookupObjId($parent_id);
        $type = ilObject::_lookupType($parent_obj_id);
        while ($parent_id != 1 && $type != "crs")
        {
            $parent_id = $tree->getParentId($parent_id);
            $parent_obj_id = ilObject::_lookupObjId($parent_id);
            $type = ilObject::_lookupType($parent_obj_id);
        }
        if ($type == "crs")
        {
            $this->ctrl->setParameterByClass('ilRepositorySearchGUI', "list_obj", $parent_obj_id);
            $ilToolbar->addButton($this->lng->txt("rep_robj_xeph_crs_add_members"),
                $this->ctrl->getLinkTargetByClass('ilRepositorySearchGUI','listUsers'));
        }

        if (count($ass) > 0)
        {
            include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphorusMemberTableGUI.php");
            $eph_tab = new ilEphorusMemberTableGUI($this, "showSubmissions", $this->object, $_GET["ass_id"]);
            $tpl->setContent($eph_tab->getHTML());
        }
        else
        {
            ilUtil::sendInfo($lng->txt("rep_robj_xeph_no_assignments_available"));
        }
        return;
    }

    function selectAssignment()
    {
        global $ilTabs;

        $ilTabs->activateTab("submissions");

        $_GET["ass_id"] = ilUtil::stripSlashes($_POST["ass_id"]);
        $this->showSubmissions();
    }

    /**
     * Save status of selected members
     */
    function saveStatus($a_part_view = false)
    {
        global $ilCtrl;

        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignment.php");
		include_once ("./Services/Tracking/classes/class.ilLPMarks.php");

        $saved_for = array();
        foreach($_POST["id"] as $key => $value)
        {
            if (!$a_part_view)
            {
                if (count($_POST["member"]) > 0 && $_POST["member"][$key] != "1")
                {
                    continue;
                }
                else
                {
                    if (count($_POST["member"]) > 0)
                    {
                        $uname = ilObjUser::_lookupName($key);
                        $saved_for[] = $uname["lastname"].", ".$uname["firstname"];
                    }
                }
            }

            if (!$a_part_view)
            {
                $ass_id = (int) $_GET["ass_id"];
                $user_id = (int) $key;
            }
            else
            {
                $ass_id = (int) $key;
                $user_id = (int) $_GET["part_id"];
            }

            ilEphAssignment::updateStatusOfUser($ass_id, $user_id, ilUtil::stripSlashes($_POST["status"][$key]));
            ilEphAssignment::updateNoticeForUser($ass_id, $user_id, ilUtil::stripSlashes($_POST["notice"][$key]));

            if (ilUtil::stripSlashes($_POST['mark'][$key]) != ilEphAssignment::lookupMarkOfUser($ass_id, $user_id))
            {
                ilEphAssignment::updateStatusTimeOfUser($ass_id, $user_id);
            }

            ilEphAssignment::updateMarkOfUser($ass_id, $user_id,  ilUtil::stripSlashes($_POST['mark'][$key]));
            ilEphAssignment::updateCommentForUser($ass_id, $user_id, ilUtil::stripSlashes($_POST['lcomment'][$key]));
        }
        if (count($saved_for) > 0)
        {
            $save_for_str = "(".implode($saved_for, " - ").")";

        }
        ilUtil::sendSuccess($this->lng->txt("rep_robj_xeph_status_saved")." ".$save_for_str,true);

        if (!$a_part_view)
        {
            $ilCtrl->redirect($this, "showSubmissions");
        }
        else
        {
            $ilCtrl->redirect($this, "showParticipants");
        }
    }

    /**
     * set feedback status for member and redirect to mail screen
     */
    function redirectFeedbackMail()
    {
        if ($_GET["member_id"] != "")
        {
            ilEphAssignment::updateStatusFeedbackForUser((int) $_GET["ass_id"], (int) $_GET["member_id"], 1);
            $login = ilObjUser::_lookupLogin((int) $_GET["member_id"]);

            require_once 'Services/Mail/classes/class.ilMailFormCall.php';
            ilUtil::redirect(ilMailFormCall::getRedirectTarget($this, 'showSubmissions', array(), array('type' => 'new', 'rcp_to' => urlencode($login))));
        }
        else if(count($_POST["member"]) > 0)
        {
            include_once('./Services/User/classes/class.ilObjUser.php');
            $logins = array();
            foreach($_POST["member"] as $member => $val)
            {
                $logins[] = ilObjUser::_lookupLogin($member);
                ilEphAssignment::updateStatusFeedbackForUser((int) $_GET["ass_id"], $member, 1);
            }
            $logins = implode($logins, ",");

            require_once 'Services/Mail/classes/class.ilMailFormCall.php';
            ilUtil::redirect(ilMailFormCall::getRedirectTarget($this, 'showSubmissions', array(), array('type' => 'new', 'rcp_to' => $logins)));
        }

        ilUtil::sendFailure($this->lng->txt("no_checkbox"),true);
        $this->ctrl->redirect($this, "showSubmissions");
    }

    /**
     * Send assignment per mail to participants
     */
    function sendMembers()
    {
        global $ilCtrl;

        if(!count($_POST["member"]))
        {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"),true);
        }
        else
        {
            $this->object->sendAssignment($this->object->getId(),
                (int) $_GET["ass_id"], $_POST["member"]);
            ilUtil::sendSuccess($this->lng->txt("rep_robj_xeph_sent"),true);
        }
        $ilCtrl->redirect($this, "showSubmissions");
    }

    /**
     * Confirm deassigning members
     */
    function confirmDeassignMembers()
    {
        global $ilCtrl, $tpl, $lng, $ilTabs;

        $ilTabs->activateTab("grades");

        if (!is_array($_POST["member"]) || count($_POST["member"]) == 0)
        {
            ilUtil::sendFailure($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "showSubmissions");
        }
        else
        {
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("rep_robj_xeph_msg_sure_to_deassign_participant"));
            $cgui->setCancel($lng->txt("cancel"), "showSubmissions");
            $cgui->setConfirm($lng->txt("remove"), "deassignMembers");

            include_once("./Services/User/classes/class.ilUserUtil.php");
            foreach ($_POST["member"] as $k => $m)
            {
                $cgui->addItem("member[$k]", $m,
                    ilUserUtil::getNamePresentation((int) $k, false, false, "", true));
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Deassign members from ephorus
     */
    function deassignMembers()
    {
        global $ilCtrl, $lng;

        if(is_array($_POST["member"]))
        {
            foreach($_POST["member"] as $usr_id => $member)
            {
                $this->object->members_obj->deassignMember((int) $usr_id);
            }
            ilUtil::sendSuccess($lng->txt("rep_robj_xeph_msg_participants_removed"), true);
            $ilCtrl->redirect($this, "showSubmissions");
        }
        else
        {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"),true);
            $ilCtrl->redirect($this, "showSubmissions");
        }
    }

    function addUserFromAutoComplete()
    {
        if(!strlen(trim($_POST['user_login'])))
        {
            ilUtil::sendFailure($this->lng->txt('msg_no_search_string'));
            $this->showSubmissions();
            return false;
        }
        $users = explode(',', $_POST['user_login']);

        $user_ids = array();
        foreach($users as $user)
        {
            $user_id = ilObjUser::_lookupId($user);

            if(!$user_id)
            {
                ilUtil::sendFailure($this->lng->txt('user_not_known'));
                return $this->showSubmissions();
            }
            $user_ids[] = $user_id;
        }

        if(!$this->addParticipant($user_ids));
        {
            $this->showSubmissions();
            return false;
        }
        return true;
    }

    /**
     * Add new partipant
     */
    function addParticipant($a_user_ids = array())
    {
        global $ilAccess, $ilErr;

        if(!count($a_user_ids))
        {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"));
            return false;
        }

        if(!$this->object->members_obj->assignMembers($a_user_ids))
        {
            ilUtil::sendFailure($this->lng->txt("rep_robj_xeph_members_already_assigned"));
            return false;
        }
        else
        {
            ilUtil::sendSuccess($this->lng->txt("rep_robj_xeph_members_assigned"),true);
        }

        $this->ctrl->redirect($this, "showSubmissions");
        return true;
    }

    /**
     * Show participants
     */
    function showParticipants()
    {
        global $ilCtrl, $tpl, $ilTabs, $ilToolbar;

        $ilTabs->activateTab("submissions");
        $this->addSubmissionSubTabs("participant");

        // participant selection
        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignment.php");
        $ass = ilEphAssignment::getAssignmentDataOfEphorus($this->object->getId());
        $members = $this->object->members_obj->getMembers();

        if (count($members) == 0)
        {
            ilUtil::sendInfo($this->txt("no_participants"));
            return;
        }

        $mems = array();
        foreach ($members as $mem_id)
        {
            if (ilObject::_lookupType($mem_id) == "usr")
            {
                include_once("./Services/User/classes/class.ilObjUser.php");
                $name = ilObjUser::_lookupName($mem_id);
                $mems[$mem_id] = $name;
            }
        }

        $mems = ilUtil::sortArray($mems, "lastname", "asc", false, true);

        if ($_GET["part_id"] == "" && count($mems) > 0)
        {
            $_GET["part_id"] = key($mems);
        }

        reset($mems);
        if (count($mems) > 1)
        {
            $options = array();
            foreach ($mems as $k => $m)
            {
                $options[$k] = $m["lastname"].", ".$m["firstname"]." [".$m["login"]."]";
            }
            include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
            $si = new ilSelectInputGUI($this->lng->txt(""), "part_id");
            $si->setOptions($options);
            $si->setValue($_GET["part_id"]);
            $ilToolbar->addInputItem($si);

            $ilToolbar->setFormAction($ilCtrl->getFormAction($this));
            $ilToolbar->addFormButton($this->lng->txt("rep_robj_xeph_select_part"), "selectParticipant");
        }

        if (count($mems) > 0)
        {
            include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphParticipantTableGUI.php");
            $part_tab = new ilEphParticipantTableGUI($this, "showParticipants",
                $this->object, $_GET["part_id"]);
            $tpl->setContent($part_tab->getHTML());
        }
        else
        {
            ilUtil::sendInfo($this->lng->txt("rep_robj_xeph_no_assignments_available"));
        }
    }

    /**
     * Select participant
     */
    function selectParticipant()
    {
        global $ilTabs;

        $ilTabs->activateTab("submissions");

        $_GET["part_id"] = ilUtil::stripSlashes($_POST["part_id"]);
        $this->showParticipants();
    }

    /**
     * Save assignment status (participant view)
     */
    function saveStatusParticipant()
    {
        $this->saveStatus(true);
    }

    function downloadSubmittedFile()
    {
        ilEphAssignment::downloadSingleFile($this->object->getId(), $this->ass->getId(), $_GET["part_id"], $_GET['filename'], $_GET['filetitle']);
        exit;
    }

    /**
     * Download all delivered files
     */
    function downloadAllDeliveredFiles()
    {
        global $ilCtrl;

        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignment.php");
        $mem_obj = new ilEphorusMembers($this->object);
        $members = array_flip($mem_obj->getMembers());

        $ilEphAssignment = new ilEphAssignment();
        $ilEphAssignment->downloadAllDeliveredFiles($this->object->getId(), $this->ass->getId(), $members);
        exit();
    }

    /**
     * Show grades
     */
    function showGradesOverview()
    {
        global $tree, $tpl, $ilToolbar, $ilCtrl, $ilTabs, $lng;

        $ilTabs->activateTab("submissions");
        $this->addSubmissionSubTabs("grades");

        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignment.php");
        $mem_obj = new ilEphorusMembers($this->object);
        $members = $mem_obj->getMembers();

        if (count($members) > 0)
        {
            foreach($members as $mem)
            {
                $this->object->updateUserStatus($mem);
            }

            $ilToolbar->addButton($lng->txt("rep_robj_xeph_export_excel"),
                $ilCtrl->getLinkTarget($this, "exportExcel"));
        }

        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphGradesTableGUI.php");
        $grades_tab = new ilExGradesTableGUI($this, "showGradesOverview", $this->object, $mem_obj);
        $tpl->setContent($grades_tab->getHTML());
    }

    /**
     * Export as excel
     */
    function exportExcel()
    {
        $this->object->exportGradesExcel();
        exit;
    }

    /**
     * Save grades
     */
    function saveGrades()
    {
        global $ilCtrl, $lng;

        include_once 'Services/Tracking/classes/class.ilLPMarks.php';

        if (is_array($_POST["lcomment"]))
        {
            foreach ($_POST["lcomment"] as $k => $v)
            {
                $marks_obj = new ilLPMarks($this->object->getId(), (int) $k);
                $marks_obj->setComment(ilUtil::stripSlashes($v));
                $marks_obj->setMark(ilUtil::stripSlashes($_POST["mark"][$k]));
                $marks_obj->update();
            }
        }
        ilUtil::sendSuccess($lng->txt("rep_robj_xeph_msg_saved_grades"), true);
        $ilCtrl->redirect($this, "showGradesOverview");
    }

    /**
     * adds tabs to tab gui object
     *
     * @param	object		$tabs_gui		ilTabsGUI object
     */
    function addSubmissionSubTabs($a_activate)
    {
        global $ilTabs, $lng, $ilCtrl;

        $ilTabs->addSubTab("submissions", $this->txt("submission_view"),
            $ilCtrl->getLinkTarget($this, "showSubmissions"));
        $ilTabs->addSubTab("participant", $this->txt("participant_view"),
            $ilCtrl->getLinkTarget($this, "showParticipants"));
        $ilTabs->addSubTab("grades", $this->txt("grades_view"),
            $ilCtrl->getLinkTarget($this, "showGradesOverview"));
        $ilTabs->activateSubTab($a_activate);
    }

    function viewReport()
    {
        global $ilCtrl, $ilTabs, $lng, $tpl;

        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphorusReportTableGUI.php");
        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphorusReportGUI.php");

        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/include/class.DLEApi.php");
        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/include/class.EphorusApi.php");

        $document = DLEApi::getDocument($_GET['doc_id']);

        $mode = (isset($_GET["mode"])) ? $_GET["mode"] : "summary";

        $ephorus_report = new EphorusReport($_GET['doc_id'], $mode);

        $tpl->setTitle(sprintf($lng->txt("rep_robj_xeph_report_for assignment"), ilEphAssignment::lookupTitle($_GET["ass_id"])));
        $tpl->setDescription("");
        $tpl->addCss($this->object->plugin->getStyleSheetLocation("report.css"));

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "showSubmissions"));

        $eph_tab = new ilEphorusReportHeaderTableGUI($this, "viewReport", $document);
        $header = $eph_tab->getHTML()."<br />";
        $matches = "";

        $eph_content = new ilEphorusReportGUI($this);
        if($document->status == 1)
        {
            $pre_header = "<div class=\"small xephModeSelection\">";
            $ilCtrl->setParameter($this, "mode", "summary");
            $pre_header .= (($mode == "summary") ?
                "<span style=>Summary</span>" :
                "<a href=\"".$ilCtrl->getLinkTarget($this, "viewReport")."\">Summary</a>").
                " / ";
            $ilCtrl->setParameter($this, "mode", "detailed");
            $pre_header .= ($mode == "detailed") ?
                "<span>Detailed</span>" :
                "<a href=\"".$ilCtrl->getLinkTarget($this, "viewReport")."\">Detailed</a>";

            $header = $pre_header."</div>".$header;

            $ilCtrl->setParameter($this, "mode", $mode);

            $results = DLEApi::getResults($document->guid);

            if($mode == "detailed")
            {
                $js = "$('input[name=\"diff\"]').change(function() {
                    $(this).parents('form').append('<input type=\"hidden\" name=\"cmd[viewReport]\">');
                    $(this).parents('form').submit();
                 });";

                $tpl->addOnLoadCode($js);

                $result = isset($_POST['diff']) ? $_POST["diff"] : reset($results)->guid;

                $eph_matches = new ilEphorusReportMatchesDetailedTableGUI($this, "viewReport", $document, $ephorus_report->getHeader($result));
                $matches = $eph_matches->getHTML()."<br />";
                $content = $eph_content->getReport($ephorus_report->getReport(array(), $results[$result]->comparison));
            }
            else
            {
                $guids = isset($_POST['guids_use']) ? $_POST["guids_use"] : array_keys($results);

                $eph_matches = new ilEphorusReportMatchesTableGUI($this, "viewReport", $document, $ephorus_report->getHeader($guids));
                $matches = $eph_matches->getHTML()."<br />";
                $content = $eph_content->getReport($ephorus_report->getReport($guids));
            }
        }
        else
        {
            $content = $eph_content->getReport($ephorus_report->getReport());
        }
        $tpl->setContent($header.$matches.$content);
    }

    function changeVisibility()
    {
        global $ilCtrl, $lng;

        include_once(dirname(dirname(__FILE__)).'/include/class.EphorusApi.php');

        $document = DLEApi::getDocument($_GET['doc_id']);
        $index = ($document->visibility_index == 1) ? 2 : 1;

        $ephorus_service = new EphorusService();

        if($ephorus_service->visibilityService($_GET['doc_id'], $index))
        {
            // The service worked well, getting the result.
            ilUtil::sendSuccess($lng->txt("rep_robj_xeph_msg_change_index"), true);
            $ilCtrl->redirect($this, "showSubmissions");
        }
        else
        {
            ilUtil::sendFailure($lng->txt("rep_robj_xeph_msg_no_change_index"), true);
            $ilCtrl->redirect($this, "showSubmissions");
        }
    }
}
?>