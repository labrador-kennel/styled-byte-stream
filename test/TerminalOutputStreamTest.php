<?php declare(strict_types=1);

namespace Cspray\Labrador\StyledByteStream;

use Amp\ByteStream\OutputBuffer;
use Amp\Loop;
use PHPUnit\Framework\TestCase;

class TerminalOutputStreamTest extends TestCase {

    private OutputBuffer $buffer;

    private TerminalOutputStream $subject;

    public function setUp() : void {
        $this->buffer = new OutputBuffer();
        $this->subject = new TerminalOutputStream($this->buffer);
    }

    public function normalColorMap() {
        return [
            'black' => ['black', 0],
            'red' => ['red', 1],
            'green' => ['green', 2],
            'yellow' => ['yellow', 3],
            'blue' => ['blue', 4],
            'magenta' => ['magenta', 5],
            'cyan' => ['cyan', 6],
            'white' => ['white', 7]
        ];
    }

    /**
     * @dataProvider normalColorMap
     */
    public function testBackgroundColor(string $name, int $code) {
        Loop::run(function() use($name, $code) {
            $method = 'background' . ucfirst($name);
            yield $this->subject->$method('text');
            $this->buffer->end();

            $expected = "\033[4" . $code . "mtext\033[49m" . PHP_EOL;
            $actual = yield $this->buffer;
            $this->assertSame($expected, $actual);
        });
    }

    /**
     * @dataProvider normalColorMap
     */
    public function testForegroundColor(string $name, int $code) {
        Loop::run(function() use($name, $code) {
            yield $this->subject->$name('text');
            $this->buffer->end();

            $expected = "\033[3" . $code . "mtext\033[39m" . PHP_EOL;
            $actual = yield $this->buffer;
            $this->assertSame($expected, $actual);
        });
    }

    /**
     * @dataProvider normalColorMap
     */
    public function testBoldingColors(string $name, int $code) {
        Loop::run(function() use($name, $code) {
            yield $this->subject->bold()->$name()->write('text');
            $this->buffer->end();

            $expected = "\033[1;3" . $code . "mtext\033[22;39m";
            $actual = yield $this->buffer;
            $this->assertSame($expected, $actual);
        });
    }

    /**
     * @dataProvider normalColorMap
     */
    public function testUnderliningColors(string $name, int $code) {
        Loop::run(function() use($name, $code) {
            yield $this->subject->underline()->$name()->write('text');
            $this->buffer->end();

            $expected = "\033[4;3" . $code . "mtext\033[24;39m";
            $actual = yield $this->buffer;
            $this->assertSame($expected, $actual);
        });
    }

    /**
     * @dataProvider normalColorMap
     */
    public function testReversingColors(string $name, int $code) {
        Loop::run(function() use($name, $code) {
            yield $this->subject->reverse()->$name()->write('text');
            $this->buffer->end();

            $expected = "\033[7;3" . $code . "mtext\033[27;39m";
            $actual = yield $this->buffer;
            $this->assertSame($expected, $actual);
        });
    }

    /**
     * @dataProvider normalColorMap
     */
    public function testConcealingColors(string $name, int $code) {
        Loop::run(function() use($name, $code) {
            yield $this->subject->conceal()->$name()->write('text');
            $this->buffer->end();

            $expected = "\033[8;3" . $code . "mtext\033[28;39m";
            $actual = yield $this->buffer;
            $this->assertSame($expected, $actual);
        });
    }

    /**
     * @dataProvider normalColorMap
     */
    public function testForceLineWrite(string $name, int $code) {
        Loop::run(function() use($name, $code) {
            yield $this->subject->forceNewline(5)->$name()->write('foobar');
            $this->buffer->end();

            $expected = "\033[3" . $code . "mfoobar\033[39m" . str_repeat(PHP_EOL, 5);
            $actual = yield $this->buffer;
            $this->assertSame($expected, $actual);
        });
    }

    /**
     * @dataProvider normalColorMap
     */
    public function testForceLineWriteln(string $name, int $code) {
        Loop::run(function() use($name, $code) {
            yield $this->subject->forceNewline(5)->$name()->writeln('foobar');
            $this->buffer->end();

            $expected = "\033[3" . $code . "mfoobar\033[39m" . str_repeat(PHP_EOL, 6);
            $actual = yield $this->buffer;
            $this->assertSame($expected, $actual);
        });
    }

    public function chainedForeGroundAndBackground() {
        return [
            // White foreground
            'whiteOnBlack' => ['white', 'backgroundBlack', 7, 0],
            'whiteOnRed' => ['white', 'backgroundRed', 7, 1],
            'whiteOnGreen' => ['white', 'backgroundGreen', 7, 2],
            'whiteOnYellow' => ['white', 'backgroundYellow', 7, 3],
            'whiteOnBlue' => ['white', 'backgroundBlue', 7, 4],
            'whiteOnMagenta' => ['white', 'backgroundMagenta', 7, 5],
            'whiteOnCyan' => ['white', 'backgroundCyan', 7, 6],
            'whiteOnWhite' => ['white', 'backgroundWhite', 7, 7],

            // Cyan foreground
            'cyanOnBlack' => ['cyan', 'backgroundBlack', 6, 0],
            'cyanOnRed' => ['cyan', 'backgroundRed', 6, 1],
            'cyanOnGreen' => ['cyan', 'backgroundGreen', 6, 2],
            'cyanOnYellow' => ['cyan', 'backgroundYellow', 6, 3],
            'cyanOnBlue' => ['cyan', 'backgroundBlue', 6, 4],
            'cyanOnMagenta' => ['cyan', 'backgroundMagenta', 6, 5],
            'cyanOnCyan' => ['cyan', 'backgroundCyan', 6, 6],
            'cyanOnWhite' => ['cyan', 'backgroundWhite', 6, 7],

            // Magenta foreground
            'magentaOnBlack' => ['magenta', 'backgroundBlack', 5, 0],
            'magentaOnRed' => ['magenta', 'backgroundRed', 5, 1],
            'magentaOnGreen' => ['magenta', 'backgroundGreen', 5, 2],
            'magentaOnYellow' => ['magenta', 'backgroundYellow', 5, 3],
            'magentaOnBlue' => ['magenta', 'backgroundBlue', 5, 4],
            'magentaOnMagenta' => ['magenta', 'backgroundMagenta', 5, 5],
            'magentaOnCyan' => ['magenta', 'backgroundCyan', 5, 6],
            'magentaOnWhite' => ['magenta', 'backgroundWhite', 5, 7],

            // Blue foreground
            'blueOnBlack' => ['blue', 'backgroundBlack', 4, 0],
            'blueOnRed' => ['blue', 'backgroundRed', 4, 1],
            'blueOnGreen' => ['blue', 'backgroundGreen', 4, 2],
            'blueOnYellow' => ['blue', 'backgroundYellow', 4, 3],
            'blueOnBlue' => ['blue', 'backgroundBlue', 4, 4],
            'blueOnMagenta' => ['blue', 'backgroundMagenta', 4, 5],
            'blueOnCyan' => ['blue', 'backgroundCyan', 4, 6],
            'blueOnWhite' => ['blue', 'backgroundWhite', 4, 7],

            // Yellow foreground
            'yellowOnBlack' => ['yellow', 'backgroundBlack', 3, 0],
            'yellowOnRed' => ['yellow', 'backgroundRed', 3, 1],
            'yellowOnGreen' => ['yellow', 'backgroundGreen', 3, 2],
            'yellowOnYellow' => ['yellow', 'backgroundYellow', 3, 3],
            'yellowOnBlue' => ['yellow', 'backgroundBlue', 3, 4],
            'yellowOnMagenta' => ['yellow', 'backgroundMagenta', 3, 5],
            'yellowOnCyan' => ['yellow', 'backgroundCyan', 3, 6],
            'yellowOnWhite' => ['yellow', 'backgroundWhite', 3, 7],

            // Green foreground
            'greenOnBlack' => ['green', 'backgroundBlack', 2, 0],
            'greenOnRed' => ['green', 'backgroundRed', 2, 1],
            'greenOnGreen' => ['green', 'backgroundGreen', 2, 2],
            'greenOnYellow' => ['green', 'backgroundYellow', 2, 3],
            'greenOnBlue' => ['green', 'backgroundBlue', 2, 4],
            'greenOnMagenta' => ['green', 'backgroundMagenta', 2, 5],
            'greenOnCyan' => ['green', 'backgroundCyan', 2, 6],
            'greenOnWhite' => ['green', 'backgroundWhite', 2, 7],

            // Red foreground
            'redOnBlack' => ['red', 'backgroundBlack', 1, 0],
            'redOnRed' => ['red', 'backgroundRed', 1, 1],
            'redOnGreen' => ['red', 'backgroundGreen', 1, 2],
            'redOnYellow' => ['red', 'backgroundYellow', 1, 3],
            'redOnBlue' => ['red', 'backgroundBlue', 1, 4],
            'redOnMagenta' => ['red', 'backgroundMagenta', 1, 5],
            'redOnCyan' => ['red', 'backgroundCyan', 1, 6],
            'redOnWhite' => ['red', 'backgroundWhite', 1, 7],

            // Black foreground
            'blackOnBlack' => ['black', 'backgroundBlack', 0, 0],
            'blackOnRed' => ['black', 'backgroundRed', 0, 1],
            'blackOnGreen' => ['black', 'backgroundGreen', 0, 2],
            'blackOnYellow' => ['black', 'backgroundYellow', 0, 3],
            'blackOnBlue' => ['black', 'backgroundBlue', 0, 4],
            'blackOnMagenta' => ['black', 'backgroundMagenta', 0, 5],
            'blackOnCyan' => ['black', 'backgroundCyan', 0, 6],
            'blackOnWhite' => ['black', 'backgroundWhite', 0, 7],
        ];
    }

    /**
     * @dataProvider chainedForeGroundAndBackground
     */
    public function testChainingForegroundAndBackgroundWriteln(string $foreground, string $background, int $foregroundCode, int $backgroundCode) {
        Loop::run(function() use($foreground, $background, $foregroundCode, $backgroundCode) {
            yield $this->subject->$background()->$foreground()->writeln('text');
            $this->buffer->end();
            $expected = "\033[4" . $backgroundCode . ";3" . $foregroundCode . "mtext\033[49;39m" . PHP_EOL;
            $actual = yield $this->buffer;
            $this->assertSame($expected, $actual);
        });
    }

    /**
     * @dataProvider chainedForeGroundAndBackground
     */
    public function testChainingForegroundAndBackgroundWrite(string $foreground, string $background, int $foregroundCode, int $backgroundCode) {
        Loop::run(function() use($foreground, $background, $foregroundCode, $backgroundCode) {
            yield $this->subject->$background()->$foreground()->write('text');
            $this->buffer->end();
            $expected = "\033[4" . $backgroundCode . ";3" . $foregroundCode . "mtext\033[49;39m";
            $actual = yield $this->buffer;
            $this->assertSame($expected, $actual);
        });
    }

    /**
     * @dataProvider chainedForeGroundAndBackground
     */
    public function testChainingForeGroundAndBackgroundEndsOnForeGround(string $foreground, string $background, int $foregroundCode, int $backgroundCode) {
        Loop::run(function() use($foreground, $background, $foregroundCode, $backgroundCode) {
            yield $this->subject->$background()->$foreground('text');
            $this->buffer->end();
            $expected = "\033[4" . $backgroundCode . ";3" . $foregroundCode . "mtext\033[49;39m" . PHP_EOL;
            $actual = yield $this->buffer;
            $this->assertSame($expected, $actual);
        });
    }

    /**
     * @dataProvider chainedForeGroundAndBackground
     */
    public function testChainingForeGroundAndBackgroundEndsOnBackGround(string $foreground, string $background, int $foregroundCode, int $backgroundCode) {
        Loop::run(function() use($foreground, $background, $foregroundCode, $backgroundCode) {
            yield $this->subject->$foreground()->$background('text');
            $this->buffer->end();
            $expected = "\033[3" . $foregroundCode . ";4" . $backgroundCode . "mtext\033[39;49m" . PHP_EOL;
            $actual = yield $this->buffer;
            $this->assertSame($expected, $actual);
        });
    }

    /**
     * @dataProvider chainedForeGroundAndBackground
     */
    public function testChainedForegroundAndBackgroundInlineEndOnBackground(string $foreground, string $background, int $foregroundCode, int $backgroundCode) {
        Loop::run(function() use($foreground, $background, $foregroundCode, $backgroundCode) {
            yield $this->subject->$foreground()->inline()->$background('text');
            $this->buffer->end();
            $expected = "\033[3" . $foregroundCode . ";4" . $backgroundCode . "mtext\033[39;49m";
            $actual = yield $this->buffer;
            $this->assertSame($expected, $actual);
        });
    }

    /**
     * @dataProvider chainedForeGroundAndBackground
     */
    public function testChainedForegroundAndBackgroundInlineEndOnForeground(string $foreground, string $background, int $foregroundCode, int $backgroundCode) {
        Loop::run(function() use($foreground, $background, $foregroundCode, $backgroundCode) {
            yield $this->subject->$background()->inline()->$foreground('text');
            $this->buffer->end();
            $expected = "\033[4" . $backgroundCode . ";3" . $foregroundCode . "mtext\033[49;39m";
            $actual = yield $this->buffer;
            $this->assertSame($expected, $actual);
        });
    }

    public function formattingOptionsMap() {
        return [
            'bold' => ['bold', 1, 22],
            'underline' => ['underline', 4, 24],
            'reverse' => ['reverse', 7, 27],
            'conceal' => ['conceal', 8, 28]
        ];
    }

    /**
     * @dataProvider formattingOptionsMap
     */
    public function testFormatting(string $method, int $set, int $unset) {
        Loop::run(function() use($method, $set, $unset) {
            yield $this->subject->$method('foo');
            $this->buffer->end();

            $expected = sprintf(
                "\033[%smfoo\033[%sm" . PHP_EOL,
                $set,
                $unset
            );
            $actual = yield $this->buffer;
            $this->assertSame($expected, $actual);
        });
    }

    /**
     * @dataProvider formattingOptionsMap
     */
    public function testInlineFormatting(string $method, int $set, int $unset) {
        Loop::run(function() use($method, $set, $unset) {
            yield $this->subject->inline()->$method('foo');
            $this->buffer->end();

            $expected = sprintf(
                "\033[%smfoo\033[%sm",
                $set,
                $unset
            );
            $actual = yield $this->buffer;
            $this->assertSame($expected, $actual);
        });
    }

    public function testChainedAllFormatting() {
        Loop::run(function() {
            yield $this->subject->bold()->underline()->reverse()->conceal('foobar');
            $this->buffer->end();
            $expected = "\033[1;4;7;8mfoobar\033[22;24;27;28m" . PHP_EOL;
            $actual = yield $this->buffer;
            $this->assertSame($expected, $actual);
        });
    }

    public function testWriteLn() {
        Loop::run(function() {
            yield $this->subject->writeln('AsyncUnit');
            $this->buffer->end();

            $expected = 'AsyncUnit' . PHP_EOL;
            $actual = yield $this->buffer;
            $this->assertSame($expected, $actual);
        });
    }

    public function testBrNoArgs() {
        Loop::run(function() {
            yield $this->subject->br();
            $this->buffer->end();

            $this->assertSame(PHP_EOL, yield $this->buffer);
        });
    }

    public function testBrArgs() {
        Loop::run(function() {
            $randomInt = random_int(1, 20);
            yield $this->subject->br($randomInt);
            $this->buffer->end();

            $this->assertSame(str_repeat(PHP_EOL, $randomInt), yield $this->buffer);
        });
    }

    public function testEnding() {
        Loop::run(function() {
            yield $this->subject->end('the end');

            $this->assertSame('the end', yield $this->buffer);
        });
    }

}