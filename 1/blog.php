<?php 
	include('inc/config.php');
	include('inc/functions.php');
	is_logged();
	
	if(isset($_POST['blog_id']) && !empty($_POST['blog_id'])){
		$blog_id = explode("\n", $_POST["blog_id"]);
		
		foreach($blog_id as $line) {
			$blogid = $line;
			$blogid = rtrim($blogid);
			$blogid = mysql_escape_string($blogid);	
			
			$duplicate = mysql_query("SELECT * FROM blog_id where blog_id='$blogid'");
			$duplicate = mysql_num_rows($duplicate);
			if($duplicate < 1){
				mysql_query("INSERT INTO blog_id (id,blog_id,count) VALUES ('','$blogid','0')");
			}
		}
	}
	
	if(isset($_GET["empty"])){
		mysql_query("DELETE FROM blog_id");
	}
	
	if(isset($_GET["reset"])){
		mysql_query("UPDATE blog_id set count='0'");
	}

	if(isset($_GET["delete"])){
		$id = $_GET["delete"];
		mysql_query("DELETE FROM blog_id where id='$id'");
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
	   
		<!-- Form begins -->
	    <form action="blog.php" method="POST" class="mainForm">
			<!-- Input text fields -->
            <fieldset>
                <div class="widget first">
                    <div class="head"><h5 class="iList">Add Blog ID</h5></div>
                        <div class="rowElem"><label>One Blog ID Per Line</label><div class="formRight"><textarea rows="8" cols="" name="blog_id"></textarea></div><div class="fix"></div></div>
                        <input type="submit" value="Add Blog ID" class="greyishBtn submitForm" />
                        <div class="fix"></div>

                </div>
            </fieldset>
        </<form> 
        <a href="bloglist.php" title="" class="btnIconLeft mr10 mt5"><img src="images/icons/dark/adminUser.png" alt="" class="icon" /><span>Scrape Blog ID</span></a>
        <a href="bloglist_auto.php" title="" class="btnIconLeft mr10 mt5"><img src="images/icons/dark/adminUser.png" alt="" class="icon" /><span>Auto Scrape Blog ID</span></a>
        <a href="blog.php?reset" title="" class="btnIconLeft mr10 mt5"><img src="images/icons/dark/adminUser.png" alt="" class="icon" /><span>Reset Post</span></a>
        <a href="blog.php?empty" title="" class="btnIconLeft mr10 mt5"><img src="images/icons/dark/adminUser.png" alt="" class="icon" /><span>Remove All Blog ID</span></a>
		
		
		<!-- Notification messages -->
        <div class="nNote nInformation">
            <p><strong>INFORMATION: </strong>You have about <? echo sql_totalblogID()?> Blogspot ID with <? echo sql_totapost() ?> post.</p>
        </div>   

		<!-- Static table -->
        <div class="widget first">
        	<div class="head"><h5 class="iFrames">Blog Management</h5></div>
            <table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
            	<thead>
                	<tr>
                        <td width="5%">NO.</td>
                        <td width="20%">BLOG ID</td>
                        <td width="20%">TOTAL POST</td>
                        <td width="20%">DELETE</td>
                    </tr>
                </thead>
                <tbody>
					<? 	
					$query = mysql_query('SELECT * FROM blog_id order by id DESC');
					$i = 1;
					while( $row = mysql_fetch_object($query)){
                	   echo "<tr>";
                       echo "<td><center>" .$i. "</center></td>";
                       echo "<td><center>" .$row->blog_id. "</center></td>";
                       echo "<td><center>" .$row->count. "</center></td>";
                       echo "<td><center>" . anchor('blog.php?delete=' .$row->id , 'DELETE') . "</center></td>";
                	   echo "</tr>";
					$i++;
					}
					?>
                </tbody>
            </table>
        </div>

    </div><!-- End Content -->
    <div class="fix"></div>
</div>
<?php include('inc_footer.php')?>