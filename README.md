# Styled ByteStream

A library for printing rich output to a terminal using [amphp/byte-stream](https://github.com/amphp/byte-stream)!

- Supports default foreground and background colors
  - black
  - blue
  - cyan
  - green
  - magenta
  - red
  - white
  - yellow
- Supports default formatting options
  - bold
  - underline
  - reverse
  - conceal
- Other helper methods designed to make terminal output easy to create
- An immutable fluent API for creating OutputStream with a specific style

## Installation

[Composer](https://getcomposer.org/) is the only supported way to install Styled ByteStream

```
composer require cspray/labrador-styled-byte-stream
```

## Documentation

Styled ByteStream provides a single `Amp\ByteStream\OutputStream` decorator; the `Cspray\Labrador\StyledByteStream\TerminalOutputStream`. 
This object provides an API for creating rich terminal output. This includes outputting content with new lines, changing 
the color of the text, and/or adding formatting options. All of these methods can be chained together to come up with 
exactly the right style of content for your Amp powered CLI app.

All examples below assume to be running in the following boilerplate:

```php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Amp\Loop;
use Cspray\Labrador\StyledByteStream\TerminalOutputStream;
use function Amp\ByteStream\getStdout;

Loop::run(function() {
    $stream = new TerminalOutputStream(getStdout());

    // The example code is expected to be executed here
});
```

### Write with new line

The `writeln(string $text)` method writes some text to the decorated `OutputStream` and appends a `PHP_EOL` on the end.

```php
yield $stream->writeln('This is content on a new line');
```

### Add multiple line breaks

The `br(int $count = 1)` method will write `$count` number of `PHP_EOL` to the decorated `OutputStream`.

```php
yield $stream->br(3); // adds 3 new lines to the terminal output
```

### Background Colors

For every supported color there is a corresponding background method. For example, `backgroundBlack()`, `blackgroundBlue()`, etc. 
These methods output text with the appropriate background.

```php
yield $stream->backgroundBlue('This has a blue background');
yield $stream->backgroundYellow('This has a yellow background');
```

### Foreground Colors

Every supported color is also a method that allows setting the foreground color. For example, `black()`, `blue`, etc. 
These methods output text with the appropriate text color.

```php
yield $stream->magenta('This is magenta text');
yield $stream->cyan('This is cyan text');
```

### Formatting Options

Every supported formatting option is also a method that allows changing the way the output text is displayed. For example, 
`bold()`, `underline()`, etc. These methods output text with the appropriate formatting.

```php
yield $stream->bold('This is bolded text');
yield $stream->underline('This text has an underline');
```

### Chaining styles

All color and formatting options can be chained together to compose exactly the style you need.

```php
yield $stream->backgroundWhite()->red('This has a white background and red text');
yield $stream->bold()->yellow()->underline()->backgroundRed('The order of the chaining does not matter');
```

### Chaining styles and new lines

By default, the color and formatting options will append a new line to the end of any text passed to them. Bypass this
default behavior by chaining the `inline()` method. The `inline()` method can only be chained, text cannot be passed to 
this method.

```php
yield $stream->inline()->bold()->red('This is inline bold red text');
yield $stream->bold()->red('... This is text with a new line at the end');
```

Alternatively, skip over automatically appended lines by chaining to the `write` method.

```php
yield $stream->bold()->red()->write('I am bold red inline text too!');
```

It is possible to _force_ new lines... even if the `write` method is being used. Control the number of new lines by 
passing an `int` to the `forceNewline()` method. The `forceNewline` method can only be chained, text cannot be passed to 
this method.

```php
yield $stream->bold()->red()->forceNewline(2)->write('I am bold red text with 2 new lines at the end');
```

### Using Immutability

Chaining method calls is a straightforward approach for outputting rich text. It is important to realize that the 
`TerminalOutputStream` is an immutable object; each call to a color or formatting option will create a new instance with 
the defined formatting. This design can be taken advantage of to quickly compose reusable "styles" and have dependencies 
only be aware of the underlying `Amp\ByteStream\OutputStream` interface.

Let's assume that an implementation similar to the following is provided. Our fictional application will run a series 
of reports that can be of a state _successful_, _failed_, or _disabled_. The CLI application running the reports should 
print out a summary of which reports were processed and style the reports differently based on its state.

> The below code examples are standalone and not intended to run in the example boilerplate

```php
<?php

use Amp\ByteStream\OutputStream;
use Amp\Promise;use function Amp\call;

class ReportSummaryPrinter {

    public function __construct(private OutputStream $output) {}

    public function writeReportResults(array $report) : Promise {
        return call(function() use($report) {
            yield $this->output->write($report['name'] . ' received');
        });    
    }

}
```

Use `TerminalOutputStream` to create decorated `OutputStream` so the `ReportSummaryPrinter` is not complicated with 
styling logic or knowledge of the `TerminalOutputStream`. Later on if a different `OutputStream` is required, for example 
to write the summary to a file, it will only be necessary to swap out the implementations.

```php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Amp\Loop;
use Cspray\Labrador\StyledByteStream\TerminalOutputStream;
use function Amp\ByteStream\getStdout;

Loop::run(function() {
    $stream = new TerminalOutputStream(getStdout());

    $successfulReportOutput = $stream->forceNewline()->green();
    $failedReportOutput = $stream->forceNewline()->backgroundRed()->white()->bold();
    $disabledReportOutput = $stream->forceNewline()->yellow()->underline();
    
    $successfulReportPrinter = new ReportSummaryPrinter($successfulReportOutput);
    $failedReportPrinter = new ReportSummaryPrinter($failedReportOutput);
    $disabledReportPrinter = new ReportSummaryPrinter($disabledReportOutput);
    
    yield $successfulReportPrinter->writeReportResults(['name' => 'Foo Bar Report']);
    yield $failedReportPrinter->writeReportResults(['name' => 'A failed report!']);
    yield $disabledReportPrinter->writeReportResults(['name' => 'We never ran this report...']);
});
```

> This is a working example! If you clone the repo and run `php examples/report-summary-printer.php` you'll 
> see the expected output in your terminal!

## Governance

All Labrador packages adhere to the rules laid out in the [Labrador Governance repo](https://github.com/labrador-kennel/governance)
