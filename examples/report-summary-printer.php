<?php declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Amp\ByteStream\WritableStream;
use Cspray\Labrador\StyledByteStream\TerminalOutputStream;
use function Amp\ByteStream\getStdout;

function createSummaryPrinter(WritableStream $output) : object {
    return new class($output) {

        public function __construct(private WritableStream $output) {}

        public function writeReportResults(array $report) : void {
            $this->output->write($report['name'] . ' received');
        }

    };
}

$stream = new TerminalOutputStream(getStdout());

$successfulReportOutput = $stream->forceNewline()->green();
$failedReportOutput = $stream->forceNewline()->backgroundRed()->white()->bold();
$disabledReportOutput = $stream->forceNewline()->yellow()->underline();

$successfulReportPrinter = createSummaryPrinter($successfulReportOutput);
$failedReportPrinter = createSummaryPrinter($failedReportOutput);
$disabledReportPrinter = createSummaryPrinter($disabledReportOutput);

$successfulReportPrinter->writeReportResults(['name' => 'Foo Bar Report']);
$failedReportPrinter->writeReportResults(['name' => 'Bad Data Report']);
$disabledReportPrinter->writeReportResults(['name' => 'Old Legacy Report']);
