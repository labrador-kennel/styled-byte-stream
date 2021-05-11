<?php declare(strict_types=1);

namespace Cspray\Labrador\StyledByteStream;

use Amp\ByteStream\OutputStream;
use Amp\Promise;

final class TerminalOutputStream implements OutputStream {

    private const FOREGROUND_UNSET_CODE = '39';
    private const BACKGROUND_UNSET_CODE = '49';

    private const BACKGROUND_BLACK_SET_CODE = '40';
    private const BLACK_SET_CODE = '30';

    private const BACKGROUND_RED_SET_CODE = '41';
    private const RED_SET_CODE = '31';

    private const BACKGROUND_GREEN_SET_CODE = '42';
    private const GREEN_SET_CODE = '32';

    private const BACKGROUND_YELLOW_SET_CODE = '43';
    private const YELLOW_SET_CODE = '33';

    private const BACKGROUND_BLUE_SET_CODE = '44';
    private const BLUE_SET_CODE = '34';

    private const BACKGROUND_MAGENTA_SET_CODE = '45';
    private const MAGENTA_SET_CODE = '35';

    private const BACKGROUND_CYAN_SET_CODE = '46';
    private const CYAN_SET_CODE = '36';

    private const BACKGROUND_WHITE_SET_CODE = '47';
    private const WHITE_SET_CODE = '37';

    private const BOLD_SET_CODE = '1';
    private const BOLD_UNSET_CODE = '22';

    private const UNDERLINE_SET_CODE = '4';
    private const UNDERLINE_UNSET_CODE = '24';

    private const REVERSE_SET_CODE = '7';
    private const REVERSE_UNSET_CODE = '27';

    private const CONCEAL_SET_CODE = '8';
    private const CONCEAL_UNSET_CODE = '28';

    private array $set = [];
    private array $unset = [];

    private bool $inline = false;
    private int $forceLineCount = 0;

    public function __construct(private OutputStream $output) {}

    public function write(string $data) : Promise {
        return $this->doWrite($data);
    }

    public function end(string $finalData = "") : Promise {
        return $this->output->end($finalData);
    }

    public function writeln(string $data) : Promise {
        return $this->doWriteLn($data);
    }

    public function br(int $count = 1) : Promise {
        return $this->write(str_repeat(PHP_EOL, $count));
    }

    public function inline() : TerminalOutputStream {
        $newStream = clone $this;
        $newStream->inline = true;
        return $newStream;
    }

    public function forceNewline(int $count = 1) : TerminalOutputStream {
        $newStream = clone $this;
        $newStream->forceLineCount = $count;
        return $newStream;
    }

    public function backgroundBlack(string $text = null) : Promise|TerminalOutputStream {
        return $this->handle($text, self::BACKGROUND_BLACK_SET_CODE, self::BACKGROUND_UNSET_CODE);
    }

    public function black(string $text = null) : Promise|TerminalOutputStream {
        return $this->handle($text, self::BLACK_SET_CODE, self::FOREGROUND_UNSET_CODE);
    }

    public function backgroundRed(string $text = null) : Promise|TerminalOutputStream {
        return $this->handle($text, self::BACKGROUND_RED_SET_CODE, self::BACKGROUND_UNSET_CODE);
    }

    public function red(string $text = null) : Promise|TerminalOutputStream {
        return $this->handle($text, self::RED_SET_CODE, self::FOREGROUND_UNSET_CODE);
    }

    public function backgroundGreen(string $text = null) : Promise|TerminalOutputStream {
        return $this->handle($text, self::BACKGROUND_GREEN_SET_CODE, self::BACKGROUND_UNSET_CODE);
    }

    public function green(string $text = null) : Promise|TerminalOutputStream {
        return $this->handle($text, self::GREEN_SET_CODE, self::FOREGROUND_UNSET_CODE);
    }

    public function backgroundYellow(string $text = null) : Promise|TerminalOutputStream {
        return $this->handle($text, self::BACKGROUND_YELLOW_SET_CODE, self::BACKGROUND_UNSET_CODE);
    }

    public function yellow(string $text = null) : Promise|TerminalOutputStream {
        return $this->handle($text, self::YELLOW_SET_CODE, self::FOREGROUND_UNSET_CODE);
    }

    public function backgroundBlue(string $text = null) : Promise|TerminalOutputStream {
        return $this->handle($text, self::BACKGROUND_BLUE_SET_CODE, self::BACKGROUND_UNSET_CODE);
    }

    public function blue(string $text = null) : Promise|TerminalOutputStream {
        return $this->handle($text, self::BLUE_SET_CODE, self::FOREGROUND_UNSET_CODE);
    }

    public function backgroundMagenta(string $text = null) : Promise|TerminalOutputStream {
        return $this->handle($text, self::BACKGROUND_MAGENTA_SET_CODE, self::BACKGROUND_UNSET_CODE);
    }

    public function magenta(string $text = null) : Promise|TerminalOutputStream {
        return $this->handle($text, self::MAGENTA_SET_CODE, self::FOREGROUND_UNSET_CODE);
    }

    public function backgroundCyan(string $text = null) : Promise|TerminalOutputStream {
        return $this->handle($text, self::BACKGROUND_CYAN_SET_CODE, self::BACKGROUND_UNSET_CODE);
    }

    public function cyan(string $text = null) : Promise|TerminalOutputStream {
        return $this->handle($text, self::CYAN_SET_CODE, self::FOREGROUND_UNSET_CODE);
    }

    public function backgroundWhite(string $text = null) : Promise|TerminalOutputStream {
        return $this->handle($text, self::BACKGROUND_WHITE_SET_CODE, self::BACKGROUND_UNSET_CODE);
    }

    public function white(string $text = null) : Promise|TerminalOutputStream {
        return $this->handle($text, self::WHITE_SET_CODE, self::FOREGROUND_UNSET_CODE);
    }

    public function bold(string $text = null) : Promise|TerminalOutputStream {
        return $this->handle($text, self::BOLD_SET_CODE, self::BOLD_UNSET_CODE);
    }

    public function underline(string $text = null) : Promise|TerminalOutputStream {
        return $this->handle($text, self::UNDERLINE_SET_CODE, self::UNDERLINE_UNSET_CODE);
    }

    public function reverse(string $text = null) : Promise|TerminalOutputStream {
        return $this->handle($text, self::REVERSE_SET_CODE, self::REVERSE_UNSET_CODE);
    }

    public function conceal(string $text = null) : Promise|TerminalOutputStream {
        return $this->handle($text, self::CONCEAL_SET_CODE, self::CONCEAL_UNSET_CODE);
    }

    private function handle(?string $text, string $setCode, string $unsetCode) : Promise|TerminalOutputStream {
        if (is_null($text)) {
            $newStream = clone $this;
            $newStream->set[] = $setCode;
            $newStream->unset[] = $unsetCode;
            return $newStream;
        } else {
            $method = $this->inline ? 'doWrite' : 'doWriteln';
            return $this->$method($text, [$setCode], [$unsetCode]);
        }
    }

    private function doWriteLn(string $text, array $set = [], array $unset = []) : Promise {
        return $this->output->write($this->getFormattedText($text, $set, $unset, PHP_EOL));
    }

    private function doWrite(string $text, array $set = [], array $unset = []) : Promise {
        return $this->output->write($this->getFormattedText($text, $set, $unset));
    }

    private function getFormattedText(string $text, array $set, array $unset, string $suffix = '') : string {
        $finalSet = array_merge([], $this->set, $set);
        $finalUnset = array_merge([], $this->unset, $unset);
        if ($this->forceLineCount > 0) {
            $suffix .= str_repeat(PHP_EOL, $this->forceLineCount);
        }
        if (empty($finalSet)) {
            return $text . $suffix;
        } else {
            return sprintf(
                "\033[%sm%s\033[%sm%s",
                implode(';', $finalSet),
                $text,
                implode(';', $finalUnset),
                $suffix
            );
        }
    }

}