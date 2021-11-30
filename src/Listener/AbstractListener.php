<?php

declare(strict_types=1);

namespace Laminas\ModuleManager\Listener;

use Brick\VarExporter\ExportException;
use Brick\VarExporter\VarExporter;
use Laminas\ModuleManager\Listener\Exception\ConfigCannotBeCachedException;
use Webimpress\SafeWriter\FileWriter;

abstract class AbstractListener
{
    /** @var ListenerOptions */
    protected $options;

    public function __construct(?ListenerOptions $options = null)
    {
        $options = $options ?: new ListenerOptions();
        $this->setOptions($options);
    }

    public function getOptions(): ListenerOptions
    {
        return $this->options;
    }

    public function setOptions(ListenerOptions $options): AbstractListener
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Write a simple array of scalars to a file
     *
     * @param array $array
     */
    protected function writeArrayToFile(string $filePath, array $array): AbstractListener
    {
        try {
            $content = "<?php\n" . VarExporter::export(
                $array,
                VarExporter::ADD_RETURN | VarExporter::CLOSURE_SNAPSHOT_USES
            );
        } catch (ExportException $e) {
            throw ConfigCannotBeCachedException::fromExporterException($e);
        }

        FileWriter::writeFile($filePath, $content);

        return $this;
    }
}
