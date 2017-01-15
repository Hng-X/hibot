<?php

namespace App\Jobs;

use App\HNG\Custom;
use App\Models\Credential;
use App\Slack\SlackMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class HandleSlackEvent implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $request;
    protected $slack;

    /**
     * Create a new job instance.
     *
     * @param array $request
     */
    public function __construct(array $request)
    {
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $userId = $this->request['event']['user'];
        Log::info("Job::: " . print_r($this->request, true));

        if (isset($this->request['event']['subtype'])
            && $this->request['event']['subtype']== "channel_join") {
            $message = SlackMessage::sendWelcomeMessage($userId, $this->request['team_id']);
        } else {
            $rawText = $this->request['event']['text'];
            $parsedText = $this->parseText($rawText);
            if (isset($parsedText["type"])) {
                if ($parsedText["type"] == "gitlab-add") {
                    $result = Custom::addToGitlab($parsedText["username"], $parsedText["project"]);
                    Custom::sendGitlabAddResult($result, $parsedText["project"], $this->request);

                } else if ($parsedText["type"] == "gitlab-problem-access") {
                    $text = "Hey, <@$userId>. looks like you're having trouble with gitlab. Have you been added to the project? If you aren't sure, please post a message here, telling me your username and the project. Hope this helps.";
                    $message = new SlackMessage($this->request["team_id"], $userId, $text);
                    $message->send();
                }
            }
        }
    }

    public function parseText($text)
    {
        $botUserId = Credential::where('team_id', $this->request['team_id'])->get()->first()->bot_user_id;
        if (preg_match("/<@$botUserId>/i", $text)) {
            $matches = [];
            if (preg_match("/username\s*:\s*(\w+)/i", $text, $matches)) {
                $parsed = array(
                    'type' => 'gitlab-add',
                    'username' => $matches[1]
                );
                if (preg_match("/project\s*:\s*(\w+-?\w+)/i", $text, $matches)) {
                    $parsed["project"] = strtolower($matches[1]);
                } else $parsed["project"] = "getting-started";
                Log::info("Parsed: " . print_r($parsed, true));
                return $parsed;
            }
        }
        if (preg_match("/(\b404\b)/i", $text)
            || (stripos($text, "gitlab") !== false
                && stripos($text, "error") !== false
                && stripos($text, "access"))
        ) {
            $parsed = array(
                'type' => 'gitlab-problem-access'
            );
            return $parsed;
        }
        return [];
    }


}