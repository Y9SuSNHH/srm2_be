<?php

namespace App\Helpers;

use Illuminate\Pagination\LengthAwarePaginator as Base;

class LengthAwarePaginator extends Base
{
    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'currentPage' => $this->currentPage(),
            'data' => $this->items->toArray(),
            'firstPageUrl' => $this->url(1),
            'from' => $this->firstItem(),
            'lastPage' => $this->lastPage(),
            'lastPageUrl' => $this->url($this->lastPage()),
            'links' => $this->linkCollection()->toArray(),
            'nextPageUrl' => $this->nextPageUrl(),
            'path' => $this->path(),
            'perPage' => $this->perPage(),
            'prevPageUrl' => $this->previousPageUrl(),
            'to' => $this->lastItem(),
            'total' => $this->total(),
        ];
    }
}
