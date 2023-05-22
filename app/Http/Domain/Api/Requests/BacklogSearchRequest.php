<?php

namespace App\Http\Domain\Api\Requests;

use App\Helpers\Interfaces\PaginateSearchRequest;
use App\Helpers\Request;;
use App\Http\Enum\PerPage;
use App\Http\Enum\WorkDiv;
use App\Http\Enum\WorkStatus;
use Illuminate\Validation\Rule;

/**
 * Class BacklogSearchRequest
 * @package App\Http\Domain\Api\Requests
 *
 * @property int|null $work_div
 * @property int|null $work_status
 */
class BacklogSearchRequest extends Request implements PaginateSearchRequest
{
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
            'work_div' => [
                'nullable',
                Rule::in(WorkDiv::toArray()),
            ],
            'work_status' => [
                'nullable',
                Rule::in(WorkStatus::toArray()),
            ],
        ];
    }

    /**
     * @return int|null
     */
    public function page(): ?int
    {
        return $this->page ?? 1;
    }

    /**
     * @return int|null
     */
    public function perPage(): ?int
    {
        return $this->per_page ?? PerPage::getDefault();
    }
}
