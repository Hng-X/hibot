<?php

namespace Hibot\Jobs;

use Hibot\Bot\Actions\Gitlab;
use Hibot\Bot\Actions\PivotalTracker;
use Hibot\Custom\Conjure;
use Hibot\Slack\MessageParser;
use Hibot\Slack\SlackMessage;
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

        if ($this->request['event']['type'] == "team_join" && config("bot.welcome.on")) {
            $message = SlackMessage::sendWelcomeMessage($userId, $this->request['team_id']);
        } else {
            $parsedText = MessageParser::request($this->request)->parse();

            if ($parsedText["type"] == "gitlab-add") {
                bot_action(new Gitlab($parsedText, $this->request));
            } else if ($parsedText["type"] == "pivotal-add") {
                bot_action(new PivotalTracker($parsedText, $this->request));
            } else if ($parsedText["type"] == "conjure-add") {
                $result = Conjure::addToConjure($parsedText["email"]);
                Conjure::sendAddResult($result, $this->request);

            }
        }
    }
}
