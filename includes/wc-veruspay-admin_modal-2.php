<?php 
// No Direct Access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>
<p>
    <span style="font-weight:bold">Store Address: </span><?php echo $key; ?>
</p>
<p>
    <span style="font-weight:bold">Cashout Address: </span><?php echo $item['cashout_address']; ?>
</p>
<p>
    <span style="font-weight:bold">Amount: </span><?php echo $item['amount']; ?>
</p>
<p>
    <span style="font-weight:bold">Opid: </span><?php echo $item['opid']; ?>
</p>
<p style="border: solid 1px #000;"></p>';