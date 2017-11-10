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
            // dd($this->fields);
        }
    }

    public function parseFields($fields) {
     
        // (do the required processing...)
        $temp = explode(',' , $fields, 2);

        if (count($temp) === 0) {
            // end the recursion
            return;
        } else {
            if (str_contains($fields, '(') && strpos($fields, ',') > strpos($fields, '(')) {
                $start = strpos($fields, '(');
                $end = strpos($fields, ')');
                $_relation = substr($fields, 0, $start); 
                $_fields = substr($fields, $start+1, $end-$start-1);
                $this->fields[$_relation] = $_fields;
                // continue the recursion
                return $this->parseFields(substr($fields, $end+2).',');
            } else if (isset($temp[1])) {
                if (!empty($temp[0])) {
                    $this->fields[] = $temp[0];
                }
                // continue the recursion
                return $this->parseFields($temp[1]);
            } else {
                // end the recursion
                return;
            }
        }
    }

    public function parseRelations($fields, $relations = [])
    {     
        foreach ($fields as $key => $value) {
            $relations[] = $key.(empty($value)?'':':'.strtoupper($value));
        }
        return $relations;
    }

    public function negotiateRelations($fields)
    {
        $this->model->with($this->parseRelations($fields));
        return $this;
    }

    public function negotiateFields($table, $fields)
    {
        if (count($fields) === 0) {
            return $this;
        } else {

            $field = head($fields);

            if (empty($field)) {
                return $this;
            }

            $fields = array_splice($fields, 1);
            if (Schema::hasColumn($table, $field)) {
                $this->model = $this->model->addSelect(strtoupper($field));
            }

            // continue the recursion
            return $this->negotiateFields($table, $fields);

        }
    }

    public function negotiate($model)
    {
        $modelPrefix = '\App\\';
        $modelNameSpace = $modelPrefix.$model;
        $this->model = new $modelNameSpace;
        
        $fields = array_where($this->fields, function ($value, $key) {
            return !is_string($key);
        });

        $relations = array_where($this->fields, function ($value, $key) {
            return is_string($key);
        });

        $this->negotiateFields($this->model->getTable(), $fields)
            ->negotiateRelations($relations);

        return $this->model;
    }
}