# GeoRanker API PHP Connector

### Usage:
```php
<?PHP 

include("GeoRankerAPI.php");

$grapi = new GeoRankerAPI("email", "apikey");
$loginobj = $grapi->login();
if (empty($loginobj) || isset($loginobj->msg)) {
	echo json_encode($loginobj, JSON_PRETTY_PRINT);
	exit;
}

$reportid = 1117597; 

$reportobj = $grapi->reportget($reportid);

if (empty($reportobj) || isset($reportobj->msg)) {
	echo json_encode($reportobj, JSON_PRETTY_PRINT);
	exit;
}

if (empty($reportobj->is_pending)) {
	// Handle the report object...
	echo json_encode($reportobj, JSON_PRETTY_PRINT);
} else {
	// The report is still pending.
	echo json_encode($reportobj, JSON_PRETTY_PRINT); // Just show a JSON string 
}
```