<?php

namespace App\Http\Domain\Common\Requests;

use App\Helpers\Interfaces\PaginateSearchRequest;
use App\Helpers\Request;
use App\Http\Enum\PerPage;

/**
 * Class BaseSearchRequest
 * @package App\Http\Domain\Common\Requests
 *
 * @property int|null $page
 * @property int|null $per_page
 * @property int|null $keyword
 */
class BaseSearchRequest extends Request implements PaginateSearchRequest
{
    private static $replace_keyword;
    /**
     * @return int
     */
    public function page(): int
    {
        return $this->page ?? 1;
    }

    /**
     * @return int
     */
    public function perPage(): int
    {
        return $this->per_page ?? PerPage::getDefault();
    }

    /**
     * @return string
     */
    public function getKeyword(): string
    {
        if (!static::$replace_keyword && $this->keyword) {
            static::$replace_keyword = '%'. str_replace(' ', '%', $this->keyword) .'%';
        }

        return self::$replace_keyword ?? '';
    }

    public function rules(array $input): array
    {
        return [
            'page' => [
                'nullable',
                'integer'
            ],
            'per_page' => [
                'nullable',
                'integer'
            ],
            'keyword' => [
                'nullable',
                'string'
            ],
        ];
    }
}
