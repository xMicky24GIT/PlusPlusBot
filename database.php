<?php
/**
 * User: michele
 * Date: 03/09/17
 * Time: 22.09
 *
 * Database main class
 *
 */

class Database
{

    private $connection;

    /**
     * Database constructor.
     * @param $connection String
     */
    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param $username String
     * @return true or false
     */
    public function getUser($username)
    {
        $prepare = $this->connection->prepare("SELECT * FROM Users WHERE username = ?");
        $prepare->execute([$username]);
        $fetch = $prepare->fetch();
        if ($fetch){
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $user_id INT
     * @param $username String
     */
    public function addUser($user_id, $username)
    {
        $prepare = $this->connection->prepare("SELECT * FROM Users WHERE user_id = ?");
        $prepare->execute([$user_id]);
        $fetch = $prepare->fetch();
        if (!$fetch){
            $prepare = $this->connection->prepare("INSERT INTO Users(user_id, username) VALUES (?, ?)");
            $prepare->execute([$user_id, '@'.$username]);
        }
    }

    /**
     * @param $chat_id INT
     * @param $username String
     */
    public function addPointToUser($chat_id, $username)
    {
        $prepare = $this->connection->prepare("SELECT * FROM Users_vote WHERE chat_id = ? AND username = ?");
        $prepare->execute([$chat_id, $username]);
        $fetch = $prepare->fetch();
        if ($fetch) {
            $points = $fetch['votes'];
            $points += 1;
            $prepare = $this->connection->prepare("UPDATE Users_vote SET votes = " . $points . " WHERE chat_id = ? AND username = ?");
            $prepare->execute([$chat_id, $username]);
        } else {
            $prepare = $this->connection->prepare("INSERT INTO Users_vote(chat_id, username, votes) VALUES (?, ?, 1)");
            $prepare->execute([$chat_id, $username]);
        }
    }

    /**
     * @param $chat_id INT
     * @param $username String
     */
    public function deductPointToUser($chat_id, $username)
    {
        $prepare = $this->connection->prepare("SELECT * FROM Users_vote WHERE chat_id = ? AND username = ?");
        $prepare->execute([$chat_id, $username]);
        $fetch = $prepare->fetch();
        if ($fetch) {
            $points = $fetch['votes'];
            $points -= 1;
            $prepare = $this->connection->prepare("UPDATE Users_vote SET votes = " . $points . " WHERE chat_id = ? AND username = ?");
            $prepare->execute([$chat_id, $username]);
        } else {
            $prepare = $this->connection->prepare("INSERT INTO Users_vote(chat_id, username, votes) VALUES (?, ?, -1)");
            $prepare->execute([$chat_id, $username]);
        }
    }

    /**
     * Get a chat's leaderboard
     * @param $chat_id INT
     * @return String
     */
    public function getLeaderboard($chat_id)
    {
        $prepare = $this->connection->prepare("SELECT * FROM Users_vote WHERE chat_id = ? ORDER BY votes DESC");
        $prepare->execute([$chat_id]);
        $fetchAll = $prepare->fetchAll();
        $text = "Chat leaderboard \n";
        $i = 1;
        foreach ($fetchAll as $fetch) {
            $text .= $i." - ".$fetch['username']." with ".$fetch['votes']." points \n";
            $i++;
        }
        return $text;
    }

}