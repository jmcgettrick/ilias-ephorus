<?php

include_once "./Services/Repository/classes/class.ilObjectPluginListGUI.php";

/**
* ListGUI implementation for Example object plugin. This one
* handles the presentation in container items (categories, courses, ...)
* together with the corresponfing ...Access class.
*
* PLEASE do not create instances of larger classes here. Use the
* ...Access class to get DB data and keep it small.
*
* @author Alex Killing <alex.killing@gmx.de>
*/
class ilObjEphorusListGUI extends ilObjectPluginListGUI
{
	
	/**
	* Init type
	*/
	function initType()
	{
		$this->setType("xeph");
	}
	
	/**
	* Get name of gui class handling the commands
	*/
	function getGuiClass()
	{
		return "ilObjEphorusGUI";
	}

    /**
     * Get commands
     */
    function initCommands()
    {
        return array
        (
            array(
                "permission" => "read",
                "cmd" => "showOverview",
                "default" => true),
            array(
                "permission" => "write",
                "cmd" => "settings",
                "txt" => $this->txt("edit"),
                "default" => false),
        );
    }

    /**
     * Get item properties
     *
     * @return	array		array of property arrays:
     *						"alert" (boolean) => display as an alert property (usually in red)
     *						"property" (string) => property name
     *						"value" (string) => property value
     */
    function getProperties()
    {
        global $lng;
        
        $props = array();
        $this->plugin->includeClass("class.ilObjEphorusAccess.php");
        $rem = ilObjEphorusAccess::_lookupRemainingWorkingTimeString($this->obj_id);
        if ($rem != "")
        {
            $props[] = array(
                "property" => $lng->txt("exc_next_deadline"),
                "value" => ilObjEphorusAccess::_lookupRemainingWorkingTimeString($this->obj_id)
            );
        }
        return $props;
    }
}
?>