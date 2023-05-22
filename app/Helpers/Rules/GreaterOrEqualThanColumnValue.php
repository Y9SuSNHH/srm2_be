<?php

namespace App\Helpers\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class GreaterOrEqualThanColumnValue implements Rule
{
    private string $table;
    private string $column;
    private int $id;

    /**
     * @param string $table
     * @param int $id
     * @param string $column
     */
    public function __construct(string $table, int $id, string $column)
    {
        $this->table  = $table;
        $this->id     = $id;
        $this->column = $column;
    }

    /**
     * @param $attribute
     * @param $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $columnValue = DB::table($this->table)->where('id', $this->id)->value($this->column);
        return $value >= $columnValue;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return 'The :attribute must be greater or equal than the corresponding column value in the database.';
    }
}
