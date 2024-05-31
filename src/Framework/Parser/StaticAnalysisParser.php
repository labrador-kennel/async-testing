<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Parser;

use Amp\ByteStream\Payload;
use Amp\File\Filesystem;
use Labrador\AsyncUnit\Framework\ImplicitTestSuite;
use Labrador\AsyncUnit\Framework\Model\TestSuiteModel;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\NodeConnectingVisitor;
use PhpParser\Parser as PhpParser;
use PhpParser\ParserFactory;
use function Amp\File\filesystem;

/**
 * Responsible for iterating over a directory of PHP source code, analyzing it for code annotated with AysncUnit
 * Attributes, and converting them into the appropriate AsyncUnit Model.
 *
 * @package Labrador\AsyncUnit\Framework
 * @see AsyncUnitModelNodeVisitor
 */
final class StaticAnalysisParser implements Parser {

    use AttributeGroupTraverser;

    private PhpParser $phpParser;
    private NodeTraverser $nodeTraverser;
    private Filesystem $filesystem;

    public function __construct() {
        $this->phpParser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        $this->nodeTraverser = new NodeTraverser();
        $this->filesystem = filesystem();
    }

    public function parse(string|array $dirs) : ParserResult {
        $dirs = is_string($dirs) ? [$dirs] : $dirs;

        $collector = new AsyncUnitModelCollector();
        $nodeConnectingVisitor = new NodeConnectingVisitor();
        $nameResolver = new NameResolver();
        $asyncUnitVisitor = new AsyncUnitModelNodeVisitor($collector);

        $this->nodeTraverser->addVisitor($nameResolver);
        $this->nodeTraverser->addVisitor($nodeConnectingVisitor);
        $this->nodeTraverser->addVisitor($asyncUnitVisitor);

        foreach ($dirs as $dir) {
            $this->traverseDir($dir);
        }

        if (!$collector->hasDefaultTestSuite()) {
            $collector->attachTestSuite(new TestSuiteModel(ImplicitTestSuite::class, true));
        }
        $collector->finishedCollection();

        return new ParserResult($collector);
    }

    private function traverseDir(string $dir) : void {
        $files = $this->filesystem->listFiles($dir);

        foreach ($files as $fileOrDir) {
            $fullPath = $dir . '/' . $fileOrDir;
            if ($this->filesystem->isDirectory($fullPath)) {
                $this->traverseDir($fullPath);
            } else {
                $pathFragments = explode(DIRECTORY_SEPARATOR, $fullPath);
                $lastPathFragment = array_pop($pathFragments);
                if (!strpos($lastPathFragment, '.')) {  // intentionally treating 0 as false because a hidden file shouldn't be tested
                    continue;
                }
                $extension = strtolower(explode('.', $lastPathFragment, 2)[1]);
                if ($extension !== 'php') {
                    continue;
                }

                $handle = $this->filesystem->openFile($fullPath, 'r');
                $statements = $this->phpParser->parse($handle->read());
                $this->nodeTraverser->traverse($statements);
                $handle->close();

                unset($handle, $contents);
            }
        }
    }

}