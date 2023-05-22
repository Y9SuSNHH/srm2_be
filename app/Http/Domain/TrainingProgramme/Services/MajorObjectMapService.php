<?php

namespace App\Http\Domain\TrainingProgramme\Services;

use App\Http\Domain\TrainingProgramme\Repositories\MajorObjectMap\MajorObjectMapRepository;
use App\Http\Domain\TrainingProgramme\Repositories\MajorObjectMap\MajorObjectMapRepositoryInterface;

/**
 * Class MajorObjectMapService
 * @package App\Http\Domain\TrainingProgramme\Services
 */
class MajorObjectMapService
{
    /** @var MajorObjectMapRepository */
    private $major_object_map_repository;

    public function __construct(MajorObjectMapRepositoryInterface $major_object_map_repository)
    {
        $this->major_object_map_repository = $major_object_map_repository;
    }

    /**
     * @param int|null $area_id
     * @return array|null
     */
    public function getMajorAndObject(?int $area_id): ?array
    {
        if (null === $area_id) {
            return [];
        }

        $major_objects = $this->major_object_map_repository->getMajorAndObject($area_id);
        $result = [];

        /** @var \App\Eloquent\MajorObjectMap $major_object */
        foreach ($major_objects as $major_object) {
            $major_id = $major_object->major->id;
            if (!isset($result[$major_id])) {
                $result[$major_id] = [
                    'id' => $major_id,
                    'code' => $major_object->major->code,
                    'name' => $major_object->major->name,
                    'shortcode' => $major_object->major->shortcode,
                    'enrollmentObjects' => [],
                ];
            }

            $result[$major_id]['enrollmentObjects'][] = [
                'id' => $major_object->enrollment_object->id,
                'code' => $major_object->enrollment_object->code,
                'classification' => $major_object->enrollment_object->classification,
                'name' => $major_object->enrollment_object->name,
                'shortcode' => $major_object->enrollment_object->shortcode,
            ];
        }

        return array_values($result);
    }

    public function getByArea(int $area_id)
    {
        return $this->major_object_map_repository->getMajorAndObject($area_id);
    }
}