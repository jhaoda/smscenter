<?php

namespace JhaoDa\SmsCenter\Message;

use JhaoDa\SmsCenter\Exception;

abstract class AbstractMessageWithAttachments extends AbstractMessage
{
    protected $files       = [];
    protected $maxFiles    = 3;
    protected $maxFileSize = 524288; // 0.5Mb

    /**
     * Добавить файл к сообщению.
     *
     * @param  string|array  $path  путь к файлу или массив путей к файлам
     *
     * @throws Exception
     *
     * @return bool
     */
    public function addFile($path)
    {
        if (count($this->files) == $this->maxFiles) {
            throw new Exception(sprintf(
                'Максимум %d файла.', $this->maxFiles
            ));
        }

        if (is_array($path)) {
            foreach ($path as $item) {
                $this->addFile($item);
            }
        }

        if (!is_readable($path)) {
            throw new Exception(sprintf(
                'Невозможно получить доступ к файлу "%s".', $path
            ));
        }

        if (filesize($path) > $this->maxFileSize) {
            throw new Exception(sprintf(
                'Размер файла "%s" больше, чем %fМб.', $path, $this->maxFileSize / 1024
            ));
        }

        $this->files[] = $path;

        return true;
    }
}
