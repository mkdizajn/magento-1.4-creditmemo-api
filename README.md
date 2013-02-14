# Magento 1.4 credit memo api:

Custom module for magento credit memos

- - -

## What you get

- cm_api.list method for listing credit memos

- cm_api.item method for info about credit memo

## How to use it

```php
<?php 
$client = new Zend_XmlRpc_Client($cp);
$session = $client->call('login', array($u, $p));

$allcms = $client->call('call', array($session, 'cm_api.list' )); // ,array(array('status' => 'complete'))
foreach ($allcm as $_cm) {
	$cm = $client->call('call', array($session, 'cm_api.info',  $_cm['increment_id'] ));
	// do some stuff
}
?>
```

that's it, hth

----
cheers, kreso
----