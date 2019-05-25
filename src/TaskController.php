<?php

namespace App;

use Base\Application;
use Base\DrawableInterface;
use Base\Input;
use Base\ListItem;
use Base\OrderedList;
use Base\Template;
use Base\TextArea;
use Base\BaseController;
use Base\Workspace;

class TaskController extends BaseController
{
    /* components */
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
     * @param Application $app
     * @param Workspace $workspace
     */
    public function __construct(Application $app, Workspace $workspace)
    {
        parent::__construct($app, $workspace);

        $render = $app->render();

        // templates
        $mainView = $render->template('main');
        $this->loginView = $render->template('login-popup');

        // components
        $this->taskList = $mainView->component('task-list');
        $this->taskDescription = $mainView->component('task-description');
        $this->taskStatus = $mainView->component('task-status');
        $this->taskTitle = $mainView->component('task-title');
    }

    /**
     * @param OrderedList $list
     */
    public function taskStatuses(OrderedList $list): void
    {
        $list->addItems(
            new ListItem(Task::WAITING, 'Waiting'),
            new ListItem(Task::OLD, 'Old'),
            new ListItem(Task::FAILED, 'Failed'),
            new ListItem(Task::DONE, 'Done'),
            new ListItem(Task::IN_PROGRESS, 'In progress')
        );
    }


    public function load(): void
    {
        $list = $this->taskList;
        /** @var Input $usernameInput */
        $usernameInput = $this->loginView->component('login-username');

        $this->username = $usernameInput->getText();

        $this->loginView->component('login-validation-username')->visibility(false);
        if (empty($this->username)) {
            $this->loginView->component('login-validation-username')->visibility(true);
            return;
        }

        $tasks = $this->workspace->fromFile("{$this->username}-tasks.ser");
        $list->setItems($tasks ?? []);

        $this->switchTo('main');
        $this->focusOn($this->taskList);
    }

    public function save(): void
    {
        $task = $this->taskList->getSelectedItem();
        if ($task) {
            $this->updateTask($task);
        }
        $this->workspace->toFile("{$this->username}-tasks.ser", $this->taskList->getItems());
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
            ->addItems($newTask)
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
        $view = 'main';
        $this->switchTo($view);
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