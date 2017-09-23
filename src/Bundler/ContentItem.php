<?php

namespace Hostnet\Component\Resolver\Bundler;

use Hostnet\Component\Resolver\Bundler\Pipeline\ReaderInterface;
use Hostnet\Component\Resolver\File;

class ContentItem
{
    public $file;
    public $module_name;

    private $content;
    private $reader;
    private $state;

    public function __construct(File $file, string $module_name, ReaderInterface $reader)
    {
        $this->file = $file;
        $this->module_name = $module_name;
        $this->reader = $reader;
        $this->state = new ContentState($this->file->extension);
    }

    public function getState(): ContentState
    {
        return $this->state;
    }

    public function getContent(): string
    {
        if (null === $this->content) {
            $this->content = $this->reader->read($this->file);
        }

        return $this->content;
    }

    public function transition(string $state, string $new_content = null, string $new_extension = null)
    {
        $this->state->transition($state, $new_extension);

        if (null !== $new_content) {
            $this->content = $new_content;
        }
    }
}
