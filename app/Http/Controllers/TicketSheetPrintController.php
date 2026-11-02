<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\TicketSheet;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class TicketSheetPrintController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $request->user()->activeTenantId();
        $defaults = TicketSheet::defaults($request->user()->tenant->name, Setting::current());
        $data = Validator::make(array_replace($defaults, $request->only(array_keys(TicketSheet::rules()))), TicketSheet::rules(), TicketSheet::messages())->validate();

        return response()->view('tickets.sheets-print', ['design' => TicketSheet::design($data)])
            ->withHeaders(['Cache-Control' => 'private, no-store, max-age=0', 'X-Robots-Tag' => 'noindex, nofollow']);
    }
}
