<?php

    namespace ncc\Utilities;

    use ncc\Exceptions\IOException;
    use SplFileInfo;
    use SplFileObject;

    class IO
    {
        /**
         * Attempts to write the specified file, with proper error handling
         *
         * @param string $uri
         * @param string $data
         * @param int $perms
         * @param string $mode
         * @return void
         * @throws IOException
         */
        public static function fwrite(string $uri, string $data, int $perms=0644, string $mode='w'): void
        {
            $fileInfo = new SplFileInfo($uri);

            if(!is_dir($fileInfo->getPath()))
            {
                throw new IOException(sprintf('Attempted to write data to a directory instead of a file: (%s)', $uri));
            }

            $file = new SplFileObject($uri, $mode);

            if (!$file->flock(LOCK_EX | LOCK_NB))
            {
                throw new IOException(sprintf('Unable to obtain lock on file: (%s)', $uri));
            }
            elseif (!$file->fwrite($data))
            {
                throw new IOException(sprintf('Unable to write content to file: (%s)... to (%s)', substr($data,0,25), $uri));
            }
            elseif (!$file->flock(LOCK_UN))
            {
                throw new IOException(sprintf('Unable to remove lock on file: (%s)', $uri));
            }
            elseif (!@chmod($uri, $perms))
            {
                throw new IOException(sprintf('Unable to chmod: (%s) to (%s)', $uri, $perms));
            }
        }
    }