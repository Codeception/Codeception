<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Component\Console\Tests\Formatter;

use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class FormatterStyleTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyTag()
    {
        $formatter = new OutputFormatter(true);
        $this->assertEquals("foo<>bar", $formatter->format('foo<>bar'));
    }

    public function testLGCharEscaping()
    {
        $formatter = new OutputFormatter(true);

        $this->assertEquals("foo<bar", $formatter->format('foo\\<bar'));
        $this->assertEquals("<info>some info</info>", $formatter->format('\\<info>some info\\</info>'));
        $this->assertEquals("\\<info>some info\\</info>", OutputFormatter::escape('<info>some info</info>'));

        $this->assertEquals(
            "\033[33mSymfony\\Component\\Console does work very well!\033[0m",
            $formatter->format('<comment>Symfony\Component\Console does work very well!</comment>')
        );
    }

    public function testBundledStyles()
    {
        $formatter = new OutputFormatter(true);

        $this->assertTrue($formatter->hasStyle('error'));
        $this->assertTrue($formatter->hasStyle('info'));
        $this->assertTrue($formatter->hasStyle('comment'));
        $this->assertTrue($formatter->hasStyle('question'));

        $this->assertEquals(
            "\033[37;41msome error\033[0m",
            $formatter->format('<error>some error</error>')
        );
        $this->assertEquals(
            "\033[32msome info\033[0m",
            $formatter->format('<info>some info</info>')
        );
        $this->assertEquals(
            "\033[33msome comment\033[0m",
            $formatter->format('<comment>some comment</comment>')
        );
        $this->assertEquals(
            "\033[30;46msome question\033[0m",
            $formatter->format('<question>some question</question>')
        );
    }

    public function testNestedStyles()
    {
        $formatter = new OutputFormatter(true);

        $this->assertEquals(
            "\033[37;41msome \033[0m\033[32msome info\033[0m\033[37;41m error\033[0m",
            $formatter->format('<error>some <info>some info</info> error</error>')
        );
    }

    public function testDeepNestedStyles()
    {
        $formatter = new OutputFormatter(true);

        $this->assertEquals(
            "\033[37;41merror\033[0m\033[32minfo\033[0m\033[33mcomment\033[0m\033[37;41merror\033[0m",
            $formatter->format('<error>error<info>info<comment>comment</info>error</error>')
        );
    }

    public function testNewStyle()
    {
        $formatter = new OutputFormatter(true);

        $style = new OutputFormatterStyle('blue', 'white');
        $formatter->setStyle('test', $style);

        $this->assertEquals($style, $formatter->getStyle('test'));
        $this->assertNotEquals($style, $formatter->getStyle('info'));

        $this->assertEquals("\033[34;47msome custom msg\033[0m", $formatter->format('<test>some custom msg</test>'));
    }

    public function testRedefineStyle()
    {
        $formatter = new OutputFormatter(true);

        $style = new OutputFormatterStyle('blue', 'white');
        $formatter->setStyle('info', $style);

        $this->assertEquals("\033[34;47msome custom msg\033[0m", $formatter->format('<info>some custom msg</info>'));
    }

    public function testInlineStyle()
    {
        $formatter = new OutputFormatter(true);

        $this->assertEquals("\033[34;41msome text\033[0m", $formatter->format('<fg=blue;bg=red>some text</>'));
        $this->assertEquals("\033[34;41msome text\033[0m", $formatter->format('<fg=blue;bg=red>some text</fg=blue;bg=red>'));
    }

    public function testNonStyleTag()
    {
        $formatter = new OutputFormatter(true);
        $this->assertEquals("\033[32msome \033[0m\033[32m<tag> styled\033[0m", $formatter->format('<info>some <tag> styled</info>'));
    }

    public function testNotDecoratedFormatter()
    {
        $formatter = new OutputFormatter(false);

        $this->assertTrue($formatter->hasStyle('error'));
        $this->assertTrue($formatter->hasStyle('info'));
        $this->assertTrue($formatter->hasStyle('comment'));
        $this->assertTrue($formatter->hasStyle('question'));

        $this->assertEquals(
            "some error", $formatter->format('<error>some error</error>')
        );
        $this->assertEquals(
            "some info", $formatter->format('<info>some info</info>')
        );
        $this->assertEquals(
            "some comment", $formatter->format('<comment>some comment</comment>')
        );
        $this->assertEquals(
            "some question", $formatter->format('<question>some question</question>')
        );

        $formatter->setDecorated(true);

        $this->assertEquals(
            "\033[37;41msome error\033[0m", $formatter->format('<error>some error</error>')
        );
        $this->assertEquals(
            "\033[32msome info\033[0m", $formatter->format('<info>some info</info>')
        );
        $this->assertEquals(
            "\033[33msome comment\033[0m", $formatter->format('<comment>some comment</comment>')
        );
        $this->assertEquals(
            "\033[30;46msome question\033[0m", $formatter->format('<question>some question</question>')
        );
    }

    public function testContentWithLineBreaks()
    {
        $formatter = new OutputFormatter(true);

        $this->assertEquals(<<<EOF
\033[32m
some text\033[0m
EOF
            , $formatter->format(<<<EOF
<info>
some text</info>
EOF
        ));

        $this->assertEquals(<<<EOF
\033[32msome text
\033[0m
EOF
            , $formatter->format(<<<EOF
<info>some text
</info>
EOF
        ));

        $this->assertEquals(<<<EOF
\033[32m
some text
\033[0m
EOF
            , $formatter->format(<<<EOF
<info>
some text
</info>
EOF
        ));

        $this->assertEquals(<<<EOF
\033[32m
some text
more text
\033[0m
EOF
            , $formatter->format(<<<EOF
<info>
some text
more text
</info>
EOF
        ));
    }
}
