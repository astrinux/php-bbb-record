<?php

$presentation_url_base = "https://cloud.lmsnovinparsian.com/playback/presentation/2.0/playback.html?meetingId=";
$presentation_dir_base = "/var/bigbluebutton/published/presentation/";

$presentation_dirs = array_filter(glob($presentation_dir_base . '*'), 'is_dir'); // Find directories in the base dir
array_multisort(array_map('filemtime', $presentation_dirs), SORT_NUMERIC, SORT_DESC, $presentation_dirs); // Sort by file mtime

date_default_timezone_set('Asia/Tehran'); // Time zone

foreach ($presentation_dirs as $dir)
{
	$meeting_id = substr($dir, strlen($presentation_dir_base)); // Extract meeting ID from directory's name 

	$xml = simplexml_load_file($presentation_dir_base . $meeting_id . "/metadata.xml");

	$meeting_name = $xml -> meta -> meetingName;
	if (isset($_GET["meeting_name"]))
	{
		if ($meeting_name != $_GET["meeting_name"])
		{
			continue;
		}
	}

	$bbb_origin = $xml -> meta -> {'bbb-origin'};
	$bbb_origin_server = $xml -> meta -> {'bbb-origin-server-name'};

	$meeting_start_time = intval(substr($xml -> start_time, 0, 10)); // Extract 10 first digits (unix timestamp)
	$meeting_date = date("Y-m-d H:i:s", $meeting_start_time);

	$playback_size = (int)($xml -> playback -> size / 1000000); // Size in MB

	$playback_duration = (int)($xml -> playback -> duration / 1000); // Divide by 1000 to get duration in sec

	$playback_hours = (int)($playback_duration / 3600); 
	$playback_hours = sprintf("%02d", $playback_hours);
	$playback_duration -= ($playback_hours * 3600);

	$playback_minutes = (int)($playback_duration / 60);
	$playback_minutes = sprintf("%02d", $playback_minutes);
	$playback_duration -= ($playback_minutes * 60);

	$playback_seconds = sprintf("%02d", $playback_duration);
  
	echo("<p><b>$meeting_name</b><a target=\"_blank\" href=\"$presentation_url_base$meeting_id\">playback</a> <a target=\"_blank\" href=\"?meeting_name=$meeting_name\">all meetings</a><br>ID: $meeting_id<br>Date: $meeting_date<br>Duration: $playback_hours:$playback_minutes:$playback_seconds<br>size: $playback_size MB<br>From: $bbb_origin_server $bbb_origin</p>\r\n");
}

?>
