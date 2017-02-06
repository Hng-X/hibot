<?php

namespace Hibot\Bot\Actions;

use Hibot\Bot\Slack\SlackMessage;

class Onboard extends Action
{

    public function __construct($data, array $request)
    {
        parent::__construct($data, $request);
    }

    public function run()
    {
        $team = $this->request['team_id'];
        $user = $this->request['event']['user']["id"];

        $welcomeMessages = bot_config("bot.messages.team-join");
        $text = $welcomeMessages[array_rand($welcomeMessages)];
        $message = new SlackMessage($team, $user, $text);
        return $message->send();
    }

    /**
     * Uncomment this method if you want to return a response
     * based on the result of the action
     * The value returned from run() is automatically passed
     * as the parameter $result is automatically
     */
    /* add a single slash at the start of this line to uncomment the function
    public function respond($result) {

        $team = $this->data['team_id'];
        $user = $this->data['event']['user'];
        $channel = $this->data['event']['channel'];

        $text = "Done! <@$user>";
        $message = new SlackMessage($team, $channel, $text);
        return $message->send();
    }
    //*/
}