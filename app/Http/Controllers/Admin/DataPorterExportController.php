<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\DataPorter\DataPorter;
use Illuminate\Http\Request;

class DataPorterExportController extends Controller
{
    public function __construct(
        private readonly DataPorter $porter
    ) {
    }

    public function __invoke(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 403);

        $dataset = (string) $request->query('dataset', '');
        $format = (string) $request->query('format', 'csv');

        $filters = $request->except(['dataset', 'format', 'signature', 'expires', 'locale']);

        return $this->porter->stream($dataset, $format, $filters, $user);
    }
}

