<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FiscalReceiptController extends Controller
{
    protected $pricingService;

    public function __construct(PricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    /**
     * Check if user is super admin
     * Verifies that user is authenticated and has SUPER_ADMIN role
     */
    private function checkSuperAdmin()
    {
        $user = Auth::user();
        
        // Check if user is authenticated
        if (!$user) {
            abort(403, 'Trebuie să fiți autentificat');
        }
        
        // Ensure role relationship is loaded
        if (!isset($user->role)) {
            $user->load('role');
        }
        
        // Check if user has a role
        if (!$user->role) {
            abort(403, 'Utilizatorul nu are un rol asignat');
        }
        
        // Check if role is SUPER_ADMIN
        if ($user->role->name !== 'SUPER_ADMIN') {
            abort(403, 'Acces permis doar pentru super admin');
        }
    }

    /**
     * Show the fiscal receipt form
     */
    public function index()
    {
        $this->checkSuperAdmin();

        // Get all locations for super admin to select
        $locations = Location::where('is_active', true)->with('company')->orderBy('name')->get();

        return view('fiscal-receipts.index', [
            'locations' => $locations,
        ]);
    }

    /**
     * Calculate price based on hours and minutes
     */
    public function calculatePrice(Request $request)
    {
        $this->checkSuperAdmin();

        $request->validate([
            'hours' => 'required|integer|min:0|max:24',
            'minutes' => 'required|integer|min:0|max:59',
            'location_id' => 'required|exists:locations,id',
        ]);

        $location = Location::findOrFail($request->location_id);
        $hours = (int) $request->hours;
        $minutes = (int) $request->minutes;

        // Calculate total duration in hours
        $totalHours = $hours + ($minutes / 60);

        // Get hourly rate for the location (using current date)
        $hourlyRate = $this->pricingService->getHourlyRate($location, Carbon::now());

        // Round according to pricing rules
        $roundedHours = $this->pricingService->roundToHalfHour($totalHours);

        // Calculate price
        $price = round($roundedHours * $hourlyRate, 2);

        // Format duration for display
        $duration = $this->formatDuration($hours, $minutes);

        return response()->json([
            'success' => true,
            'price' => $price,
            'roundedHours' => $roundedHours,
            'hourlyRate' => $hourlyRate,
            'duration' => $duration,
        ]);
    }

    /**
     * Prepare fiscal receipt data for printing
     * Returns calculated data that will be sent to bridge from client-side
     */
    public function preparePrint(Request $request)
    {
        $this->checkSuperAdmin();

        $request->validate([
            'hours' => 'required|integer|min:0|max:24',
            'minutes' => 'required|integer|min:0|max:59',
            'location_id' => 'required|exists:locations,id',
            'paymentType' => 'required|in:CASH,CARD',
        ]);

        $location = Location::findOrFail($request->location_id);
        $hours = (int) $request->hours;
        $minutes = (int) $request->minutes;
        $paymentType = $request->paymentType;

        // Calculate total duration in hours
        $totalHours = $hours + ($minutes / 60);

        // Get hourly rate
        $hourlyRate = $this->pricingService->getHourlyRate($location, Carbon::now());

        // Round according to pricing rules
        $roundedHours = $this->pricingService->roundToHalfHour($totalHours);

        // Calculate price
        $price = round($roundedHours * $hourlyRate, 2);

        // Format rounded duration for display (fiscalized duration)
        $roundedHoursInt = floor($roundedHours);
        $roundedMinutes = round(($roundedHours - $roundedHoursInt) * 60);
        // Handle case where roundedMinutes might be 60 (from rounding)
        if ($roundedMinutes >= 60) {
            $roundedHoursInt += 1;
            $roundedMinutes = 0;
        }
        $durationFiscalized = $this->formatDuration($roundedHoursInt, $roundedMinutes);

        // Product name
        $productName = 'Ora de joacă';

        // Return data for client-side bridge call
        return response()->json([
            'success' => true,
            'data' => [
                'productName' => $productName,
                'duration' => $durationFiscalized,
                'price' => $price,
                'paymentType' => $paymentType,
            ],
            'location' => [
                'id' => $location->id,
                'name' => $location->name,
            ],
        ]);
    }

    /**
     * Prepare fiscal receipt data for printing 1 leu receipt
     * Returns calculated data that will be sent to bridge from client-side
     */
    public function preparePrintOneLeu(Request $request)
    {
        $this->checkSuperAdmin();

        $request->validate([
            'paymentType' => 'required|in:CASH,CARD',
        ]);

        $paymentType = $request->paymentType;

        // Fixed values for 1 leu receipt
        $price = 1.00;
        $productName = 'Serviciu';
        $duration = '1 leu';

        // Return data for client-side bridge call
        return response()->json([
            'success' => true,
            'data' => [
                'productName' => $productName,
                'duration' => $duration,
                'price' => $price,
                'paymentType' => $paymentType,
            ],
        ]);
    }

    /**
     * Handle print result from bridge and display message
     * This endpoint receives the result from client-side bridge call
     * and stores it in session for display
     */
    public function handlePrintResult(Request $request)
    {
        $this->checkSuperAdmin();

        $request->validate([
            'status' => 'required|in:success,error',
            'message' => 'required|string',
            'file' => 'nullable|string',
            'price' => 'nullable|numeric',
            'duration' => 'nullable|string',
            'paymentType' => 'nullable|in:CASH,CARD',
            'details' => 'nullable|string',
        ]);

        if ($request->status === 'success') {
            $message = $request->message;
            if ($request->file) {
                $message .= " Fișier: {$request->file}";
            }
            if ($request->price) {
                $message .= " | Preț: " . number_format($request->price, 2) . " RON";
            }
            if ($request->duration) {
                $message .= " | Durată: {$request->duration}";
            }
            if ($request->paymentType) {
                $paymentLabel = $request->paymentType === 'CASH' ? 'Cash' : 'Card';
                $message .= " | Plată: {$paymentLabel}";
            }
            
            return redirect()->route('fiscal-receipts.index')
                ->with('success', $message);
        } else {
            $errorMessage = $request->message;
            if ($request->details) {
                $errorMessage .= ' - ' . $request->details;
            }
            
            return redirect()->route('fiscal-receipts.index')
                ->with('error', $errorMessage);
        }
    }

    /**
     * Format duration as "Xh Ym" or "Xh" if no minutes
     */
    private function formatDuration(int $hours, int $minutes): string
    {
        if ($minutes === 0) {
            return "{$hours}h";
        }
        return "{$hours}h {$minutes}m";
    }
}

