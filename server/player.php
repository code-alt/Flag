<?php 
include "header.php";

$servername = $_ENV['MYSQL_SERVER'];
$username = $_ENV["MYSQL_USERNAME"];
$password = $_ENV["MYSQL_PASSWORD"];
$dbname = $_ENV["MYSQL_DATABASE"];

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT views FROM stat WHERE `id`=?";
$stmt = $conn->prepare($sql); 
$stmt->bind_param("s", $req);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $pre_view = $row["views"];
}

$new_views = $pre_view + 1;

$sql = "UPDATE `stat` SET `views`=? WHERE id=?";
$stmt = $conn->prepare($sql); 
$stmt->bind_param("ss", $new_views, $req);
$stmt->execute();

$sql = "SELECT * FROM videos WHERE `v_id`=?";
$stmt = $conn->prepare($sql); 
$stmt->bind_param("s", $req);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    if(!isset($row["v_id"]))
    {
        header("HTTP/1.1 404 Not Found");
        die("<h1>404: Video not found. Was it removed?</h1>");
    }
    $title = $row["v_title"];
    $url = $row["v_url"];
    $thumb = $row["v_thumb"];
    $desc = $row["v_desc"];
    $user = $row["v_uploader"];
    $pretime = $row["v_time"];
}


$ago = time() - $pretime;

$mins = round($ago/60);
$hours = round($ago/3600);
$days = round($ago/86400);
$months = round($ago/2629800);
$years = round($ago/31557600);

if($mins > 60)
{
  if($hours > 24)
  {
    if($days > 7)
    {
      if($months > 12)
      {
        $final = $years . " years ago";
      }else{
        $final = $months . " months ago";
      }
    }else{
      $final = $days . " days ago";
    }
  }else{
    $final = $hours . " hours ago";
  }
}else{
  $final = $mins . " minutes ago";
}

$sql = "SELECT * FROM users WHERE `username`=?";
$stmt = $conn->prepare($sql); 
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $pfp = $row["pfp"];
}

?>
<script>
<?php
$video = htmlspecialchars($req);
echo "const video = '${video}';"
?>
</script>
<script src="/frontend/vote.js"></script>
<script src="/frontend/comment.js"></script>
<link href="https://unpkg.com/video.js/dist/video-js.min.css" rel="stylesheet">
<script src="https://unpkg.com/video.js/dist/video.min.js"></script>
<br>
<video
    id="watch"
    class="video-js vjs-fluid"
    controls
    preload="auto"
    <?php echo "poster='" . $thumb . "'"; ?>
    data-setup='{}'
    width="720"
    height="480"
    >
  <source <?php echo "src='" . $url . "'"; ?> type="video/mp4"></source>
  <p class="vjs-no-js">
    To view this video please enable JavaScript, and consider upgrading to a
    web browser that
    <a href="https://videojs.com/html5-video-support/" target="_blank">
      supports HTML5 video
    </a>
  </p>
</video>
<?php echo "<br><h2>" . $title . "</h2>"; ?> <button class="btn btn-primary" type="button" style="margin-left:10px"><i class="fas fa-thumbs-up" id="like"></i><p id="like_count">?</p></button><button class="btn btn-primary" type="button" style="margin-left:10px"><i class="fas fa-thumbs-down" id="dislike"></i><p id="dislike_count">?</p></button>
<?php echo "<p>" . $new_views . " views - Uploaded ${final}</p>"; ?>
<hr>
<div class="desc" width="60%">
    <?php echo "<h4>${desc}</h4><br>"; ?>
</div>
<?php echo "<a href='/user/${user}'><img src='${pfp}' class='img-fluid rounded-circle' alt='rounded circle image' width='75px' height='75px'><h2>${user}</h2></a>"; ?>
<br>
<h5>Comments</h5>
<hr>
<p>Leave a comment!</p>
<form method="post" class="w-400 mw-full">
<div class="form-group">
    <textarea class="form-control" id="comment" name="comment" placeholder="What a great video!"></textarea>
  </div>
  <input class="btn btn-primary" type="submit" value="Comment">
</form>
<script src="/frontend/404.js"></script>
<?php
$limit = $_GET["show"];
if(! $limit)
{
  $limit = 10;
}
$sql = "SELECT * FROM comments WHERE `id`=? ORDER BY epoch DESC LIMIT ?";
$stmt = $conn->prepare($sql); 
$stmt->bind_param("si", $req, $limit);
$stmt->execute();
$result = $stmt->get_result();
$c = 0;
while ($row = $result->fetch_assoc()) {
    $c++;
    echo "<br><h5><a href='/user/" . $row["username"] . "'>" . $row["username"] . "</a> -" . $row["comment"] . "</h5><hr>";
}
$next = $limit + 5;
if($c == $limit || $c > $limit)
{
  echo "<br><a href='/watch/${req}/?show=${next}'><button class='btn btn-success' type='button'>Load Next 5</button></a>";
}
?>

<script>

if (!$(window).width() < 1270) {
   $("#video").removeAttr("class");
}
// thanks to aboutDa
</script>
