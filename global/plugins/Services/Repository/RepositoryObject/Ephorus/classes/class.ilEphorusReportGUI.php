<?php
/**
 * GUI clas for ephorus assignments
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup
 *
 */
class ilEphorusReportGUI
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
    function getReport($a_data)
    {
        global $lng, $ilCrtl, $ilUser;

        $tpl = new ilTemplate("tpl.eph_report_results.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus");
        $tpl->setVariable("CONTENT", $a_data);

        return $tpl->get();
    }
}