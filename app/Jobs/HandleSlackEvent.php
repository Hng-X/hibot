<?php

namespace App\Jobs;

use App\Custom\Conjure;
use App\Custom\Gitlab;
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
        $userId = isset($this->request['event']['user']) ? $this->request['event']['user'] : $this->request['event']['user']['id'];
        Log::info("Job::: " . print_r($this->request, true));

        if ($this->request['event']['type']== "team_join") {
            $message = SlackMessage::sendWelcomeMessage($userId, $this->request['team_id']);
        } else {
            $rawText = $this->request['event']['text'];
            $mustMention = !(preg_match('/^D/', $this->request['event']['channel']));
            $parsedText = $this->parseText($rawText, $mustMention);
            if (isset($parsedText["type"])) {
                if ($parsedText["type"] == "gitlab-add") {
                    $result = Gitlab::addToGitlab($parsedText["username"], $parsedText["project"]);
                    Gitlab::sendGitlabAddResult($result, $parsedText["project"], $this->request);

                } else if ($parsedText["type"] == "gitlab-problem-access") {
                    $text = "Hey, <@$userId>. looks like you're having trouble with gitlab. Have you been added to the project? If you aren't sure, please post a message here, telling me your username and the project. Hope this helps.";
                    $message = new SlackMessage($this->request["team_id"], $userId, $text);
                    $message->send();
                } else if ($parsedText["type"] == "conjure-add") {
                    $result = Conjure::addToConjure($parsedText["email"]);
                    Conjure::sendAddResult($result, $this->request);

                }
            }
        }
    }

    public function parseText($text, $mustMention=true)
    {
        $botUserId = Credential::where('team_id', $this->request['team_id'])->get()->first()->bot_user_id;
        if (($mustMention && preg_match("/<@$botUserId>/i", $text))
        || !$mustMention) {
            $matches = [];
            if(preg_match("/conjure/i", $text)) {
                //sorry for the long regex, folks
                if(preg_match("/
(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))/iD", $text, $matches)) {

                    $parsed = array(
                        'type' => 'conjure-add',
                        'email' => $matches[0]
                    );
                }
                return $parsed;
            }
            else if (preg_match("/username\s*:\s*([^@\s]+)/i", $text, $matches)) {
                $parsed = array(
                    'type' => 'gitlab-add',
                    'username' => $matches[1]
                );
                if (preg_match("/project\s*:\s*([^@\s]+)/i", $text, $matches)) {
                    $parsed["project"] = strtolower($matches[1]);
                } else $parsed["project"] = "getting-started";
                Log::info("Parsed: " . print_r($parsed, true));
                return $parsed;
            }
        }
        if (preg_match("/\b404\b/i", $text)
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