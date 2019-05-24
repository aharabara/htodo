<?php

namespace App;

use Base\Application;
use Base\Input;
use Base\ListItem;
use Base\OrderedList;
use Base\TextArea;
use Base\BaseController;
use Base\Workspace;

class TaskController extends BaseController
{
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
    /** @var Application */
    protected $app;

    /** @var string */
    protected $username;
    
    /** @var Workspace */
    protected $workspace;


    /**
     * TaskController constructor.
     * @param Application $app
     * @param Workspace $workspace
     */
    public function __construct(Application $app, Workspace $workspace)
    {
        $this->app = $app;
        $view = $app->view();
        $this->taskList = $view->component('task-list');
        $this->taskDescription = $view->component('task.description');
        $this->taskStatus = $view->component('task.status');
        $this->taskTitle = $view->component('task.title');
        $this->workspace = $workspace;
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
        /** @var Input $usernameInput*/
        $usernameInput = $this->app->view()->component('login.username');
        $this->username = $usernameInput->getText();

        $this->app->view()->component('login.validation.username')->setVisibility(false);
        if (empty($this->username)){
            $this->app->view()->component('login.validation.username')->setVisibility(true);
            return;
        }

        $tasks = $this->workspace->fromFile("{$this->username}-tasks.ser");
        $list->setItems($tasks ?? []);
        
        $this->app->switchTo('main');
        $this->app->focusOn($this->taskList);
    }

    public function save()
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
        if($task){
            $this->updateTask($task);
        }
        $newTask = new Task('Task title');
        $newTask->setStatus(Task::WAITING);
        $this->taskList
            ->addItems($newTask)
            ->selectItem($newTask);

        $this->app->focusOn($this->taskTitle);
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
        $this->app->switchTo('main');
        $this->app->focusOn($this->taskList);
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