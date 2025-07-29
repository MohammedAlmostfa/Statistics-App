<?php
namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use App\Models\NotificationLog;

/**
 * Custom channel to send WhatsApp notifications via an external Node.js server
 */
class WhatsAppChannel
{
    /**
     * Send the notification via WhatsApp channel
     *
     * @param  mixed  $notifiable  The entity to be notified (e.g., user or customer)
     * @param  Notification  $notification  The notification instance containing WhatsApp message data
     * @return array|null  JSON response array from the Node.js server or null on failure
     */
    public function send($notifiable, Notification $notification)
    {
        // Get WhatsApp message from notification
        $message = $notification->toWhatsApp($notifiable);
        
        // URL of the Node.js server that handles sending WhatsApp messages
        $url = "http://localhost:3000/send";

        try {
            // Send POST request to server with phone number and message
            $response = Http::post($url, [
                'number' => $message['phone'],  // Phone number only (without @c.us)
                'message' => $message['body'],  // Message text
            ]);

            // Decode JSON response from server into array
            $responseData = $response->json();

            // Extract 'success' boolean from response or default to false
            $success = $responseData['success'] ?? false;

            // Extract status message text or default if missing
            $statusText = $responseData['status'] ?? 'No response message';

            // Log the notification sending result to the database
            NotificationLog::create([
                'customer_id' => $notifiable->id ?? null,  // Link to customer if available
                'message' => $message['body'],             // The message sent
                'status' => $success,                       // Boolean success/failure
                'response' => $statusText,                  // Status text message from server
            ]);

            // Return full response if successful, else return null
            if ($success) {
                return $responseData;
            } else {
                return null;
            }
        } catch (\Exception $e) {
            // Log failure if an exception occurs during the HTTP request
            NotificationLog::create([
                'customer_id' => $notifiable->id ?? null,
                'message' => $message['body'],
                'status' => false,
                'response' => 'Sending failed',
            ]);

            return null;
        }
    }
}
