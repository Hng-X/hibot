<?php

return array(
    /*
    *
    * Whether the bot should post as a normal user
    * This gives it the ability to send DMs
    * You can override this for a speciic message in the SlackMessage constructor
    */
    "as_user" => "true",


    "welcome" => array(
        /*
        * Whether the bot should send a welcome message when a new user joins the team
        * The welcome message is always sent via DM
        */
        "on" => "true",

        /*
        * Welcome messages (randomly chosen) which the bot sends
        * To mention the username, write @{user}
        */
        "messages" => array(
            "Hey there, @{user}! Welcome to the Hotels.ng remote internship Slack team. I'm hibot, your friendly neighbourhood bot.\nHere's everything you need to know to get up and running :point_down:\nhttps://sites.google.com/hotels.ng/internship/home\nGreat to have you here. We'e gonna have lots of ~fun~ coding/design together!",
            "Hi, @{user}! Welcome to the Hotels.ng remote internship Slack team.\nGot any questions? Go here first :point_right: https://sites.google.com/hotels.ng/internship/home\nThe name's hibot. Peace!",
            "Welcome to the Hotels.ng remote internship Slack team, @{user}!\nCheck out :point_right: https://sites.google.com/hotels.ng/internship/home for how to get started.\nMy name's hibot. Pleased to meet you. :wave:"
        )
    ),

    /*
    * Rules the bot should follows
    * This is the bots main power
    */
    "rules" => array(
        /*
         * Each rule consists of two parts:
         * "parse" => patterns to be matched (and assigned names)
         * "run" => script ro run
         */
        array(
            /*
             * An array of pattern-matching rules
             * Valid keys are "containsAll", "containsAny", "canContain", and "containsType"
             * All of these except "containsType" receive regex values
             * "containsType" can only be one of the following:
             * "{email}",
             *
             * To extract a parenthesized regex pattern into a variable,
             * put the name of the variable (in order) in square bracket
            */
            "parse" => array(
                "containsAll[username]" => array(
                    "/gitlab/i",
                    "/username\s*:\s*([^@\s]+)/i"
                ),
                "canContain[project]" => array(
                    "/project\s*:\s*([^@\s]+)/i",
                ),
            ),

            /*
             * PHP class name located in the namespace \App\Custom
             * The static method run() is called,
             * with the original request body (as an array)
             * and then the extracted values above passedin as params
             * where they can be accessed as $valueName
             */
            "run" => "Gitlab",
        ),

        array(
            "parse" => array(
                "containsAll" => array(
                    "/pivotal/i"
                ),
                "containsType[email]" => array(
                    "{email}",
                ),
                "canContain[project]" => array(
                    "/project\s*:\s*([^@\s]+)/"
                )
            ),
            "run" => "PivotalTracker",
        ),
    ),
);