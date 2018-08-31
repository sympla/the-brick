<?php

namespace Sympla\Search\Model;

use Illuminate\Database\Eloquent\Model as IlluminateModel;

class Model extends IlluminateModel
{
    /**
     * Model class
     * @var string
     */
    protected $class = null;

    /**
     * Should appends $customAppends
     *
     * @var boolean
     */
    protected static $customAppends = [];

    /**
     * Should appends $withAppends
     *
     * @var boolean
     */
    protected static $withAppends = [];

    /**
     * __construct
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->class = $this->getClass();
        parent::__construct($attributes);

        if (empty(self::$customAppends[$this->class])) {
            self::$customAppends[$this->class] = [];
        }
        if (empty(self::$withAppends[$this->class])) {
            self::$withAppends[$this->class] = [];
        }
    }

    /**
     * Check appends
     *
     * @return array appends
     */
    protected function getArrayableAppends()
    {
        if (self::$withAppends[$this->class]) {
            return self::$customAppends[$this->class];
        }
        return [];
    }

    /**
     * Set withAppends
     * @param bool $withAppends
     */
    protected function setWithAppends(bool $withAppends)
    {
        self::$withAppends[$this->class] = $withAppends;
    }

    /**
     * Set new custom appends
     * @param string $customAppends
     */
    protected function setCustomAppends(string $customAppends)
    {
        $this->setWithAppends(true);
        array_push(self::$customAppends[$this->class], $customAppends);
    }

    /**
     * add new custom appends
     * @param string $customAppends
     */
    public function addCustomAppends(string $customAppends)
    {
        $this->setCustomAppends($customAppends);
    }

    /**
     * add with appends
     * @param bool $withAppends
     */
    public function addWithAppends(bool $withAppends)
    {
        $this->setCustomAppends($customAppends);
    }

    /**
     * Get model class
     * @return string
     */
    private function getClass()
    {
        return get_class($this);
    }
}
