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

function formatDateForForm($date)
{
	$time = substr($date, 11, 5);
	$date = substr($date, 0, 10);
	return ['date' => $date, 'time' => $time];
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

function formatArrayKeysInCamelCase(array $array, string $separator)
{
	$formattedArray = [];

	foreach($array as $key => $element)
	{
		$key_array = explode($separator, $key);

		$newKey_array = array_map('ucfirst', $key_array);
		$newKey_array[0] = $key_array[0];

		$newKey = implode('', $newKey_array);

		$formattedArray[$newKey] = $element;
	}

	return $formattedArray;
}