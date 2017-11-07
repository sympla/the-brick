<?php

namespace Sympla\Search\Search;

use Request;
use Schema;

class Search {

    var $fields = [];

    public function __construct()
    {
        $this->request = Request::all();
        if (Request::exists('fields')) {
            $this->parseFields($this->request['fields']);            
        }
    }

    public function parseFields($fields, $relation = null) {
     
        // (do the required processing...)
        $temp = explode($relation ? ',' : ',' , $fields, 2);
     
        if (count($temp) === 0) {
            // end the recursion
            return;
        } else {
            if (strpos($temp[0], '(') === false) {
                if (is_null($relation)) {
                    $this->fields[] = $temp[0];
                } else {
                    $this->fields[$relation][] = str_replace(')', '', $temp[0]);
                }
            } else {
                $temp = explode('(', $fields, 2);
                // continue the recursion
                return $this->parseFields($temp[1], $temp[0]);
            }

            if (isset($temp[1])) {
                // continue the recursion
                return $this->parseFields($temp[1], $relation);
            } else {
                // end the recursion
                return;
            }
        }
    }

    public function negotiate($model)
    {
        $modelPath = '\App\\';
        $model = $modelPath.$model;
        $model = new $model;

        $this->classModel = $model;
        $this->table = $model->getTable();  
        
        if (!is_null($this->fields)) {
            foreach ($this->fields as $key => $value) {
                if (is_array($value)) {
                    if (method_exists($this->classModel, $key)) {      
                        $model = $model->with([
                            $key => function ($query) use ($value) {
                                $_relation = $query->getRelated()->getTable();
                                foreach ($value as $key2 => $value2) {
                                    if (Schema::hasColumn($_relation, $value2)) {
                                        $query->addSelect($value2);
                                    }
                                }
                                
                            }
                        ]);
                    }
                } else {
                    if (Schema::hasColumn($this->table, $value)) {
                        $model = $model->addSelect($value);
                    }
                }
            }
            return $model;
        } else {
            return $model;
        }
        return $model;
    }
}