<?php 
	include('inc/config.php');
	include('inc/functions.php');
	is_logged();
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
		
		<!-- Headings -->        
        <div class="widget first">
            <div class="head"><h5 class="iCreate">Manual Guide</h5></div>
            <div class="body">
                <h1 class="pt10">Step 1 : Input Your Blogspot Username & Password</h1>
				<p>
				Masukan username dan password anda <a href="account.php" target="_blank"/>di sini.</a>
				</p>

				<h1 class="pt10">Step 2 : Scrape Blog ID</h1>
				<p>
				Jika password dan username anda benar, maka script akan menggenerate blog ID anda <a href="bloglist.php" target="_blank"/>di sini.</a>
				</p>
				
				<h1 class="pt10">Step 3 : Masukan Blog ID</h1>
				<p>
				Masukan Blog ID <a href="blog.php" target="_blank"/>di sini.</a>
				</p>

				<h1 class="pt10">Step 4 : Masukan Keyword & Source Content</h1>
				<p>
				Masukan keyword list anda disini  <a href="keyword.php" target="_blank"/>di sini.</a> <br />
				Default Content berasal dari bing image dan bing text <br />
				Bisa pilih amazon, youtube or redtube la yaowww...
				</p>
				
				<h1 class="pt10">Step 5 : Run Cron Job</h1>
				<p>
				Pasang Cron Job di cpanel anda dengan format<br />
				http://namadomain.com/post_bing.php<br />
				atau http://namadomain.com/post_amazon.php<br />
				Maka Software akan berjalan otomatis tanpa campur tangan KOTOR ANDA<br />
				Masukan Blog ID sebanyak banyaknya biar tambah kaya *monyet. 1 juta blog aja, biar postingnya nggak berhenti sampe turunan ke 7.
				</p>
				
				<h1 class="pt10">Step 6 : Call 108</h1>
				<p>
				Hubungi tukang pijet terdekat daripada anda nganggur sendirian di rumah.
				</p>



            </div>
			
			
        </div>
        <div class="widget first">
            <div class="head"><h5 class="iCreate">1.1 Release Notes</h5></div>
            <div class="body">
               <ul>
               		<li>[Deprecated] Azure API</li>
               		<li>[New] Using Bing Image XML ( Multiple use )</li>
               		<li>[New] License removed</li>
               </ul>
            </div>
        </div>

		
		
        
    </div><!-- End Content -->
    <div class="fix"></div>
</div>
<?php include('inc_footer.php')?>