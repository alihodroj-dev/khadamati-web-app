<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\RequestDocument;
use App\Services\ServiceRequestQrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CitizenServiceController extends Controller
{
    public function __construct(
        private ServiceRequestQrCodeService $qrCodeService
    ) {}

    public function index(Request $request)
    {
        $query = Service::with(['category', 'office'])
            ->where('is_active', true);

        if ($request->filled('category_id')) {
            $query->where('service_category_id', $request->category_id);
        }

        if ($request->filled('office_id')) {
            $query->where('office_id', $request->office_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $services = $query->paginate(10);
        $categories = ServiceCategory::where('is_active', true)->get();
        $offices = Office::where('is_active', true)->get();

        return view('citizen.services.index', compact('services', 'categories', 'offices'));
    }

    public function show($id)
    {
        $service = Service::with(['category', 'office'])->findOrFail($id);
        
        return view('citizen.services.show', compact('service'));
    }

    public function createRequest($id)
    {
        $service = Service::with(['category', 'office'])->findOrFail($id);
        
        return view('citizen.services.request', compact('service'));
    }

    public function storeRequest(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        
        $request->validate([
            'citizen_notes' => ['nullable', 'string'],
        ]);

        DB::beginTransaction();

        try {
            // Create service request
            $serviceRequest = ServiceRequest::create([
                'user_id' => auth()->id(),
                'service_id' => $service->id,
                'office_id' => $service->office_id,
                'reference_number' => $this->generateReferenceNumber(),
                'tracking_token' => ServiceRequest::generateTrackingToken(),
                'status' => 'pending',
                'citizen_notes' => $request->citizen_notes,
                'submitted_data' => $request->except(['citizen_notes', '_token']),
                'submitted_at' => now(),
            ]);

            // Handle document uploads if any
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $documentType => $file) {
                    $path = $file->store('requests/' . $serviceRequest->id . '/documents', 'public');
                    
                    RequestDocument::create([
                        'service_request_id' => $serviceRequest->id,
                        'uploaded_by' => auth()->id(),
                        'source' => RequestDocument::SOURCE_CITIZEN,
                        'purpose' => RequestDocument::PURPOSE_REQUIREMENT,
                        'document_type' => $documentType,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'mime_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                        'status' => 'pending',
                    ]);
                }
            }

            // Generate QR code
            $this->qrCodeService->ensureStored($serviceRequest);

            DB::commit();

            return redirect()->route('citizen.requests.show', $serviceRequest->id)
                ->with('success', 'Service request submitted successfully! Your reference number is: ' . $serviceRequest->reference_number);
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to submit request: ' . $e->getMessage()]);
        }
    }

    private function generateReferenceNumber(): string
    {
        return 'SR-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }
}