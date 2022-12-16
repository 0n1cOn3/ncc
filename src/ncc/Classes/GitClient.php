<?php

    namespace ncc\Classes;

    use ncc\Exceptions\GitCheckoutException;
    use ncc\Exceptions\GitCloneException;
    use ncc\Exceptions\GitTagsException;
    use ncc\Exceptions\InvalidScopeException;
    use ncc\ThirdParty\Symfony\Process\Process;
    use ncc\Utilities\Console;
    use ncc\Utilities\Functions;

    class GitClient
    {
        /**
         * Clones a remote repository to a temporary directory.
         *
         * @param string $url
         * @return string
         * @throws GitCloneException
         * @throws InvalidScopeException
         */
        public static function cloneRepository(string $url): string
        {
            $path = Functions::getTmpDir();
            $process = new Process(["git", "clone", $url, $path]);
            $process->setTimeout(3600); // 1 hour
            $process->run(function ($type, $buffer)
            {
                if (Process::ERR === $type)
                {
                    Console::outWarning($buffer);
                }
                else
                {
                    Console::outVerbose($buffer);
                }
            });

            if (!$process->isSuccessful())
                throw new GitCloneException($process->getErrorOutput());

            Console::outVerbose('Repository cloned to ' . $path);
            return $path;
        }

        /**
         * Checks out a specific branch or tag.
         *
         * @param string $path
         * @param string $branch
         * @throws GitCheckoutException
         */
        public static function checkout(string $path, string $branch)
        {
            $process = new Process(["git", "checkout", $branch], $path);
            $process->setTimeout(3600); // 1 hour
            $process->run(function ($type, $buffer)
            {
                if (Process::ERR === $type)
                {
                    Console::outWarning($buffer);
                }
                else
                {
                    Console::outVerbose($buffer);
                }
            });

            if (!$process->isSuccessful())
                throw new GitCheckoutException($process->getErrorOutput());

            Console::outVerbose('Checked out branch ' . $branch);
        }

        /**
         * Returns an array of tags that are available in the repository.
         *
         * @param string $path
         * @return array
         * @throws GitTagsException
         */
        public static function getTags(string $path): array
        {
            $process = new Process(["git", "fetch", '--all', '--tags'] , $path);
            $process->setTimeout(3600); // 1 hour
            $process->run(function ($type, $buffer)
            {
                if (Process::ERR === $type)
                {
                    Console::outWarning($buffer);
                }
                else
                {
                    Console::outVerbose($buffer);
                }
            });

            if (!$process->isSuccessful())
                throw new GitTagsException($process->getErrorOutput());

            $process = new Process(['git', '--no-pager', 'tag', '-l'] , $path);

            $process->run(function ($type, $buffer)
            {
                if (Process::ERR === $type)
                    Console::outWarning($buffer);

            });

            if (!$process->isSuccessful())
                throw new GitTagsException($process->getErrorOutput());

            $tags = explode(PHP_EOL, $process->getOutput());
            return array_filter($tags);
        }

    }