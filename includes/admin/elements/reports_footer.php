<?php

/**
 * Outputs the reports header
 *
 * 
 */
?>
	</div> <!--End main area-->	
	<div class="col s12 l4"> <!--Start revenue stream-->
		<h3> Revenue Stream </h3>
		<ul class="list-group-inner">
			<li v-for="stream in revenueStreams" class="list-group-inner-item">
				<div>
					<span class="white-text stream-price" :class="stream.priceClass" v-html="stream.price"></span>
					{{stream.status}}
					<a href="#" class="hubaga-unstyled" @click.prevent="showStream(stream.id)" v-show = "currentStream !== stream.id">{{stream.readMore}}</a>
					<span class="hubaga-price-muted">{{stream.date}}</span>
					<div class="hubaga-price-more z-depth-2" v-show="currentStream === stream.id"  v-hubaga-click-outside="hideStream">
						<div class="elementa-row" v-html="stream.moreData"></div>
					</div>
				</div>
			</li>
		</ul>
	</div><!--End revenue stream-->
</div> <!--End full report-->

<div class="hubaga-overlay-wrapper" v-if="loading"><div class="hubaga-loader"></div></div>