<?php
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
     $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
     $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
     $ip = $_SERVER['REMOTE_ADDR'];
}
$user_id = get_current_user_id();
$locdata=get_user_meta($user_id,'login_location_data',true);
$lln_ajax_nonce = wp_create_nonce("my-lln-secure-string" )
?>
<div class="wrap">
    <h1>Login Location Notifier</h1>
<div class="card pressthis">
    <form name="lln-form" class="llnfrm" id="lln-form" id="bulk-form" action="" method="post" onsubmit="javascript:event.preventDefault();return  submit_ajax();">
        <fieldset>
    <label>Your Current Ip:</label>
    <input type="text" name="current_ip" value="<?php if(!empty($locdata['ip'])){echo $locdata['ip'];}else{ echo $ip; } ?>" id="current_ip">
    </fieldset>
    <input class="lln-button button button-primary" type="submit" name="lln-submit" id="llnbuton" value="Save" />
</form>
</div>
</div>
<script type="text/javascript">
function submit_ajax(){
var valip=jQuery('#current_ip').val();
if(valip != '' && checkip(valip)){
jQuery.ajax({
    type: "POST",
    url: ajaxurl,
    data: { action: 'save_ip' ,security: '<?php echo $lln_ajax_nonce; ?>', ip: jQuery('#current_ip').val()}
  }).done(function( msg ) {
         alert( "Your current IP Saved Successfully");
 });
 }
 return false;
 }
jQuery(document).ready(function(){
        jQuery.validator.addMethod('IP4Checker', function(value) {
            var split = value.split('.');
            if (split.length != 4) 
                return false;

            for (var i=0; i<split.length; i++) {
                var s = split[i];
                if (s.length==0 || isNaN(s) || s<0 || s>255)
                    return false;
            }
            return true;
        }, ' Invalid IP Address');
       jQuery("#lln-form").validate({
			rules: {
               
				current_ip: {
                        required: true,
                        IP4Checker: true
                        }
				
			},
			messages: {
				firstname: "Please enter your current ip addrress",
				
			}
	});
    })
</script>