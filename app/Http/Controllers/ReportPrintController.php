<?php

namespace App\Http\Controllers;

use App\ActivityReport;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportPrintController extends Controller
{
    public function __invoke(Request $request, ActivityReport $builder): Response|StreamedResponse
    {
        $request->user()->activeTenantId();
        $data = $request->validate([...ActivityReport::rules(), 'format' => ['sometimes', 'string', 'in:print,csv']], ActivityReport::messages());
        $report = $builder->build($data['startDate'], $data['endDate']);
        if (($data['format'] ?? 'print') === 'csv') {
            return response()->streamDownload(function () use ($report, $data): void {
                $stream = fopen('php://output', 'w');
                fwrite($stream, "\xEF\xBB\xBF");
                fputcsv($stream, ['Du', 'Au', 'Client', 'Référence', 'Téléphone', 'Versements actifs', 'Encaissé GNF', 'Versements annulés', 'Montant annulé GNF', 'Cycles clôturés', 'Commissions prévues GNF', 'Journées impayées', 'Retards GNF (état actuel)'], ';', '"', '');
                foreach ($report['clients'] as $row) {
                    $values = [$data['startDate'], $data['endDate'], $row['name'], $row['reference'], $row['phone'] ?? '', $row['payments'], $row['paid'], $row['cancelledPayments'], $row['cancelled'], $row['closedCycles'], $row['plannedCommission'], $row['overdueDays'], $row['overdueAmount']];
                    fputcsv($stream, array_map(fn (string|int $value): string => $this->csvCell($value), $values), ';', '"', '');
                }
                fclose($stream);
            }, 'rapport-'.$data['startDate'].'-'.$data['endDate'].'.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'private, no-store', 'X-Robots-Tag' => 'noindex, nofollow']);
        }

        return response()->view('reports.print', ['settings' => Setting::current(), 'report' => $report, 'startDate' => $data['startDate'], 'endDate' => $data['endDate'], 'tenant' => $request->user()->tenant])
            ->withHeaders(['Cache-Control' => 'private, no-store', 'X-Robots-Tag' => 'noindex, nofollow']);
    }

    private function csvCell(string|int $value): string
    {
        $value = (string) $value;

        return preg_match('/^[\x00-\x20]*[=+@-]/', $value) ? "'".$value : $value;
    }
}
