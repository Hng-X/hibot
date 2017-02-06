<?php

namespace Hibot\Jobs;

use Hibot\Bot\Slack\MessageParser;
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
        Log::info("Job::: " . print_r($this->request, true));

        //parse
        $parsedText = MessageParser::request($this->request)->parse();

        //if the type is listed in config, dispatch the appropriate action
        $classname = bot_config("bot.actions." . $parsedText["type"], "");
        if ($classname) {
            bot_action(new $classname($parsedText, $this->request));
        } else {
            //or else, do something
        }
    }
}

