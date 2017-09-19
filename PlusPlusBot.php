<?php
/*
 * Made by @kaneki666 on Telegram!
 */

// Include the framework
require './vendor/autoload.php';
require 'database.php';

// Create a bot
$bot = new PhpBotFramework\Bot("BOT_TOKEN");

//Create pdo object
$bot->database->pdo = new PDO('mysql:host=localhost;dbname=DATABASE_NAME;charset=utf8mb4', 'DATABASE_USER', 'DATABASE_PASSWORD');

// Create a Database object
$db = new Database($bot->getPdo());


// start command.
$bot->addMessageCommand('start', function($bot, $message) {
    $user_id = $message['from']['id'];
    $username = $message['from']['username'];
    global $db;
    if ($username) {
        $db->addUser($user_id, $username);
        $bot->sendMessage("PlusPlus++  allows you to plus, minus and keep score of all the good and not so good things your friends say and do on Telegram.
You can add a point by typing @user++, deduct a point by typing @user-- and check the leaderboard by typing /leaderboard.");
    } else {
        $bot->sendMessage("You need an username to use this bot. Please set one from Telegram options");
    }
});


$bot->answerUpdate['message'] = function ($bot, $message) {
    global $db;
    $user_id = $message['from']['id'];
    $chat_id = $message['chat']['id'];
    if ($message['from']['username']) {
        foreach ($message['entities'] as $entity) {
            if ($entity['type'] == 'mention') {
                if (substr($chat_id, 0, 1) == "-") {
                    if ($db->getUser(substr($message['text'], $entity['offset'], $entity['length'])) == true) {
                        if (substr($message['text'], $entity['offset'] + $entity['length'], 2) == "++") {
                            //this add a point
                            $db->addPointToUser($chat_id, substr($message['text'], $entity['offset'], $entity['length']));
                            $bot->sendMessage("You added a point to " . substr($message['text'], $entity['offset'], $entity['length']));

                        } elseif (substr($message['text'], $entity['offset'] + $entity['length'], 2) == "--") {
                            //this deduct a point
                            $db->deductPointToUser($chat_id, substr($message['text'], $entity['offset'], $entity['length']));
                            $bot->sendMessage("You deduct a point to " . substr($message['text'], $entity['offset'], $entity['length']));

                        } else {
                            //when there is no ++ or -- after the mention
                            $bot->sendMessage("Invalid action for " . substr($message['text'], $entity['offset'], $entity['length']));

                        }
                    } else {
                        //if the username you want to vote isn't in the database
                        $bot->sendMessage("Username not found, please ask " . substr($message['text'], $entity['offset'], $entity['length']) . " to start the bot");
                    }
                } else {
                    $bot->sendMessage("You can't use this bot in a private chat, please add it to a group");
                }
            }
        }

        //add user to database
        $db->addUser($user_id, $message['from']['username']);
    } else if (isset($message['entities']) and !isset($message['from']['username'])){
        //if the user doesn't have an username and try to vote
        $bot->sendMessage("Please set an username to use this bot");
    }
};

//Help message
$bot->addMessageCommand('help', function ($bot, $message){

    $bot->sendMessage("PlusPlus++  allows you to plus, minus and keep score of all the good and not so good things your friends say and do on Telegram.
You can add a point by typing @user++, deduct a point by typing @user-- and check the leaderboard by typing /leaderboard.");

});

$bot->addMessageCommand('leaderboard', function ($bot, $message){
    global $db;
    $leaderboard = $db->getLeaderboard($message['chat']['id']);
    $bot->sendMessage($leaderboard);
});



// Receive updates from Telegram using getUpdates
$bot->getUpdatesLocal();
