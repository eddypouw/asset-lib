<?php
namespace Hostnet\Component\Resolver\Transform\BuildIn;

use Hostnet\Component\Resolver\Import\ImportInterface;
use Hostnet\Component\Resolver\Transform\ContentTransformerInterface;

class AngularHtmlTransformer implements ContentTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(ImportInterface $file): bool
    {
        return $file->getExtension() === 'ts' && 1 === preg_match('/\.component\.ts$/', $file->getPath());
    }

    /**
     * {@inheritdoc}
     */
    public function transform(ImportInterface $file, string $content, string $output_dir): string
    {
        $content = preg_replace_callback('/templateUrl\s*:(\s*[\'"`](.*?)[\'"`]\s*)/m', function ($match) use ($file, $output_dir) {
            $file_path = $match[2];

            if ($file_path[0] === '.') {
                $file_path = substr($file->getDirectory(), strpos($file->getDirectory(), '/') + 1) . substr($file_path, 1);
            }

            return 'templateUrl: "' . $output_dir . '/' . $file_path . '"';
        }, $content);
        $content = preg_replace_callback('/styleUrls *:(\s*\[[^\]]*?\])/', function ($match) use ($file, $output_dir) {
            $urls = [];

            if (preg_match_all('/([\'`"])((?:[^\\\\]\\\\\1|.)*?)\1/', $match[1], $inner_matches) > 0) {
                foreach ($inner_matches[2] as $inner_match) {
                    $file_path = $inner_match;

                    if ($file_path[0] === '.') {
                        $file_path = substr($file->getDirectory(), strpos($file->getDirectory(), '/') + 1) . substr($file_path, 1);
                    }

                    $file_path = dirname($file_path) . '/' . substr(basename($file_path), 0, strrpos(basename($file_path), '.')) . '.css';

                    $urls[] = '"' . $output_dir . '/' . $file_path . '"';
                }
            }

            return 'styleUrls: [' . implode(', ', $urls) . ']';
        }, $content);

        return $content;
    }
}