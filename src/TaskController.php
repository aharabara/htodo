<?php

namespace App;

use Base\Application;
use Base\Components\Input;
use Base\Components\Label;
use Base\Components\OrderedList\OrderedList;
use Base\Components\TextArea;
use Base\Core\BaseController;
use Base\Core\Template;
use Base\Core\Workspace;

class TaskController extends BaseController
{
    /* components */
    /** @var Label|null */
    public $usernameInputFailed;

    /** @var OrderedList */
    protected $taskList;
    /** @var Input */
    protected $usernameInput;
    /** @var OrderedList */
    protected $taskStatus;
    /** @var TextArea */
    protected $taskDescription;
    /** @var Input */
    protected $taskTitle;

    /* templates */
    /** @var Template */
    protected $loginView;

    /* useful data */
    /** @var string */
    protected $username;


    /**
     * TaskController constructor.
     *
     * @param Application $app
     * @param Workspace   $workspace
     * @param Input       $usernameInput
     */
    public function __construct(Application $app, Workspace $workspace) {
        parent::__construct($app, $workspace);

        // components
        $this->usernameInputFailed = $app->findFirst('#login-validation-username', 'login-popup');
        $this->usernameInput       = $app->findFirst('[name=username]', 'login-popup');
        $this->taskList            = $app->findFirst('[name=task-list]', 'main');
        $this->taskDescription     = $app->findFirst('[name=task-description]', 'main');
        $this->taskStatus          = $app->findFirst('[name=task-status]', 'main');
        $this->taskTitle           = $app->findFirst('[name=task-title]', 'main');
    }

    public function load(): void
    {
        $list = $this->taskList;
        /** @var Input $usernameInput */

        $this->username = $this->usernameInput->getText();

        $this->switchTo('main');
        $this->usernameInputFailed->visibility(false);
        if (empty($this->username)) {
            $this->usernameInputFailed->visibility(true);

            return;
        }
        $tasks = $this->workspace->fromFile("{$this->username}-tasks.ser");
        if ($tasks) {
            foreach ($tasks as $task) {
                $list->addComponent($task);
            }
            $firstTask = reset($tasks);
            if ($firstTask) {
                $this->taskSelect($firstTask);
            }
        }

        $this->switchTo('main');
        $this->focusOn($this->taskList);
    }

    public function save(): void
    {
        $task = $this->taskList->getSelectedItem();
        if ($task) {
            $this->updateTask($task);
        }
        $this->workspace->toFile("{$this->username}-tasks.ser", $this->taskList->getComponents());
    }

    public function addItem(): void
    {
        $task = $this->taskList->getSelectedItem();
        if ($task) {
            $this->updateTask($task);
        }
        $newTask = new Task('Task title');
        $newTask->setStatus(Task::WAITING);
        $this->taskList
            ->addComponent($newTask)
            ->selectItem($newTask);

        $this->focusOn($this->taskTitle);
    }

    public function taskSelect(Task $task): void
    {
        $this->taskTitle->setText($task->getText());
        $this->taskDescription->setText($task->getDescription());
        $this->taskStatus->selectItemByValue($task->getStatus());
    }

    public function beforeTaskSelect(Task $task): void
    {
        $this->updateTask($task);
    }

    public function deleteTask(): void
    {
        $this->taskList->delete($this->taskList->getFocusedItem());
        $this->switchTo('main');
        $this->focusOn($this->taskList);
    }

    /**
     * @param Task $task
     */
    protected function updateTask(Task $task): void
    {
        $task->setText($this->taskTitle->getText());
        $task->setDescription($this->taskDescription->getText());
        $selectedStatus = $this->taskStatus->getSelectedItem();
        $task->setStatus($selectedStatus ? $selectedStatus->getValue() : Task::WAITING);
    }
}