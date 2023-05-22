<?php

namespace App\Eloquent;

use App\Eloquent\Traits\HasSchool;
use Illuminate\Database\Eloquent\Model as BaseModel;

/**
 * Class Model
 * @package App\Eloquent
 * @method newModelInstance(array $attributes)
 */
class Model extends BaseModel
{
    /**
     * Save a new model and return the instance.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model|$this
     */
    public function create(array $attributes = [])
    {
        $this->pushCreation($attributes);

        return tap($this->newModelInstance($attributes), function ($instance) {
            $instance->save();
        });
    }

    /**
     * Create the model in the database.
     *
     * @param array $attributes
     * @return Model|\Illuminate\Database\Eloquent\Builder|BaseModel
     * @throws \ErrorException
     */
    public function createOrFail(array $attributes = []): BaseModel|\Illuminate\Database\Eloquent\Builder|Model
    {
        $this->pushCreation($attributes);

        $model = $this->newModelQuery()->create($attributes);

        if (!$model) {
            throw new \ErrorException(sprintf('save %s fails.', $this->table));
        }

        return $model;
    }

    /**
     * Update the model in the database.
     *
     * @param array $attributes
     * @param array $options
     * @return bool
     */
    public function update(array $attributes = [], array $options = []): bool
    {
        if (in_array('updated_by', $this->getFillable())) {
            $attributes['updated_by'] = auth()->getId();
        }

        return parent::update($attributes, $options);
    }

    /**
     * Update the model in the database within a transaction.
     *
     * @param  array  $attributes
     * @param  array  $options
     * @return bool
     *
     * @throws \Throwable
     */
    public function updateOrFail(array $attributes = [], array $options = []): bool
    {
        if (in_array('updated_by', $this->getFillable())) {
            $attributes['updated_by'] = auth()->getId();
        }

        return parent::updateOrFail($attributes, $options);
    }

    /**
     * @return static
     */
    public static function getModel(): static
    {
        return new static;
    }

    /**
     * @return array
     */
    public static function getListEloquent(): array
    {
        $results = [];
        $d = dir(__DIR__);

        while (false !== ($entry = $d->read())) {
            $classname = __NAMESPACE__ .'\\' . substr($entry, 0, strpos($entry, '.'));
            if (class_exists($classname) && __CLASS__ !== $classname) {
                $results[] = $classname;
            }
        }

        $d->close();

        return $results;
    }

    /**
     * @param array $attributes
     */
    private function pushCreation(array &$attributes)
    {
        if (in_array('created_by', $this->getFillable())) {
            $attributes['created_by'] = auth()->getId();
        }

        if (in_array('school_id', $this->getFillable()) && in_array(HasSchool::class, class_uses($this))) {
            $attributes['school_id'] = school()->getId();
        }
    }
}
