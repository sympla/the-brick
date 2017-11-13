<?php

namespace Sympla\Search\Search;

use Request;
use Schema;

class Search {

    var $fields = [];
    var $relations = [];
    var $filters = [];
    var $relationsFilters = [];
    var $namingConvention = 'lowercase';

    public function __construct()
    {
        $this->request = Request::all();

        if (Request::exists('fields')) {
            $this->parseFields($this->request['fields']);
        }

        if (Request::exists('filters')) {
            $this->parseFilters($this->request['filters']);
        }
    }

    public function setAttribute($val)
    {
        switch ($this->namingConvention) {
            case 'lowercase':
                return strtolower($val);
                break;

            case 'uppercase':
                return strtoupper($val);
                break;
            
            default:
                return $val;
                break;
        }
    }

    public function setUpperCaseConvention()
    {
        $this->namingConvention = 'uppercase';
        return $this;
    }

    public function negotiateRelations($fields)
    {
        $this->model->with($this->parseRelations($fields));
        return $this;
    }

    public function negotiateRelationsFilters($filters)
    {
        foreach ($filters as $key => $value) {
            $this->model->whereHas($key, function ($query) use ($value) {
                if (strpos($value, '%') !== false) {
                    $temp = explode('%', $value);
                    $query->where($this->setAttribute($temp[0]), 'like', '%'.$temp[1].'%');
                } else {
                    $temp = explode('=', $value);
                    $query->where($this->setAttribute($temp[0]), $temp[1]);
                }
            });
        }
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
                $this->model = $this->model->addSelect($this->setAttribute($field));
            }

            // continue the recursion
            return $this->negotiateFields($table, $fields);

        }
    }

    public function negotiateFilters($table, $filters)
    {
        if (count($filters) === 0) {
            return $this;
        } else {

            $filter = head($filters);

            if (empty($filter)) {
                return $this;
            }

            $filters = array_splice($filters, 1);
            $filter = $this->str_array_pos($filter, ['!=', '>=', '<=', '=', '>', '<']);
            if (Schema::hasColumn($table, $filter[0])) {
                $this->model = $this->model->where($this->setAttribute($filter[0]), $filter[1], $filter[2]);
            }

            // continue the recursion
            return $this->negotiateFilters($table, $filters);

        }
    }

    public function negotiate($model)
    {
        $modelPrefix = '\App\\';
        $modelNameSpace = $modelPrefix.$model;
        $this->model = new $modelNameSpace;
        $this->table = $this->model->getTable();

        $this->negotiateFields($this->table, $this->fields)
            ->negotiateRelations($this->relations)
            ->negotiateFilters($this->table, $this->filters)
            ->negotiateRelationsFilters($this->relationsFilters);

        return $this->model;
    }

    public function parseFields($fields)
    {
     
        // (do the required processing...)
        $temp = explode(',' , $fields, 2);

        if (count($temp) === 0) {
            // end the recursion
            return;
        } else {
            if (str_contains($fields, '(') && (strpos($fields, ',') > strpos($fields, '(') || strpos($fields, ',') === false)) {
                $start = strpos($fields, '(');
                $end = strpos($fields, ')');
                $_relation = substr($fields, 0, $start); 
                $_fields = substr($fields, $start+1, $end-$start-1);
                $this->relations[$_relation] = $_fields;
                // continue the recursion
                return $this->parseFields(substr($fields, $end+2).',');
            } else if (isset($temp[1])) {
                if (!empty($temp[0])) {
                    $this->fields[] = $temp[0];
                }
                // continue the recursion
                return $this->parseFields($temp[1]);
            } else {
                if (!empty($temp[0])) {
                    $this->fields[] = $temp[0];
                }
                // end the recursion
                return;
            }
        }
    }

    public function parseFilters($filters)
    {
     
        // (do the required processing...)
        $temp = explode(',' , $filters, 2);

        if (count($temp) === 0) {
            // end the recursion
            return;
        } else {
            if (str_contains($filters, '(') && (strpos($filters, ',') > strpos($filters, '(') || strpos($filters, ',') === false)) {
                $start = strpos($filters, '(');
                $end = strpos($filters, ')');
                $_relation = substr($filters, 0, $start); 
                $_filters = substr($filters, $start+1, $end-$start-1);
                $this->relationsFilters[$_relation] = $_filters;
                // continue the recursion
                return $this->parseFilters(substr($filters, $end+2).',');
            } else if (isset($temp[1])) {
                if (!empty($temp[0])) {
                    $this->filters[] = $temp[0];
                }
                // continue the recursion
                return $this->parseFilters($temp[1]);
            } else {
                if (!empty($temp[0])) {
                    $this->filters[] = $temp[0];
                }
                // end the recursion
                return;
            }
        }
    }

    public function parseRelations($fields, $relations = [])
    {
        foreach ($fields as $key => $value) {
            $relations[] = $key.(empty($value)?'':':'.$this->setAttribute($value));
        }
        return $relations;
    }

    public function str_array_pos($string, $array)
    {
        for ($i = 0, $n = count($array); $i < $n; $i++) {
            if (($pos = strpos($string, $array[$i])) !== false) {
                $temp = explode($array[$i], $string);
                return [
                    $temp[0],
                    $array[$i],
                    $temp[1]
                ];
            }
        }
        return false;
    }
}