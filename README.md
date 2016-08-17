# GeoRanker API PHP Connector

### Usage:
```php
<?PHP 

include("GeoRankerAPI.php");

$grapi = new GeoRankerAPI("email@example.com", "yourapikey");
$loginobj = $grapi->login();
if (empty($loginobj) || isset($loginobj->debug)) {
	die("Error: " . isset($loginobj->msg) ? $loginobj->msg : 'Error on login object.');
}

$reportid = 12345; 

$reportobj = $grapi->reportget($reportid);

if (empty($reportobj) || isset($reportobj->debug)) {
	die("Error: " . isset($reportobj->msg) ? $reportobj->msg : 'Error on report object.');
}

if (empty($reportobj->is_pending)) {
	// Handle the report object...
	var_dump($reportobj);
} else {
	// The report is still pending.
	var_dump($reportobj);
}
```