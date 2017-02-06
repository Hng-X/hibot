<?php

return array(
    /*
    *
    * Whether the bot should post as a normal user
    * This gives it the ability to send DMs
    * You can override this for a speciic message in the SlackMessage constructor
    */
    "as_user" => "true",


    /*
    * Actions the bot does, based on type of message
    * This is the bots main power
    */
    "actions" => array(
        "team-join" => \Hibot\Bot\Actions\Onboard::class,
        "gitlab-add" => \Hibot\Bot\Actions\Gitlab::class,
        "pivotal-add" => \Hibot\Bot\Actions\PivotalTracker::class,

    ),


    /*
    * Messages (randomly chosen) which the bot sends
    * To mention the username, write @{user}
    */
    "messages" => array(
        "team-join" => array(
            "Hey there, @{user}! Welcome to the Hotels.ng remote internship Slack team. I'm hibot, your friendly neighbourhood bot.\nHere's everything you need to know to get up and running :point_down:\nhttps://sites.google.com/hotels.ng/internship/home\nGreat to have you here. We'e gonna have lots of ~fun~ coding/design together!",
            "Hi, @{user}! Welcome to the Hotels.ng remote internship Slack team.\nGot any questions? Go here first :point_right: https://sites.google.com/hotels.ng/internship/home\nThe name's hibot. Peace!",
            "Welcome to the Hotels.ng remote internship Slack team, @{user}!\nCheck out :point_right: https://sites.google.com/hotels.ng/internship/home for how to get started.\nMy name's hibot. Pleased to meet you. :wave:"
        )
    ),
);