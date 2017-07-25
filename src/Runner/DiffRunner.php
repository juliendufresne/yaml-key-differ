<?php

declare(strict_types=1);

namespace JulienDufresne\YAMLKeyDiffer\Runner;

use InvalidArgumentException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

final class DiffRunner
{
    /** @var int */
    private $maxDepth;
    /** @var string */
    private $sourceFile;
    /** @var string */
    private $testedFile;

    public function __construct(string $sourceFile, string $testedFile, int $maxDepth = 0)
    {
        $this->sourceFile = $sourceFile;
        $this->testedFile = $testedFile;
        $this->maxDepth = $maxDepth;
    }

    public function __invoke(): array
    {
        $sourceContent = $this->getContent($this->sourceFile);
        $testedContent = $this->getContent($this->testedFile);

        return $this->diff($sourceContent, $testedContent);
    }

    private function diff(array $sourceContent, array $testedContent, string $currentPath = '', int $currentDepth = 1)
    {
        $diff = [];
        foreach ($sourceContent as $itemKey => $itemValue) {
            if (!array_key_exists($itemKey, $testedContent)) {
                $diff[] = $itemKey;
                continue;
            }
            $testedItemValue = $testedContent[$itemKey];

            if (!is_array($itemValue) || $currentDepth === $this->maxDepth) {
                continue;
            }

            if (!is_array($testedItemValue)) {
                $diff[] = $itemKey;
                continue;
            }
            $diffOnDeeperDepth = $this->diff($itemValue, $testedContent[$itemKey], $itemKey, $currentDepth + 1);
            if (!empty($diffOnDeeperDepth)) {
                $diff[$itemKey] = $diffOnDeeperDepth;
            }
        }

        return $diff;
    }

    private function getContent(string $file): array
    {
        $filesystem = new Filesystem();
        if (!$filesystem->exists($file)) {
            throw new InvalidArgumentException(sprintf('The file "%s" does not exists.', $file));
        }

        return Yaml::parse(file_get_contents($file));
    }
}
