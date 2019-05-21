<?php

namespace App;

use Base\Application;
use Base\Input;
use Base\ListItem;
use Base\OrderedList;
use Base\TextArea;
use Base\BaseController;

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


    /**
     * TaskController constructor.
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $view = $app->view();
        $this->taskList = $view->component('task-list');
        $this->taskDescription = $view->component('task.description');
        $this->taskStatus = $view->component('task.status');
        $this->taskTitle = $view->component('task.title');
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
        $home = getenv('HOME');
        if (is_dir("$home/.config/starlight") && file_exists("$home/.config/starlight/{$this->username}-tasks.ser")) {
            $serializedData = file_get_contents("$home/.config/starlight/{$this->username}-tasks.ser");
            $list->setItems(unserialize($serializedData) ?? []);
        }
        $this->app->switchTo('main');
        $this->app->focusOn($this->taskList);
    }

    public function save()
    {
        $task = $this->taskList->getSelectedItem();
        if ($task) {
            $this->updateTask($task);
        }
        $home = getenv('HOME');
        $this->createDir("$home/.config")
            ->createDir("$home/.config/starlight");
        file_put_contents("$home/.config/starlight/{$this->username}-tasks.ser", serialize($this->taskList->getItems()));
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

    public function confirmDelete(): void
    {
        $this->app->switchTo('popup');
    }

    public function closePopUp(): void
    {
        $this->app->switchTo('main');
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

    /**
     * @param string $configFolder
     * @return TaskController
     */
    protected function createDir(string $configFolder)
    {
        if (!is_dir($configFolder) && !mkdir($configFolder) && !is_dir($configFolder)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $configFolder));
        }
        return $this;
    }
}