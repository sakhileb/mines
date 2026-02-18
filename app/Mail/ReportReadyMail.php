<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Report;

class ReportReadyMail extends Mailable
{
    use Queueable, SerializesModels;

    public $report;

    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    public function build()
    {
        $downloadUrl = $this->report->file_path ? url($this->report->file_path) : '#';

        return $this->subject('Your report is ready — ' . $this->report->title)
                    ->view('emails.report-ready')
                    ->with([
                        'report' => $this->report,
                        'downloadUrl' => $downloadUrl,
                    ]);
    }
}
