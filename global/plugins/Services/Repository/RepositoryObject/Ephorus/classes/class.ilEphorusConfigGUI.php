<?php

include_once("./Services/Component/classes/class.ilPluginConfigGUI.php");
 
/**
 * Example configuration user interface class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 */
class ilEphorusConfigGUI extends ilPluginConfigGUI
{
	/**
	* Handles all commmands, default is "configure"
	*/
	function performCommand($cmd)
	{
		switch ($cmd)
		{
			case "configure":
			case "save":
			case "testConnection":
				$this->$cmd();
				break;
		}
	}

	/**
	 * Configure screen
	 */
	function configure()
	{
		global $tpl, $ilDB;

		$config = array();
		$settings = $ilDB->query("SELECT keyword, value FROM settings ".
				" WHERE module = ".$ilDB->quote("rep_robj_xeph", "text")
		);
		while ($record = $ilDB->fetchAssoc($settings))
		{
			$config[$record["keyword"]] = $record["value"];
		}

		$form = $this->initConfigurationForm();
		$form->setValuesByArray($config);
		$tpl->setContent($form->getHTML());
	}

	/**
	 * Init configuration form.
	 *
	 * @return object form object
	 */
	public function initConfigurationForm()
	{
		global $lng, $ilCtrl;

		$pl = $this->getPluginObject();

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();

		// Setting for the ephorus logging
		$ephorus_logging = new ilCheckboxInputGUI($pl->txt("ephorus_logging"), "ephorus_logging");
		$ephorus_logging->setValue(1);
		$ephorus_logging->setInfo($pl->txt("ephorus_logging_description"));
		$form->addItem($ephorus_logging);

		// Setting for the hand-in code
		$handin_code = new ilTextInputGUI($pl->txt("handin_code"), "handin_code");
		$handin_code->setRequired(true);
		$form->addItem($handin_code);

		// Setting for the hand-in address
		$handin_address = new ilTextInputGUI($pl->txt("handin_address"), "handin_address");
		$handin_address->setSize(80);
		$handin_address->setRequired(true);
		$form->addItem($handin_address);

		// Setting for the index address
		$index_address = new ilTextInputGUI($pl->txt("index_address"), "index_address");
		$index_address->setSize(80);
		$index_address->setRequired(true);
		$form->addItem($index_address);

		// Setting for the processtype
		$processtype = new ilSelectInputGUI($pl->txt("default_processtype"), "processtype");
		$processtype->setOptions(array(1 => $pl->txt("default"), 3 => $pl->txt("private")));
		$processtype->setInfo($pl->txt("default_processtype_description"));
		$form->addItem($processtype);

		// Setting for the disclosure
		$disclosure = new ilTextAreaInputGUI($pl->txt("disclosure"), "disclosure");
		$disclosure->setCols(79);
		$disclosure->setRows(4);
		$form->addItem($disclosure);

		$form->addCommandButton("save", $lng->txt("save")." / ".$pl->txt("check_connection"));

		$form->setTitle($pl->txt("ephorus_plugin_configuration"));
		$form->setFormAction($ilCtrl->getFormAction($this));

		return $form;
	}

	/**
	 * Save form input (currently does not save anything to db)
	 *
	 */
	public function save()
	{
		global $tpl, $lng, $ilCtrl, $ilDB;
		
		$pl = $this->getPluginObject();
		
		$form = $this->initConfigurationForm();
		if ($form->checkInput())
		{
			// Get submitted data
			$ephorus_logging = $form->getInput("ephorus_logging");
			$handin_code = $form->getInput("handin_code");
			$handin_address = $form->getInput("handin_address");
			$index_address = $form->getInput("index_address");
			$processtype = $form->getInput("processtype");
			$disclosure = $form->getInput("disclosure");

			// Saving to the database.
			$ilDB->manipulate($up = "UPDATE settings SET".
					" value = ".$ilDB->quote($ephorus_logging, "integer").
					" WHERE module = ".$ilDB->quote("rep_robj_xeph", "text").
					" AND keyword = ".$ilDB->quote("ephorus_logging", "text")
			);

			$ilDB->manipulate($up = "UPDATE settings SET".
					" value = ".$ilDB->quote($handin_code, "text").
					" WHERE module = ".$ilDB->quote("rep_robj_xeph", "text").
					" AND keyword = ".$ilDB->quote("handin_code", "text")
			);

			$ilDB->manipulate($up = "UPDATE settings SET".
					" value = ".$ilDB->quote($handin_address, "text").
					" WHERE module = ".$ilDB->quote("rep_robj_xeph", "text").
					" AND keyword = ".$ilDB->quote("handin_address", "text")
			);

			$ilDB->manipulate($up = "UPDATE settings SET".
					" value = ".$ilDB->quote($index_address, "text").
					" WHERE module = ".$ilDB->quote("rep_robj_xeph", "text").
					" AND keyword = ".$ilDB->quote("index_address", "text")
			);

			$ilDB->manipulate($up = "UPDATE settings SET".
					" value = ".$ilDB->quote($processtype, "integer").
					" WHERE module = ".$ilDB->quote("rep_robj_xeph", "text").
					" AND keyword = ".$ilDB->quote("processtype", "text")
			);

			$ilDB->manipulate($up = "UPDATE settings SET".
					" value = ".$ilDB->quote($disclosure, "text").
					" WHERE module = ".$ilDB->quote("rep_robj_xeph", "text").
					" AND keyword = ".$ilDB->quote("disclosure", "text")
			);

			ilUtil::sendInfo($pl->txt("saving_invoked"), true);

			include ("Customizing/global/plugins/Services/Repository/RepositoryObject/Ephorus/include/class.EphorusApi.php");

			$status = new EphorusStatus();
			$connection = $status->connectivityTest();
			if($connection["handin"] && $connection["index"]) {
				ilUtil::sendSuccess($pl->txt("handin_index_okay"), true);
			} elseif($connection["handin"]) {
				ilUtil::sendSuccess($pl->txt("handin_okay"), true);
				ilUtil::sendFailure($pl->txt("index_not_okay"), true);
			} elseif($connection["index"]) {
				ilUtil::sendSuccess($pl->txt("index_okay"), true);
				ilUtil::sendFailure($pl->txt("handin_not_okay"), true);
			} else {
				ilUtil::sendFailure($pl->txt("handin_index_not_okay"), true);
			}
			$ilCtrl->redirect($this, "configure");
		}
		else
		{
			$form->setValuesByPost();
			$tpl->setContent($form->getHtml());
		}
	}
}
?>