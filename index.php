<?php

require_once("./Telebot.php");

// Masukkan token bot sebagai string
$bot = new Telebot("8138496370:AAFxwCWctQnMn9QyaM9VT4HSuCjae5Z5Tnc");

// Handle start command
$bot->command("start", function ($ctx) {
    $ctx->replyWithText("Kamu mengirimkan command /start");
});

// Handle hello command
$bot->command("hello", function ($ctx) {
    $ctx->replyWithText("Halo kak " . $ctx->from["first_name"]);
});

// Jalankan bot
$bot->run();
