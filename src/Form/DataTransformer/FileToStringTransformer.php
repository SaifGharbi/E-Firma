<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;

class FileToStringTransformer implements DataTransformerInterface
{
    public function transform($value): mixed
    {
        // Transform the File instance to a string (file path or null)
        if ($value instanceof File) {
            return $value->getPathname();
        }

        return null; // Return null if no file or invalid value
    }

    public function reverseTransform($value): mixed
    {
        // Transform the string (file path) or UploadedFile back to a File instance
        if ($value instanceof UploadedFile) {
            return $value; // Return the UploadedFile as is for processing
        }

        if ($value === null || $value === '') {
            return null; // Return null if no file is uploaded
        }

        // Handle an existing file path (e.g., from an entity)
        if (is_string($value) && file_exists($value)) {
            return new File($value);
        }

        return null; // Default to null for safety
    }
}
