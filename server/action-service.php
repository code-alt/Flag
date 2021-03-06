<?php
header("Content-type: application/json");

$servername = $_ENV['MYSQL_SERVER'];
$username = $_ENV["MYSQL_USERNAME"];
$password = $_ENV["MYSQL_PASSWORD"];
$dbname = $_ENV["MYSQL_DATABASE"];

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/*===================================

You will need to send:

$_POST


====================================*/


if(isset($_POST["video"]))
{
    if($_POST["action"] == "Like")
    {
        if(isset($_SESSION["username"]))
        {
            $sql = "SELECT likes FROM stat WHERE `id`=?";
            $stmt = $conn->prepare($sql); 
            $stmt->bind_param("s", $_POST["video"]);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $og_likes = $row["likes"];
            }

            $new_likes = $og_likes + 1;

            $sql = "SELECT `action` FROM actions WHERE `username`=? AND id=?";
            $stmt = $conn->prepare($sql); 
            $stmt->bind_param("ss", $_SESSION["username"], $_POST["video"]);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                if($row["action"] == "Like")
                {
                    die(json_encode(array("success" => "false", "message" => "You cannot like a video twice.", "likes" => $og_likes), true));
                }
            }

            $sql = "UPDATE `stat` SET `likes`=? WHERE id=?";
            $stmt = $conn->prepare($sql); 
            $stmt->bind_param("ss", $new_likes, $_POST["video"]);
            $stmt->execute();

            $sql = "INSERT INTO actions (username, `action`, id, epoch) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $action = htmlspecialchars($_POST["action"]);
            $video = htmlspecialchars($_POST["video"]);
            $epoch = time();
            $authed = $_SESSION["username"];
            $stmt->bind_param("sssi", $authed, $action, $video, $epoch);
            $stmt->execute();
            $stmt->close();

            die(json_encode(array("success" => "true", "likes" => $new_likes), true));

        }else{
            header("HTTP/1.1 400 Bad Request");
            die(json_encode(array("success" => "false", "message" => "Bad Request - Not signed in"), true));
        }
    }else{
        $sql = "SELECT dislikes FROM stat WHERE `id`=?";
        $stmt = $conn->prepare($sql); 
        $stmt->bind_param("s", $_POST["video"]);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $og_dislikes = $row["dislikes"];
        }

        $new_dislikes = $og_dislikes + 1;

        $sql = "SELECT `action` FROM actions WHERE `username`=? AND id=?";
        $stmt = $conn->prepare($sql); 
        $stmt->bind_param("ss", $_SESSION["username"], $_POST["video"]);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            if($row["action"] == "Dislike")
            {
                die(json_encode(array("success" => "false", "message" => "You cannot dislike a video twice.", "likes" => $og_likes), true));
            }
        }

        $sql = "UPDATE `stat` SET `dislikes`=? WHERE id=?";
        $stmt = $conn->prepare($sql); 
        $stmt->bind_param("ss", $new_dislikes, $_POST["video"]);
        $stmt->execute();

        $stmt = $conn->prepare("INSERT INTO actions (username, `action`, id, epoch) VALUES (?, ?, ?, ?)");
        $action = htmlspecialchars($_POST["action"]);
        $video = htmlspecialchars($_POST["video"]);
        $epoch = time();
        $authed = $_SESSION["username"];
        $stmt->bind_param("sssi", $authed, $action, $video, $epoch);
        $stmt->execute();
        $stmt->close();

        die(json_encode(array("success" => "true", "dislikes" => $new_dislikes), true));
    }
}
else
{
    header("HTTP/1.1 400 Bad Request");
    die(json_encode(array("success" => "false", "message" => "Bad Request - Did not POST video"), true));
}