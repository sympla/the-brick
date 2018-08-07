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

    public function setWithAppends($withAppends)
    {
        self::$withAppends = $withAppends;
    }

    public function addCustomAppends($customAppends)
    {
        $this->setWithAppends(true);
        array_push(self::$customAppends, $customAppends);
    }
}
