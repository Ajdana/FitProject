<?php

namespace App\Http\Controllers\Api;

use App\Models\ScanHistory;
use App\Repositories\ScanHistoryRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\ScanHistoryResource;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\ScanHistoryIndexRequest;

class ScanHistoryController extends Controller
{
    use AuthorizesRequests;

    protected ScanHistoryRepository $repo;

    public function __construct(ScanHistoryRepository $repo)
    {
        $this->repo = $repo;
    }

    public function index(ScanHistoryIndexRequest $request)
    {
        $userId = auth()->id();
        $user = auth()->user();
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        Log::info('ScanHistory index request', [
            'user_id' => $userId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);

        // ADMIN
        if ($user->can('scan.read.all')) {
            $scans = $this->repo->forAdmin($dateFrom, $dateTo);

            Log::info('ScanHistory index returned paginated for admin', [
                'count' => $scans->count()
            ]);

            return ScanHistoryResource::collection($scans);
        }


        // USER
        $scans = $this->repo->forUser(auth()->id(), $dateFrom, $dateTo);

        Log::info('ScanHistory index returned for user', [
            'count' => $scans->count()
        ]);

        return ScanHistoryResource::collection($scans);
    }

    public function show(ScanHistory $scan)
    {
        Log::info('ScanHistory show request', [
            'scan_id' => $scan->id,
            'user_id' => auth()->id()
        ]);

        try {
            $this->authorize('view', $scan);
            return new ScanHistoryResource($scan);
        } catch (\Throwable $e) {
            Log::error('ScanHistory show failed', [
                'scan_id' => $scan->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function destroy(ScanHistory $scan)
    {
        Log::info('ScanHistory delete request', [
            'scan_id' => $scan->id,
            'user_id' => auth()->id()
        ]);

        try {
            $this->authorize('delete', $scan);

            Storage::disk('public')->delete($scan->image);
            $this->repo->delete($scan);

            Log::info('ScanHistory deleted', [
                'scan_id' => $scan->id
            ]);

            return response()->json(['message' => 'Scan deleted'], 200);

        } catch (\Throwable $e) {
            Log::error('ScanHistory delete failed', [
                'scan_id' => $scan->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
