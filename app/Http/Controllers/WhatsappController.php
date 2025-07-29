<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Http\Requests\WhatsAppRequest\FilterWhatsAppData;

use App\Http\Requests\WhatsAppRequest\FiltterData;


use App\Http\Resources\WhatsappResource;
use App\Notifications\SendWhatsAppNotification;
use App\Services\WhatsappService;
use Illuminate\Http\Request;


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
   public function index(FilterWhatsAppData $request)
    {
        $this->authorize('GetWhatssapMessage');

        $validatedData = $request->validated();

        $result = $this->WhatsappService->getMessage($validatedData);

        return $result['status'] === 200
            ? $this->paginated($result['data'], WhatsappResource::class, $result['message'], $result['status'])
            : self::error(null, $result['message'], $result['status']);
    }


    // public function sendMessage(Request $request)
    // {
    //     $request->validate([
    //         'number' => 'required|string',
    //         'message' => 'required|string',
    //     ]);

    //     try {
    //         // أنشئ كائن مؤقت مع trait Notifiable
    //         $notifiable = new class {
    //             use Notifiable;

    //             public $phone;

    //             public function routeNotificationFor($channel)
    //             {
    //                 return $this->phone;
    //             }
    //         };

    //         $notifiable->phone = $request->number;

    //         // الآن تقدر تستخدم notify
    //         $notifiable->notify(new SendWhatsAppNotification($request->message));

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'تم إرسال الرسالة عبر واتساب'
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'حدث خطأ أثناء إرسال الرسالة عبر واتساب',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }
}
