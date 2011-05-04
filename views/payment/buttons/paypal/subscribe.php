<form action="<?=$url?>" method="post">
<?foreach ($params AS $name=>$value):?>
	<input type="hidden" name="<?=$name?>" value="<?=$value?>">
<?endforeach?>
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_subscribeCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>