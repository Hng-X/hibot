<?php

if (!function_exists("bot_action")) {
    function bot_action(\Hibot\Bot\Actions\Action $action)
    {
        $result = $action->run();
        if (method_exists($action, "respond")) {
            $action->respond($result);
        }
    }
}

if (!function_exists("bot_config")) {
    function bot_config($key = null, $default = null)
    {
        $result = config($key, $default);
        if (is_array($result)) {
            foreach ($result as &$item) {
                if (is_string($item)) {
                    $item = str_replace("@{user}", '<@$user>', $item);
                }
            }
        } else {
            return str_replace("@{user}", '<@$user>', $result);
        }
    }
}