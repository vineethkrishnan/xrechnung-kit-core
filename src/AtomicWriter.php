<?php

declare(strict_types=1);

namespace XrechnungKit;

use RuntimeException;

/**
 * Atomic write with quarantine sibling.
 *
 * write() always lands the XML at either $targetPath (when $valid is true) or
 * the sibling quarantine path *_invalid.xml (when $valid is false), via a
 * temp file in the same directory plus a POSIX-atomic rename. The opposite
 * sibling is removed on every successful write so the name space is monotonic
 * per (filename, time): a reader picking up name.xml is guaranteed to see XML
 * that passed validation at the moment it landed there.
 */
final class AtomicWriter
{
    /**
     * Writes $xml to either $targetPath or its quarantine sibling, atomically.
     *
     * @return string The final path (either $targetPath or the quarantine sibling).
     *
     * @throws RuntimeException If the temp file cannot be created or the rename fails (e.g., cross-filesystem rename).
     */
    public function write(string $xml, string $targetPath, bool $valid): string
    {
        $finalPath = $valid ? $targetPath : self::quarantinePath($targetPath);
        $oppositePath = $valid ? self::quarantinePath($targetPath) : $targetPath;

        $directory = \dirname($finalPath);
        if (!is_dir($directory) && !@mkdir($directory, 0o755, true) && !is_dir($directory)) {
            throw new RuntimeException("Could not create output directory: {$directory}");
        }

        $tempPath = $directory . DIRECTORY_SEPARATOR . '.xrechnung_kit_' . bin2hex(random_bytes(8)) . '.tmp';

        $handle = @fopen($tempPath, 'wb');
        if ($handle === false) {
            throw new RuntimeException("Could not open temp file for writing: {$tempPath}");
        }

        try {
            if (@flock($handle, LOCK_EX) === false) {
                throw new RuntimeException("Could not acquire exclusive lock on temp file: {$tempPath}");
            }
            if (@fwrite($handle, $xml) === false) {
                throw new RuntimeException("Could not write XML to temp file: {$tempPath}");
            }
            @fflush($handle);
            @flock($handle, LOCK_UN);
        } finally {
            @fclose($handle);
        }

        if (!@rename($tempPath, $finalPath)) {
            @unlink($tempPath);
            throw new RuntimeException("Could not atomically rename temp file to: {$finalPath}");
        }

        if ($oppositePath !== $finalPath && file_exists($oppositePath)) {
            @unlink($oppositePath);
        }

        return $finalPath;
    }

    /**
     * Computes the quarantine sibling path for a given target. e.g.
     * /out/RE-1.xml -> /out/RE-1_invalid.xml
     */
    public static function quarantinePath(string $targetPath): string
    {
        $info = pathinfo($targetPath);
        $dir = $info['dirname'] ?? '.';
        $base = $info['filename'] ?? 'xrechnung';
        $ext = isset($info['extension']) ? '.' . $info['extension'] : '';
        return $dir . DIRECTORY_SEPARATOR . $base . '_invalid' . $ext;
    }
}
