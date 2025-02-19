<?php

// Membuat Kelas
class Telebot 
{ 

// Menambahkan Private dan Public Properties

    private $update;
    private $tasks = [];

    public string $token;
    public string $apiURL;


    //Menambahkan Method "__construct"
    public function __construct(string $token)
    {
        $this->token = $token;
        $this->apiURL = "https://api.telegram.org/bot$token/";
    }


    //Menambahkan Method "createContext"
    private function createContext($update)
    {
        return new class($this->apiURL, $update)
        {
            public
                $apiURL,
                $update,
                $updateId,
                $message,
                $messageId,
                $from,
                $chat,
                $chatId,
                $date,
                $text;

            public function __construct($apiURL, $update)
            {
                $this->apiURL = $apiURL;
                $this->update = $update;
                $this->updateId = $update->update_id;
                if ($update->message != null) {
                    $this->message = $update->message;
                    $this->messageId = $update->message->message_id;
                    $this->from = $update->message->from;
                    $this->chat = $update->message->chat;
                    $this->chatId = $update->message->chat->id;
                    $this->date = $update->message->date;
                    $this->text = $update->message->text;
                }
            }

            public function replyWithText(string $text, array $options = [])
            {
                $data["chat_id"] = $this->chatId;
                $data["text"] = $text;

                if (array_key_exists("reply_to_message_id", $options)) {
                    $data["reply_to_message_id"] = $options["reply_to_message_id"];
                }

                if (array_key_exists("parse_mode", $options)) {
                    if (in_array($options["parse_mode"], ["Markdown", "MarkdownV2", "HTML"])) {
                        $data["parse_mode"] = $options["parse_mode"];
                    }
                }
                $queries = http_build_query($data);
                file_get_contents($this->apiURL . "/sendMessage?$queries");
            }
        };
    }


    //Menambahkan Method "command"
    public function command(string $command, callable $callback)
    {
        $task = [
            "args" => [$command, $callback],
            "do" =>  function (string $command, callable $callback) {
                if ($this->update == null) return;

                $ctx = $this->createContext($this->update);

                if ($ctx->message != null) {
                    if ($ctx->text !== null && strpos($ctx->text, "/$command") === 0) {
                        $callback($ctx);
                    }
                }
            }
        ];
        array_push($this->tasks, $task);
    }


    //Menambahkan Method "run"
    public function run() {
        {
            $update = json_decode(file_get_contents("https://api.telegram.org/bot{$this->token}/getUpdates"), true);
            if ($update && isset($update["result"])) {
                foreach ($update["result"] as $result) {
                    $this->update = $result;
                    foreach ($this->tasks as $task) {
                        $task["do"](...$task["args"]);
                    }
                }
            }
            sleep(1); // Hindari spam request ke server Telegram
        }
    }
}