<?php 
	include('inc/config.php');
	include('inc/functions.php');
	is_logged();
	
	if(isset($_POST['edit'])){
		$blogspot_username = mysql_escape_string($_POST['blogspot_username']);
		$blogspot_password = mysql_escape_string($_POST['blogspot_password']);
		$amazon_key = mysql_escape_string($_POST['amazon_key']);
		$amazon_secret = mysql_escape_string($_POST['amazon_secret']);
		$amazon_tag = mysql_escape_string($_POST['amazon_tag']);
		
		mysql_query("UPDATE options set value='$blogspot_username' where name='blogspot_username'");
		mysql_query("UPDATE options set value='$blogspot_password' where name='blogspot_password'");
		mysql_query("UPDATE options set value='$amazon_key' where name='amazon_key'");
		mysql_query("UPDATE options set value='$amazon_secret' where name='amazon_secret'");
		mysql_query("UPDATE options set value='$amazon_tag' where name='amazon_tag'");
		
		// set allert
		$updated = TRUE;
	}
	
	include('inc_header.php');
	include('inc_top.php');
	include('inc_menu.php');
?>
<!-- Content wrapper -->
<div class="wrapper">
<?php include('inc_sidebar.php')?>	
	
    <!-- Content -->
    <div class="content">
    	<div class="title"><h5>Auto Blogspot Dashboard</h5></div>
		
		<? if(isset($updated)){?>
        <div class="pt20">
            <div class="nNote nSuccess hideit">
                <p><strong>SUCCESS: </strong>Your Account is updated</p>
            </div>  
        </div>
		<?}?>

		
		
		 <!-- Form begins -->
        <form action="account.php" class="mainForm" method="POST">
	    	<!-- Input text fields -->
		
	        <fieldset>
	            <div class="widget first">
	                <div class="head"><h5 class="iList">Blogspot Account</h5></div>
	                    <div class="rowElem noborder"><label>Blogspot Username</label><div class="formRight"><input type="text" name="blogspot_username" value="<? echo get_options('blogspot_username')?>"/></div><div class="fix"></div></div>
	                    <div class="rowElem noborder"><label>Blogspot Password</label><div class="formRight"><input type="text" name="blogspot_password" value="<? echo get_options('blogspot_password')?>"/></div><div class="fix"></div></div>
	                    <div class="fix"></div>
	            </div>
	        </fieldset>
	
			
	        <fieldset>
	            <div class="widget first">
	                <div class="head"><h5 class="iList">Amazon Account</h5></div>
	                    <div class="rowElem noborder"><label>Amazon Key</label><div class="formRight"><input type="text" name="amazon_key" value="<? echo get_options('amazon_key')?>"/></div><div class="fix"></div></div>
	                    <div class="rowElem noborder"><label>Amazon Secret Key</label><div class="formRight"><input type="text" name="amazon_secret" value="<? echo get_options('amazon_secret')?>"/></div><div class="fix"></div></div>
	                    <div class="rowElem noborder"><label>Amazon ID</label><div class="formRight"><input type="text" name="amazon_tag" value="<? echo get_options('amazon_tag')?>"/></div><div class="fix"></div></div>
	                    <input type="hidden" name="edit"/>
						<input type="submit" value="Update Account" class="greyishBtn submitForm" />
	                    <div class="fix"></div>
	            </div>
	        </fieldset>
		</form>
       
		
        
    </div><!-- End Content -->
    <div class="fix"></div>
</div>
<?php include('inc_footer.php')?>