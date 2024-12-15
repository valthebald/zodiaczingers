<?php

namespace Drupal\tg_bot_sdk\Controller;

use Symfony\Component\HttpFoundation\Request;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\Message;

class UpdatesController {

  public function getUpdates(Request $request) {
    $bot = new Api();
    $bot->sendMessage(new Message());
  }

}
