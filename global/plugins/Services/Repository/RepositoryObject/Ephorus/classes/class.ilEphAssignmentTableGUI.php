<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
//include_once("./Modules/Ephorus/classes/class.ilEphAssignment.php");

/**
 * Assignments table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesExercise
 */
class ilEphAssignmentTableGUI extends ilTable2GUI
{

    /**
     * Constructor
     */
    function ilEphAssignmentTableGUI($a_parent_obj, $a_parent_cmd, $a_eph)
    {
        global $ilCtrl, $lng, $ilAccess;

        $this->eph = $a_eph;

        $this->setId("ephass".$this->eph->getId());

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTitle($lng->txt("rep_robj_xeph_assignments"));
        $this->setTopCommands(true);

        // if you add pagination and disable the unlimited setting:
        // fix saving of ordering of single pages!
        $this->setLimit(9999);

        $this->addColumn("", "", "1", true);
        $this->addColumn($lng->txt("title"), "title");
        $this->addColumn($lng->txt("rep_robj_xeph_presentation_order"), "order_val");
        $this->addColumn($lng->txt("rep_robj_xeph_start_time"), "start_time");
        $this->addColumn($lng->txt("rep_robj_xeph_deadline"), "deadline");
        $this->addColumn($lng->txt("rep_robj_xeph_mandatory"), "mandatory");
        $this->addColumn($lng->txt("rep_robj_xeph_instruction"), "", "40%");
        $this->addColumn($lng->txt("actions"));

        $this->setDefaultOrderField("val_order");
        $this->setDefaultOrderDirection("asc");

        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.eph_assignments_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus");

        $this->setEnableTitle(true);
        $this->setSelectAllCheckbox("id");

        $this->addMultiCommand("confirmDeleteAssignment", $lng->txt("delete"));

        $this->addCommandButton("orderAssignmentsByDeadline", $lng->txt("rep_robj_xeph_order_by_deadline"));
        $this->addCommandButton("saveAssignmentsOrder", $lng->txt("rep_robj_xeph_save_order"));

        include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/classes/class.ilEphAssignment.php");
        $data = ilEphAssignment::getAssignmentDataOfEphorus($this->eph->getId());
        $this->setData($data);
    }

    /**
     * Fill table row
     */
    protected function fillRow($d)
    {
        global $lng, $ilCtrl;

        $this->tpl->setVariable("ID", $d["id"]);
        $this->tpl->setVariable("TXT_DEADLINE",
            ilDatePresentation::formatDate(new ilDateTime($d["deadline"],IL_CAL_UNIX)));
        if ($d["start_time"] > 0)
        {
            $this->tpl->setVariable("TXT_START_TIME",
                ilDatePresentation::formatDate(new ilDateTime($d["start_time"],IL_CAL_UNIX)));
        }
        $this->tpl->setVariable("TXT_INSTRUCTIONS",
            ilUtil::shortenText($d["instruction"], 200, true));

        if ($d["mandatory"])
        {
            $this->tpl->setVariable("TXT_MANDATORY", $lng->txt("yes"));
        }
        else
        {
            $this->tpl->setVariable("TXT_MANDATORY", $lng->txt("no"));
        }

        $this->tpl->setVariable("TXT_TITLE", $d["title"]);
        $this->tpl->setVariable("ORDER_VAL", $d["order_val"]);

        $this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
        $ilCtrl->setParameter($this->parent_obj, "ass_id", $d["id"]);
        $this->tpl->setVariable("CMD_EDIT",
            $ilCtrl->getLinkTarget($this->parent_obj, "editAssignment"));
    }

}
?>