<?php
/**
 * Created by PhpStorm.
 * User: alive
 * Date: 10/7/17
 * Time: 12:19 PM
 */

namespace App\Resources;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;

class RequestHelper
{
    //TODO some method  of this class (like uniqueCheck) must be return $this
    /**
     * @var array
     */
    protected $validatorMethods = ["POST", "PUT", "PATCH"];

    /**
     * @var array
     */
    protected $uniqueFields = [];

    /**
     * @var null
     */
    protected $model = null;

    /**
     * @param Request $request
     * @return array
     */
    public function getJsonPaginateValues(Request $request)
    {
        $request = $request->toArray();
        $size = 10;
        $number = 1;
        if (array_key_exists('page', $request)) {
            if (array_key_exists('size', $request['page'])) {
                $size = $request['page']['size'];
            }
            if (array_key_exists('number', $request['page'])) {
                $number = $request['page']['number'];
            }
        }
        return array($size, $number);
    }

    /**
     * @param $param
     * @return bool
     */
    public function isJson($param)
    {
        return collect(json_decode($param, true))->count() == 0 ? false : true;
    }

    /**
     * @param $param
     * @return mixed|string
     */
    public function getOperatorFromJson($param)
    {
        if (collect(json_decode($param, true))->get('operator') == null) {
            return '=';
        }
        return collect(json_decode($param, true))->get('operator');
    }

    /**
     * @param $param
     * @return mixed
     */
    public function getValueFromJson($param)
    {
        if (collect(json_decode($param, true))->get('value') == null) {
            return json_decode($param, true);
        }
        return collect(json_decode($param, true))->get('value');
    }

    /**
     * @param $param
     * @return \Illuminate\Support\Collection
     */
    public function getCollectFromJson($param)
    {
        return collect(json_decode($param, true));
    }

    /**
     * @param Request $request
     * @param $validatorArray
     * @param string $message
     * @return \Illuminate\Http\JsonResponse|MessageBag|null
     */
    public function validator(Request $request, $validatorArray, $message = 'validation_fails')
    {
        $validationErrors = $this->checkRequestValidation($request, $validatorArray);
        if ($validationErrors != null) {
            return $validationErrors;
        }
        return $validationErrors;
    }

    /**
     * @param Request $request
     * @param $validationArray
     * @return MessageBag|null
     */
    public function checkRequestValidation(Request $request, $validationArray)
    {
        $requestParams = $request->toArray();
        $validator = Validator::make($request->all(), $validationArray);
        if ($validator->fails()) {
            return $validator->errors();
        }
        if (is_numeric(array_search($request->getMethod(), $this->validatorMethods))) {
            $errors = new MessageBag();
            foreach ($requestParams as $requestParamKey => $requestParamValue) {
                if (is_numeric(array_search($requestParamKey, $this->uniqueFields))) {
                    if ($this->checkExistUniqueRecord($requestParamKey, $requestParamValue)) {
                        $errors->add($requestParamKey, 'This ' . $requestParamKey . ' is exist try another.');
                    }
                }
            }
            if (collect($errors)->count() > 0) {
                return $errors;
            }
        }
        return null;
    }

    /**
     * @param $key
     * @param $value
     * @return bool
     */
    public function checkExistUniqueRecord($key, $value)
    {
        if (is_null($this->model)) {
            if ($this->model->where($key, $value)->count()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array
     */
    public function getUniqueFields()
    {
        return $this->uniqueFields;
    }

    /**
     * @param array $uniqueFields
     */
    public function setUniqueFields($uniqueFields)
    {
        $this->uniqueFields = $uniqueFields;
    }

    /**
     * @return array
     */
    public function getValidatorMethods()
    {
        return $this->validatorMethods;
    }

    /**
     * @param array $validatorMethods
     */
    public function setValidatorMethods($validatorMethods)
    {
        $this->validatorMethods = $validatorMethods;
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param mixed $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

}