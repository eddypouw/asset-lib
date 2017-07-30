<?php
declare(strict_types=1);

namespace Hostnet\Component\Resolver\Transpile;

use Hostnet\Component\Resolver\Import\Dependency;
use Hostnet\Component\Resolver\Import\ImportFinderInterface;
use Hostnet\Component\Resolver\Import\Nodejs\FileResolver;

/**
 * Wrap a javascript file such that can be used as a module.
 */
class JsModuleWrapper implements JsModuleWrapperInterface
{
    private $import_finder;
    private $nodejs_resolver;

    public function __construct(ImportFinderInterface $import_finder, FileResolver $nodejs_resolver)
    {
        $this->import_finder = $import_finder;
        $this->nodejs_resolver = $nodejs_resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function wrapModule(string $file_name, string $alias, string $content): string
    {
        $file = $this->nodejs_resolver->asImport($file_name);

        return $this->formatJs(
            $alias,
            $this->import_finder->get($file->getImportedFile()),
            $content
        );
    }

    /**
     * @param string $name
     * @param Dependency[] $dependencies
     * @param string $content
     * @return string
     */
    private function formatJs(string $name, array $dependencies, string $content): string
    {
        $js = 'define("' . $name . '", ["require", "exports", "module"';
        $args = 'require, exports, module';

        // dependencies
        $i = 1;
        foreach ($dependencies as $imported_as => $import) {
            // do not include static resources
            if ($import->isStatic()) {
                continue;
            }

            $js .= ', "' . $imported_as . '"';
            $args .= ', arg_' . $i;

            $i++;
        }

        $js .= '], function (' . $args . ") {\n";
        $js .= $content;
        $js .= "\n});\n";

        return $js;
    }
}