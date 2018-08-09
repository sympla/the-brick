<?php

namespace Sympla\Search\Search;

use DB;
use Request;
use Schema;

class Search
{
    protected $debug = false;
    protected $fields = [];
    protected $filters = [];
    protected $limit = null;
    protected $namingConvention = 'lowercase';
    protected $noPaginate = false;
    protected $orderBy = '';
    protected $relations = [];
    protected $relationsFilters = [];
    protected $size = 10;
    protected $sort = 'ASC';
    private $model;
    private $modelObj;
    private $table;

    /**
     * Search constructor.
     */
    public function __construct()
    {
        $this->request = Request::all();

        if (Request::exists('fields')) {
            $this->addFields($this->request['fields']);
        }

        if (Request::exists('filters')) {
            $this->addFilters($this->request['filters']);
        }

        if (Request::exists('orderBy')) {
            $this->setOrderBy($this->request['orderBy']);
        }

        if (Request::exists('sort')) {
            $this->setSort($this->request['sort']);
        }

        if (Request::exists('limit')) {
            $this->setLimit($this->request['limit']);
        }

        if (Request::exists('noPaginate')) {
            $this->setNoPaginate($this->request['noPaginate']);
        }

        if (Request::exists('debug')) {
            $this->setDebug($this->request['debug']);
        }

        if (Request::exists('size')) {
            $this->setSize($this->request['size']);
        }
    }

    /**
     * Execute negotiate query
     * @param $model
     * @return mixed
     */
    public function negotiate($model)
    {
        try {
            $this->enableQueryLog();

            $response = $this->mountBuilder($model)
                ->negotiateExecute();

            return $response;
        } catch (\Exception $e) {
            return [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get model builder
     * @param  string $model
     * @return Builder
     */
    public function getBuilder($model)
    {
        try {
            $this->mountBuilder($model);
            return $this->model;
        } catch (\Exception $e) {
            return [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }
    }


    /**
     * Mount model builder by request
     * @param $model
     * @return mixed
     */
    private function mountBuilder($model)
    {
        if (!$this->model) {
            $modelPrefix = config('the-brick-search.models.namespace_prefix') ?? 'App\\';
            $modelNameSpace = $modelPrefix . $model;
            $this->model = new $modelNameSpace;
        }

        $this->modelObj = $this->model;
        $this->table = $this->model->getTable();

        return $this->negotiateFields($this->table, $this->fields)
            ->negotiateRelations($this->relations)
            ->negotiateFilters($this->table, $this->filters)
            ->negotiateRelationsFilters($this->relationsFilters)
            ->negotiateOrder($this->table, $this->orderBy, $this->sort)
            ->negotiateLimit($this->limit);
    }

    /**
     * @param $limit
     * @return $this
     */
    private function negotiateLimit($limit)
    {
        if (!is_null($limit)) {
            $this->model->limit($limit);
        }
        return $this;
    }

    /**
     * @param $table
     * @param string $order
     * @param string $sort
     * @return $this
     */
    private function negotiateOrder($table, $order = '', $sort = 'ASC')
    {
        if (!empty($order) && Schema::hasColumn($table, $order)) {
            $this->model->orderBy($order, $sort?:'ASC');
        }
        return $this;
    }

    /**
     * @param $fields
     * @return $this
     */
    private function negotiateRelations($fields)
    {
        $this->model->with($this->parseRelations($fields));
        return $this;
    }

    /**
     * @param $filters
     * @return $this
     */
    private function negotiateRelationsFilters($filters)
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

    /**
     * @param $table
     * @param $fields
     * @return $this|Search
     */
    private function negotiateFields($table, $fields)
    {
        if (count($fields) === 0) {
            return $this;
        } else {

            $field = head($fields);
            $fieldSearch = str_replace($table.'.', '', $field);
            if (empty($field)) {
                return $this;
            }

            $fields = array_splice($fields, 1);
            if (Schema::hasColumn($table, $fieldSearch)) {
                $this->model = $this->model->addSelect($this->setAttribute($field));
            } elseif (method_exists($this->modelObj, 'scope'.ucfirst(camel_case($field)))) {
                $scope = camel_case($field);
                $this->model = $this->model->$scope();
            } elseif (method_exists($this->modelObj, 'get'.ucfirst(camel_case($field)).'Attribute')) {
                $attribute = strtoupper($field);
                $this->modelObj->addCustomAppends($attribute);
            }

            // continue the recursion
            return $this->negotiateFields($table, $fields);

        }
    }

    /**
     * @param $table
     * @param $filters
     * @return $this|mixed
     */
    private function negotiateFilters($table, $filters)
    {
        if (count($filters) === 0) {
            return $this;
        } else {

            $filter = head($filters);

            if (empty($filter)) {
                return $this;
            }

            $filters = array_splice($filters, 1);
            $condition = $this->strArrayPos($filter, ['!=', '>=', '<=', '=', '>', '<', '%']);

            if (Schema::hasColumn($table, $condition['attribute'])) {
                if (strtolower(substr($condition['attribute'], -strlen('_date'))) === '_date') {
                    $this->model = $this->model->whereRaw(
                        'DATE_FORMAT('.$this->setAttribute($condition['attribute']).', "%d/%m/%Y %H:%i") LIKE "%'.$condition['value'].'%"'
                    );
                } elseif (strpos($condition['operator'], '%') !== false) {
                    $this->model = $this->model->where(
                        $this->setAttribute($condition['attribute']),
                        'like',
                        '%'.$condition['value'].'%'
                    );
                } else {
                    $this->model = $this->model->where(
                        $this->setAttribute($condition['attribute']),
                        $condition['operator'],
                        $condition['value']
                    );
                }
            } elseif (method_exists($this->modelObj, 'scope'.ucfirst(camel_case($filter)))) {
                $scope = camel_case($filter);
                $this->model = $this->model->$scope();
            }

            // continue the recursion
            return $this->negotiateFilters($table, $filters);

        }
    }

    /**
     * @param $fields
     */
    private function parseFields($fields)
    {

        // (do the required processing...)
        $temp = explode(',', $fields, 2);

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

    /**
     * @param $filters
     */
    private function parseFilters($filters)
    {

        // (do the required processing...)
        $temp = explode(',', $filters, 2);

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

    /**
     * @param $fields
     * @param array $relations
     * @return array
     */
    private function parseRelations($fields, $relations = [])
    {
        foreach ($fields as $key => $value) {
            $relations[] = $key.(empty($value)?'':':'.$this->setAttribute($value));
        }
        return $relations;
    }

    /**
     * @param $val
     * @return string
     */
    private function setAttribute($val)
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

    /**
     * Enable query log in debug mode
     */
    public function enableQueryLog()
    {
        // enable debug log
        if ($this->debug) {
            DB::connection()->enableQueryLog();
        }
    }

    /**
     * Execute query
     * @return $array
     */
    public function negotiateExecute()
    {
        if ($this->noPaginate) {
            $res = $this->model->get();
        } else {
            $res = $this->model->paginate($this->size);
        }

        // return debug log
        if ($this->debug) {
            return DB::getQueryLog();
        }

        return $res;
    }

    /**
     * @return $this
     */
    public function setUpperCaseConvention()
    {
        $this->namingConvention = 'uppercase';
        return $this;
    }

    /**
     * @return $this
     */
    public function setLowerCaseConvention()
    {
        $this->namingConvention = 'lowercase';
        return $this;
    }

    /**
     * @param $string
     * @param $array
     * @return array|bool
     */
    private function strArrayPos($string, $array)
    {
        for ($i = 0, $n = count($array); $i < $n; $i++) {
            if (($pos = strpos($string, $array[$i])) !== false) {
                $temp = explode($array[$i], $string);
                return [
                    'attribute' => $temp[0],
                    'operator' => $array[$i],
                    'value' => $temp[1]
                ];
            }
        }
        return false;
    }

    /**
     * @param $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = app($model);
        return $this;
    }

    /**
     * Add fields separated by comma
     * @param string $fields
     * @return $this
     */
    public function addFields(string $fields)
    {
        $this->parseFields($fields);
        return $this;
    }

    /**
     * Add filters separated by comma
     * @param string $fields
     * @return $this
     */
    public function addFilters(string $fields)
    {
        $this->parseFilters($fields);
        return $this;
    }

    /**
     * Set order by
     * @param string $orderBy
     * @return $this
     */
    public function setOrderBy(string $orderBy)
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    /**
     * Set sort
     * @param string $sort
     * @return $this
     */
    public function setSort(string $sort)
    {
        $this->sort = strtoupper($sort);
        return $this;
    }

    /**
     * Set limit $limit
     * @param int $limit
     * @return $this
     */
    public function setLimit(int $limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Set noPaginate $noPaginate
     * @param bool $noPaginate
     * @return $this
     */
    public function setNoPaginate(bool $noPaginate)
    {
        $this->noPaginate = $noPaginate;
        return $this;
    }

    /**
     * Set debug $debug
     * @param bool $debug
     * @return $this
     */
    public function setDebug(bool $debug)
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * Set size
     * @param int $size
     * @return $this
     */
    public function setSize(int $size)
    {
        $this->size = $size;
        return $this;
    }
}
