<?php
/*
 * This file is part of PHP Selenium Library.
 * (c) Alexandre Salomé <alexandre.salome@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Selenium\Specification\Loader;

use Selenium\Specification\Specification;
use Selenium\Specification\Method;
use Selenium\Specification\Parameter;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Loads a XML file into a specification object.
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
class XmlLoader
{
    /**
     * @var Selenium\Specification\Specification
     */
    protected $specification;

    public function __construct(Specification $specification)
    {
        $this->specification = $specification;
    }

    /**
     * Loads the specification from a XML file
     *
     * @param string $file Path to the file to load
     */
    public function load($file)
    {
        if (!file_exists($file)) {
            throw new \RuntimeException(sprintf('The file "%s" does not exists', $file));
        }

        $content = file_get_contents($file);

        // HACK: DOMNode seems to bug when a node is named "param"
        $content = str_replace('<param', '<parameter', $content);
        $content = str_replace('</param', '</parameter', $content);

        $crawler = new Crawler();
        $crawler->addContent($content, 'xml');

        foreach ($crawler->filterXPath('//function') as $node) {
            $method = $this->getMethod($node);
            $this->specification->addMethod($method);
        }
    }

    /**
     * Returns a method in the current specification from a DOMNode
     *
     * @param DOMNode $node A DOMNode
     *
     * @return Selenium\Specification\Method
     */
    public function getMethod(\DOMNode $node)
    {
        $crawler = new Crawler($node);
        $name    = $crawler->attr('name');

        // Initialize
        $method  = new Method($name);

        // Type
        $method->setType(
            preg_match('/(^(get|is)|ToString$)/', $name) ?
            Method::TYPE_ACCESSOR :
            Method::TYPE_ACTION
        );

        // Description
        $descriptions = $crawler->filterXPath('//comment');
        if (count($descriptions) !== 1) {
            throw new \Exception('Only one comment expected');
        }
        $descriptions->rewind();
        $description = $this->getInner($descriptions->current());
        $method->setDescription($description);

        // Parameters
        foreach ($crawler->filterXPath('//parameter') as $node) {
            $method->addParameter($this->getParameter($node));
        }

        // Return
        $returnNodes = $crawler->filterXPath('//return');

        if (count($returnNodes) > 1) {
            throw new \Exception("Should not be more than one return node");
        } elseif (count($returnNodes) == 1) {
            $returnNodes->rewind();
            list($type, $description) = $this->getReturn($returnNodes->current());

            $method->setReturnType($type);
            $method->setReturnDescription($description);
        }

        return $method;
    }

    /**
     * Get return informations (type, description) from a DOMNode
     *
     * @param DOMNode $node The DOMNode to parse
     *
     * @return array First element is the type, second the description
     */
    protected function getReturn(\DOMNode $node)
    {
        $crawler = new Crawler($node);
        $type = $crawler->attr('type');
        $description = $this->getInner($node);

        return array($type, $description);

    }

    /**
     * Get a parameter model object from a DOMNode
     *
     * @param DOMNode $node A DOMNode to convert to specification parameter
     *
     * @return Selenium\Specification\Parameter The parameter model object
     */
    protected function getParameter(\DOMNode $node)
    {
        $name = $node->getAttribute('name');

        $parameter = new Parameter($name);
        $parameter->setDescription($this->getInner($node));

        return $parameter;
    }

    /**
     * Get the inner content of a DOMNode.
     *
     * @param DOMNode $node A DOMNode instance
     *
     * @return string The inner content
     */
    protected function getInner(\DOMNode $node)
    {
        $c14n = $node->C14N();
        $begin = strpos($c14n, '>');
        $end   = strrpos($c14n, '<');

        $content = substr($c14n, $begin + 1, $end - $begin - 1);

        return $content;
    }
}
