<?php

namespace App\Services\Interfaces;

interface FileServiceInterface
{
    /**
     * Upload formulir file
     *
     * @param mixed $file
     * @return array
     */
    public function uploadFormulir($file): array;

    /**
     * Download formulir file
     *
     * @return array
     */
    public function downloadFormulir(): array;
}
