<?php

namespace App;

use Base\Components\OrderedList\ListItem;

class Task extends ListItem implements \Serializable
{
    public const WAITING = 'waiting';
    public const IN_PROGRESS = 'in_progress';
    public const DONE = 'done';
    public const FAILED = 'failed';
    public const OLD = 'old';

    /** @var string */
    protected $status;

    /** @var string */
    protected $description;

    /**
     * Task constructor.
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->status = self::WAITING;
        $this->description = 'Task description...';
        parent::__construct([]);
    }

    /**
     * @param string|null $status
     * @return Task
     */
    public function setStatus(?string $status = ''): Task
    {
        $status = $status ?? self::WAITING;
        $allowedStates = [
            self::OLD,
            self::FAILED,
            self::DONE,
            self::IN_PROGRESS,
            self::WAITING,
        ];
        if (!in_array($status, $allowedStates, true)) {
            throw new \Error('Wrong Task state.');
        }
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status ?: self::WAITING;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description ?: '';
    }

    /**
     * @param string $content
     * @return Task
     */
    public function setDescription(?string $content = '')
    {
        $this->description = $content;
        return $this;
    }

    /**
     * String representation of object
     * @link https://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return serialize([
            'title' => $this->text,
            'status' => $this->status,
            'description' => $this->description,
        ]);
    }

    /**
     * Constructs the object
     * @link https://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->text = $data['title'] ?? '';
        $this->status = $data['status'] ?? self::WAITING;
        $this->description = $data['description'] ?? '';
    }
}