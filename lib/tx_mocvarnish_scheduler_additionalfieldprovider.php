<?php
class tx_mocvarnish_scheduler_additionalfieldprovider implements tx_scheduler_AdditionalFieldProvider {

	/**
	 * This method is used to define new fields for adding or editing a task
	 * In this case, it adds an sleep time field
	 *
	 * @param array $taskInfo: reference to the array containing the info used in the add/edit form
	 * @param object $task: when editing, reference to the current task object. Null when adding.
	 * @param tx_scheduler_Module $parentObject: reference to the calling object (Scheduler's BE module)
	 * @return array Array containg all the information pertaining to the additional fields
	 *               The array is multidimensional, keyed to the task class name and each field's id
	 *               For each field it provides an associative sub-array with the following:
	 *               ['code']		=> The HTML code for the field
	 *               ['label']		=> The label of the field (possibly localized)
	 *               ['cshKey']		=> The CSH key for the field
	 *               ['cshLabel']	=> The code of the CSH label
	 */
	public function getAdditionalFields(array &$taskInfo, $task, tx_scheduler_Module $parentObject) {
			// Initialize extra field value
		if (empty($taskInfo['limit'])) {
			if ($parentObject->CMD == 'add') {
					// In case of new task and if field is empty, set default sleep time
				$taskInfo['limit'] = 10000;
			} else if ($parentObject->CMD == 'edit') {
				$taskInfo['limit'] = $task->limit;
			} else {
					// Otherwise set an empty value, as it will not be used anyway
				$taskInfo['limit'] = '';
			}
		}

			// Write the code for the field
		$fieldID = 'task_orderExport';
		$fieldCode = '<input type="text" name="tx_scheduler[limit]" id="' . $fieldID . '" value="' . intval($taskInfo['limit']) . '" />';
		$additionalFields = array();
		$additionalFields[$fieldID] = array(
			'code' => $fieldCode,
			'label' => 'Limit of purge requests per run'
		);

		return $additionalFields;
	}

	/**
	 * This method checks any additional data that is relevant to the specific task
	 * If the task class is not relevant, the method is expected to return true
	 *
	 * @param array $submittedData: reference to the array containing the data submitted by the user
	 * @param tx_scheduler_Module $parentObject: reference to the calling object (Scheduler's BE module)
	 * @return boolean True if validation was ok (or selected class is not relevant), false otherwise
	 */
	public function validateAdditionalFields(array &$submittedData, tx_scheduler_Module $parentObject) {
		$limit = intval($submittedData['limit']);
		if ($limit < 1) {
			$parentObject->addMessage(
				sprintf(
					$GLOBALS['LANG']->sL('Limit has to be more than zero'),
					'<strong>' . $limit . '</strong>'
				),
				t3lib_FlashMessage::INFO
			);
		}
		return TRUE;
	}

	/**
	 * This method is used to save any additional input into the current task object
	 * if the task class matches
	 *
	 * @param array $submittedData: array containing the data submitted by the user
	 * @param tx_scheduler_Task $task: reference to the current task object
	 * @return void
	 */
	public function saveAdditionalFields(array $submittedData, tx_scheduler_Task $task) {
		$task->limit = $submittedData['limit'];
	}

}
?>