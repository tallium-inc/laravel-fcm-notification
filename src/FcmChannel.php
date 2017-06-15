<?php

namespace Benwilkins\FCM;

use GuzzleHttp\Client;
use Illuminate\Notifications\Notification;

/**
 * Class FcmChannel
 * @package Benwilkins\FCM
 */
class FcmChannel
{
    /**
     * @const The API URL for Firebase
     */
    const API_URI = 'https://fcm.googleapis.com/fcm/send';

    /**
     * @var Client
     */
    private $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param mixed $notifiable
     * @param Notification $notification
     */
    public function send($notifiable, Notification $notification)
    {
      $messages = $notification->toFCM($notifiable);

      foreach ($messages as $format => $message) {

          if (is_null($message->getTo())) {
              $to = $notifiable->routeNotificationFor('fcm' . ucfirst($format));

              if (count($to)) {
                  $message->toGroup($to);
              }
              else {
                  return;
              }
          }

          $this->client->post(self::API_URI, [
              'headers' => [
                  'Authorization' => 'key=' . $this->getApiKey(),
                  'Content-Type'  => 'application/json',
              ],
              'body'    => $message->formatData(),
          ]);
      }
    }

    /**
     * @return string
     */
    private function getApiKey()
    {
        return config('laravel-fcm-notification.api_key');
    }
}
