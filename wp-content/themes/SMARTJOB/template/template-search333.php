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
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.css" rel="stylesheet" />
<link href="http://select2.github.io/select2-bootstrap-theme/css/select2-bootstrap.css" rel="stylesheet" />
<link href="https://yastatic.net/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" />
<script src="https://yastatic.net/jquery/2.2.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.full.js"></script>
<style>
.select2-results__option[aria-selected=true] {
    display: none;
}
</style>

<select multiple class="form-control input-sm" placeholder="Select language..." data-placeholder="Select language"  name="job_location">

</select>

<script type='text/javascript'>
var data = [
  {
    id: 'ha noi',
    text: 'Hà nội'
  },
  {
    id: 'Hồ Chí Minh',
    text: 'Hồ Chí Minh'
  }
];
$('select').select2({
  data: data,
  theme: 'bootstrap',


});
</script>

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