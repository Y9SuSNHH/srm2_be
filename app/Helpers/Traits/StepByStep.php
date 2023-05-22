<?php

namespace App\Helpers\Traits;

use Illuminate\Http\Exceptions\HttpResponseException;

trait StepByStep
{
    /**
     * @param array $steps
     */
    public function initializationStep(array $steps)
    {
        if (empty($steps)) {
            session()->remove($this->getKeyName())->save();
        } else {
            $value = array_map(function ($item, $index) {
                return [$index + 1, $item, false];
            }, $steps, array_keys($steps));
            session()->set($this->getKeyName(), $value);
        }
    }

    /**
     * @param $step
     * @return bool
     */
    public function passStep($step): bool
    {
        try {
            [$steps, $passed] = $this->getSteps();

            if (count($passed) === count($steps)) {
                return true;
            }

            $passed_end = count($passed) ? end($passed) : null;
            $index = !$passed_end ? 0 : $passed_end[0];

            if (!isset($steps[$index]) || $step !== $steps[$index][1]) {
                return false;
            }

            $steps[$index] = [$steps[$index][0], $steps[$index][1], true];
            session()->set($this->getKeyName(), $steps);

            return true;
        } catch (\Exception $exception) {

        }

        return false;
    }

    /**
     * @param $step
     * @return bool
     */
    public function checkPassesStep($step): bool
    {
        [$steps, $passed] = $this->getSteps();

        if (0 === count($passed)) {
            return false;
        }

        return 0 < count(array_filter($passed, function ($item) use ($step) {
            return $step === $item[1];
        }));
    }

    /**
     * @param $step
     */
    public function passesStepOrFail($step): void
    {
        if (!$this->checkPassesStep($step)) {
            throw new HttpResponseException(response()->json(['successful' => false, 'data' => [], 'errors' => "check step $step is failed"]));
        }
    }

    /**
     * @param int $number
     * @return bool
     */
    public function checkPassesStepNum(int $number): bool
    {
        try {
            [$steps, $passed] = $this->getSteps();

            if (0 === count($passed)) {
                return false;
            }

            return 0 < count(array_filter($passed, function ($item) use ($number) {
                return $number === $item[0];
            }));
        } catch (\Exception $exception) {

        }

        return false;
    }

    /**
     * @param $step
     * @return bool
     */
    public function backStep($step): bool
    {
        try {
            [$steps, $passed] = $this->getSteps();

            if (0 === count($passed)) {
                return true;
            }

            $passed_end = end($passed);
            $index = $passed_end[0] - 1;

            if (isset($steps[$index]) && $step === $steps[$index][1]) {
                $steps[$index] = [$steps[$index][0], $steps[$index][1], false];
                session()->set($this->getKeyName(), $steps);
                return true;
            }
        } catch (\Exception $exception) {

        }

        return false;
    }

    public function clearSteps()
    {
        session()->remove($this->getKeyName())->save();
    }

    public function resetSteps()
    {
        [$steps] = $this->getSteps();
        $steps = array_map(function ($item) {
            $item[2] = false;
            return $item;
        }, $steps);
        session()->set($this->getKeyName(), $steps);
    }

    /**
     * @param array $input
     */
    public function setData(array $input): void
    {
        $key = $this->getKeyName('data');
        if (empty($input)) {
            session()->remove($key)->save();
        } else {
            session()->put([$key => json_encode($input)])->save();
        }
    }

    /**
     * @return mixed
     */
    public function getData(): mixed
    {
        $key = $this->getKeyName('data');

        if (session()->has($key)) {
            $data = json_decode(session()->get($key), true);

            if ($data) {
                return $data;
            }
        }

        return null;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getKeyName($name = 'step'): string
    {
        return auth()->user()->getRememberToken() .'.'. str_replace('\\', '_', get_class($this)) .'.'. $name;
    }

    /**
     * @return array
     */
    protected function getSteps(): array
    {
        $steps = session()->get($this->getKeyName(), []);
        $passed = array_filter($steps, function ($item) {
            return (bool)$item[2];
        });

        return [(array)$steps, $passed];
    }
}
