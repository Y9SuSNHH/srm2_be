<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;

/**
 * Class CsvParser
 * @package App\Helpers
 */
class CsvParser
{
    public const LABEL_ERR = 'LABEL_ERR';

    /** @var array[] */
    private $errors;
    /** @var false|resource */
    private $resource;
    /** @var array */
    private $columns;
    /** @var array|null */
    private $titles;
    /** @var int */
    private $index;
    /** @var array */
    private $labels;

    /**
     * CsvParser constructor.
     * @param UploadedFile $file
     * @param array $validate_labels
     * @param int title_line_number
     */
    public function __construct(UploadedFile $file = null, array $validate_labels = [], int $starting_line_number = 1)
    {
        if (!$file) {
            return;
        }

        $error = $file->getError();

        if ($error && $error !== UPLOAD_ERR_OK) {
            if ($error === UPLOAD_ERR_INI_SIZE) {
                $size = ini_get('upload_max_filesize');
                $this->errors = [[UPLOAD_ERR_INI_SIZE, "upload max filesize $size"]];
                return;
            }
            $this->errors = [[$error, "upload error #$error"]];
            return;
        }

        $this->getResource($file->getPathname(), $validate_labels, $starting_line_number);
    }

    /**
     * @param string $tmp_name
     * @param array $validate_labels
     * @param int $starting_line_number
     */
    public function getResource(string $tmp_name, array $validate_labels, int $starting_line_number = 1)
    {
        if ($this->resource) {
            return;
        }

        $this->resource = fopen($tmp_name, 'r');
        $this->labels = $validate_labels;
        $this->index = $starting_line_number;

        if (fgets($this->resource, 4) !== "\xEF\xBB\xBF") {
            // BOM not found - rewind pointer to start of file.
            rewind($this->resource);
        }

        if ($this->resource && !feof($this->resource)) {

            for ($i = 1; $i < $this->index; $i++) {
                fgetcsv($this->resource);
            }

            if (!empty($this->labels)) {
                $data = $this->fGetCsvRow($this->resource);
                $this->index++;
                if (array_values($this->labels) !== $data) {
                    $this->errors = [[self::LABEL_ERR, $data]];
                }

                $this->columns = self::createColumns(count($data));
                $this->titles = array_combine($this->columns, $data);

            }
        }

        if ($this->errors && $this->resource) {
            fclose($this->resource);
            $this->resource = null;
        }
    }

    public function reset()
    {
        return $this;
    }

    public function __destruct()
    {
        if ($this->resource) {
            fclose($this->resource);
        }
    }

    /**
     * @return array|null
     */
    public function getErrors(): ?array
    {
        return $this->errors;
    }

    /**
     * @return array
     */
    public function getTitles(): array
    {
        return $this->titles ?? [];
    }

    /**
     * @param callable $callback
     * @return bool
     */
    public function each(callable $callback): bool
    {
        if ($this->errors) {
            return false;
        }

        $loop = true;

        while (!feof($this->resource) && false !== $loop) {
            $data = $this->fGetCsvRow($this->resource);

            if ($data) {
                $not_matches = count($data) !== count($this->columns ?? []);

                if (empty($this->labels) && $not_matches) {
                    $this->columns = self::createColumns(count($data));
                    $loop = $callback(array_combine($this->columns, $data), $this->index, false);
                } else {
                    $loop = $callback($not_matches ? $data : array_combine($this->columns, $data), $this->index, $not_matches);
                }
            }

            $this->index++;
        }

        fclose($this->resource);
        $this->resource = null;
        return true;
    }

    /**
     * @param int $count
     * @return array
     */
    public static function createColumns(int $count): array
    {
        $result = [];
        $a = array_chunk(range(0, $count - 1), 26);

        foreach ($a as $i => $v) {
            $first = $i ? chr(64 + $i) : '';
            $result[] = array_map(function ($key) use ($first) {
                return $first . chr(65 + $key);
            }, array_keys($v));
        }

        return array_merge(...$result);
    }

    /**
     * Create Csv UTF-8 BOM Tmp
     *
     * @param array $data
     * @return resource|null
     */
    public static function createCsvUTF8BOMTmp(array $data)
    {
        // create file UTF8-BOM
        $BOM = "\xEF\xBB\xBF"; // UTF-8 BOM
        $tmp_file = tmpfile();

        if (!$tmp_file) {
            return null;
        }

        register_shutdown_function(static function () use ($tmp_file) {
            fclose($tmp_file);
        });
        fwrite($tmp_file, $BOM);
        // write content
        foreach ($data as $row) {
            fputs($tmp_file, implode(',', array_map(static function ($cell) {
                    return '"' . str_replace('"', '""', $cell) . '"';
                }, $row))."\n");
        }

        return $tmp_file;
    }

    /**
     * getting CSV array with UTF-8 encoding
     *
     * @param resource    &$handle
     * @return array
     */
    private function fGetCsvRow(&$handle): array
    {
        if ($buffer = fgetcsv($handle)) {
            return array_map('trim', $buffer);
        }

        return [];
    }

    /**
     * automatic convertion windows-1250 and iso-8859-2 info utf-8 string
     *
     * @param string $s
     *
     * @return  string
     */
    private function autoUTF(string $s): string
    {
        // detect UTF-8
        if (preg_match('#[\x80-\x{1FF}\x{2000}-\x{3FFF}]#u', $s))
            return $s;

        // detect WINDOWS-1250
        if (preg_match('#[\x7F-\x9F\xBC]#', $s))
            return iconv('WINDOWS-1250', 'UTF-8', $s);

        // assume ISO-8859-2
        return iconv('ISO-8859-2', 'UTF-8', $s);
    }
}
