<?php

namespace App\ContohBootcamp\Services;

use App\ContohBootcamp\Repositories\TaskRepository;
use MongoDB\Operation\Delete;

class TaskService
{
	private TaskRepository $taskRepository;

	public function __construct()
	{
		$this->taskRepository = new TaskRepository();
	}

	/**
	 * NOTE: untuk mengambil semua tasks di collection task
	 */
	public function getTasks()
	{
		$tasks = $this->taskRepository->getAll();
		return $tasks;
	}

	/**
	 * NOTE: menambahkan task
	 */
	public function addTask(array $data)
	{
		$taskId = $this->taskRepository->create($data);
		return $taskId;
	}

	/**
	 * NOTE: UNTUK mengambil data task
	 */
	public function getById(string $taskId)
	{
		$task = $this->taskRepository->getById($taskId);
		return $task;
	}

	/**
	 * NOTE: untuk update task
	 */
	public function updateTask(array $editTask, array $formData)
	{
		if (isset($formData['title'])) {
			$editTask['title'] = $formData['title'];
		}

		if (isset($formData['description'])) {
			$editTask['description'] = $formData['description'];
		}

		if (isset($formData['assigned'])) {
			$editTask['assigned'] = $formData['assigned'];
		}

		$id = $this->taskRepository->save($editTask);
		return $id;
	}

	/**
	 * NOTE: untuk delete task
	 */
	public function deleteTask(string $taskId)
	{
		$existTask = $this->getById($taskId);

		if (!$existTask) {
			return response()->json([
				"message" => "Task " . $taskId . " tidak ada"
			], 401);
		}

		$this->taskRepository->deleteById($taskId);
	}

	/**
	 * NOTE: untuk assignTask task
	 */
	public function assignTask(string $taskId, array $formData)
	{
		$existTask = $this->getById($taskId);

		if (!$existTask) {
			return response()->json([
				"message" => "Task " . $taskId . " tidak ada"
			], 401);
		}

		$this->updateTask($existTask, $formData);
	}

	public function unassignTask(string $taskId)
	{
		$existTask = $this->getById($taskId);

		if (!$existTask) {
			return response()->json([
				"message" => "Task " . $taskId . " tidak ada"
			], 401);
		}

		$existTask['assigned'] = null;
		$formData = $existTask;

		$this->updateTask($existTask, $formData);

		return $existTask;
	}

	public function createSubtask(string $taskId, array $data)
	{
		$existTask = $this->getById($taskId);

		if (!$existTask) {
			return response()->json([
				"message" => "Task " . $taskId . " tidak ada"
			], 401);
		}

		$subtasks = isset($existTask['subtasks']) ? $existTask['subtasks'] : [];
		$subtasks[] = [
			'_id' => (string) new \MongoDB\BSON\ObjectId(),
			'title' => $data['title'],
			'description' => $data['description'],
		];

		$existTask['subtasks'] = $subtasks;

		$this->taskRepository->save($existTask);
	}

	public function deleteSubTask(string $taskId, string $subtaskId)
	{
		$existTask = $this->taskRepository->getById($taskId);
		if(!$existTask)
		{
			return response()->json([
				"message"=> "Task ".$taskId." tidak ada"
			], 401);
		}

		$subtasks = isset($existTask['subtasks']) ? $existTask['subtasks'] : [];

		// Pencarian dan penghapusan subtask
		$subtasks = array_filter($subtasks, function($subtask) use($subtaskId) {
			if($subtask['_id'] == $subtaskId)
			{
				return false;
			} else {
				return true;
			}
		});
		$subtasks = array_values($subtasks);
		$existTask['subtasks'] = $subtasks;

		$this->taskRepository->save($existTask);
	}
}
