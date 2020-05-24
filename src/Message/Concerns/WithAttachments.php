<?php

/**
 * This file is part of SmsCenter SDK package.
 *
 * © JhaoDa (https://github.com/jhaoda)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace JhaoDa\SmsCenter\Message\Concerns;

use JhaoDa\SmsCenter\Exception\CouldNotAttachFile;

trait WithAttachments
{
    /** @var \SplFileInfo[] */
    protected $files = [];

    abstract protected function getMaxFileSize(): ?int;
    abstract protected function getMaxNumberOfFiles(): int;
    abstract protected function getMaxTotalSizeOfFiles(): ?int;

    /**
     * Добавление файла.
     *
     * @param  \SplFileInfo|string  $file  файл
     *
     * @throws CouldNotAttachFile
     *
     * @return $this
     */
    public function attach($file)
    {
        if ($this->getMaxNumberOfFiles() && (\count($this->files) > $this->getMaxNumberOfFiles())) {
            throw CouldNotAttachFile::becauseMaxNumberOfFilesExceeded($this->getMaxNumberOfFiles());
        }

        if (\is_string($file)) {
            $file = new \SplFileInfo($file);
        }

        if (! $file->isReadable()) {
            throw CouldNotAttachFile::becauseNotReadable($file->getRealPath());
        }

        if ($this->getMaxFileSize() && ($file->getSize() > $this->getMaxFileSize())) {
            throw CouldNotAttachFile::becauseMaxFileSizeExceeded($file->getRealPath(), $this->getMaxFileSize());
        }

        if ($this->getMaxTotalSizeOfFiles()) {
            $total = \array_reduce($this->files, static function (int $total, \SplFileInfo $file) {
                return $total + $file->getSize();
            }, 0);

            if ($total > $this->getMaxTotalSizeOfFiles()) {
                throw CouldNotAttachFile::becauseMaxFileSizeExceeded($file->getRealPath(), $this->getMaxFileSize());
            }
        }

        $this->files[] = $file;

        return $this;
    }

    public function getFiles()
    {
        return $this->files;
    }
}
