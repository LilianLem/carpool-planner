<?php
function checkDateFormat($date)
{
	if (strtotime($date) === false)
	{
		return false;
	}

	$date_array = explode('-', $date);
	if(count($date_array) != 3)
	{
		return false;
	}

	list($year, $month, $day) = explode('-', $date);
	return checkdate($month, $day, $year);
}

function checkTime($time)
{
	$time_array = explode(':', $time);
	if(count($time_array) != 2)
	{
		return false;
	}

	if(!ctype_digit($time_array[0]) OR !ctype_digit($time_array[1]))
	{
		return false;
	}

	if($time_array[0] < 0 OR $time_array[0] > 23 OR $time_array[1] < 0 OR $time_array[1] > 59)
	{
		return false;
	}
	else
	{
		return true;
	}
}

function formatDateTimeForDb($date,$time)
{
	$date_array = explode('-', $date);
	$day_alpha = ucfirst(strftime("%A",strtotime($date)));
	return $date.' '.$time.':00';
}

function getPlatformFormat()
{
	if(strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
	{
		return ['day' => '%#d', 'hour' => '%#H'];
	}
	else
	{
		return ['day' => '%e', 'hour' => '%k'];
	}
}