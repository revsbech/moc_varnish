<?php
namespace MOC\MocVarnish\Scheduler;

/**
 * Provide additional fields for the MOC Varnish purge event queue scheduler task.
 *
 * Will add a new limit field on the task.
 *
 * @package MOC\MocVarnish
 */
class HandlePurgeEventsAdditionalFieldProvider implements \TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface {

	/**
	 * This method is used to define new fields for adding or editing a task
	 * In this case, it adds an sleep time field
	 *
	 * @param array $taskInfo reference to the array containing the info used in the add/edit form
	 * @param object $task when editing, reference to the current task object. Null when adding.
	 * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject reference to the calling object (Scheduler's BE module)
	 * @return array
	 */
	public function getAdditionalFields(array $taskInfo, $task, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject) {

			// Initialize extra field value
		if (empty($taskInfo['limit'])) {
			if ($parentObject->CMD == 'add') {
					// In case of new task and if field is empty, set default sleep time
				$taskInfo['limit'] = 10000;
			} elseif ($parentObject->CMD == 'edit') {
				$taskInfo['limit'] = $task->limit;
			} else {
					// Otherwise set an empty value, as it will not be used anyway
				$taskInfo['limit'] = '';
			}
		}

			// Write the code for the field
		$fieldIdentifier = 'task_orderExport';
		$fieldCode = '<input type="text" name="tx_scheduler[limit]" id="' . $fieldIdentifier . '" value="' . intval($taskInfo['limit']) . '" />';
		$additionalFields = array();
		$additionalFields[$fieldIdentifier] = array(
			'code' => $fieldCode,
			'label' => 'Limit of purge requests per run'
		);

		return $additionalFields;
	}

	/**
	 * This method checks any additional data that is relevant to the specific task
	 * If the task class is not relevant, the method is expected to return true
	 *
	 * @param array $submittedData reference to the array containing the data submitted by the user
	 * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject reference to the calling object (Scheduler's BE module)
	 * @return boolean True if validation was ok (or selected class is not relevant), false otherwise
	 */
	public function validateAdditionalFields(array $submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject) {
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
	 * @param array $submittedData array containing the data submitted by the user
	 * @param \TYPO3\CMS\Scheduler\Task\AbstractTask $task reference to the current task object
	 * @return void
	 */
	public function saveAdditionalFields(array $submittedData,  \TYPO3\CMS\Scheduler\Task\AbstractTask $task) {
		$task->limit = $submittedData['limit'];
	}

}