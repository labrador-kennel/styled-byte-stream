<?php declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Amp\ByteStream\OutputStream;
use Amp\Loop;
use Amp\Promise;
use Cspray\Labrador\StyledByteStream\TerminalOutputStream;
use function Amp\ByteStream\getStdout;
use function Amp\call;

class ReportSummaryPrinter {

    public function __construct(private OutputStream $output) {}

    public function writeReportResults(array $report) : Promise {
        return call(function() use($report) {
            yield $this->output->write($report['name'] . ' received');
        });
    }

}

Loop::run(function() {
    $stream = new TerminalOutputStream(getStdout());

    $successfulReportOutput = $stream->forceNewline()->green();
    $failedReportOutput = $stream->forceNewline()->backgroundRed()->white()->bold();
    $disabledReportOutput = $stream->forceNewline()->yellow()->underline();

    $successfulReportPrinter = new ReportSummaryPrinter($successfulReportOutput);
    $failedReportPrinter = new ReportSummaryPrinter($failedReportOutput);
    $disabledReportPrinter = new ReportSummaryPrinter($disabledReportOutput);

    yield $successfulReportPrinter->writeReportResults(['name' => 'Foo Bar Report']);
    yield $failedReportPrinter->writeReportResults(['name' => 'Bad Data Report']);
    yield $disabledReportPrinter->writeReportResults(['name' => 'Old Legacy Report']);
});