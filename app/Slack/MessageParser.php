<?php

namespace App\Slack;


use App\Models\Credential;
use Illuminate\Support\Facades\Log;

class MessageParser
{

    protected $request;
    protected $wasDm;

    function __construct($request, $wasDm)
    {
        $this->request = $request;
        $this->wasDm = $wasDm;
    }


    public static function request(array $request)
    {
        $mustMention = !(preg_match('/^D/', $request['event']['channel']));
        return new MessageParser($request, !$mustMention);
    }

    public function parse()
    {
        $text = $this->request["event"]["text"];
        $parsed = array(
            "type" => "unknown"
        );

        $botUserId = Credential::where('team_id', $this->request['team_id'])
            ->first()
            ->bot_user_id;
        if (preg_match("/<@$botUserId>/i", $text)
            || $this->wasDm
        ) {

            $matches = [];
            if (preg_match("/conjure/i", $text)) {
                if ($email = $this->findEmail($text)) {
                    $parsed = array(
                        'type' => 'conjure-add',
                        'email' => $email
                    );
                }
            } else if (preg_match("/pivotal/i", $text)) {
                if ($email = $this->findEmail($text)) {
                    $parsed = array(
                        'type' => 'pivotal-add',
                        'email' => $email
                    );
                }
            } else if (preg_match("/username\s*:\s*([^@\s]+)/i", $text, $matches)) {
                $parsed = array(
                    'type' => 'gitlab-add',
                    'username' => $matches[1]
                );
                if (preg_match("/project\s*:\s*([^@\s]+)/i", $text, $matches)) {
                    $parsed["project"] = strtolower($matches[1]);
                } else $parsed["project"] = "getting-started";
                return $parsed;
            }
        }
        Log::info("Parsed: " . print_r($parsed, true));

        return $parsed;
    }

    protected function findEmail($text)
    {
        $v = "/mailto:(.+@.+)\|/i";
        $matches = [];
        preg_match($v, $text, $matches);
        return isset($matches[1]) ? $matches[1] : false;
    }


}