<div class="header-filter" id="header-filter" style="position:relative">
	<div class="main-center f-left-all" style="overflow: hidden;">
		<div class="keyword" style="font-size: 24px;">
            SmartJob is Smart Choice !
		</div>
	</div>
	<div class="main-center f-left-all">
		<div class="keyword">
			<input type="text" name="s" class="search-box job-searchbox input-search-box border-radius" placeholder="<?php _e('Enter a keyword', ET_DOMAIN) ?> ..." value="<?php echo get_query_var( 's' ) ?>" />
			<span class="icon" data-icon="s"></span>
		</div>
		<div class="location">
			<input type="text" name="job_location" class="search-box job-searchbox input-search-box border-radius" placeholder="<?php _e('Enter a location', ET_DOMAIN) ?> ..." value="<?php echo get_query_var( 'location' ) ?>" />
			<span class="icon" data-icon="@"></span>
		</div>
	</div>
	<div class="main-center f-left-all" style="overflow: hidden;">
		<div class="keyword" style="">
           <span style="font-size:16px"> Popular keywords: </span>
		   <span class="morepolar"><a target="_blank" href="<?php bloginfo('url');?>/?s=java" class="a_morepola" > Java</a></span>
		   <span class="morepolar"><a target="_blank" href="<?php bloginfo('url');?>/?s=php" class="a_morepola"> Php</a></span>		 
		   <span class="morepolar"><a target="_blank" href="<?php bloginfo('url');?>/?s=ios" class="a_morepola" > IOS</a></span>		 
		   <span class="morepolar"><a target="_blank" href="<?php bloginfo('url');?>/?s=android" class="a_morepola" > Android</a></span>		  
		   <span class="morepolar"><a target="_blank" href="<?php bloginfo('url');?>/?s=C#" class="a_morepola"> C#</a></span>		  
		   <span class="morepolar"><a target="_blank" href="<?php bloginfo('url');?>/?s=net" class="a_morepola"> Net</a></span>
		   <span class="morepolar"><a target="_blank" href="<?php bloginfo('url');?>/?s=oracle" class="a_morepola"> Oracle</a></span>
		</div>
	</div>
</div>