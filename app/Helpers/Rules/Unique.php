<?php

namespace App\Helpers\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class Unique implements Rule
{
    private $message;
    private $attribute;
    private $value;
    private $transform_message;
    /** @var Builder */
    private $query;
    private $column;

    /**
     * Unique constructor.
     * @param $eloquent_model
     * @param $column
     */
    public function __construct($eloquent_model, $column)
    {
        /** @var Builder $query */
        $this->query = call_user_func([$eloquent_model, 'query']);
        $this->column = $column;
    }

    /**
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->attribute = $attribute;
        $this->value = $value;

        try {

            $this->query->where($this->column, $value);

            if ($this->query->count()) {
                $this->message = __('validation.unique', ['attribute' => $attribute]);
                return false;
            }
        } catch (\Throwable $exception) {

        }

        return true;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        if (is_callable($this->transform_message)) {
            return (string)call_user_func_array($this->transform_message, [$this->attribute, $this->value]);
        }

        return (string)$this->message;
    }

    /**
     * @param callable $callback (attribute, value)
     * @return $this
     */
    public function transformMessage(callable $callback): Unique
    {
        $this->transform_message = $callback;
        return $this;
    }

    /**
     * @param string $column
     * @param $value
     * @return $this
     */
    public function where(string $column, $value): Unique
    {
        $this->query->where($column, $value);
        return $this;
    }

    /**
     * @param string $column
     * @param array $value
     * @return Unique
     */
    public function whereIn(string $column, array $value): Unique
    {
        $this->query->whereIn($column, $value);
        return $this;
    }

    /**
     * @param string $column
     * @return $this
     */
    public function whereNull(string $column): Unique
    {
        $this->query->whereNull($column);
        return $this;
    }

    /**
     * Ignore the given ID during the unique check.
     *
     * @param $id
     * @param string $idColumn
     * @return $this
     */
    public function ignore($id, $idColumn = 'id'): Unique
    {
        if ($id) {
            $this->query->where($idColumn, '<>', $id);
        }

        return $this;
    }
}
