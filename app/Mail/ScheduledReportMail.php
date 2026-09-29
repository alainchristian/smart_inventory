<?php
namespace App\Mail;

use App\Services\Reports\ReportDocument;
use App\Services\Reports\ReportExporter;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * A scheduled custom report: headline figures and key findings in the
 * body, the full report attached (PDF or Excel).
 */
class ScheduledReportMail extends Mailable
{
    public function __construct(
        public ReportDocument $doc,
        public string $format,
        private string $file,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->doc->report->name . ' · ' . $this->doc->periodLabel);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.scheduled-report', with: [
            'doc'      => $this->doc,
            'findings' => ReportDocument::findings($this->doc->results),
            'url'      => route('owner.reports.custom.view', $this->doc->report->id),
            'tenant'   => config('tenant.name'),
            // not "format": the public $format property would override it in the view
            'attachedAs' => $this->format === 'xlsx' ? 'an Excel workbook' : 'a PDF',
        ]);
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->file, $this->doc->fileName($this->format))
                ->withMime(ReportExporter::FORMATS[$this->format]),
        ];
    }
}
