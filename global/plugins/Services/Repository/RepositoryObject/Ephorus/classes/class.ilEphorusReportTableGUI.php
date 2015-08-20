<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Assignments table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesExercise
 */
class ilEphorusReportHeaderTableGUI extends ilTable2GUI
{
    function ilEphorusReportHeaderTableGUI($a_parent_obj, $a_parent_cmd, $document)
    {
        global $ilCtrl, $lng;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTopCommands(false);
        $this->addColumn($lng->txt("rep_robj_xeph_document_info"));
        $this->addColumn("");
        $this->setEnableHeader(true);
        $this->setRowTemplate("tpl.eph_report_header.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus");
        $this->disable("footer");

        $member = ilObjUser::_lookupName($document->user_id);

        $a_data = array();

        $student = array();
        $student["key"] = "student";
        $student["value"] = $member["firstname"]." ".$member["lastname"];
        array_push($a_data, $student);

        $ilCtrl->setParameter($this->parent_obj, "filename", $document->filename);
        $ilCtrl->setParameter($this->parent_obj, "filetitle", $document->filetitle);
        $ilCtrl->setParameter($this->parent_obj, "part_id", $document->user_id);

        $doc = array();
        $doc["key"] = "document";
        $doc["value"] = "<a href=\"".$ilCtrl->getLinkTarget($this->parent_obj, "downloadSubmittedFile")."\">".$document->filetitle."</a>";
        array_push($a_data, $doc);

        $ilCtrl->setParameter($this->parent_obj, "filename", "");
        $ilCtrl->setParameter($this->parent_obj, "filetitle", "");

        $submission_date = array();
        $submission_date["key"] = "submission_date";
        $submission_date["value"] = ilDatePresentation::formatDate(new ilDateTime($document->date_created, IL_CAL_DATETIME));
        array_push($a_data, $submission_date);

        $this->setData($a_data);
    }

    protected function fillRow($a_data)
    {
	    global $lng;

        $this->tpl->setVariable("KEY", $lng->txt("rep_robj_xeph_".$a_data["key"]));
        $this->tpl->setVariable("VALUE",  $a_data["value"]);

        $this->tpl->parseCurrentBlock();
    }
}


class ilEphorusReportMatchesTableGUI extends ilTable2GUI
{
    function ilEphorusReportMatchesTableGUI($a_parent_obj, $a_parent_cmd, $document, $report)
    {
        global $ilCtrl, $lng;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTopCommands(false);
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->addColumn($document->percentage. "% ");
        $this->addColumn("", "", "1", true);
        $this->addColumn($lng->txt("rep_robj_xeph_total_score"));
        $this->setLimit(9999);
        $this->setRowTemplate("tpl.eph_report_matches.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus");
        $this->disable("footer");

        $this->addCommandButton("viewReport", $lng->txt("rep_robj_xeph_update_selection"));

        $this->setData($report);
    }

    protected function fillRow($a_data)
    {
        $this->tpl->setVariable("PERCENTAGE", $a_data["percentage"]."%");
        $this->tpl->setVariable("VAL_CHKBOX", ilUtil::formCheckbox((int) $a_data['input']['checked'], $a_data['input']['name'], $a_data["input"]["value"]));
        $this->tpl->setVariable("SOURCE", ($a_data["source"]["link"]) ?
	        "<a href=\"".$a_data["source"]["link"]."\" target=\"_blank\">".$a_data["source"]["title"]."</a>" :
		    "<span>".$a_data["source"]["title"]."</span>"
        );

        $this->tpl->parseCurrentBlock();
    }
}

class ilEphorusReportMatchesDetailedTableGUI extends ilTable2GUI
{
    function ilEphorusReportMatchesDetailedTableGUI($a_parent_obj, $a_parent_cmd, $document, $report)
    {
        global $ilCtrl, $lng;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTopCommands(false);
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->addColumn($document->percentage. "% ");
        $this->addColumn("", "", "1", true);
        $this->addColumn($lng->txt("rep_robj_xeph_total_score"));
        $this->setLimit(9999);
        $this->setRowTemplate("tpl.eph_report_matches.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus");
        $this->disable("footer");

        $this->setData($report);
    }

    protected function fillRow($a_data)
    {
        $this->tpl->setVariable("PERCENTAGE", $a_data["percentage"]."%");
        $this->tpl->setVariable("VAL_CHKBOX", ilUtil::formRadioButton((int) $a_data['input']['checked'], $a_data['input']['name'], $a_data["input"]["value"]));
	    $this->tpl->setVariable("SOURCE", ($a_data["source"]["link"]) ?
			    "<a href=\"".$a_data["source"]["link"]."\" target=\"_blank\">".$a_data["source"]["title"]."</a>" :
			    "<span>".$a_data["source"]["title"]."</span>"
	    );

        $this->tpl->parseCurrentBlock();
    }
}