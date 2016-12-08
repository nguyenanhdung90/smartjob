<form method="get" id="searchform" action="<?php bloginfo('url'); ?>/">
<div class="header-filter" id="header-filter" style="position:relative">
	<div class="main-center f-left-all" style="overflow: hidden;">
		<div class="keyword" style="font-size: 24px;">
            SmartJob is Smart Choice !
		</div>
	</div>
		
	<div class="main-center f-left-all">
	<form method="get" id="searchform" action="<?php bloginfo('url'); ?>/">

				<div class="keyword">
					<input type="text" name="s" class="search-box job-searchbox input-search-box border-radius" placeholder="<?php _e('Enter a keyword', ET_DOMAIN) ?> ..." value="<?php echo get_query_var( 's' ) ?>" />
					<span class="icon" data-icon="s"></span>
				</div>
				<div class="form-control input-sm location" style="margin-right: 0px;">
					<select name="job_location2"  class="search_province" >
						<option <?php if(isset($_GET["job_location2"])&& $_GET["job_location2"]==2146) echo 'selected';?>  value="2146">All</option>
						<option <?php if(isset($_GET["job_location2"])&& $_GET["job_location2"]==2147) echo 'selected';?>  value="2147">Hà Nội</option>
						<option <?php if(isset($_GET["job_location2"])&& $_GET["job_location2"]==2148) echo 'selected';?>  value="2148">Hồ Chí Minh</option>
						<option <?php if(isset($_GET["job_location2"])&& $_GET["job_location2"]==2149) echo 'selected';?>  value="2149">Đà Nẵng</option>
						<option <?php if(isset($_GET["job_location2"])&& $_GET["job_location2"]==2150) echo 'selected';?>  value="2150">Other</option>
					</select>
					<input type="submit" id="search_submit" value="Search" />
                </div>
				
    </form>
	
	</div>

	<div class="main-center f-left-all" style="overflow: hidden;">
		<div class="keyword" style="">
           <span style="font-size:16px"> Popular keywords: </span>
		   <span class="morepolar"><a target="_blank" href="<?php bloginfo('url');?>/tuyen-dung-viec-lam-java" class="a_morepola" > Java</a></span>
		   <span class="morepolar"><a target="_blank" href="<?php bloginfo('url');?>/tuyen-dung-viec-lam-php" class="a_morepola"> Php</a></span>		 
		   <span class="morepolar"><a target="_blank" href="<?php bloginfo('url');?>/tuyen-dung-viec-lam-ios" class="a_morepola" > IOS</a></span>		 
		   <span class="morepolar"><a target="_blank" href="<?php bloginfo('url');?>/tuyen-dung-viec-lam-android" class="a_morepola" > Android</a></span>		  
		   <span class="morepolar"><a target="_blank" href="<?php bloginfo('url');?>/tuyen-dung-viec-lam-c-sharp" class="a_morepola"> C#</a></span>		  
		   <span class="morepolar"><a target="_blank" href="<?php bloginfo('url');?>/tuyen-dung-viec-lam-net" class="a_morepola"> Net</a></span>
		   <span class="morepolar"><a target="_blank" href="<?php bloginfo('url');?>/tuyen-dung-viec-lam-oracle" class="a_morepola"> Oracle</a></span>
		   <span class="morepolar"><a target="_blank" href="<?php bloginfo('url');?>/tuyen-dung-viec-lam-seo" class="a_morepola"> SEO</a></span>
		   <span class="morepolar"><a target="_blank" href="<?php bloginfo('url');?>/tuyen-dung-viec-lam-mkt-online" class="a_morepola"> MKT Online</a></span>
		</div>
	</div>
</div>
</form>