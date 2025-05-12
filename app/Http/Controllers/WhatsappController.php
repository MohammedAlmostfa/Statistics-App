<?php

namespace App\Http\Controllers;

use App\Models\ActivitiesLog;
use App\Services\WhatsappService;
use App\Http\Resources\WhatsappResource;
use App\Http\Requests\WhatsAppRequest\FiltterData;
use App\Http\Requests\WhatsAppRequest\FiltterWhatsAppData;

class WhatsappController extends Controller
{
    protected $WhatsappService;

    /**
     * Constructor to inject the WhatsappService.
     *
     * @param WhatsappService $WhatsappService
     */
    public function __construct(WhatsappService $WhatsappService)
    {
        // Initialize the WhatsappService
        $this->WhatsappService = $WhatsappService;
    }

    /**
     * Method to fetch the messages based on the validated filter data.
     *
     * @param FiltterData $request The validated request data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(FiltterWhatsAppData $request)
    {
        $this->authorize('GetWhatssapMessage');

        // Validate and retrieve the data from the request
        $validatedData = $request->validated();

        // Call the WhatsappService to fetch messages
        $result = $this->WhatsappService->getMessage($validatedData);

        // Return response based on the result
        return $result['status'] === 200
        ? response()->json([
            'status'     => 'success',
            'message'    => $result['message'],
            'data'       => new WhatsappResource($result['data']),
            'pagination' => $result['pagination'],
        ], $result['status'])
        : response()->json([
            'status'  => 'error',
            'message' => $result['message'],
        ], $result['status']);
    }
}
