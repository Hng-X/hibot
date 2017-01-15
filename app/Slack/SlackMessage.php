<?php

namespace App\Slack;


class SlackMessage
{
    protected $channel;

    protected $text;

    protected $team;

    public function __construct($teamId, $channelId, $text)
    {
        $this->team = $teamId;
        $this->channel = $channelId;
        $this->text = $text;
    }

    public function send()
    {
        return SlackHandler::dispatch($this);
    }

    public function toArray()
    {
        return array(
            "team_id" => $this->team,
            "channel_id" => $this->channel,
            "text" => $this->text
        );
    }

    public static function sendWelcomeMessage($user, $team)
    {
        $welcomeMessages = array(
            "Hey there, <@$user>! Welcome to the Hotels.ng remote internship Slack team. I'm hibot, your friendly neighbourhood bot.\nHere's everything you need to know to get up and running :point_down:\nhttps://sites.google.com/hotels.ng/internship/home\nGreat to have you here. We'e gonna have lots of ~fun~ coding/design together!",
            "Hi, <@$user>! Welcome to the Hotels.ng remote internship Slack team.\nGot any questions? Go here first :point_right: https://sites.google.com/hotels.ng/internship/home\nThe name's hibot. Peace!",
            "Welcome to the Hotels.ng remote internship Slack team, <@$user>!\nCheck out :point_right: https://sites.google.com/hotels.ng/internship/home for how to get started.\nMy name's hibot. Pleased to meet you. :wave:"
        );
        $message = new SlackMessage($team, $user, $welcomeMessages[array_rand($welcomeMessages)]);
        return $message->send();
    }
}