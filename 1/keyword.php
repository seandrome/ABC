<?php 

	include('inc/config.php');
	include('inc/functions.php');
	is_logged();

	if(isset($_POST['keyword']) && !empty($_POST['keyword'])){
		$keyword = explode("\n", $_POST["keyword"]);
		
		foreach($keyword as $line) {
			$kw = $line;
			$kw = clean_keyword($kw);
			$kw = rtrim($kw);	
			$kw = mysql_escape_string($kw);
			
			$duplicate = mysql_query("SELECT * FROM keyword where keyword='$kw'");
			$duplicate = mysql_num_rows($duplicate);
			if($duplicate < 1){
				mysql_query("INSERT INTO keyword (id,keyword,count) VALUES ('','$kw','0')");
			}
		}
	}
	
	if(isset($_GET['empty'])){
		mysql_query("DELETE FROM keyword");
	}
	
	
	if(isset($_GET['delete']) && !empty($_GET['delete'])){
		$id = $_GET['delete'];
		$id = mysql_escape_string($id);
		mysql_query("DELETE FROM keyword WHERE id='$id'");
	}
	
	if(isset($_GET['gtrends'])){
		google_trends();
	}

	if(isset($_GET['twitrends'])){
		twitter_trends();
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
	    <form action="keyword.php" method="POST" class="mainForm">
			<!-- Input text fields -->
            <fieldset>
                <div class="widget first">
                    <div class="head"><h5 class="iList">Add Keyword</h5></div>
                        <div class="rowElem"><label>One Keyword Per Line</label><div class="formRight"><textarea rows="8" cols="" name="keyword"></textarea></div><div class="fix"></div></div>
                        <input type="submit" value="Add Keyword" class="greyishBtn submitForm" />
                        <div class="fix"></div>

                </div>
            </fieldset>
        </<form>            
        <a href="keyword.php?gtrends" class="btnIconLeft mr10 mt5"><img src="images/icons/dark/adminUser.png" alt="" class="icon" /><span>Add Google Trends</span></a>
        <a href="keyword.php?twitrends" class="btnIconLeft mr10 mt5"><img src="images/icons/dark/adminUser.png" alt="" class="icon" /><span>Add Twiiter Trends</span></a>
        <a href="keyword.php?empty" title="" class="btnIconLeft mr10 mt5"><img src="images/icons/dark/adminUser.png" alt="" class="icon" /><span>Remove All Keyword</span></a>

		<!-- Notification messages -->
        <div class="nNote nInformation">
            <p><strong>INFORMATION: </strong>You have about <? echo sql_totalkeyword()?> keywords in database. More keyword more unique posting !</p>
        </div>   
        
		<!-- Static table -->
        <div class="widget first">
        	<div class="head"><h5 class="iFrames">Keyword List</h5></div>
            <table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
            	<thead>
                	<tr>
                        <td width="5%">NO.</td>
                        <td width="20%">YOUR KEYWORD</td>
                        <td width="20%">DELETE</td>
                    </tr>
                </thead>
                <tbody>
					<? 	
					$query = mysql_query('SELECT * FROM keyword order by count DESC');
					$i = 1;
					while( $row = mysql_fetch_object($query)){
                	   echo "<tr>";
                       echo "<td><center>" .$i. "</center></td>";
                       echo "<td><center>" .$row->keyword. "</center></td>";
                       echo "<td><center>" . anchor('keyword.php?delete=' .$row->id , 'DELETE') . "</center></td>";
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