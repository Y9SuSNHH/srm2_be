<?php

namespace App\Http\Domain\Common\Model\Backlog;

use App\Http\Enum\ReferenceType;

class Reference implements \JsonSerializable, \Stringable
{
    /**
     * @var array
     */
    private $reference_list;
    /** @var mixed */
    private $title;

    /**
     * Reference constructor.
     * @param array $argument
     * @param null $title
     * @throws \ReflectionException
     */
    public function __construct(array $argument = [], $title = null)
    {
        $this->title = isset($argument['title']) ? $argument['title'] : $title;
        $this->reference_list = array_filter(array_map(function ($item) {
            if (is_array($item)) {
                $item = $this->validated($item);
                return $item;
            }

            return null;
        }, isset($argument['_list']) ? $argument['_list'] : $argument));
    }

    /**
     * @return mixed
     */
    public function getTitle(): mixed
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle(mixed $title): void
    {
        $this->title = $title;
    }

    /**
     * @return array
     */
    public function idList(): array
    {
        return array_column($this->reference_list, 'id');
    }

    /**
     * @param int $type
     * @param int $id
     * @throws \ReflectionException
     */
    public function add(int $type, int $id): void
    {
        $item = $this->validated(['type' => $type, 'id' => $id]);

        if ($item) {
            $this->reference_list[] = $item;
        }
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->reference_list;
    }

    /**
     * @return false|string
     */
    public function toJson(): bool|string
    {
        return json_encode($this->reference_list);
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return ['_list' => $this->reference_list, 'title' => $this->title];
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $json = json_encode(['_list' => $this->reference_list, 'title' => $this->title]);
        return $json ?: '';
    }

    /**
     * @param array $item
     * @return array|null
     * @throws \ReflectionException
     */
    private function validated(array $item): ?array
    {
        if (!isset($item['type']) || !isset($item['id'])) {
            return null;
        }

        if (!in_array($item['type'], ReferenceType::toArray())) {
            return null;
        }

        if (!(int)$item['id']) {
            return null;
        }

        return ['type' => $item['type'], 'id' => $item['id']];
    }
}
