<?php

namespace Sympla\Search\Model;

use Illuminate\Database\Eloquent\Model as IlluminateModel;

class Model extends IlluminateModel
{
    /**
     * Dynamic/custom attributes
     *
     * @var array
     */
    protected static $customAppends = [];

    /**
     * Should appends $customAppends
     *
     * @var boolean
     */
    protected static $withAppends = false;

    /**
     * Check appends
     *
     * @return array appends
     */
    protected function getArrayableAppends()
    {
        if (self::$withAppends) {
            return self::$customAppends;
        }
        return [];
    }

    /**
     * Set withAppends
     * @param bool $withAppends
     */
    protected function setWithAppends(bool $withAppends)
    {
        self::$withAppends = $withAppends;
    }

    /**
     * Set new custom appends
     * @param string $customAppends
     */
    protected function setCustomAppends(string $customAppends)
    {
        $this->setWithAppends(true);
        array_push(self::$customAppends, $customAppends);
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
}
